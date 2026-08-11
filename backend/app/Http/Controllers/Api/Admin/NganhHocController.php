<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiNganhHoc;
use App\Http\Controllers\Controller;
use App\Models\NganhHoc;
use App\Models\NhomNganh;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NganhHocController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));
            $nhomNganhId = $request->query('nhom_nganh_id');

            $query = NganhHoc::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_nganh', 'like', "%{$keyword}%")
                            ->orWhere('ma_nganh', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiNganhHoc::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->when(
                    $nhomNganhId !== null && $nhomNganhId !== '',
                    function ($query) use ($nhomNganhId) {
                        $id = (int) $nhomNganhId;
                        $query->where(function ($q) use ($id) {
                            $q->whereJsonContains('nhom_nganh_ids', $id)
                                ->orWhereJsonContains('nhom_nganh_ids', (string) $id);
                        });
                    },
                )
                ->orderBy('ten_nganh');

            $page = OffsetPaginator::paginate($query, $request);
            $page['data'] = collect($page['data'])
                ->map(fn (NganhHoc $item) => $this->enrichItem($item))
                ->values()
                ->all();

            return ApiResponse::success($page['data'], 'Lấy danh sách ngành học thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(NganhHoc $nganhHoc): JsonResponse
    {
        return $this->tryApi(function () use ($nganhHoc) {
            return ApiResponse::success(
                $this->enrichItem($nganhHoc),
                'Lấy chi tiết ngành học thành công.',
            );
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_nganh' => ['required', 'string', 'max:255'],
                'ma_nganh' => ['required', 'string', 'max:50', 'unique:danh_muc_nganh_hoc,ma_nganh'],
                'ghi_chu' => ['nullable', 'string'],
                'nhom_nganh_ids' => ['nullable', 'array'],
                'nhom_nganh_ids.*' => ['integer', 'distinct', 'exists:danh_muc_nhom_nganh,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiNganhHoc::class)],
            ]);

            $validated['nhom_nganh_ids'] = $this->normalizeNhomNganhIds($validated['nhom_nganh_ids'] ?? []);

            $item = NganhHoc::create($validated);

            return ApiResponse::success(
                $this->enrichItem($item),
                'Tạo ngành học thành công.',
            );
        });
    }

    public function update(Request $request, NganhHoc $nganhHoc): JsonResponse
    {
        return $this->tryApi(function () use ($request, $nganhHoc) {
            $validated = $request->validate([
                'ten_nganh' => ['sometimes', 'string', 'max:255'],
                'ma_nganh' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('danh_muc_nganh_hoc', 'ma_nganh')->ignore($nganhHoc->id),
                ],
                'ghi_chu' => ['nullable', 'string'],
                'nhom_nganh_ids' => ['nullable', 'array'],
                'nhom_nganh_ids.*' => ['integer', 'distinct', 'exists:danh_muc_nhom_nganh,id'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiNganhHoc::class)],
            ]);

            if (array_key_exists('nhom_nganh_ids', $validated)) {
                $validated['nhom_nganh_ids'] = $this->normalizeNhomNganhIds($validated['nhom_nganh_ids'] ?? []);
            }

            $nganhHoc->update($validated);

            return ApiResponse::success(
                $this->enrichItem($nganhHoc->fresh()),
                'Cập nhật ngành học thành công.',
            );
        });
    }

    public function destroy(NganhHoc $nganhHoc): JsonResponse
    {
        return $this->tryApi(function () use ($nganhHoc) {
            $nganhHoc->delete();

            return ApiResponse::success(null, 'Đã xóa ngành học.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_nganh_hoc,id'],
            ]);

            $count = NganhHoc::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} ngành học.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_nganh_hoc,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiNganhHoc::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiNganhHoc
                ? $validated['trang_thai']
                : TrangThaiNganhHoc::from($validated['trang_thai']);

            $count = NganhHoc::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} ngành học.",
            );
        });
    }

    /**
     * @param  list<int|string>|mixed  $ids
     * @return list<int>
     */
    private function normalizeNhomNganhIds(mixed $ids): array
    {
        if (! is_array($ids)) {
            return [];
        }

        $normalized = array_values(array_unique(array_map('intval', $ids)));

        if ($normalized === []) {
            return [];
        }

        $validIds = NhomNganh::query()
            ->whereIn('id', $normalized)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $invalid = array_values(array_diff($normalized, $validIds));
        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'nhom_nganh_ids' => ['Một số nhóm ngành không hợp lệ.'],
            ]);
        }

        return $validIds;
    }

    /**
     * @return array<string, mixed>
     */
    private function enrichItem(NganhHoc $item): array
    {
        $ids = array_values(array_map('intval', $item->nhom_nganh_ids ?? []));
        $nhomNganhs = $ids === []
            ? collect()
            : NhomNganh::query()
                ->whereIn('id', $ids)
                ->get(['id', 'ten_nhom_nganh', 'trang_thai'])
                ->sortBy(fn (NhomNganh $row) => array_search($row->id, $ids, true))
                ->values();

        $data = $item->toArray();
        $data['nhom_nganh_ids'] = $ids;
        $data['nhom_nganhs'] = $nhomNganhs->all();

        return $data;
    }
}
