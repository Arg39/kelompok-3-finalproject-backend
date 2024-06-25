<?php

namespace App\Http\Controllers\Api;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        if (auth()->user()->role !== 'owner') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'building_id' => 'exists:buildings,id',
            'capacity' => 'required',
            'price' => 'required|integer',
            'description' => 'string',
            'is_available' => 'nullable|boolean',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $building_id = auth()->user()->building->id;

        $room = auth()->user()->building->rooms()->create(array_merge($validator->validate(), ['building_id' => $building_id]));

        if ($request->hasFile('images')) {
            foreach ($request->images as $image) {
                $path = Storage::disk('public')->put('room', $image);
                $room->roomImages()->create(['image' => $path]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => new RoomResource($room),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        return response()->json([
            'status' => 'success',
            'data' => new RoomResource($room),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        if (auth()->user()->role !== 'owner') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'building_id' => 'exists:buildings,id',
            'capacity' => 'required',
            'price' => 'required|integer',
            'description' => 'string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $room->update($validator->validate());

        if ($request->hasFile('images')) {
            foreach ($request->images as $image) {
                $path = Storage::disk('public')->put('room', $image);
                $room->roomImages()->create(['image' => $path]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => new RoomResource($room),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        if (Gate::denies('delete', $room)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $room->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Room deleted successfully',
        ]);
    }
}
