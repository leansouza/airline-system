<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\Request;
use App\Http\Resources\FlightResource;
use App\Http\Resources\FlightCollection;
use Illuminate\Validation\ValidationException;
use Exception;
use Carbon\Carbon;

class FlightController extends Controller
{
    public function index()
    {
        try {
            $flights = Flight::with(['originAirport', 'destinationAirport', 'classes'])->get();
            return new FlightCollection($flights);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'origin_airport_id' => 'required|exists:airports,id|different:destination_airport_id',
                'destination_airport_id' => 'required|exists:airports,id|different:origin_airport_id',
                'flight_number' => 'required|string|max:255|unique:flights,flight_number',
                'departure_time' => 'required|date|after:now',
            ]);

            $flight = Flight::create($request->all());
            return new FlightResource($flight);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function show($id)
    {
        try {
            $flight = Flight::with(['originAirport', 'destinationAirport', 'classes'])->find($id);
            if (!$flight) {
                return response()->json(['error' => 'Flight not found'], 404);
            }
            return new FlightResource($flight);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'origin_airport_id' => 'sometimes|required|exists:airports,id|different:destination_airport_id',
                'destination_airport_id' => 'sometimes|required|exists:airports,id|different:origin_airport_id',
                'flight_number' => 'sometimes|required|string|max:255|unique:flights,flight_number,' . $id,
                'departure_time' => 'sometimes|required|date|after:now',
            ]);

            $flight = Flight::find($id);
            if (!$flight) {
                return response()->json(['error' => 'Flight not found'], 404);
            }

            $flight->update($request->all());
            return new FlightResource($flight);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $flight = Flight::find($id);
            if (!$flight) {
                return response()->json(['error' => 'Flight not found'], 404);
            }

            $flight->delete();
            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function passengers($id)
    {
        try {
            $flight = Flight::with('tickets.visitor')->find($id);
            if (!$flight) {
                return response()->json(['error' => 'Flight not found'], 404);
            }

            $passengers = $flight->tickets->map(function ($ticket) {
                return [
                    'ticket_number' => $ticket->ticket_number,
                    'passenger_name' => $ticket->passenger_name,
                    'passenger_cpf' => $ticket->passenger_cpf,
                    'passenger_birthdate' => $ticket->passenger_birthdate,
                    'class' => $ticket->classFlight->class_type,
                    'has_baggage' => $ticket->has_baggage,
                ];
            });

            return response()->json(['data'=>$passengers], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $request->validate([
                'origin_airport_id' => 'required|exists:airports,id',
                'destination_airport_id' => 'required|exists:airports,id',
                'date' => 'required|date',
                'max_price' => 'sometimes|numeric|min:0',
            ]);

            $query = Flight::with(['originAirport', 'destinationAirport', 'classes' => function ($query) {
                $query->whereHas('tickets', function ($query) {
                    $query->havingRaw('COUNT(*) < seat_quantity');
                });
            }])
            ->where('origin_airport_id', $request->origin_airport_id)
            ->where('destination_airport_id', $request->destination_airport_id)
            ->whereDate('departure_time', '=', $request->date)
            ->where('departure_time', '>', Carbon::now());

            if ($request->has('max_price')) {
                $query->whereHas('classes', function ($query) use ($request) {
                    $query->where('price', '<=', $request->max_price);
                });
            }

            $flights = $query->get();

            return new FlightCollection($flights);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }
}