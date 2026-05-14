<?php

namespace App\Services;

use App\Models\Seat;
use Exception;

class SeatAssignmentService
{
    public function assignSeat(int $aircraftId): Seat
    {
        $seat = Seat::where('aircraft_id', $aircraftId)
            ->where('status', 'available')
            ->lockForUpdate()
            ->first();

        if (!$seat) {
            throw new Exception('No available seats');
        }

        $seat->update([
            'status' => 'booked'
        ]);

        return $seat;
    }
}