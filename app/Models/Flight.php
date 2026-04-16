<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_number', 
        'departure_airport_id', 
        'arrival_airport_id', 
        'departure_time', 
        'arrival_time', 
        'base_price', 
        'available_seats', 
        'status'
    ];

    // Trỏ về bảng Airport để lấy thông tin Sân bay đi
    public function departureAirport() 
    {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }

    // Trỏ về bảng Airport để lấy thông tin Sân bay đến
    public function arrivalAirport() 
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }

    // 1 Chuyến bay có nhiều Vé hành khách (1-n)
    public function tickets() 
    {
        return $this->hasMany(Ticket::class);
    }
}