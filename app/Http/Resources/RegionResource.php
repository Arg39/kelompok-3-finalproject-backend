<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    public $status;
    public $code;
    public $message;
    public $errors;

    public function __construct($status, $code, $message, $resource = null, $errors = null) {
        parent::__construct($resource);
        $this->status = $status;
        $this->code = $code;
        $this->message = $message;
        $this->errors = $errors;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        if ($this->resource instanceof \Illuminate\Support\Collection) {
            // Case: Data provinsi
            return [
                'meta' => [
                    'status' => $this->status,
                    'code' => $this->code,
                    'message' => $this->message,
                ],
                'data' => $this->resource->map(function ($province) {
                    return [
                        'id' => $province->id,
                        'name' => $province->name,
                    ];
                }),
                'errors' => $this->errors,
            ];
        } else {
            // Case: Data kabupaten (regencies dari sebuah provinsi)
            return [
                'meta' => [
                    'status' => $this->status,
                    'code' => $this->code,
                    'message' => $this->message,
                ],
                'data' => [
                    'id' => $this->id,
                    'name' => $this->name,
                    'regencies' => $this->when(isset($this->regencies), function () {
                        return $this->regencies->map(function ($regency) {
                            return [
                                'id' => $regency->id,
                                'name' => $regency->name,
                            ];
                        });
                    }),
                ],
                'errors' => $this->errors,
            ];
        }
    }
}
