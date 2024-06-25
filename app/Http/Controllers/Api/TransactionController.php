<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TransactionResource;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $transactions = Transaction::with('user')->paginate(10);

                break;
            case 'owner':
                $transactions = Transaction::select('transactions.*')
                    ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                    ->where('building_id', $user->building->id)
                    ->with('user')
                    ->latest()
                    ->distinct()
                    ->paginate(10);

                break;
            default:
                $transactions = Transaction::select('transactions.*')
                    ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                    ->where('transactions.user_id', $user->id)
                    ->with('user')
                    ->latest()
                    ->distinct()
                    ->paginate(10);

                break;
        }

        return TransactionResource::collection($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
