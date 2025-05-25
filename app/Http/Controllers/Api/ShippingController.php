<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ShippingController extends Controller
{
    protected $binderbyteKey;

    public function __construct()
    {
        $this->binderbyteKey = config('binderbyte.api_key');
    }

    public function provinces()
    {
        $response = Http::get("https://api.binderbyte.com/wilayah/provinsi", [
            'api_key' => $this->binderbyteKey
        ]);

        $data = json_decode($response->body(), true);

        return response()->json([
            'data' => $data['value'] ?? []
        ]);
    }

    public function cities(Request $request)
    {
        $provinceId = $request->query('province_id');

        $response = Http::get("https://api.binderbyte.com/wilayah/kabupaten", [
            'api_key' => $this->binderbyteKey,
            'id_provinsi' => $provinceId
        ]);

        $data = json_decode($response->body(), true);

        return response()->json([
            'cities' => $data['value'] ?? []
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string',
        ]);

        $origin = $validated['origin'];
        $destination = $validated['destination'];
        $weight = $validated['weight'];
        $courier = strtolower($validated['courier']);

        $biayaPerKg = match ($courier) {
            'jne' => 10000,
            'tiki' => 12000,
            'pos' => 8000,
            default => 11000,
        };

        $beratKg = ceil($weight / 1000);
        $ongkir = $biayaPerKg * $beratKg;

        $results = [
            [
                'service' => strtoupper($courier) . ' REG',
                'description' => 'Regular Service',
                'cost' => $ongkir,
                'etd' => '2-4',
            ],
            [
                'service' => strtoupper($courier) . ' YES',
                'description' => 'Yakin Esok Sampai',
                'cost' => $ongkir + 8000,
                'etd' => '1',
            ],
        ];

        return response()->json([
            'results' => $results,
        ]);
    }
}
