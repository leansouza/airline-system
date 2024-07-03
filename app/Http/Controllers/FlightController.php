<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\Request;
use App\Http\Resources\FlightResource;
use App\Http\Resources\FlightCollection;
use Illuminate\Validation\ValidationException;
use Exception;
use Carbon\Carbon;


/**
 * @OA\Tag(
 *     name="Flights",
 *     description="API Endpoints de Flights"
 * )
 */
class FlightController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/flights",
     *     tags={"Flights"},
     *     summary="Listar todos os voos",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de voos"
     *     )
     * )
     */
    public function index()
    {
        try {
            $flights = Flight::with(['originAirport', 'destinationAirport', 'classes'])->get();
            return new FlightCollection($flights);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }


     /**
     * @OA\Post(
     *     path="/api/flights",
     *     tags={"Flights"},
     *     summary="Criar um novo voo",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="origin_airport_id", type="integer", example=1),
     *             @OA\Property(property="destination_airport_id", type="integer", example=2),
     *             @OA\Property(property="flight_number", type="string", example="FL123"),
     *             @OA\Property(property="departure_time", type="string", format="date-time", example="2024-07-01T10:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Voo criado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro na validação"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
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


    /**
 * @OA\Get(
 *     path="/api/flights/{id}",
 *     tags={"Flights"},
 *     summary="Mostrar um voo específico",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Detalhes do voo"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Voo não encontrado"
 *     )
 * )
 */
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

    /**
 * @OA\Put(
 *     path="/api/flights/{id}",
 *     tags={"Flights"},
 *     summary="Atualizar um voo",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="origin_airport_id", type="integer", example=1),
 *             @OA\Property(property="destination_airport_id", type="integer", example=2),
 *             @OA\Property(property="flight_number", type="string", example="FL123"),
 *             @OA\Property(property="departure_time", type="string", format="date-time", example="2024-07-01T10:00:00Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Voo atualizado com sucesso"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erro na validação"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erro interno do servidor"
 *     )
 * )
 */
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

    /**
 * @OA\Delete(
 *     path="/api/flights/{id}",
 *     tags={"Flights"},
 *     summary="Deletar um voo",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Voo deletado com sucesso"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Voo não encontrado"
 *     )
 * )
 */
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

    /**
 * @OA\Get(
 *     path="/api/flights/{id}/passengers",
 *     tags={"Flights"},
 *     summary="Listar passageiros de um voo",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de passageiros"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Voo não encontrado"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erro interno do servidor"
 *     )
 * )
 */

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


/**
 * @OA\Schema(
 *     schema="Flight",
 *     type="object",
 *     required={"id", "origin_airport_id", "destination_airport_id", "departure_time", "arrival_time", "price"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Flight ID"
 *     ),
 *     @OA\Property(
 *         property="origin_airport_id",
 *         type="integer",
 *         description="ID of the origin airport"
 *     ),
 *     @OA\Property(
 *         property="destination_airport_id",
 *         type="integer",
 *         description="ID of the destination airport"
 *     ),
 *     @OA\Property(
 *         property="departure_time",
 *         type="string",
 *         format="date-time",
 *         description="Departure time of the flight"
 *     ),
 *     @OA\Property(
 *         property="arrival_time",
 *         type="string",
 *         format="date-time",
 *         description="Arrival time of the flight"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Price of the flight"
 *     )
 * )
 */

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