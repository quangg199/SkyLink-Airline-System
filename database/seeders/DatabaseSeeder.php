<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use App\Models\Flight;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. TẠO TÀI KHOẢN ADMIN
        User::factory()->create([
            'name' => 'Quang Admin',
            'email' => 'admin@skylink.com',
            'password' => bcrypt('123456'), 
            'role' => 'admin',              
            'membership_tier' => 'gold',
        ]);
        User::factory(10)->create(); // Đẻ 10 user ảo

        // 2. GỌI AIRPORT SEEDER CỦA EM
        $this->call([
            AirportSeeder::class,
        ]);

        // 3. TẠO DỮ LIỆU TĨNH: DỊCH VỤ BỔ TRỢ
        $services = [
            ['name' => 'Hành lý ký gửi 20kg', 'price' => 250000],
            ['name' => 'Hành lý ký gửi 30kg', 'price' => 400000],
            ['name' => 'Suất ăn nóng', 'price' => 120000],
            ['name' => 'Ưu tiên làm thủ tục', 'price' => 150000],
        ];
        foreach ($services as $service) {
            Service::create($service);
        }

        // 4. KÍCH HOẠT FACTORY CHUYẾN BAY (Tạo 50 chuyến)
        Flight::factory(50)->create();
    }
}