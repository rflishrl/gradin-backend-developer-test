<?php

namespace Database\Seeders;

use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    /**
     * Seed 20 sample couriers. Safe to run more than once: rows are matched by email.
     */
    public function run(): void
    {
        $couriers = [
            ['Budiono Hadi Agung', 3, VehicleType::Motorcycle],
            ['Budi Santoso', 2, VehicleType::Motorcycle],
            ['Agung Prasetyo', 5, VehicleType::Car],
            ['Siti Nurhaliza', 4, VehicleType::Motorcycle],
            ['Dewi Lestari', 1, VehicleType::Bicycle],
            ['Rizky Ramadhan', 2, VehicleType::Motorcycle],
            ['Andi Wijaya', 3, VehicleType::Van],
            ['Ahmad Fauzi', 1, VehicleType::Motorcycle],
            ['Putri Maharani', 4, VehicleType::Car],
            ['Eko Prabowo', 5, VehicleType::Van],
            ['Hendra Gunawan', 2, VehicleType::Motorcycle],
            ['Lina Marlina', 3, VehicleType::Motorcycle],
            ['Joko Susilo', 1, VehicleType::Bicycle],
            ['Fitriani Putri', 4, VehicleType::Motorcycle],
            ['Dimas Anggara', 2, VehicleType::Car],
            ['Yusuf Maulana', 3, VehicleType::Motorcycle],
            ['Rina Kartika', 5, VehicleType::Car],
            ['Bayu Setiawan', 1, VehicleType::Motorcycle],
            ['Maya Sari', 4, VehicleType::Van],
            ['Teguh Santosa', 2, VehicleType::Motorcycle],
        ];

        foreach ($couriers as $index => [$name, $level, $vehicle]) {
            $number = $index + 1;

            Courier::updateOrCreate(
                ['email' => sprintf('courier%02d@example.com', $number)],
                [
                    'name' => $name,
                    'phone' => sprintf('0812345678%02d', $number),
                    'vehicle_type' => $vehicle,
                    'license_plate' => $vehicle === VehicleType::Bicycle ? null : sprintf('B %04d XYZ', 1000 + $number),
                    'level' => $level,
                    'is_active' => $number % 7 !== 0,
                    'created_at' => now()->subDays(40 - $number * 2),
                ],
            );
        }
    }
}
