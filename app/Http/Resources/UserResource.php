<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    // Define properties
    public $status;
    public $message;
    public $access_token;
    public $errors;

    public function __construct($status, $message, $resource = null, $access_token = null, $errors = null) {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
        $this->access_token = $access_token;
        $this->errors = $errors;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $user = $this->resource ? [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone_number' => $this->phone_number,
            'gender' => $this->gender,
            'birthdate' => $this->birthdate,
            'address' => $this->address,
            'description' => $this->description,
            'photo' => $this->photo,
        ] : null;

        $response = [
            'meta' => [
                'code' => 200,
                'status' => $this->status,
                'message' => $this->message,
            ],
            'data' => $user ? ['user' => $user] : null,
        ];

        if ($this->access_token) {
            $response['data']['access_token'] = $this->access_token;
        }

        if ($this->errors) {
            $response['errors'] = $this->errors;
        }

        return $response;
    }
}
