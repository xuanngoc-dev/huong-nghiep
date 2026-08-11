<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiNhomNganh;
use App\Http\Controllers\Controller;
use App\Models\NganhHoc;
use App\Models\NhomNganh;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NhomNganhController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));

            $query = NhomNganh::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_nhom_nganh', 'like', "%{$keyword}%")
                            ->orWhere('mo_ta', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiNhomNganh::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->orderBy('ten_nhom_nganh');

            $page = OffsetPaginator::paginate($query, $request);
            $page['data'] = $this->attachSoLuongNganh(collect($page['data']));

            return ApiResponse::success($page['data'], 'Lấy danh sách nhóm ngành thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(NhomNganh $nhomNganh): JsonResponse
    {
        return $this->tryApi(function () use ($nhomNganh) {
            return ApiResponse::success(
                $this->attachSoLuongNganh(collect([$nhomNganh]))[0],
                'Lấy chi tiết nhóm ngành thành công.',
            );
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_nhom_nganh' => ['required', 'string', 'max:255'],
                'mo_ta' => ['nullable', 'string'],
                'trang_thai' => ['required', Rule::enum(TrangThaiNhomNganh::class)],
            ]);

            $item = NhomNganh::create($validated);

            return ApiResponse::success(
                $this->attachSoLuongNganh(collect([$item]))[0],
                'Tạo nhóm ngành thành công.',
            );
        });
    }

    public function update(Request $request, NhomNganh $nhomNganh): JsonResponse
    {
        return $this->tryApi(function () use ($request, $nhomNganh) {
            $validated = $request->validate([
                'ten_nhom_nganh' => ['sometimes', 'string', 'max:255'],
                'mo_ta' => ['nullable', 'string'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiNhomNganh::class)],
            ]);

            $nhomNganh->update($validated);

            return ApiResponse::success(
                $this->attachSoLuongNganh(collect([$nhomNganh->fresh()]))[0],
                'Cập nhật nhóm ngành thành công.',
            );
        });
    }

    /**
     * @param  Collection<int, NhomNganh>  $items
     * @return list<array<string, mixed>>
     */
    private function attachSoLuongNganh(Collection $items): array
    {
        $ids = $items->pluck('id')->map(fn ($id) => (int) $id)->all();
        $counts = array_fill_keys($ids, 0);

        if ($ids !== []) {
            NganhHoc::query()
                ->get(['nhom_nganh_ids'])
                ->each(function (NganhHoc $nganh) use (&$counts) {
                    foreach ($nganh->nhom_nganh_ids ?? [] as $nhomId) {
                        $nhomId = (int) $nhomId;
                        if (array_key_exists($nhomId, $counts)) {
                            $counts[$nhomId]++;
                        }
                    }
                });
        }

        return $items
            ->map(function (NhomNganh $item) use ($counts) {
                $data = $item->toArray();
                $data['so_luong_nganh'] = $counts[(int) $item->id] ?? 0;

                return $data;
            })
            ->values()
            ->all();
    }

    public function destroy(NhomNganh $nhomNganh): JsonResponse
    {
        return $this->tryApi(function () use ($nhomNganh) {
            $this->assertNhomNganhDeletable([(int) $nhomNganh->id]);

            $nhomNganh->delete();

            return ApiResponse::success(null, 'Đã xóa nhóm ngành.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_nhom_nganh,id'],
            ]);

            $ids = array_values(array_map('intval', $validated['ids']));
            $this->assertNhomNganhDeletable($ids);

            $count = NhomNganh::query()->whereIn('id', $ids)->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} nhóm ngành.",
            );
        });
    }

    /**
     * Chặn xóa khi còn ngành học thuộc nhóm ngành (quan hệ 1-N qua nhom_nganh_ids).
     *
     * @param  list<int>  $nhomNganhIds
     */
    private function assertNhomNganhDeletable(array $nhomNganhIds): void
    {
        $nhomNganhIds = array_values(array_unique(array_filter(
            array_map('intval', $nhomNganhIds),
            fn (int $id) => $id > 0,
        )));

        if ($nhomNganhIds === []) {
            return;
        }

        $linkedIds = [];
        NganhHoc::query()
            ->get(['nhom_nganh_ids'])
            ->each(function (NganhHoc $nganh) use ($nhomNganhIds, &$linkedIds) {
                foreach ($nganh->nhom_nganh_ids ?? [] as $nhomId) {
                    $nhomId = (int) $nhomId;
                    if (in_array($nhomId, $nhomNganhIds, true)) {
                        $linkedIds[$nhomId] = true;
                    }
                }
            });

        if ($linkedIds === []) {
            return;
        }

        $blocked = NhomNganh::query()
            ->whereIn('id', array_keys($linkedIds))
            ->orderBy('ten_nhom_nganh')
            ->get(['id', 'ten_nhom_nganh']);

        if ($blocked->count() === 1) {
            $ten = $blocked->first()->ten_nhom_nganh;

            throw ValidationException::withMessages([
                'ids' => ["Không thể xóa nhóm ngành «{$ten}» vì vẫn còn ngành học thuộc nhóm này."],
            ]);
        }

        $tenList = $blocked->pluck('ten_nhom_nganh')->implode(', ');

        throw ValidationException::withMessages([
            'ids' => ["Không thể xóa các nhóm ngành sau vì vẫn còn ngành học liên kết: {$tenList}."],
        ]);
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_nhom_nganh,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiNhomNganh::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiNhomNganh
                ? $validated['trang_thai']
                : TrangThaiNhomNganh::from($validated['trang_thai']);

            $count = NhomNganh::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} nhóm ngành.",
            );
        });
    }
}
