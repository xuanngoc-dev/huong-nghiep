<?php

namespace App\Support;

use App\Models\LichSuNapEduCoin;
use App\Models\NapEduCoin;
use App\Models\TracNghiemLichSuThanhToan;
use App\Models\TracNghiemPhienDaHoanThanh;
use RuntimeException;

class MaGiaoDich
{
    public const PREFIX_NAP = 'NAP';

    public const PREFIX_PAY = 'PAY';

    public const SUFFIX = 'ECOIN';

    public const TOKEN_LENGTH = 8;

    public const MAX_ATTEMPTS = 32;

    private const ALNUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * NAP + 8 ký tự ngẫu nhiên + ECOIN.
     */
    public static function taoMaNap(): string
    {
        return self::taoUnique(
            fn (): string => self::PREFIX_NAP.self::randomAlnum().self::SUFFIX,
        );
    }

    /**
     * PAY + 8 số ngẫu nhiên + ECOIN.
     */
    public static function taoMaThanhToan(): string
    {
        return self::taoUnique(
            fn (): string => self::PREFIX_PAY.self::randomDigits().self::SUFFIX,
        );
    }

    public static function isValidNap(?string $code): bool
    {
        return (bool) preg_match(
            '/^'.self::PREFIX_NAP.'[A-Z0-9]{'.self::TOKEN_LENGTH.'}'.self::SUFFIX.'$/',
            self::normalize($code),
        );
    }

    public static function isValidPay(?string $code): bool
    {
        return (bool) preg_match(
            '/^'.self::PREFIX_PAY.'\d{'.self::TOKEN_LENGTH.'}'.self::SUFFIX.'$/',
            self::normalize($code),
        );
    }

    /**
     * Mã PAY cũ: PAY + 8 số (chưa có hậu tố ECOIN).
     */
    public static function isLegacyPay(?string $code): bool
    {
        return (bool) preg_match(
            '/^'.self::PREFIX_PAY.'\d{'.self::TOKEN_LENGTH.'}$/',
            self::normalize($code),
        );
    }

    /**
     * Chuẩn hóa PAY... / PAY...ECOIN về PAY + 8 số + ECOIN.
     */
    public static function canonicalizePay(?string $code): ?string
    {
        $code = self::normalize($code);
        if (self::isValidPay($code)) {
            return $code;
        }
        if (self::isLegacyPay($code)) {
            return $code.self::SUFFIX;
        }

        return null;
    }

    public static function normalize(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }

    /**
     * Lấy NAP...ECOIN hoặc PAY...ECOIN từ nội dung chuyển khoản (bỏ text dư của ngân hàng).
     */
    public static function extractFromText(?string $text): ?string
    {
        $compact = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $text) ?? '');
        if ($compact === '') {
            return null;
        }

        $napPattern = '/'.self::PREFIX_NAP.'[A-Z0-9]{'.self::TOKEN_LENGTH.'}'.self::SUFFIX.'/';
        if (preg_match($napPattern, $compact, $matches) && self::isValidNap($matches[0])) {
            return $matches[0];
        }

        $payPattern = '/'.self::PREFIX_PAY.'\d{'.self::TOKEN_LENGTH.'}'.self::SUFFIX.'/';
        if (preg_match($payPattern, $compact, $matches) && self::isValidPay($matches[0])) {
            return $matches[0];
        }

        $legacyPayPattern = '/'.self::PREFIX_PAY.'\d{'.self::TOKEN_LENGTH.'}(?!'.self::SUFFIX.')/';
        if (preg_match($legacyPayPattern, $compact, $matches)) {
            return self::canonicalizePay($matches[0]);
        }

        return null;
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
        $candidate = self::canonicalizePay($candidate);
        if ($candidate === null || self::isTaken($candidate) || self::isTokenTaken(self::extractToken($candidate))) {
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
        if (self::isValidPay($code) || self::isLegacyPay($code)) {
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
            TracNghiemPhienDaHoanThanh::class,
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

        $codes = [self::PREFIX_NAP.$token.self::SUFFIX];
        if (ctype_digit($token)) {
            $codes[] = self::PREFIX_PAY.$token.self::SUFFIX;
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
