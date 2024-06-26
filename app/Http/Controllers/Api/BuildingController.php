<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\ImageBuilding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\BuildingResource;
use App\Http\Resources\ImageResource;
use Illuminate\Support\Facades\Storage;

class BuildingController extends Controller
{
    public function index()
    {
        try {
            $buildings = Building::all();
            return new BuildingResource(true, 200, 'Data berhasil diperlihatkan', $buildings);
        } catch (\Exception $e) {
            return new BuildingResource(false, 500, 'Terjadi kesalahan saat mengambil data', null, $e->getMessage());
        }
    }

    public function store(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            'regency' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'type' => 'required|in:villa,hotel,apartment',
            'address' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return new BuildingResource(false, 400, 'Validasi gagal', null, $validator->errors());
        }

        try {
            $building = Building::create([
                'user_id' => $user_id,
                'regency' => $request->input('regency'),
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'address' => $request->input('address'),
                'description' => $request->input('description'),
            ]);

            return new BuildingResource(true, 201, 'Building berhasil disimpan', $building);
        } catch (\Exception $e) {
            return new BuildingResource(false, 500, 'Terjadi kesalahan saat menyimpan data', null, $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $building = Building::findOrFail($id);
            return new BuildingResource(true, 200, 'Data building berhasil diperlihatkan', $building);
        } catch (\Exception $e) {
            return new BuildingResource(false, 500, 'Terjadi kesalahan saat mengambil data building', null, $e->getMessage());
        }
    }

    public function showByUserId($user_id)
    {
        try {
            $buildings = Building::where('user_id', $user_id)->get();
            return new BuildingResource(true, 200, 'Data bangunan berhasil diperlihatkan berdasarkan user ID', $buildings);
        } catch (\Exception $e) {
            return new BuildingResource(false, 500, 'Terjadi kesalahan saat mengambil data bangunan', null, $e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        try {
            $building = Building::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'regency' => 'sometimes|required|string|max:255',
                'name' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|in:villa,hotel,apartment',
                'address' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
            ]);

            if ($validator->fails()) {
                return new BuildingResource(false, 400, 'Validasi gagal', null, $validator->errors());
            }

            if ($request->has('regency')) {
                $building->regency = $request->input('regency');
            }
            if ($request->has('name')) {
                $building->name = $request->input('name');
            }
            if ($request->has('type')) {
                $building->type = $request->input('type');
            }
            if ($request->has('address')) {
                $building->address = $request->input('address');
            }
            if ($request->has('description')) {
                $building->description = $request->input('description');
            }

            $building->save();

            return new BuildingResource(true, 200, 'Building berhasil diperbarui', $building);

        } catch (\Exception $e) {
            return new BuildingResource(false, 500, 'Terjadi kesalahan saat memperbarui data', null, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $building = Building::findOrFail($id);
            $building->delete(); 

            return new BuildingResource(true, 200, 'Building berhasil dihapus', null);

        } catch (\Exception $e) {
            return new BuildingResource(false, 500, 'Terjadi kesalahan saat menghapus data', null, $e->getMessage());
        }
    }

    public function storeImage(Request $request, $building_id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return new ImageResource(false, 'Validasi gambar gagal', $validator->errors());
        }

        try {
            $building = Building::findOrFail($building_id);

            $imageFile = $request->file('image');
            $imagePath = $imageFile->storeAs('buildings/' . $building_id, $imageFile->getClientOriginalName(), 'public');

            $image = ImageBuilding::create([
                'building_id' => $building->id,
                'image' => $imageFile->getClientOriginalName(),
            ]);

            $imageUrl = url('/storage/buildings/' . $building->id . '/' . $imageFile->getClientOriginalName());

            return new ImageResource(true, 'Gambar berhasil disimpan', ['image_url' => $imageUrl]);
        } catch (\Exception $e) {
            return new ImageResource(false, 'Terjadi kesalahan saat menyimpan gambar', $e->getMessage());
        }
    }
    public function showImages($building_id)
    {
        try {
            $images = ImageBuilding::where('building_id', $building_id)->get();
            
            $imageData = $images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => url($image->image),
                ];
            });
    
            return new ImageResource(true, 'Data gambar berhasil diperlihatkan', $imageData);
        } catch (\Exception $e) {
            return new ImageResource(false, 'Terjadi kesalahan saat mengambil data gambar', $e->getMessage());
        }
    }

    public function destroyImage($id)
    {
        try {
            $image = ImageBuilding::findOrFail($id);
    
            Storage::disk('public')->delete('buildings/' . $image->building_id . '/' . $image->image);
            $image->delete();
    
            return new ImageResource(true, 'Gambar berhasil dihapus', null);
        } catch (\Exception $e) {
            return new ImageResource(false, 'Terjadi kesalahan saat menghapus gambar', $e->getMessage());
        }
    }
}
