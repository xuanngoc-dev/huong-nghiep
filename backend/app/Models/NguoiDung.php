<?php

namespace App\Models;

use Closure;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'ngay_sinh',
    'gioi_tinh',
    'dan_toc',
    'ton_giao',
    'trinh_do_hoc_van',
    'suc_khoe_the_chat',
    'kha_nang_tai_chinh',
    'vi_tri_dia_ly',
    'edu_coin',
    'xu_he_thong',
    'mat_khau_thanh_toan',
])]
#[Hidden(['mat_khau_thanh_toan'])]
class NguoiDung extends Model
{
    protected $table = 'thong_tin_nguoi_dung';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trinh_do_hoc_van' => 'array',
            'suc_khoe_the_chat' => 'array',
            'kha_nang_tai_chinh' => 'array',
            'vi_tri_dia_ly' => 'array',
            'edu_coin' => 'integer',
            'xu_he_thong' => 'integer',
            'mat_khau_thanh_toan' => 'hashed',
        ];
    }

    /**
     * Chuẩn hóa ngày sinh về dd/mm/yyyy. Trả về null nếu không hợp lệ.
     */
    public static function normalizeNgaySinh(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        foreach (['d/m/Y', 'j/n/Y', 'Y-m-d'] as $format) {
            $date = DateTimeImmutable::createFromFormat('!'.$format, $raw);
            $errors = DateTimeImmutable::getLastErrors();
            if ($date && ($errors === false || (($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0))) {
                if ($format === 'Y-m-d' && $date->format('Y-m-d') !== $raw) {
                    continue;
                }

                return $date->format('d/m/Y');
            }
        }

        return null;
    }

    /**
     * @return list<string|Closure>
     */
    public static function ngaySinhRules(): array
    {
        return [
            'nullable',
            'string',
            'max:10',
            function (string $attribute, mixed $value, Closure $fail): void {
                if ($value === null || $value === '') {
                    return;
                }

                $normalized = self::normalizeNgaySinh($value);
                if ($normalized === null) {
                    $fail('Ngày sinh không hợp lệ.');

                    return;
                }

                $date = DateTimeImmutable::createFromFormat('!d/m/Y', $normalized);
                if ($date > new DateTimeImmutable('today')) {
                    $fail('Ngày sinh không được sau hôm nay.');
                }
            },
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function ngaySinh(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): ?string => self::normalizeNgaySinh($value),
        );
    }
}
