<?php

namespace App\Http\Controllers;

use App\Models\Baggage;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\BaggageResource;
use App\Http\Resources\BaggageCollection;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Baggages",
 *     description="API Endpoints de Baggages"
 * )
 */
class BaggageController extends Controller
{

     /**
     * @OA\Get(
     *     path="/api/baggages",
     *     tags={"Baggages"},
     *     summary="Listar todas as bagagens",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de bagagens"
     *     )
     * )
     */
    public function index()
    {
        $baggages = Baggage::all();
        return new BaggageCollection($baggages);
    }

    /**
     * @OA\Post(
     *     path="/api/baggages",
     *     tags={"Baggages"},
     *     summary="Criar uma nova bagagem",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ticket_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bagagem criada com sucesso"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        $ticket = Ticket::find($request->ticket_id);

        if ($ticket->has_baggage) {
            return response()->json(['error' => 'Baggage already exists for this ticket.'], 400);
        }

        $baggage = Baggage::create([
            'ticket_id' => $request->ticket_id,
            'baggage_number' => Str::uuid(),
        ]);

        $ticket->update(['has_baggage' => true]);

        return new BaggageResource($baggage);
    }

     /**
     * @OA\Get(
     *     path="/api/baggages/{id}",
     *     tags={"Baggages"},
     *     summary="Mostrar uma bagagem específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da bagagem"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bagagem não encontrada"
     *     )
     * )
     */
    public function show($id)
    {
        $baggage = Baggage::find($id);
        if (!$baggage) {
            return response()->json(['error' => 'Baggage not found'], 404);
        }
        return new BaggageResource($baggage);
    }

        /**
     * @OA\Put(
     *     path="/api/baggages/{id}",
     *     tags={"Baggages"},
     *     summary="Atualizar uma bagagem",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ticket_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bagagem atualizada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bagagem não encontrada"
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $request->validate([
            'ticket_id' => 'sometimes|required|exists:tickets,id',
        ]);

        $baggage = Baggage::find($id);
        if (!$baggage) {
            return response()->json(['error' => 'Baggage not found'], 404);
        }

        $baggage->update($request->all());
        return new BaggageResource($baggage);
    }

        /**
     * @OA\Delete(
     *     path="/api/baggages/{id}",
     *     tags={"Baggages"},
     *     summary="Deletar uma bagagem",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Bagagem deletada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bagagem não encontrada"
     *     )
     * )
     */

    public function destroy($id)
    {
        $baggage = Baggage::find($id);
        if (!$baggage) {
            return response()->json(['error' => 'Baggage not found'], 404);
        }

        $ticket = Ticket::find($baggage->ticket_id);
        if ($ticket) {
            $ticket->update(['has_baggage' => false]);
        }

        $baggage->delete();
        return response()->json(null, 204);
    }

        /**
     * @OA\Post(
     *     path="/api/baggages/label",
     *     tags={"Baggages"},
     *     summary="Emitir etiqueta da bagagem",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ticket_number", type="string", example="ticket-number-123"),
     *             @OA\Property(property="baggage_number", type="string", example="baggage-number-456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Etiqueta da bagagem emitida com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bagagem ou ticket não encontrado"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro na emissão da etiqueta da bagagem"
     *     )
     * )
     */

    public function issueBaggageLabel(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|string|exists:tickets,ticket_number',
            'baggage_number' => 'required|string|exists:baggage,baggage_number',
        ]);

        $ticket = Ticket::where('ticket_number', $request->ticket_number)->first();
        $baggage = Baggage::where('baggage_number', $request->baggage_number)->first();

        if (!$ticket || !$baggage || $baggage->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Invalid ticket or baggage number.'], 404);
        }

        $departureTime = Carbon::parse($ticket->flight->departure_time);
        if ($departureTime->diffInHours(Carbon::now()) < 5) {
            return response()->json(['error' => 'Cannot issue baggage label within 5 hours of departure'], 400);
        }

        if ($ticket->status !== 'active') {
            return response()->json(['error' => 'Cannot issue baggage label for a non-active ticket'], 400);
        }

        $baggageLabel = [
            'ticket_number' => $ticket->ticket_number,
            'baggage_number' => $baggage->baggage_number,
            'passenger_name' => $ticket->passenger_name,
        ];

        return response()->json(['data'=> $baggageLabel]);
    }
}