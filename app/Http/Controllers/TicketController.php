<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Visitor;
use App\Models\ClassFlight;
use App\Models\Baggage;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::all();
        return new TicketCollection($tickets);
    }

    public function store(Request $request)
    {
        $request->validate([
            'flight_id' => 'required|exists:flights,id',
            'class_flights_id' => 'required|exists:class_flights,id',
            'visitor' => 'required|array',
            'visitor.name' => 'required|string|max:255',
            'visitor.cpf' => 'required|string|max:14',
            'visitor.email' => 'required|string|email|max:255',
            'visitor.birthdate' => 'required|date',
            'tickets' => 'required|array',
            'tickets.*.passenger_name' => 'required|string|max:255',
            'tickets.*.passenger_cpf' => 'required|string|max:14',
            'tickets.*.passenger_birthdate' => 'required|date',
            'tickets.*.has_baggage' => 'required|boolean',
        ]);

        $classFlight = ClassFlight::find($request->class_flights_id);

        if (Ticket::where('class_flights_id', $request->class_flights_id)->count() + count($request->tickets) > $classFlight->seat_quantity) {
            return response()->json(['error' => 'Not enough seats available in this class.'], 400);
        }

        $visitor = Visitor::updateOrCreate(
            ['cpf' => preg_replace('/[^\d]/', '',$request->visitor['cpf'])],
            [
                'name' => $request->visitor['name'],
                'email' => $request->visitor['email'],
                'birthdate' => $request->visitor['birthdate']
            ]
        );

        $tickets = [];
        foreach ($request->tickets as $ticketData) {
            $totalPrice = $classFlight->price;
            $baggageNumber = null;
            if ($ticketData['has_baggage']) {
                $totalPrice *= 1.1;
                $baggageNumber = Str::uuid();
            }

            $ticket = Ticket::create([
                'flight_id' => $request->flight_id,
                'class_flights_id' => $request->class_flights_id,
                'visitor_id' => $visitor->id,
                'ticket_number' => Str::uuid(),
                'passenger_name' => $ticketData['passenger_name'],
                'passenger_cpf' => preg_replace('/[^\d]/', '', $ticketData['passenger_cpf']),
                'passenger_birthdate' => $ticketData['passenger_birthdate'],
                'total_price' => $totalPrice,
                'has_baggage' => $ticketData['has_baggage'],
                'baggage_number' => $baggageNumber,
                'status' => 'active',
            ]);

            if ($ticketData['has_baggage']) {
                Baggage::create([
                    'ticket_id' => $ticket->id,
                    'baggage_number' => $baggageNumber,
                ]);
            }

            $tickets[] = $ticket;
        }

        return new TicketCollection(collect($tickets));
    }

    public function show($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        return new TicketResource($ticket);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'flight_id' => 'sometimes|required|exists:flights,id',
            'class_flights_id' => 'sometimes|required|exists:class_flights,id',
            'visitor_id' => 'sometimes|required|exists:visitors,id',
            'passenger_name' => 'sometimes|required|string|max:255',
            'passenger_cpf' => 'sometimes|required|string|max:14',
            'passenger_birthdate' => 'sometimes|required|date',
            'total_price' => 'sometimes|required|numeric|min:0',
            'has_baggage' => 'boolean',
        ]);

        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        $ticket->update($request->all());
        return new TicketResource($ticket);
    }

    public function destroy($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        $ticket->delete();
        return response()->json(null, 204);
    }

    public function getTicketsByCPF($cpf)
    {
        $cpf = preg_replace('/[^\d]/', '', $cpf);
        $visitor = Visitor::where('cpf', $cpf)->first();

        if (!$visitor) {
            return response()->json(['error' => 'Visitor not found'], 404);
        }

        $tickets = Ticket::where('visitor_id', $visitor->id)->where('status', 'active')->get();
        return new TicketCollection($tickets);
    }

    public function cancelTicket($id)
    {
        $ticket = Ticket::find($id);
        
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        $ticket->update(['status' => 'cancelled']);
        return new TicketResource($ticket);
    }
    
    public function issueVoucher($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
    
        $departureTime = Carbon::parse($ticket->flight->departure_time);
        if ($departureTime->diffInHours(Carbon::now()) < 5) {
            return response()->json(['error' => 'Cannot issue voucher within 5 hours of departure'], 400);
        }
    
        if ($ticket->status !== 'active') {
            return response()->json(['error' => 'Cannot issue voucher for a non-active ticket'], 400);
        }
    
        $voucher = [
            'ticket_number' => $ticket->ticket_number,
            'flight_number' => $ticket->flight->flight_number,
            'origin' => $ticket->flight->originAirport->name,
            'destination' => $ticket->flight->destinationAirport->name,
            'passenger_name' => $ticket->passenger_name,
            'has_baggage' => $ticket->has_baggage ? 'Yes' : 'No',
        ];
    
        if ($ticket->has_baggage) {
            $voucher['baggage_number'] = $ticket->baggage_number;
        }
    
        return response()->json($voucher);
    }
}