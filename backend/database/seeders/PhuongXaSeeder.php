<?php

namespace Database\Seeders;

use App\Enums\TrangThaiPhuongXa;
use App\Models\PhuongXa;
use Illuminate\Database\Seeder;

class PhuongXaSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/xa_phuong.json');
        $raw = json_decode((string) file_get_contents($path), true);

        if (! is_array($raw)) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($raw as $item) {
            $maPhuongXa = (string) ($item['ma_phuong_xa'] ?? '');
            if ($maPhuongXa === '') {
                continue;
            }

            $trangThaiRaw = $item['trang_thai'] ?? 1;
            $trangThai = (int) $trangThaiRaw === 1
                ? TrangThaiPhuongXa::DangSuDung->value
                : TrangThaiPhuongXa::NgungSuDung->value;

            $rows[] = [
                'ten_phuong_xa' => (string) ($item['ten_phuong_xa'] ?? ''),
                'ma_phuong_xa' => $maPhuongXa,
                'ma_tinh_thanh' => (string) ($item['ma_tinh_thanh'] ?? ''),
                'trang_thai' => $trangThai,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            PhuongXa::query()->upsert(
                $chunk,
                ['ma_phuong_xa'],
                ['ten_phuong_xa', 'ma_tinh_thanh', 'trang_thai', 'updated_at'],
            );
        }
    }
}
