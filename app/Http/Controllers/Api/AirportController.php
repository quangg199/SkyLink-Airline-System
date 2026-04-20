<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use Illuminate\Http\Request;

class AirportController extends Controller
{
    public function index()
    {
        // Lấy tất cả sân bay từ DB
        $airports = Airport::all();

        // Trả về dữ liệu JSON kèm mã trạng thái 200 (Success)
        return response()->json([
            'status' => 'success',
            'data' => $airports
        ], 200);
    }
}