<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AirportController;
use App\Http\Controllers\Api\FlightController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CheckInController;

/*
|--------------------------------------------------------------------------
| API Route Security Tiers
|--------------------------------------------------------------------------
|
| TIER 1 — PUBLIC      : No authentication required.
| TIER 2 — AUTH        : Authentication management endpoints (no token needed).
| TIER 3 — MEMBER      : Requires valid Sanctum token (auth:sanctum).
| TIER 4 — ADMIN       : Requires valid token + 'admin' role (auth:sanctum + role:admin).
|
| The CheckRole middleware (Protection Proxy) is always chained AFTER auth:sanctum.
| Ordering matters: sanctum confirms identity, CheckRole confirms authorization.
|
*/

// -------------------------------------------------------------------------
// TIER 1: PUBLIC ROUTES
// No authentication. Safe for unauthenticated browsing.
// -------------------------------------------------------------------------
Route::get('/airports', [AirportController::class, 'index']);
Route::get('/flights',  [FlightController::class,  'index']);
Route::get('/flights/{id}/seats', [FlightController::class, 'seats']);
Route::get('/services', [ServiceController::class, 'index']);

// Check-in Routes (Public - No authentication required)
Route::prefix('check-in')->group(function () {
    Route::post('/', [CheckInController::class, 'processCheckIn']); // POST /api/check-in
    Route::get('/', [CheckInController::class, 'query']);            // GET /api/check-in?pnr_code=...&passenger_name=...
});

// -------------------------------------------------------------------------
// Development helper: create a test booking (PNR ABC123)
// Call: POST /api/dev/create-test-booking
// Only enabled when APP_DEBUG=true or running in local environment
// -------------------------------------------------------------------------
if (app()->environment('local') || config('app.debug')) {
    Route::post('/dev/create-test-booking', function () {

        $flight = \App\Models\Flight::first();
        if (!$flight) {
            return response()->json(['status' => 'error', 'message' => 'No flights found. Run seeders first.'], 500);
        }

        // Ensure flight is in 'check_in' state so check-in is allowed
        $flight->update(['status' => 'check_in']);

        $user = \App\Models\User::first() ?? \App\Models\User::factory()->create();

        $pnr = 'ABC123';

        $booking = \App\Models\Booking::firstOrCreate(
            ['pnr_code' => $pnr],
            [
                'user_id' => $user->id,
                'flight_id' => $flight->id,
                'total_amount' => 500000,
                'status' => 'paid',
                'expires_at' => now()->addDays(1),
            ]
        );

        // Ensure payment exists and is completed
        \App\Models\Payment::firstOrCreate(
            ['booking_id' => $booking->id],
            ['amount' => $booking->total_amount, 'status' => 'success', 'payment_method' => 'test', 'paid_at' => now()]
        );

        // Ensure a ticket exists for the passenger
        \App\Models\Ticket::firstOrCreate(
            ['booking_id' => $booking->id, 'passenger_name' => 'Nguyen Van A'],
            [
                'flight_id' => $flight->id,
                'ticket_code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8)),
                'identity_number' => null,
                'seat_id' => null,
                'ticket_price' => $booking->total_amount,
            ]
        );

        return response()->json(['status' => 'success', 'pnr' => $pnr]);
    });
}

// -------------------------------------------------------------------------
// TIER 2: AUTHENTICATION ROUTES
// Open endpoints for identity management (register, login).
// Logout and /me require a valid token — scoped separately inside.
// -------------------------------------------------------------------------
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // These two require a valid Sanctum token
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });
});

// -------------------------------------------------------------------------
// TIER 3: MEMBER-PROTECTED ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bookings',               [BookingController::class, 'index']);
    Route::post('/bookings/lock-seat',    [BookingController::class, 'lockSeat']);
    Route::post('/bookings',              [BookingController::class, 'store']);
    Route::post('/bookings/pay',          [PaymentController::class, 'pay']);
    Route::get('/bookings/{id}',          [BookingController::class, 'show']);
});

// -------------------------------------------------------------------------
// TIER 4: ADMIN-PROTECTED ROUTES
// Requires: Valid Sanctum token AND the 'admin' role.
// The CheckRole('admin') Protection Proxy guards all routes in this group.
// Add admin-only endpoints here as the system grows.
// -------------------------------------------------------------------------
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Flight management (admin only)
    Route::get('/flights',          [FlightController::class, 'index']);
    // Future admin endpoints will be added here:
    // Route::post('/flights',      [FlightController::class, 'store']);
    // Route::put('/flights/{id}',  [FlightController::class, 'update']);
    // Route::delete('/flights/{id}', [FlightController::class, 'destroy']);

    // Airport management (admin only)
    // Route::post('/airports', [AirportController::class, 'store']);
});

