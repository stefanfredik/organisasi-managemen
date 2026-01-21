<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class AreaController extends Controller
{
    public function provinces(): JsonResponse
    {
        $resp = Http::timeout(10)->get('https://wilayah.id/api/provinces.json');
        abort_unless($resp->ok(), 502);
        return response()->json($resp->json());
    }

    public function regencies(string $provinceCode): JsonResponse
    {
        $resp = Http::timeout(10)->get("https://wilayah.id/api/regencies/{$provinceCode}.json");
        abort_unless($resp->ok(), 502);
        return response()->json($resp->json());
    }

    public function districts(string $regencyCode): JsonResponse
    {
        $resp = Http::timeout(10)->get("https://wilayah.id/api/districts/{$regencyCode}.json");
        abort_unless($resp->ok(), 502);
        return response()->json($resp->json());
    }

    public function villages(string $districtCode): JsonResponse
    {
        $resp = Http::timeout(10)->get("https://wilayah.id/api/villages/{$districtCode}.json");
        abort_unless($resp->ok(), 502);
        return response()->json($resp->json());
    }
}
