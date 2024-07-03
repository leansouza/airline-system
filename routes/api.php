<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AirportController,
    FlightController,
    ClassFlightController,
    TicketController,
    BaggageController,
    VisitorController,
    AuthController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
 // Rotas de autenticação
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Rotas protegidas para gestores
Route::middleware(['auth:sanctum', 'manager'])->group(function () {
    Route::apiResource('airports', AirportController::class);
    Route::apiResource('classflights', ClassFlightController::class);
    Route::apiResource('flights', FlightController::class);
    Route::get('flights/{id}/passengers', [FlightController::class, 'passengers']);
    Route::apiResource('baggages', BaggageController::class);
    Route::apiResource('visitors', VisitorController::class)->except(['store']);
    Route::apiResource('tickets', TicketController::class)->except(['store']);
    Route::post('tickets/{id}/cancel', [TicketController::class, 'cancelTicket']);
});

// Rotas acessíveis por visitantes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('tickets', [TicketController::class, 'store']);
    Route::get('tickets/cpf/{cpf}', [TicketController::class, 'getTicketsByCPF']);
    Route::get('tickets/{id}/voucher', [TicketController::class, 'issueVoucher']);
    Route::get('baggages/{id}/label', [BaggageController::class, 'issueBaggageLabel']);
    Route::post('visitors', [VisitorController::class, 'store']);
});

// Rotas públicas
Route::get('flights', [FlightController::class, 'index']);
Route::get('flights/search', [FlightController::class, 'search']);
Route::get('flights/{id}', [FlightController::class, 'show']);