<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\Airport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Flight>
 */
class FlightFactory extends Factory
{
    // Khai báo Model liên kết với Factory này
    protected $model = Flight::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1. Lấy ngẫu nhiên 2 Sân bay KHÁC NHAU để tránh lỗi cất/hạ cánh cùng 1 chỗ
        $airports = Airport::inRandomOrder()->take(2)->pluck('id');
        
        // Nếu database chưa có đủ 2 sân bay, tạm thời gán null để không lỗi (dù Seeder của ta sẽ đảm bảo có đủ)
        $departure_id = $airports->first() ?? 1;
        $arrival_id = $airports->last() ?? 2;

        // 2. Logic thời gian: Giờ cất cánh random trong 1 tháng tới
        $departureTime = Carbon::instance(fake()->dateTimeBetween('now', '+1 month'));
        
        // Giờ hạ cánh = Giờ cất cánh cộng thêm random từ 1 đến 4 tiếng
        $arrivalTime = (clone $departureTime)->addHours(rand(1, 4));

        return [
            // Tạo số hiệu chuyến bay thực tế (VD: VN-124, VJ-892)
            'flight_number' => fake()->randomElement(['VN-', 'VJ-', 'QH-', 'VU-']) . fake()->unique()->numberBetween(100, 999),
            
            'departure_airport_id' => $departure_id,
            'arrival_airport_id' => $arrival_id,
            
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            
            // Random giá vé từ 500k đến 5 triệu (bước nhảy 100k cho số đẹp)
            'base_price' => fake()->numberBetween(5, 50) * 100000,
            
            // Sức chứa phổ biến của các dòng Airbus/Boeing
            'available_seats' => fake()->randomElement([120, 150, 180, 220]),
            
            // Ép tỉ lệ: 80% đúng giờ (scheduled), 20% delay để data giống thật
            'status' => fake()->randomElement(['scheduled', 'scheduled', 'scheduled', 'scheduled', 'delayed']),
        ];
    }
}