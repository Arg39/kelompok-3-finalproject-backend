<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegionResource;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Support\Facades\Log;

class RegionController extends Controller
{
    public function province()
    {
        $provinces = Province::all();
        return new RegionResource(true, 200, 'Data provinsi berhasil diperlihatkan', $provinces);
    }

    public function regencyInProvince($province_id)
    {
        $province = Province::with('regencies')->findOrFail($province_id);
        return new RegionResource(true, 200, 'Data provinsi beserta kabupaten berhasil diperlihatkan', $province);
    }

    public function regency($regency_id)
    {
        $regency = Regency::findOrFail($regency_id);
        return new RegionResource(true, 200, 'Data kabupaten berhasil diperlihatkan', $regency);
    }
}
