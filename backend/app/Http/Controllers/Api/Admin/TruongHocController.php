<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiTruongHoc;
use App\Http\Controllers\Controller;
use App\Models\TruongHoc;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TruongHocController extends Controller
{
    private const RELATION_LOAD = [
        'loaiHinhTruong:id,ma_loai_truong,ten_loai_truong',
        'heDaoTao:id,ma_he_dao_tao,ten_he_dao_tao',
        'tinhThanh:id,ma_tinh_thanh,ten_tinh_thanh',
    ];

    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));
            $loaiHinhId = $request->query('loai_hinh_truong_id');
            $heDaoTaoId = $request->query('he_dao_tao_id');
            $tinhThanhId = $request->query('tinh_thanh_id');

            $query = TruongHoc::query()
                ->with(self::RELATION_LOAD)
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_truong', 'like', "%{$keyword}%")
                            ->orWhere('ten_truong_tieng_anh', 'like', "%{$keyword}%")
                            ->orWhere('ma_truong', 'like', "%{$keyword}%")
                            ->orWhere('slug_ten_truong', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiTruongHoc::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->when(
                    filled($loaiHinhId) && is_numeric($loaiHinhId),
                    fn ($query) => $query->where('loai_hinh_truong_id', (int) $loaiHinhId),
                )
                ->when(
                    filled($heDaoTaoId) && is_numeric($heDaoTaoId),
                    fn ($query) => $query->where('he_dao_tao_id', (int) $heDaoTaoId),
                )
                ->when(
                    filled($tinhThanhId) && is_numeric($tinhThanhId),
                    fn ($query) => $query->where('tinh_thanh_id', (int) $tinhThanhId),
                )
                ->orderBy('thu_tu')
                ->orderBy('ten_truong');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách trường học thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(TruongHoc $truongHoc): JsonResponse
    {
        return $this->tryApi(function () use ($truongHoc) {
            $truongHoc->load(self::RELATION_LOAD);

            return ApiResponse::success($truongHoc, 'Lấy chi tiết trường học thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $this->validatePayload($request);
            $validated = $this->normalizeSlug($validated);

            $item = TruongHoc::create($validated);
            $item->load(self::RELATION_LOAD);

            return ApiResponse::success($item, 'Tạo trường học thành công.');
        });
    }

    public function update(Request $request, TruongHoc $truongHoc): JsonResponse
    {
        return $this->tryApi(function () use ($request, $truongHoc) {
            $validated = $this->validatePayload($request, $truongHoc);
            $validated = $this->normalizeSlug($validated, $truongHoc);

            $truongHoc->update($validated);

            return ApiResponse::success(
                $truongHoc->fresh(self::RELATION_LOAD),
                'Cập nhật trường học thành công.',
            );
        });
    }

    public function destroy(TruongHoc $truongHoc): JsonResponse
    {
        return $this->tryApi(function () use ($truongHoc) {
            $truongHoc->delete();

            return ApiResponse::success(null, 'Đã xóa trường học.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_truong_hoc,id'],
            ]);

            $count = TruongHoc::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} trường học.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_truong_hoc,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiTruongHoc::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiTruongHoc
                ? $validated['trang_thai']
                : TrangThaiTruongHoc::from($validated['trang_thai']);

            $count = TruongHoc::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} trường học.",
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?TruongHoc $truongHoc = null): array
    {
        $isUpdate = $truongHoc !== null;
        $required = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'ten_truong' => [$required, 'string', 'max:255'],
            'ten_truong_tieng_anh' => ['nullable', 'string', 'max:255'],
            'slug_ten_truong' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('danh_muc_truong_hoc', 'slug_ten_truong')->ignore($truongHoc?->id),
            ],
            'ma_truong' => [
                $required,
                'string',
                'max:50',
                Rule::unique('danh_muc_truong_hoc', 'ma_truong')->ignore($truongHoc?->id),
            ],
            'loai_hinh_truong_id' => ['nullable', 'integer', 'exists:danh_muc_loai_truong,id'],
            'he_dao_tao_id' => ['nullable', 'integer', 'exists:danh_muc_he_dao_tao,id'],
            'tinh_thanh_id' => ['nullable', 'integer', 'exists:danh_muc_tinh_thanh,id'],
            'nam_hoc' => ['nullable', 'string', 'max:20'],
            'nam_thanh_lap' => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'so_dien_thoai' => ['nullable', 'string', 'max:30'],
            'hotline' => ['nullable', 'string', 'max:30'],
            'fax' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'nguoi_dai_dien' => ['nullable', 'string', 'max:255'],
            'ma_so_thue' => ['nullable', 'string', 'max:50'],
            'dia_chi' => ['nullable', 'string', 'max:500'],
            'ghi_chu' => ['nullable', 'string'],
            'mo_ta_thong_tin_tuyen_sinh' => ['nullable', 'string'],
            'thu_tu' => ['nullable', 'integer', 'min:0'],
            'trang_thai' => [$required, Rule::enum(TrangThaiTruongHoc::class)],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeSlug(array $validated, ?TruongHoc $truongHoc = null): array
    {
        $slug = trim((string) ($validated['slug_ten_truong'] ?? ''));
        $ten = trim((string) ($validated['ten_truong'] ?? $truongHoc?->ten_truong ?? ''));

        if ($slug === '' && $ten !== '') {
            $slug = Str::slug($ten);
            if ($slug === '') {
                $slug = 'truong-'.Str::lower(Str::random(8));
            }
        }

        if ($slug !== '') {
            $base = $slug;
            $i = 1;
            while (
                TruongHoc::query()
                    ->when($truongHoc, fn ($q) => $q->where('id', '!=', $truongHoc->id))
                    ->where('slug_ten_truong', $slug)
                    ->exists()
            ) {
                $slug = $base.'-'.$i;
                $i++;
            }
            $validated['slug_ten_truong'] = $slug;
        }

        return $validated;
    }
}
