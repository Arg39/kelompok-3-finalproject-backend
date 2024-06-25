<?php

namespace App\Http\Controllers\Api;

use App\Models\City;
use App\Models\Building;
use App\Models\Province;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\BuildingResource;
use Illuminate\Support\Facades\Validator;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $buildings = Building::whereHas('city', function($query) use ($request) {
            $query->where('name', 'like', '%'.$request->search.'%');
        })->orWhereHas('city.province', function($query) use ($request) {
            $query->where('name', 'like', '%'.$request->search.'%');
        })->orWhere('name', 'like', '%'.$request->search.'%')->paginate(10);

        if ($buildings->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No building found'
            ], 404);
        }

        return BuildingResource::collection($buildings);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->user()->cannot('create', Building::class)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not allowed to create a building'
            ], 403);
        }

        if ($request->user()->building()->count() >= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have reached the maximum number of buildings'
            ], 403);
        }

        $building = $request->user()->building()->create([
            'name' => $request->name,
            'address' => $request->address,
            'city_id' => $request->city_id,
            'building_type_id' => $request->building_type_id,
            'description' => $request->description
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->images as $image) {
                $path = Storage::disk('public')->put('building', $image);
                $building->buildingImages()->create(['image' => $path]);
            }
        }

        return new BuildingResource($building);
    }

    /**
     * Display the specified resource.
     */
    public function show(Building $building)
    {
        return new BuildingResource($building);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $building = $request->user()->building()->first();

        if (Gate::denies('update', $building)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not allowed to update this building'
            ], 403);
        }

        return new BuildingResource($building);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Building $building)
    {
        if ($request->user()->cannot('update', $building)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not allowed to update this building'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'address' => 'string',
            'city_id' => 'exists:cities,id',
            'building_type_id' => 'exists:building_types,id',
            'description' => 'string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $building->update($validator->validate());

        $building->slug = Str::slug($building->name);

        $building->update();

        if ($request->hasFile('images')) {
            // Delete old images from storage
            foreach ($building->buildingImages as $image) {
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }
            }
            // Store new images and create new image records
            foreach ($request->images as $image) {
                $path = Storage::disk('public')->put('building', $image);
                $building->buildingImages()->create(['image' => $path]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => new BuildingResource($building)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Building $building)
    {
        if (Gate::denies('delete', $building)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not allowed to delete this building'
            ], 403);
        }

        $building->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Building deleted successfully'
        ]);
    }

    public function getBuildingByCity(City $city)
    {
        $buildings = $city->buildings()->paginate(10);

        if ($buildings->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No building found in this city'
            ], 404);
        }

        return BuildingResource::collection($buildings);
    }

    public function getBuildingByProvince(Province $province)
    {
        $buildings = $province->buildings()->paginate(10);

        if ($buildings->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No building found in this province'
            ], 404);
        }

        return BuildingResource::collection($buildings);
    }
}
