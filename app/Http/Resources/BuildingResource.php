<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'start_price' => $this->when($request->routeIs('building.index'), $this->getLowestRoomPrice()),
            // 'start_price' => $this->when($request->routeIs('building.index'), $this->room->min('price')),
            'type' => $this->when(!$request->routeIs('building.index'), $this->buildingType->name),
            'city' => ucwords(strtolower($this->when(!$request->routeIs('building.index'), $this->city->name))),
            'description' => $this->when(!$request->routeIs('building.index'), $this->description),
            'images' => $this->when($request->routeIs('building.show'), BuildingImageResource::collection($this->buildingImages)),
            'rooms' => $this->when($request->routeIs('building.show'), RoomResource::collection($this->rooms))
        ];
    }
}
