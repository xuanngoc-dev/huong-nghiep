<?php

namespace App\Http\Controllers\Api;

use App\Enums\HinhThucThanhToanTracNghiem;
use App\Enums\KenhThanhToan;
use App\Enums\LoaiGiaoDich;
use App\Enums\TrangThaiNganHangThanhToan;
use App\Enums\TrangThaiNapEduCoin;
use App\Enums\TrangThaiThanhToanTracNghiem;
use App\Http\Controllers\Controller;
use App\Models\LichSuNapEduCoin;
use App\Models\NganHangThanhToan;
use App\Models\NguoiDung;
use App\Models\TracNghiemLichSuThanhToan;
use App\Models\TracNghiemLichSuThanhToanEduCoin;
use App\Models\TracNghiemPhienDaHoanThanh;
use App\Models\User;
use App\Support\ApiResponse;
use App\Support\MaGiaoDich;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TracNghiemLichSuThanhToanController extends Controller
{
    public function store(Request $request, int $id): JsonResponse
    {
        return $this->tryApi(function () use ($request, $id) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để thanh toán.');
            }

            $validated = $request->validate([
                'hinh_thuc_thanh_toan' => [
                    'required',
                    Rule::enum(HinhThucThanhToanTracNghiem::class),
                ],
                'ngan_hang_thanh_toan_id' => [
                    'required_if:hinh_thuc_thanh_toan,'.HinhThucThanhToanTracNghiem::ChuyenKhoan->value,
                    'nullable',
                    'integer',
                    'exists:he_thong_ngan_hang_thanh_toan,id',
                ],
                'mat_khau_thanh_toan' => [
                    'required_if:hinh_thuc_thanh_toan,'.HinhThucThanhToanTracNghiem::EduCoin->value,
                    'nullable',
                    'string',
                ],
                'ma_giao_dich' => [
                    'nullable',
                    'string',
                    'regex:/^PAY[0-9]{8}(?:ECOIN)?$/i',
                ],
            ], [
                'hinh_thuc_thanh_toan.required' => 'Vui lòng chọn hình thức thanh toán.',
                'ngan_hang_thanh_toan_id.required_if' => 'Vui lòng chọn ngân hàng nhận chuyển khoản.',
                'ngan_hang_thanh_toan_id.exists' => 'Ngân hàng không tồn tại.',
                'mat_khau_thanh_toan.required_if' => 'Vui lòng nhập mật khẩu thanh toán.',
                'ma_giao_dich.regex' => 'Mã giao dịch thanh toán không đúng định dạng.',
            ]);

            $hinhThuc = HinhThucThanhToanTracNghiem::from($validated['hinh_thuc_thanh_toan']);

            $result = DB::transaction(function () use ($user, $id, $hinhThuc, $validated) {
                $phien = TracNghiemPhienDaHoanThanh::query()
                    ->where('id', $id)
                    ->where('nguoi_khao_sat_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if ($phien === null) {
                    return ['error' => 'Không tìm thấy phiên trắc nghiệm.'];
                }

                if ($this->isPhienDaThanhToan($phien)) {
                    return ['error' => 'Phiên trắc nghiệm này đã được thanh toán.'];
                }

                $pending = TracNghiemLichSuThanhToan::query()
                    ->where('lich_su_phien_id', $phien->id)
                    ->where('trang_thai', TrangThaiThanhToanTracNghiem::DangXuLy)
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                if ($hinhThuc === HinhThucThanhToanTracNghiem::EduCoin) {
                    if ($pending !== null) {
                        return ['error' => 'Phiên này đang chờ xác nhận chuyển khoản. Vui lòng hoàn tất hoặc chờ duyệt.'];
                    }

                    return $this->thanhToanEduCoin(
                        $user,
                        $phien,
                        (string) ($validated['mat_khau_thanh_toan'] ?? ''),
                    );
                }

                if ($pending !== null) {
                    return ['item' => $pending];
                }

                return $this->taoYeuCauChuyenKhoan(
                    $user,
                    $phien,
                    (int) $validated['ngan_hang_thanh_toan_id'],
                    $validated['ma_giao_dich'] ?? null,
                );
            });

            if (isset($result['error'])) {
                return ApiResponse::error($result['error']);
            }

            /** @var TracNghiemLichSuThanhToan $item */
            $item = $result['item'];
            $message = $item->trang_thai === TrangThaiThanhToanTracNghiem::DaHoanThanh
                ? 'Thanh toán thành công.'
                : 'Đã ghi nhận yêu cầu chuyển khoản. Vui lòng chờ xác nhận.';

            $extra = [];
            if (array_key_exists('so_du_edu_coin', $result)) {
                $extra['so_du_edu_coin'] = $result['so_du_edu_coin'];
            }

            return ApiResponse::success($this->toPublicArray($item), $message, $extra);
        });
    }

    public function show(Request $request, int $id, int $thanhToanId): JsonResponse
    {
        return $this->tryApi(function () use ($request, $id, $thanhToanId) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để xem thanh toán.');
            }

            $item = TracNghiemLichSuThanhToan::query()
                ->where('id', $thanhToanId)
                ->where('lich_su_phien_id', $id)
                ->where('nguoi_dung_id', $user->id)
                ->first();

            if ($item === null) {
                return ApiResponse::error('Không tìm thấy yêu cầu thanh toán.');
            }

            return ApiResponse::success(
                $this->toPublicArray($item),
                'Lấy chi tiết thanh toán thành công.',
            );
        });
    }

    public function doiSoat(Request $request, int $id): JsonResponse
    {
        return $this->tryApi(function () use ($request, $id) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để đối soát thanh toán.');
            }

            $validated = $request->validate([
                'ma_giao_dich' => [
                    'nullable',
                    'string',
                    'regex:/^PAY[0-9]{8}(?:ECOIN)?$/i',
                ],
                'ngan_hang_thanh_toan_id' => [
                    'nullable',
                    'integer',
                    'exists:he_thong_ngan_hang_thanh_toan,id',
                ],
            ], [
                'ma_giao_dich.regex' => 'Mã giao dịch thanh toán không đúng định dạng.',
                'ngan_hang_thanh_toan_id.exists' => 'Ngân hàng không tồn tại.',
            ]);

            $result = DB::transaction(function () use ($user, $id, $validated) {
                return $this->doiSoatChuyenKhoan(
                    $user,
                    $id,
                    $validated['ma_giao_dich'] ?? null,
                    isset($validated['ngan_hang_thanh_toan_id'])
                        ? (int) $validated['ngan_hang_thanh_toan_id']
                        : null,
                );
            });

            if (isset($result['error'])) {
                return ApiResponse::error($result['error']);
            }

            /** @var TracNghiemLichSuThanhToan|null $item */
            $item = $result['item'] ?? null;
            $matched = (bool) ($result['matched'] ?? false);
            $maGiaoDich = (string) ($result['ma_giao_dich'] ?? '');

            if ($matched && $item !== null) {
                return ApiResponse::success(
                    $this->toPublicArray($item),
                    'Thanh toán thành công.',
                    ['matched' => true],
                );
            }

            $data = $item !== null
                ? $this->toPublicArray($item)
                : [
                    'ma_giao_dich' => $maGiaoDich,
                    'trang_thai' => 'cho_chuyen_khoan',
                ];

            return ApiResponse::success(
                $data,
                'Chưa nhận được chuyển khoản khớp nội dung.',
                ['matched' => false],
            );
        });
    }

    /**
     * @return array{
     *     item?: TracNghiemLichSuThanhToan|null,
     *     matched?: bool,
     *     ma_giao_dich?: string,
     *     error?: string
     * }
     */
    private function doiSoatChuyenKhoan(
        User $user,
        int $phienId,
        ?string $maGiaoDichCandidate,
        ?int $nganHangThanhToanId,
    ): array {
        $phien = TracNghiemPhienDaHoanThanh::query()
            ->where('id', $phienId)
            ->where('nguoi_khao_sat_id', $user->id)
            ->lockForUpdate()
            ->first();

        if ($phien === null) {
            return ['error' => 'Không tìm thấy phiên trắc nghiệm.'];
        }

        $maGiaoDich = $phien->ensureMaGiaoDich($maGiaoDichCandidate);

        if ($this->isPhienDaThanhToan($phien)) {
            $daThanhToan = TracNghiemLichSuThanhToan::query()
                ->where('lich_su_phien_id', $phien->id)
                ->where('trang_thai', TrangThaiThanhToanTracNghiem::DaHoanThanh)
                ->latest('id')
                ->first();

            return [
                'item' => $daThanhToan,
                'matched' => true,
                'ma_giao_dich' => $maGiaoDich,
            ];
        }

        $pending = TracNghiemLichSuThanhToan::query()
            ->where('lich_su_phien_id', $phien->id)
            ->where('trang_thai', TrangThaiThanhToanTracNghiem::DangXuLy)
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $giaoDichNganHang = $this->timGiaoDichChuyenKhoan($maGiaoDich);
        if ($giaoDichNganHang === null) {
            return [
                'item' => $pending,
                'matched' => false,
                'ma_giao_dich' => $maGiaoDich,
            ];
        }

        $thongTinBoSung = [
            'ma_giao_dich' => $maGiaoDich,
            'noi_dung_chuyen_khoan' => $maGiaoDich,
            'thanh_toan_edu_coin_id' => $giaoDichNganHang->id,
            'sao_ke_noi_dung' => $giaoDichNganHang->noi_dung,
            'sao_ke_so_tien' => (int) $giaoDichNganHang->so_tien,
            'sao_ke_thoi_gian' => $giaoDichNganHang->thoi_gian?->format('Y-m-d H:i:s'),
        ];

        if ($pending !== null) {
            $info = is_array($pending->thong_tin_thanh_toan) ? $pending->thong_tin_thanh_toan : [];
            $pending->ma_giao_dich = $maGiaoDich;
            $pending->trang_thai = TrangThaiThanhToanTracNghiem::DaHoanThanh;
            $pending->thong_tin_thanh_toan = [...$info, ...$thongTinBoSung];
            $pending->save();

            $this->markPhienDaThanhToan($phien, $pending);

            return [
                'item' => $pending,
                'matched' => true,
                'ma_giao_dich' => $maGiaoDich,
            ];
        }

        $thongTin = $this->buildThongTinThanhToanTuNganHang($nganHangThanhToanId, $maGiaoDich);

        $item = TracNghiemLichSuThanhToan::query()->create([
            'lich_su_phien_id' => $phien->id,
            'nguoi_dung_id' => $user->id,
            'ma_giao_dich' => $maGiaoDich,
            'hinh_thuc_thanh_toan' => HinhThucThanhToanTracNghiem::ChuyenKhoan,
            'so_tien_thanh_toan' => TracNghiemLichSuThanhToan::SO_TIEN_CHUYEN_KHOAN,
            'trang_thai' => TrangThaiThanhToanTracNghiem::DaHoanThanh,
            'thong_tin_thanh_toan' => [...$thongTin, ...$thongTinBoSung],
        ]);

        $this->markPhienDaThanhToan($phien, $item);

        return [
            'item' => $item,
            'matched' => true,
            'ma_giao_dich' => $maGiaoDich,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildThongTinThanhToanTuNganHang(?int $bankId, string $maGiaoDich): array
    {
        if ($bankId === null) {
            return [
                'ma_giao_dich' => $maGiaoDich,
                'noi_dung_chuyen_khoan' => $maGiaoDich,
            ];
        }

        $bank = NganHangThanhToan::query()
            ->where('id', $bankId)
            ->where('trang_thai', TrangThaiNganHangThanhToan::DangSuDung)
            ->first();

        if ($bank === null) {
            return [
                'ma_giao_dich' => $maGiaoDich,
                'noi_dung_chuyen_khoan' => $maGiaoDich,
            ];
        }

        return $this->buildThongTinThanhToan($bank, $maGiaoDich);
    }

    private function timGiaoDichChuyenKhoan(string $maGiaoDich): ?TracNghiemLichSuThanhToanEduCoin
    {
        $codes = $this->maGiaoDichLookupValues($maGiaoDich);
        if ($codes === []) {
            return null;
        }

        return TracNghiemLichSuThanhToanEduCoin::query()
            ->whereIn('noi_dung', $codes)
            ->where('so_tien', TracNghiemLichSuThanhToan::SO_TIEN_CHUYEN_KHOAN)
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return list<string>
     */
    private function maGiaoDichLookupValues(string $maGiaoDich): array
    {
        $canonical = MaGiaoDich::canonicalizePay($maGiaoDich);
        if ($canonical === null) {
            $normalized = MaGiaoDich::normalize($maGiaoDich);

            return $normalized === '' ? [] : [$normalized];
        }

        $values = [$canonical];
        if (MaGiaoDich::isValidPay($canonical)) {
            $values[] = substr(
                $canonical,
                0,
                strlen(MaGiaoDich::PREFIX_PAY) + MaGiaoDich::TOKEN_LENGTH,
            );
        }

        return array_values(array_unique($values));
    }

    /**
     * @return array{item?: TracNghiemLichSuThanhToan, so_du_edu_coin?: int, error?: string}
     */
    private function thanhToanEduCoin(
        User $user,
        TracNghiemPhienDaHoanThanh $phien,
        string $matKhauThanhToan,
    ): array {
        $profile = NguoiDung::query()
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();

        if ($profile === null) {
            $profile = NguoiDung::query()->create([
                'user_id' => $user->id,
                'edu_coin' => 0,
                'xu_he_thong' => 0,
            ]);
        }

        $matKhauDaLuu = (string) ($profile->mat_khau_thanh_toan ?? '');
        if ($matKhauDaLuu === '') {
            return ['error' => 'Chưa thiết lập mật khẩu thanh toán. Vui lòng liên hệ quản trị viên.'];
        }
        if ($matKhauThanhToan === '' || ! Hash::check($matKhauThanhToan, $matKhauDaLuu)) {
            return ['error' => 'Mật khẩu thanh toán không đúng.'];
        }

        $soDu = (int) $profile->edu_coin;
        $phi = TracNghiemLichSuThanhToan::SO_EDU_COIN;
        if ($soDu < $phi) {
            return ['error' => 'Số dư Edu Coin không đủ. Vui lòng nạp thêm để thanh toán.'];
        }

        $soDuSau = $soDu - $phi;
        $profile->edu_coin = $soDuSau;
        $profile->save();

        $maGiaoDich = $phien->ensureMaGiaoDich();

        $item = TracNghiemLichSuThanhToan::query()->create([
            'lich_su_phien_id' => $phien->id,
            'nguoi_dung_id' => $user->id,
            'ma_giao_dich' => $maGiaoDich,
            'hinh_thuc_thanh_toan' => HinhThucThanhToanTracNghiem::EduCoin,
            'so_tien_thanh_toan' => $phi,
            'trang_thai' => TrangThaiThanhToanTracNghiem::DaHoanThanh,
            'thong_tin_thanh_toan' => [
                'so_du_truoc' => $soDu,
                'so_du_sau' => $soDuSau,
                'ma_giao_dich' => $maGiaoDich,
            ],
        ]);

        LichSuNapEduCoin::query()->create([
            'nguoi_nap_id' => $user->id,
            'nguoi_duyet_id' => null,
            'nguoi_tao_id' => $user->id,
            'ma_giao_dich' => $maGiaoDich,
            'loai_giao_dich' => LoaiGiaoDich::ThanhToanTracNghiem,
            'so_du_truoc_gd' => $soDu,
            'so_du_sau_gd' => $soDuSau,
            'so_coin_gd' => $phi,
            'so_tien_thanh_toan' => 0,
            'loai_khuyen_mai' => null,
            'coin_khuyen_mai' => 0,
            'tong_coin_nhan' => 0,
            'kenh_thanh_toan' => KenhThanhToan::EduCoin,
            'thong_tin_thanh_toan' => [
                'lich_su_phien_id' => $phien->id,
                'thanh_toan_id' => $item->id,
                'ma_giao_dich' => $maGiaoDich,
            ],
            'ghi_chu' => 'Thanh toán kết quả trắc nghiệm',
            'trang_thai' => TrangThaiNapEduCoin::DaDuyet,
        ]);

        $this->markPhienDaThanhToan($phien, $item);

        return [
            'item' => $item,
            'so_du_edu_coin' => (int) $profile->edu_coin,
        ];
    }

    /**
     * @return array{item?: TracNghiemLichSuThanhToan, error?: string}
     */
    private function taoYeuCauChuyenKhoan(
        User $user,
        TracNghiemPhienDaHoanThanh $phien,
        int $bankId,
        ?string $maGiaoDichCandidate = null,
    ): array {
        $bank = NganHangThanhToan::query()
            ->where('id', $bankId)
            ->where('trang_thai', TrangThaiNganHangThanhToan::DangSuDung)
            ->first();

        if ($bank === null) {
            return ['error' => 'Ngân hàng không khả dụng để thanh toán.'];
        }

        $maGiaoDich = $phien->ensureMaGiaoDich($maGiaoDichCandidate);

        $item = TracNghiemLichSuThanhToan::query()->create([
            'lich_su_phien_id' => $phien->id,
            'nguoi_dung_id' => $user->id,
            'ma_giao_dich' => $maGiaoDich,
            'hinh_thuc_thanh_toan' => HinhThucThanhToanTracNghiem::ChuyenKhoan,
            'so_tien_thanh_toan' => TracNghiemLichSuThanhToan::SO_TIEN_CHUYEN_KHOAN,
            'trang_thai' => TrangThaiThanhToanTracNghiem::DangXuLy,
            'thong_tin_thanh_toan' => $this->buildThongTinThanhToan($bank, $maGiaoDich),
        ]);

        return ['item' => $item];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildThongTinThanhToan(NganHangThanhToan $bank, string $maGiaoDich): array
    {
        return [
            'ngan_hang_thanh_toan_id' => $bank->id,
            'ten_ngan_hang' => $bank->ten_ngan_hang,
            'ten_viet_tat' => $bank->ten_viet_tat,
            'so_tai_khoan' => $bank->so_tai_khoan,
            'chu_tai_khoan' => $bank->chu_tai_khoan,
            'chi_nhanh' => $bank->chi_nhanh,
            'ma_giao_dich' => $maGiaoDich,
            'noi_dung_chuyen_khoan' => $maGiaoDich,
        ];
    }

    private function isPhienDaThanhToan(TracNghiemPhienDaHoanThanh $phien): bool
    {
        if (! empty($phien->thong_tin_thanh_toan)) {
            return true;
        }

        return TracNghiemLichSuThanhToan::query()
            ->where('lich_su_phien_id', $phien->id)
            ->where('trang_thai', TrangThaiThanhToanTracNghiem::DaHoanThanh)
            ->exists();
    }

    private function markPhienDaThanhToan(
        TracNghiemPhienDaHoanThanh $phien,
        TracNghiemLichSuThanhToan $thanhToan,
    ): void {
        $info = is_array($thanhToan->thong_tin_thanh_toan) ? $thanhToan->thong_tin_thanh_toan : [];
        $hinhThuc = $thanhToan->hinh_thuc_thanh_toan instanceof HinhThucThanhToanTracNghiem
            ? $thanhToan->hinh_thuc_thanh_toan->value
            : (string) $thanhToan->hinh_thuc_thanh_toan;

        $phien->thong_tin_thanh_toan = [
            ...$info,
            'thanh_toan_id' => $thanhToan->id,
            'ma_giao_dich' => $thanhToan->ma_giao_dich,
            'hinh_thuc_thanh_toan' => $hinhThuc,
            'so_tien_thanh_toan' => (int) $thanhToan->so_tien_thanh_toan,
            'thanh_toan_luc' => now()->toIso8601String(),
        ];
        $phien->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(TracNghiemLichSuThanhToan $item): array
    {
        $hinhThuc = $item->hinh_thuc_thanh_toan instanceof HinhThucThanhToanTracNghiem
            ? $item->hinh_thuc_thanh_toan
            : HinhThucThanhToanTracNghiem::tryFrom((string) $item->hinh_thuc_thanh_toan);

        $trangThai = $item->trang_thai instanceof TrangThaiThanhToanTracNghiem
            ? $item->trang_thai
            : TrangThaiThanhToanTracNghiem::tryFrom((string) $item->trang_thai);

        return [
            'id' => $item->id,
            'lich_su_phien_id' => $item->lich_su_phien_id,
            'ma_giao_dich' => $item->ma_giao_dich,
            'hinh_thuc_thanh_toan' => $hinhThuc?->value,
            'hinh_thuc_thanh_toan_label' => $hinhThuc?->label(),
            'so_tien_thanh_toan' => (int) $item->so_tien_thanh_toan,
            'trang_thai' => $trangThai?->value ?? TrangThaiThanhToanTracNghiem::DangXuLy->value,
            'trang_thai_label' => $trangThai?->label() ?? TrangThaiThanhToanTracNghiem::DangXuLy->label(),
            'thong_tin_thanh_toan' => $item->thong_tin_thanh_toan,
            'created_at' => $item->created_at?->toIso8601String(),
        ];
    }
}
