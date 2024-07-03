<?php

namespace App\Http\Controllers;

use App\Models\ClassFlight;
use Illuminate\Http\Request;
use App\Http\Resources\ClassFlightResource;
use App\Http\Resources\ClassFlightCollection;
use Illuminate\Validation\ValidationException;
use Exception;


/**
 * @OA\Tag(
 *     name="ClassFlights",
 *     description="API Endpoints de ClassFlights"
 * )
 */
class ClassFlightController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/classflights",
     *     tags={"ClassFlights"},
     * security={{"bearerAuth":{}}},
     *     summary="Listar todas as classes de voos",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de classes de voos"
     *     )
     * )
     */
    public function index()
    {
        try {
            $classFlights = ClassFlight::with('flight')->get();
            return new ClassFlightCollection($classFlights);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }


     /**
     * @OA\Post(
     *     path="/api/classflights",
     *     tags={"ClassFlights"},
     * security={{"bearerAuth":{}}},
     *     summary="Criar uma nova classe de voo",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="flight_id", type="integer", example=1),
     *             @OA\Property(property="class_type", type="string", example="Econômica"),
     *             @OA\Property(property="seat_quantity", type="integer", example=100),
     *             @OA\Property(property="price", type="number", format="float", example=500.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Classe de voo criada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro na criação da classe de voo"
     *     )
     * )
     */
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


     /**
     * @OA\Get(
     *     path="/api/classflights/{id}",
     *     tags={"ClassFlights"},
     * security={{"bearerAuth":{}}},
     *     summary="Mostrar uma classe de voo específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da classe de voo"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Classe de voo não encontrada"
     *     )
     * )
     */
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


    /**
     * @OA\Put(
     *     path="/api/classflights/{id}",
     *     tags={"ClassFlights"},
     * security={{"bearerAuth":{}}},
     *     summary="Atualizar uma classe de voo",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="flight_id", type="integer", example=1),
     *             @OA\Property(property="class_type", type="string", example="Econômica"),
     *             @OA\Property(property="seat_quantity", type="integer", example=100),
     *             @OA\Property(property="price", type="number", format="float", example=500.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Classe de voo atualizada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Classe de voo não encontrada"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/classflights/{id}",
     *     tags={"ClassFlights"},
     * security={{"bearerAuth":{}}},
     *     summary="Deletar uma classe de voo",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Classe de voo deletada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Classe de voo não encontrada"
     *     )
     * )
     */
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
