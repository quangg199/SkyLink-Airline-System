<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use App\Models\Seat;
use Illuminate\Database\Seeder;

class AircraftSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo 1 máy bay
        $aircraft = Aircraft::create([
            'tail_number' => 'VN-A' . rand(100, 999),
            'model' => 'Airbus A321',
            'status' => 1
        ]);

        // 2. Logic đẻ ra 180 ghế (30 hàng, mỗi hàng 6 ghế A->F)
        $seatsData = [];
        $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
        
        for ($row = 1; $row <= 30; $row++) {
            foreach ($letters as $letter) {
                // Hàng 1-3 là Business (Hạng 2), còn lại là Economy (Hạng 1)
                $seatClass = ($row <= 3) ? 2 : 1; 
                
                $seatsData[] = [
                    'aircraft_id' => $aircraft->id,
                    'seat_number' => $row . $letter,
                    'seat_class' => $seatClass,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // 3. Bulk Insert - Kỹ thuật tối ưu DB (Java hay PHP đều dùng)
        Seat::insert($seatsData); 
    }
}