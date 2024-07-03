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

/**
 * @OA\Info(
 *     title="API de Sistema de Passagens",
 *     version="1.0.0",
 *     description="Esta API permite a gestão de um sistema de passagens aéreas. 
 *     Para acessar os endpoints protegidos, siga os passos abaixo:
 *     
 *     1. Registre um novo usuário utilizando o endpoint `POST /register`.
 *     2. Faça login utilizando o endpoint `POST /login` com as credenciais do usuário registrado.
 *     3. O login retornará um token Bearer que deve ser usado para autenticação nos endpoints protegidos.
 *     4. Adicione o token Bearer ao cabeçalho `Authorization` em cada solicitação para os endpoints protegidos.
 *     
 *     As rotas protegidas incluem todas as operações de criação, atualização e exclusão de aeroportos, voos, classes de voo, bagagens, visitantes e tickets.
 *   
 *     Rotas disponíveis:
 *     - `POST /register`: Registrar um novo usuário
 *     - `POST /login`: Fazer login com um usuário registrado
 *     - `POST /logout`: Fazer logout de um usuário autenticado
 *     - `POST /tickets`: Criar um novo ticket (protegida)
 *     - `GET /tickets/cpf/{cpf}`: Obter tickets por CPF (protegida)
 *     - `GET /tickets/{id}/voucher`: Emitir voucher de um ticket (protegida)
 *     - `POST /visitors`: Criar um novo visitante (protegida)
 *     - `GET /flights`: Listar todos os voos
 *     - `GET /flights/search`: Buscar voos
 *     - `GET /flights/{id}`: Obter detalhes de um voo
 *     - `POST /baggages/label`: Emitir etiqueta de bagagem
 *     "
 * )
 * @OA\Tag(
 *     name="Tickets",
 *     description="API Endpoints de Tickets"
 * )
 * @OA\Server(
 *     url="http://localhost:8044",
 *     description="Servidor API de Produção"
 * )
 */
class TicketController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tickets",
     *     tags={"Tickets"},
     *  security={{"bearerAuth":{}}},
     *     summary="Listar todos os tickets",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de tickets"
     *     )
     * )
     */
    public function index()
    {
        $tickets = Ticket::all();
        return new TicketCollection($tickets);
    }

    /**
     * @OA\Post(
     *     path="/api/tickets",
     *     tags={"Tickets"},
     * security={{"bearerAuth":{}}},
     *     summary="Criar novos tickets",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="flight_id", type="integer", example=1),
     *             @OA\Property(property="class_flights_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="visitor",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="cpf", type="string", example="123.456.789-10"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="birthdate", type="string", format="date", example="1985-05-15")
     *             ),
     *             @OA\Property(
     *                 property="tickets",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="passenger_name", type="string", example="Jane Doe"),
     *                     @OA\Property(property="passenger_cpf", type="string", example="987.654.321-00"),
     *                     @OA\Property(property="passenger_birthdate", type="string", format="date", example="1990-10-10"),
     *                     @OA\Property(property="has_baggage", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tickets criados com sucesso"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro na criação dos tickets"
     *     )
     * )
     */
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
            'tickets' => 'required|array|min:1',
            'tickets.*.passenger_name' => 'required|string|max:255',
            'tickets.*.passenger_cpf' => 'required|string|max:14',
            'tickets.*.passenger_birthdate' => 'required|date',
            'tickets.*.has_baggage' => 'required|boolean',
        ]);

        $classFlight = ClassFlight::find($request->class_flights_id);

        if ($classFlight->flight_id != $request->flight_id) {
            return response()->json(['error' => 'The class_flights_id does not belong to the specified flight_id.'], 400);
        }

        if (Ticket::where('class_flights_id', $request->class_flights_id)->count() + count($request->tickets) > $classFlight->seat_quantity) {
            return response()->json(['error' => 'Not enough seats available in this class.'], 400);
        }

        $visitor = Visitor::updateOrCreate(
            ['cpf' => $request->visitor['cpf']],
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

    /**
     * @OA\Get(
     *     path="/api/tickets/{id}",
     *     tags={"Tickets"},
     * security={{"bearerAuth":{}}},
     *     summary="Mostrar um ticket específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do ticket"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket não encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        return new TicketResource($ticket);
    }

    /**
     * @OA\Put(
     *     path="/api/tickets/{id}",
     *     tags={"Tickets"},
     * security={{"bearerAuth":{}}},
     *     summary="Atualizar um ticket",
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
     *             @OA\Property(property="class_flights_id", type="integer", example=1),
     *             @OA\Property(property="visitor_id", type="integer", example=1),
     *             @OA\Property(property="passenger_name", type="string", example="Jane Doe"),
     *             @OA\Property(property="passenger_cpf", type="string", example="987.654.321-00"),
     *             @OA\Property(property="passenger_birthdate", type="string", format="date", example="1990-10-10"),
     *             @OA\Property(property="total_price", type="number", format="float", example=220.00),
     *             @OA\Property(property="has_baggage", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket atualizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket não encontrado"
     *     )
     * )
     */
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

  /**
 * @OA\Delete(
 *     path="/api/tickets/{id}",
 *     tags={"Tickets"},
 * security={{"bearerAuth":{}}},
 *     summary="Deletar um ticket",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Ticket deletado com sucesso"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Ticket não encontrado"
 *     )
 * )
 */
public function destroy($id)
{
    $ticket = Ticket::find($id);
    if (!$ticket) {
        return response()->json(['error' => 'Ticket not found'], 404);
    }

    $ticket->delete();
    return response()->json(null, 204);
}

/**
 * @OA\Get(
 *     path="/api/tickets/cpf/{cpf}",
 *     tags={"Tickets"},
 * security={{"bearerAuth":{}}},
 *     summary="Obter tickets por CPF do comprador",
 *     @OA\Parameter(
 *         name="cpf",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de tickets do comprador"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Comprador não encontrado"
 *     )
 * )
 */
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

/**
 * @OA\Post(
 *     path="/api/tickets/{id}/cancel",
 *     tags={"Tickets"},
 * security={{"bearerAuth":{}}},
 *     summary="Cancelar um ticket",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Ticket cancelado com sucesso"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Ticket não encontrado"
 *     )
 * )
 */
public function cancelTicket($id)
{
    $ticket = Ticket::find($id);
    
    if (!$ticket) {
        return response()->json(['error' => 'Ticket not found'], 404);
    }

    $ticket->update(['status' => 'cancelled']);
    return new TicketResource($ticket);
}

/**
 * @OA\Get(
 *     path="/api/tickets/{id}/voucher",
 *     tags={"Tickets"},
 * security={{"bearerAuth":{}}},
 *     summary="Emitir voucher do ticket",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Voucher emitido com sucesso"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Ticket não encontrado"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erro na emissão do voucher"
 *     )
 * )
 */
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