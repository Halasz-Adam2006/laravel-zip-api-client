<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\County;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class CountyController extends Controller
{
    public function index()
    {
        return view('counties.index');
    }

    public function showCounties()
    {
        $counties = County::orderBy('name')->get(['id', 'name']);

        return response()->json($counties);
    }

    public function showAlphabet($name)
    {
        $county = (object)['name' => $name];
        return view('counties.alphabet', compact('county'));
    }

    public function getCitiesByLetter($name, $letter)
    {
        $letter = strtoupper($letter);

        if ($letter === '' || strlen($letter) !== 1) {
            return response()->json(['error' => 'Invalid letter parameter'], 400);
        }

        $county = County::where('name', $name)->first();

        if (! $county) {
            return response()->json(['error' => 'County not found'], 404);
        }

        $cities = $county->cities()
            ->with('county')
            ->where('name', 'LIKE', $letter.'%')
            ->orderBy('name')
            ->get();

        return response()->json($cities);
    }

    public function exportCitiesPdf($name, Request $request)
    {
        $letter = strtoupper($request->query('letter', ''));

        if ($letter === '' || strlen($letter) !== 1) {
            return response()->json(['error' => 'Invalid letter parameter'], 400);
        }

        $response = Http::get(url("/api/county/{$name}/{$letter}"));
        
        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch cities'], 500);
        }

        $cities = $response->json();
        
        if (isset($cities['error'])) {
            return response()->json($cities, 404);
        }

        $county = !empty($cities) ? (object)['name' => $cities[0]['county']['name']] : (object)['name' => $name];

        $pdf = Pdf::loadView('pdf.cities', [
            'county' => $county,
            'letter' => $letter,
            'cities' => $cities
        ]);

        return $pdf->download("cities_{$name}_{$letter}.pdf");
    }

    public function exportCitiesCsv($name, Request $request)
    {
        $letter = strtoupper($request->query('letter', ''));

        if ($letter === '' || strlen($letter) !== 1) {
            return response()->json(['error' => 'Invalid letter parameter'], 400);
        }

        $response = Http::get(url("/api/county/{$name}/{$letter}"));
        
        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch cities'], 500);
        }

        $cities = $response->json();
        
        if (isset($cities['error'])) {
            return response()->json($cities, 404);
        }

        $csv = "ID,Irányítószám,Város,Megye\n";
        foreach ($cities as $city) {
            $csv .= "{$city['id']},{$city['zip']},{$city['city']},{$city['county']['name']}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"cities_{$name}_{$letter}.csv\"");
    }
}
