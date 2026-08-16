<?php

namespace App\Support;

use App\Models\LichSuNapEduCoin;
use App\Models\NapEduCoin;
use App\Models\TracNghiemLichSuThanhToan;
use RuntimeException;

class MaGiaoDich
{
    public const PREFIX_NAP = 'NAP';

    public const SUFFIX_NAP = 'ECOIN';

    public const PREFIX_PAY = 'PAY';

    public const TOKEN_LENGTH = 8;

    public const MAX_ATTEMPTS = 32;

    private const ALNUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * NAP + 8 ký tự ngẫu nhiên + ECOIN.
     */
    public static function taoMaNap(): string
    {
        return self::taoUnique(
            fn (): string => self::PREFIX_NAP.self::randomAlnum().self::SUFFIX_NAP,
        );
    }

    /**
     * PAY + 8 số ngẫu nhiên.
     */
    public static function taoMaThanhToan(): string
    {
        return self::taoUnique(
            fn (): string => self::PREFIX_PAY.self::randomDigits(),
        );
    }

    public static function isValidNap(?string $code): bool
    {
        return (bool) preg_match('/^'.self::PREFIX_NAP.'[A-Z0-9]{'.self::TOKEN_LENGTH.'}'.self::SUFFIX_NAP.'$/', self::normalize($code));
    }

    public static function isValidPay(?string $code): bool
    {
        return (bool) preg_match('/^'.self::PREFIX_PAY.'\d{'.self::TOKEN_LENGTH.'}$/', self::normalize($code));
    }

    public static function normalize(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }

    public static function isTaken(string $code): bool
    {
        $code = self::normalize($code);
        if ($code === '') {
            return false;
        }

        foreach (self::models() as $model) {
            if ($model::query()->where('ma_giao_dich', $code)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dùng mã client nếu hợp lệ và unique; không thì sinh mã NAP mới.
     */
    public static function resolveMaNap(?string $candidate): ?string
    {
        $candidate = self::normalize($candidate);
        if ($candidate === '') {
            return self::taoMaNap();
        }
        if (! self::isValidNap($candidate) || self::isTaken($candidate) || self::isTokenTaken(self::extractToken($candidate))) {
            return null;
        }

        return $candidate;
    }

    /**
     * Dùng mã client nếu hợp lệ và unique; không thì sinh mã PAY mới.
     */
    public static function resolveMaThanhToan(?string $candidate): ?string
    {
        $candidate = self::normalize($candidate);
        if ($candidate === '') {
            return self::taoMaThanhToan();
        }
        if (! self::isValidPay($candidate) || self::isTaken($candidate) || self::isTokenTaken(self::extractToken($candidate))) {
            return null;
        }

        return $candidate;
    }

    public static function extractToken(string $code): string
    {
        $code = self::normalize($code);
        if (self::isValidNap($code)) {
            return substr($code, strlen(self::PREFIX_NAP), self::TOKEN_LENGTH);
        }
        if (self::isValidPay($code)) {
            return substr($code, strlen(self::PREFIX_PAY), self::TOKEN_LENGTH);
        }

        return '';
    }

    /**
     * @return list<class-string>
     */
    private static function models(): array
    {
        return [
            NapEduCoin::class,
            LichSuNapEduCoin::class,
            TracNghiemLichSuThanhToan::class,
        ];
    }

    private static function taoUnique(callable $generator): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = $generator();
            $token = self::extractToken($code);
            if ($token !== '' && ! self::isTokenTaken($token) && ! self::isTaken($code)) {
                return $code;
            }
        }

        throw new RuntimeException('Không tạo được mã giao dịch unique. Vui lòng thử lại.');
    }

    private static function isTokenTaken(string $token): bool
    {
        $token = strtoupper($token);
        if (strlen($token) !== self::TOKEN_LENGTH) {
            return true;
        }

        $codes = [self::PREFIX_NAP.$token.self::SUFFIX_NAP];
        if (ctype_digit($token)) {
            $codes[] = self::PREFIX_PAY.$token;
        }

        foreach (self::models() as $model) {
            if ($model::query()->whereIn('ma_giao_dich', $codes)->exists()) {
                return true;
            }
        }

        return false;
    }

    private static function randomAlnum(): string
    {
        $max = strlen(self::ALNUM) - 1;
        $token = '';
        for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
            $token .= self::ALNUM[random_int(0, $max)];
        }

        return $token;
    }

    private static function randomDigits(): string
    {
        $token = '';
        for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
            $token .= (string) random_int(0, 9);
        }

        return $token;
    }
}
