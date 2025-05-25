<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class RajaOngkirController extends Controller
{
    protected $key;
    protected $baseUrl;

    public function __construct()
    {
        $this->key = config('services.rajaongkir.key');
        $this->baseUrl = config('services.rajaongkir.base_url');
    }

    public function getProvinces()
    {
        $response = Http::withHeaders([
            'key' => $this->key
        ])->get("{$this->baseUrl}/province");

        $data = json_decode($response->body(), true);

        return response()->json([
            'data' => $data['rajaongkir']['results'] ?? []
        ]);
    }

    public function getCities(Request $request)
    {
        $provinceId = $request->query('province_id');

        $response = Http::withHeaders([
            'key' => $this->key
        ])->get("{$this->baseUrl}/city", [
            'province' => $provinceId
        ]);

        $data = json_decode($response->body(), true);

        return response()->json([
            'cities' => $data['rajaongkir']['results'] ?? []
        ]);
    }

    public function getCost(Request $request)
    {
        $validated = $request->validate([
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string',
        ]);

        $response = Http::withHeaders([
            'key' => $this->key
        ])->post("{$this->baseUrl}/cost", [
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'weight' => $validated['weight'],
            'courier' => $validated['courier'],
        ]);

        $data = json_decode($response->body(), true);

        return response()->json([
            'results' => $data['rajaongkir']['results'][0]['costs'] ?? []
        ]);
    }
}
