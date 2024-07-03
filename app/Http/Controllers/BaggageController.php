<?php

namespace App\Http\Controllers;

use App\Models\Baggage;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\BaggageResource;
use App\Http\Resources\BaggageCollection;
use Carbon\Carbon;

class BaggageController extends Controller
{
    public function index()
    {
        $baggages = Baggage::all();
        return new BaggageCollection($baggages);
    }

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

    public function show($id)
    {
        $baggage = Baggage::find($id);
        if (!$baggage) {
            return response()->json(['error' => 'Baggage not found'], 404);
        }
        return new BaggageResource($baggage);
    }

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

    public function issueBaggageLabel($id)
    {
        $baggage = Baggage::where('baggage_number', $id)->first();
        if (!$baggage) {
            return response()->json(['error' => 'Baggage not found'], 404);
        }

        $ticket = $baggage->ticket;
        $departureTime = Carbon::parse($ticket->flight->departure_time);

        if ($departureTime->diffInHours(Carbon::now()) < 5) {
            return response()->json(['error' => 'Cannot issue baggage label within 5 hours of departure'], 400);
        }

        $baggageLabel = ['data'=> [
            'baggage_number' => $baggage->baggage_number,
            'ticket_number' => $ticket->ticket_number,
            'passenger_name' => $ticket->passenger_name,
        ]];

        return response()->json($baggageLabel);
    }
}