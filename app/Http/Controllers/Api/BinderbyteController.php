<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;


class BinderbyteController extends Controller
{
    protected $key;

    public function __construct()
    {
        $this->key = config('binderbyte.api_key'); // pakai config baru
    }

    public function getProvinces()
    {
        $response = Http::get("https://api.binderbyte.com/wilayah/provinsi", [
            'api_key' => $this->key
        ]);

        $data = json_decode($response->body(), true);

        return response()->json([
    'data' => $data['value'] ?? []
]);

    }

    public function getCities(Request $request)
    {
        $provinceId = $request->query('province_id');

        $response = Http::get("https://api.binderbyte.com/wilayah/kabupaten", [
            'api_key' => $this->key,
            'id_provinsi' => $provinceId
        ]);

        $data = json_decode($response->body(), true);

        return response()->json([
            'cities' => $data['value'] ?? []
        ]);
    }

    public function getCost(Request $request)
{
    $validated = $request->validate([
        'courier' => 'required',
        'origin' => 'required',
        'destination' => 'required',
        'weight' => 'required|integer',
    ]);

    Log::info('🛫 Ongkir Params', ['params' => $validated]);


    $response = Http::get("https://api.binderbyte.com/v1/cekongkir/{$validated['courier']}", [
        'api_key' => $this->key,
        'asal' => $validated['origin'],
        'tujuan' => $validated['destination'],
        'berat' => $validated['weight'],
    ]);

    $data = json_decode($response->body(), true);

    Log::info('✅ Ongkir Response', ['response' => $data ?? []]);


    if (!isset($data['status']) || $data['status'] != 200) {
        Log::error('❌ Gagal dari Binderbyte', ['response' => $data]);
        return response()->json([
            'results' => [],
            'message' => 'Gagal mengambil data ongkir',
            'binderbyte_response' => $data,
        ], 400);
    }

    if (!is_array($validated)) {
    Log::warning('⚠️ Validated params bukan array', ['validated' => $validated]);
}


    $costs = [];

    foreach ($data['data']['costs'] ?? [] as $service) {
        $costDetail = $service['cost'][0] ?? null;

        if ($costDetail) {
            $costs[] = [
                'service' => $service['service'],
                'description' => $service['description'],
                'cost' => $costDetail['value'],
                'etd' => $costDetail['etd'],
            ];
        }
    }

    return response()->json([
        'results' => $costs,
        'binderbyte_response' => $data,
    ]);
}

}
