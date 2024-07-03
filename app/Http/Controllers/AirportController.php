<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\Request;
use App\Http\Resources\AirportResource;
use App\Http\Resources\AirportCollection;
use Illuminate\Validation\ValidationException;
use Exception;

class AirportController extends Controller
{
    public function index()
    {
        try {
            $airports = Airport::with('city')->get();
            return new AirportCollection($airports);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'iata_code' => 'required|string|max:3|unique:airports,iata_code',
                'city_id' => 'required|exists:cities,id',
            ]);

            $airport = Airport::create($request->all());
            return new AirportResource($airport);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function show($id)
    {
        try {
            $airport = Airport::with('city')->find($id);
            if (!$airport) {
                return response()->json(['error' => 'Airport not found'], 404);
            }
            return new AirportResource($airport);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'iata_code' => 'sometimes|required|string|max:3|unique:airports,iata_code,' . $id,
                'city_id' => 'sometimes|required|exists:cities,id',
            ]);

            $airport = Airport::find($id);
            if (!$airport) {
                return response()->json(['error' => 'Airport not found'], 404);
            }

            $airport->update($request->all());
            return new AirportResource($airport);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $airport = Airport::find($id);
            if (!$airport) {
                return response()->json(['error' => 'Airport not found'], 404);
            }

            $airport->delete();
            return response()->json(['message' => 'Airport deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }
}
