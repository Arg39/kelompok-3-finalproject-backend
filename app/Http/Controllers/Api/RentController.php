<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Rent;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\RentResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class RentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                $rent = Rent::select('rents.*')
                    ->join('rent_details', 'rents.id', '=', 'rent_details.rent_id')
                    ->join('rooms', 'rent_details.room_id', '=', 'rooms.id')
                    ->join('users', 'rents.user_id', '=', 'users.id')
                    ->with('rentDetails.room', 'user')
                    ->latest()
                    ->distinct()
                    ->paginate(10);
                break;
            case 'owner':
                $rent = Rent::select('rents.*')
                    ->join('rent_details', 'rents.id', '=', 'rent_details.rent_id')
                    ->join('rooms', 'rent_details.room_id', '=', 'rooms.id')
                    ->join('users', 'rents.user_id', '=', 'users.id')
                    ->where('rooms.building_id', $user->building->id)
                    ->with('rentDetails.room', 'user')
                    ->latest()
                    ->distinct()
                    ->paginate(10);
                break;
            default:
                $rent = Rent::select('rents.*')
                    ->join('rent_details', 'rents.id', '=', 'rent_details.rent_id')
                    ->join('rooms', 'rent_details.room_id', '=', 'rooms.id')
                    ->join('users', 'rents.user_id', '=', 'users.id')
                    ->where('rents.user_id', $user->id)
                    ->where('rents.status', 'pending')
                    ->with('rentDetails.room', 'user')
                    ->latest()
                    ->distinct()
                    ->paginate(10);
                break;
        }

        return RentResource::collection($rent);
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
        if (!$request->user()->can('create', Rent::class)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You are not authorized to create rent'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rents' => 'required|array',
            'rents.*.room_id' => 'required|exists:rooms,id',
            'rents.*.start_date' => 'required|date',
            'rents.*.end_date' => 'required|date|after:rents.*.start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request) {
            $rent = $request->user()->rents()->create([
                'total' => 0,
            ]);

            foreach ($request->rents as $rentDetail) {
                $room = Room::findOrFail($rentDetail['room_id']);
                $duration = Carbon::parse($rentDetail['start_date'])->diffInDays($rentDetail['end_date']);
                $subTotal = $room->price * $duration;

                $rent->rentDetails()->create([
                    'room_id' => $room->id,
                    'start_date' => $rentDetail['start_date'],
                    'end_date' => $rentDetail['end_date'],
                    'duration' => $duration,
                    'sub_total' => $subTotal,
                ]);
            }

            $rent->update([
                'total' => $rent->rentDetails()->sum('sub_total'),
            ]);
        });

        return response()->json([
            'status' => 'success',
            'data' => new RentResource($request->user()->rents()->latest()->first())
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Rent $rent)
    {
        Gate::authorize('view', $rent);

        return response()->json([
            'status' => 'success',
            'data' => new RentResource($rent)
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rent $rent)
    {
        Gate::authorize('delete', $rent);

        if ($rent->status === 'done') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Rent already paid'
            ], 403);
        }

        $rent->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Rent deleted successfully'
        ]);
    }

    public function addRoom(Request $request, Room $room)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $rent = Rent::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (!$rent) {
            $rent = Rent::create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'total' => 0,
            ]);
        }

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $duration = Carbon::parse($start_date)->diffInDays($end_date);

        $rentDetail = $rent->rentDetails()->where('rent_id', $rent->id)->where('room_id', $room->id)->first();

        if ($rentDetail) {
            $rentDetail->update([
                'start_date' => $start_date,
                'end_date' => $end_date,
                'duration' => $duration,
                'sub_total' => $room->price * $duration,
            ]);
        } else {
            $rent->rentDetails()->create([
                'room_id' => $room->id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'duration' => $duration,
                'sub_total' => $room->price * $duration,
            ]);
        }

        $rent->update([
            'total' => $rent->rentDetails()->sum('sub_total'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => new RentResource($rent)
        ]);
    }
}
