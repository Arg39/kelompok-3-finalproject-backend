<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromotionResource;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    public function index()
    {
        $Promotion = Promotion::all();
        return new PromotionResource(true, 'List Data Promotion', $Promotion);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => 'required',
            "image" => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/Promotion', $image->hashName());

        $Promotion = Promotion::create([
            'title' => $request->title,
            'image' => $image->hashName(),
        ]);

        return new PromotionResource(true, 'Data Berhasil di tambahkan!', $Promotion);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "title" => 'required',
            "image" => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Promotion = Promotion::find($id);
        
        if ($request->has('image')) {
            $image = $request->file('image');
            $image->storeAs('public/Promotion', $image->hashName());

            Storage::delete('public/Promotion'. basename($Promotion->image));

            $Promotion->update([
                'title' => $request->title,
                'image' => $image->hashName()
            ]);
        } else {
            $Promotion->update([
                'title' => $request->title
            ]);
            
        }

        return new PromotionResource(true, "Data Promotion Berhasil Diubah!", $Promotion);
    }

    public function destroy($id)
    {
        $Promotion = Promotion::find($id);

        Storage::delete('public/Promotion'. basename($Promotion->image));

        $Promotion->delete();

        return new PromotionResource(true, "Data Berhasil Dihapus!", null);
    }
}
