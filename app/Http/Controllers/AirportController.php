<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\Request;
use App\Http\Resources\AirportResource;
use App\Http\Resources\AirportCollection;
use Illuminate\Validation\ValidationException;
use Exception;

/**
 * @OA\Tag(
 *     name="Airports",
 *     description="API Endpoints de Airports"
 * )
 */
class AirportController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/airports",
     *     tags={"Airports"},
     *     summary="Listar todos os aeroportos",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de aeroportos"
     *     )
     * )
     */
    public function index()
    {
        try {
            $airports = Airport::with('city')->get();
            return new AirportCollection($airports);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/airports",
     *     tags={"Airports"},
     *     summary="Criar um novo aeroporto",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Aeroporto Internacional"),
     *             @OA\Property(property="iata_code", type="string", example="XYZ"),
     *             @OA\Property(property="city_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Aeroporto criado com sucesso"
     *     )
     * )
     */
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

     /**
     * @OA\Get(
     *     path="/api/airports/{id}",
     *     tags={"Airports"},
     *     summary="Mostrar um aeroporto específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do aeroporto"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aeroporto não encontrado"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/airports/{id}",
     *     tags={"Airports"},
     *     summary="Atualizar um aeroporto",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Aeroporto Internacional Atualizado"),
     *       @OA\Property(property="iata_code", type="string", example="XYZ"),
    *             @OA\Property(property="city_id", type="integer", example=1)
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Aeroporto atualizado com sucesso"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Aeroporto não encontrado"
    *     )
    * )
    */
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

    /**
 * @OA\Delete(
 *     path="/api/airports/{id}",
 *     tags={"Airports"},
 *     summary="Deletar um aeroporto",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Aeroporto deletado com sucesso"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aeroporto não encontrado"
 *     )
 * )
 */
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
