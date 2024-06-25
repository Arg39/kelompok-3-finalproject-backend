<?php

namespace App\Http\Controllers\Api;

use App\Models\Rent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Set your Merchant Server Key
        Config::$serverKey = config('midtrans.server_key');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = config('midtrans.is_production');
        // Set sanitization on (default)
        Config::$isSanitized = config('midtrans.is_sanitized');
        // Set 3DS transaction for credit card to true
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function payment()
    {
        $user = Auth::user();

        if ($user->role != 'user') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $rent = Rent::where('user_id',Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $rentDetail = $rent->rentDetails()->with('room')->get();

        DB::transaction(function () use ($user, $rent, $rentDetail, &$payload, &$snapToken, &$url) {
            $item_details = [];

            foreach ($rentDetail as $item) {
                $item_details[] = [
                    'name' => $item->room->name,
                    'price' => $item->room->price,
                    'quantity' => $item->duration,
                ];
            };

            $orderId = 'ORDER-' . time() . rand(1000, 9999);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'username' => $user->name,
                'order_id' => $orderId,
                'gross_amount' => $rent->total,
                'transaction_status' => 'pending',
                'fraud_status' => 'accept',
            ]);

            foreach ($rentDetail as $item) {
                $transaction->transactionDetails()->create([
                    'building_id' => $item->room->building_id,
                    'room_id' => $item->room_id,
                    'room_name' => $item->room->name,
                    'price' => $item->room->price,
                    'start_date' => $item->start_date,
                    'end_date' => $item->end_date,
                    'duration' => $item->duration,
                    'sub_total' => $item->sub_total
                ]);
            };

            $payload = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $rent->total
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                'item_details' => $item_details
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($payload);
            $url = \Midtrans\Snap::createTransaction($payload)->redirect_url;

            $rent->rentDetails()->delete();
            $rent->delete();

            $transaction->update([
                'transaction_status' => 'settlement',
                'token' => $snapToken,
                'redirect_url' => $url,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'data' => $payload,
            'snap_token' => $snapToken,
            'redirect_url' => $url
        ]);
    }

    public function cancel(Transaction $transaction)
    {
        $user = Auth::user();

        if ($user->id != $transaction->user_id && $transaction->transaction_status != 'pending') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://api.sandbox.midtrans.com/v2/transactions/' . $transaction->order_id, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode(config('services.midtrans.server_key') . ':')
            ]
        ]);

        $body = json_decode($response->getBody()->getContents());

        if ($body->status_code == '404') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found'
            ], 404);
        }

        $transaction->update([
            'transaction_status' => 'cancel'
        ]);
    }

    public function callback(Transaction $transaction)
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://api.sandbox.midtrans.com/v2/transactions/' . $transaction->order_id . '/status', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode(config('services.midtrans.server_key') . ':')
            ]
        ]);

        $body = json_decode($response->getBody()->getContents());

        return response()->json($body);

        if ($body->status_code == '404') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found'
            ], 404);
        }

        $transaction->update([
            'transaction_status' => 'settlement'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction updated'
        ]);
    }
}
