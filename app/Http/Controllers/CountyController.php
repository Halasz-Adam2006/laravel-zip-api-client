<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\County;
use Illuminate\Support\Facades\Http;

class CountyController extends Controller
{
    public function index()
    {
        // fetch data if needed, e.g. $counties = County::all();
        return view('counties.index'); // or view('counties.index', compact('counties'))
    }

    public function showCounties()
    {
        // Fetch counties from the ZIP API
        try {
            $response = Http::get("http://127.0.0.1:8000/api/counties");
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch counties from API'], 500);
            }

            $data = $response->json();
            $counties = [];
            $seen = [];
            
            // Extract unique counties from the response
            if (isset($data) && is_array($data)) {
                foreach ($data as $item) {
                    if (isset($item['state']) && !in_array($item['state'], $seen)) {
                        $seen[] = $item['state'];
                        $counties[] = [
                            'id' => count($counties) + 1,
                            'name' => $item['state']
                        ];
                    }
                }
            }

            // Sort by name
            usort($counties, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return response()->json($counties);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching counties: ' . $e->getMessage()], 500);
        }
    }

    public function showAlphabet($name)
    {
        $county = (object)['name' => $name];
        return view('counties.alphabet', compact('county'));
    }

    public function getCitiesByLetter($name, $letter)
    {
        if (!$letter || strlen($letter) !== 1) {
            return response()->json(['error' => 'Invalid letter parameter'], 400);
        }
        
        // Fetch cities from the ZIP code API
        try {
            // Pass the letter to the external API
            $response = Http::get("http://127.0.0.1:8000/api/county/{$name}/{$letter}");
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch cities from API', 'status' => $response->status()], 500);
            }

            $cities = $response->json();

            // Sort cities alphabetically if the API hasn't already
            usort($cities, function($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });

            return response()->json($cities);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching data: ' . $e->getMessage()], 500);
        }
    }
}
