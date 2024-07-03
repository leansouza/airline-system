<?php

namespace App\Http\Controllers;

use App\Models\ClassFlight;
use Illuminate\Http\Request;
use App\Http\Resources\ClassFlightResource;
use App\Http\Resources\ClassFlightCollection;
use Illuminate\Validation\ValidationException;
use Exception;

class ClassFlightController extends Controller
{
    public function index()
    {
        try {
            $classFlights = ClassFlight::with('flight')->get();
            return new ClassFlightCollection($classFlights);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'flight_id' => 'required|exists:flights,id',
                'class_type' => 'required|string|max:255',
                'seat_quantity' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
            ]);

            $existingClass = ClassFlight::where('flight_id', $request->flight_id)
            ->where('class_type', $request->class_type)
            ->first();

            if ($existingClass) {
                return response()->json(['error' => 'This class type already exists for the given flight.'], 400);
            }

            $classFlight = ClassFlight::create($request->all());
            return new ClassFlightResource($classFlight);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function show($id)
    {
        try {
            $classFlight = ClassFlight::with('flight')->find($id);
            if (!$classFlight) {
                return response()->json(['error' => 'ClassFlight not found'], 404);
            }
            return new ClassFlightResource($classFlight);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'flight_id' => 'sometimes|required|exists:flights,id',
                'class_type' => 'sometimes|required|string|max:255',
                'seat_quantity' => 'sometimes|required|integer|min:1',
                'price' => 'sometimes|required|numeric|min:0',
            ]);

            $classFlight = ClassFlight::find($id);
            if (!$classFlight) {
                return response()->json(['error' => 'ClassFlight not found'], 404);
            }

            if ($request->has('class_type')) {
                $existingClass = ClassFlight::where('flight_id', $classFlight->flight_id)
                                            ->where('class_type', $request->class_type)
                                            ->where('id', '!=', $id)
                                            ->first();
    
                if ($existingClass) {
                    return response()->json(['error' => 'This class type already exists for the given flight.'], 400);
                }
            }

            $classFlight->update($request->all());
            return new ClassFlightResource($classFlight);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $classFlight = ClassFlight::find($id);
            if (!$classFlight) {
                return response()->json(['error' => 'ClassFlight not found'], 404);
            }

            $classFlight->delete();
            return response()->json(['message' => 'ClassFlight deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }
}
