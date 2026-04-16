<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'pnr_code', 'total_amount', 'status'];

    // Thuộc về 1 Người dùng
    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    // 1 Đơn hàng có nhiều Vé (1-n)
    public function tickets() 
    {
        return $this->hasMany(Ticket::class);
    }

    // 1 Đơn hàng mua nhiều Dịch vụ (n-n)
    public function services() 
    {
        return $this->belongsToMany(Service::class)
                    ->withPivot('quantity', 'price_at_purchase')
                    ->withTimestamps();
    }
}