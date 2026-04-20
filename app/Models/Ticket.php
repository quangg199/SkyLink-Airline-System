<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    // Các trường được phép lưu dữ liệu
    protected $fillable = [
        'booking_id', 
        'flight_id', 
        'passenger_name', 
        'identity_number', 
        'seat_number', 
        'ticket_price'
    ];

    /**
     * QUAN HỆ CƠ SỞ DỮ LIỆU
     */

    // 1 Vé thuộc về 1 Đơn đặt chỗ
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // 1 Vé thuộc về 1 Chuyến bay
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    // 1 Vé chỉ xuất được đúng 1 Thẻ lên máy bay (QUAN HỆ 1-1)
    public function boardingPass()
    {
        return $this->hasOne(BoardingPass::class);
    }
}