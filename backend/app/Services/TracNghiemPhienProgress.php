<?php

namespace App\Services;

use App\Enums\TrangThaiLoaiCauHoi;
use App\Models\LoaiCauHoi;

class TracNghiemPhienProgress
{
    public const CODE_CHUA_TRA_LOI_HET = 'chua_tra_loi_het';

    /**
     * Loại câu hỏi đầu tiên (theo thứ tự bước 2–5) chưa trả lời đủ.
     *
     * @param  array{by_loai?: array<string, mixed>, completed_loai?: list<string>}  $payload
     */
    public function firstIncompleteLoai(array $payload): ?string
    {
        $byLoai = is_array($payload['by_loai'] ?? null) ? $payload['by_loai'] : [];
        $completed = array_map(
            static fn ($ma) => strtolower(trim((string) $ma)),
            is_array($payload['completed_loai'] ?? null) ? $payload['completed_loai'] : [],
        );

        foreach ($this->orderedLoaiMa() as $ma) {
            if (! isset($byLoai[$ma])) {
                continue;
            }

            $group = $byLoai[$ma];
            $questionCount = (int) ($group['question_count'] ?? 0);
            if ($questionCount <= 0) {
                continue;
            }

            if (! in_array($ma, $completed, true)) {
                return $ma;
            }
        }

        foreach ($byLoai as $ma => $group) {
            $ma = strtolower(trim((string) $ma));
            $questionCount = (int) ($group['question_count'] ?? 0);
            $answeredCount = (int) ($group['answered_count'] ?? 0);
            if ($questionCount > 0 && $answeredCount < $questionCount) {
                return $ma;
            }
        }

        return null;
    }

    /**
     * Bước UI của loại câu hỏi: loại đầu tiên = bước 2.
     */
    public function buocCuaLoai(?string $maLoaiCauHoi): ?int
    {
        $ma = strtolower(trim((string) $maLoaiCauHoi));
        if ($ma === '') {
            return null;
        }

        $index = 0;
        foreach ($this->orderedLoaiMa() as $item) {
            $index++;
            if ($item === $ma) {
                return $index + 1;
            }
        }

        return null;
    }

    /**
     * Payload lỗi chuẩn cho app/web khi chưa trả lời hết bước 2–5.
     *
     * @param  array{by_loai?: array<string, mixed>, completed_loai?: list<string>}  $payload
     * @return array<string, mixed>|null
     */
    public function incompleteErrorData(string $ssid, array $payload): ?array
    {
        $ma = $this->firstIncompleteLoai($payload);
        if ($ma === null) {
            return null;
        }

        return [
            'ssid' => $ssid,
            'code' => self::CODE_CHUA_TRA_LOI_HET,
            'da_hoan_thanh' => false,
            'co_the_xem_ket_qua' => false,
            'ma_loai_chua_xong' => $ma,
            'buoc_chua_xong' => $this->buocCuaLoai($ma),
            'path_chua_xong' => 'loai/'.$ma,
            'completed_loai' => array_values($payload['completed_loai'] ?? []),
        ];
    }

    /**
     * @param  array{by_loai?: array<string, mixed>, completed_loai?: list<string>}  $payload
     * @return array<string, mixed>
     */
    public function progressMeta(string $ssid, array $payload): array
    {
        $ma = $this->firstIncompleteLoai($payload);

        return [
            'ma_loai_chua_xong' => $ma,
            'buoc_chua_xong' => $ma ? $this->buocCuaLoai($ma) : null,
            'path_chua_xong' => $ma ? 'loai/'.$ma : null,
            'co_the_xem_ket_qua' => $ma === null,
        ];
    }

    /**
     * @return list<string>
     */
    private function orderedLoaiMa(): array
    {
        return LoaiCauHoi::query()
            ->where('trang_thai', TrangThaiLoaiCauHoi::DangSuDung)
            ->orderBy('thu_tu_uu_tien')
            ->orderBy('id')
            ->pluck('ma_loai_cau_hoi')
            ->map(static fn ($ma) => strtolower(trim((string) $ma)))
            ->filter()
            ->values()
            ->all();
    }
}
