<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'user_id' =>$this->when($request->user()->role !== 'user', $this->user_id),
            'username' =>$this->when($request->user()->role !== 'user', $this->username),
            'gross_amount' => $this->gross_amount,
            'transaction_status' => $this->transaction_status,
            'token' => $this->when($request->user()->role === 'user', $this->token),
            'redirect_url' => $this->when($request->user()->role === 'user', $this->redirect_url),
            'details' => TransactionDetailResource::collection($this->transactionDetails)
        ];
    }
}
