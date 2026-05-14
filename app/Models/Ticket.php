<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

    class Ticket extends Model
    {
        use HasFactory;

        protected $fillable = [
            'booking_id',
            'flight_id',
            'ticket_code',
            'passenger_name',
            'identity_number',
            'seat_id',
            'ticket_price'
        ];

        public function booking()
        {
            return $this->belongsTo(Booking::class);
        }

        public function flight()
        {
            return $this->belongsTo(Flight::class);
        }

        public function boardingPass()
        {
            return $this->hasOne(BoardingPass::class);
        }

        // 🔥 FIX QUAN TRỌNG
        public function seat()
        {
            return $this->belongsTo(Seat::class);
        }
    }