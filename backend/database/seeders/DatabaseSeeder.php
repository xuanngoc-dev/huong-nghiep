<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@huongnghiep.local'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'role' => UserRole::Admin,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'user@huongnghiep.local'],
            [
                'name' => 'Người dùng',
                'password' => 'password',
                'role' => UserRole::User,
            ],
        );

        $this->call([
            NganhHocSeeder::class,
            ChuyenNganhSeeder::class,
            KhuVucSeeder::class,
            TinhThanhSeeder::class,
            PhuongXaSeeder::class,
            LoaiTruongSeeder::class,
            HeDaoTaoSeeder::class,
            TruongHocSeeder::class,
            LoaiCauHoiSeeder::class,
            MonHocSeeder::class,
            PhuongThucXetTuyenSeeder::class,
            ToHopMonHocSeeder::class,
            TonGiaoSeeder::class,
            DanTocSeeder::class,
        ]);
    }
}
