<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
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
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $data = is_null($this->resource) ? null : $this->resource;

        if ($data instanceof \Illuminate\Support\Collection) {
            return [
                'meta' => [
                    'status' => $this->status,
                    'code' => $this->code,
                    'message' => $this->message,
                ],
                'data' => $data->isEmpty() ? null : $data->map(function ($building) {
                    return $this->transformBuilding($building);
                }),
                'errors' => $this->errors,
            ];
        } elseif ($data instanceof \App\Models\Building) {
            return [
                'meta' => [
                    'status' => $this->status,
                    'code' => $this->code,
                    'message' => $this->message,
                ],
                'data' => $this->transformBuilding($data),
                'errors' => $this->errors,
            ];
        } elseif (is_null($data)) { 
            return [
                'meta' => [
                    'status' => $this->status,
                    'code' => $this->code,
                    'message' => $this->message,
                ],
                'data' => null, 
                'errors' => $this->errors,
            ];
        } else {
            return [];
        }
    }

    /**
     * Transform a single building model into an array.
     *
     * @param  \App\Models\Building  $building
     * @return array<string, mixed>
     */
    private function transformBuilding($building)
    {
        return [
            'id' => $building->id,
            'name' => $building->name,
            'type' => $building->type,
            'address' => $building->address,
            'description' => $building->description,
            'regency' => [
                'id' => $building->regency->id,
                'name' => $building->regency->name,
            ],
        ];
    }
}
