<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonHoc;
use App\Models\ToHopMonHoc;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ToHopMonHocController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));

            $query = ToHopMonHoc::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_to_hop', 'like', "%{$keyword}%")
                            ->orWhere('ghi_chu', 'like', "%{$keyword}%");
                    });
                })
                ->orderBy('ten_to_hop');

            $page = OffsetPaginator::paginate($query, $request);
            $page['data'] = collect($page['data'])
                ->map(fn (ToHopMonHoc $item) => $this->enrichItem($item))
                ->values()
                ->all();

            return ApiResponse::success($page['data'], 'Lấy danh sách tổ hợp môn học thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(ToHopMonHoc $toHopMonHoc): JsonResponse
    {
        return $this->tryApi(function () use ($toHopMonHoc) {
            return ApiResponse::success(
                $this->enrichItem($toHopMonHoc),
                'Lấy chi tiết tổ hợp môn học thành công.',
            );
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $this->validatePayload($request);
            $validated['mon_hoc_ids'] = $this->normalizeMonHocIds($validated['mon_hoc_ids'] ?? []);

            $item = ToHopMonHoc::create($validated);

            return ApiResponse::success(
                $this->enrichItem($item),
                'Tạo tổ hợp môn học thành công.',
            );
        });
    }

    public function update(Request $request, ToHopMonHoc $toHopMonHoc): JsonResponse
    {
        return $this->tryApi(function () use ($request, $toHopMonHoc) {
            $validated = $this->validatePayload($request, true);

            if (array_key_exists('mon_hoc_ids', $validated)) {
                $validated['mon_hoc_ids'] = $this->normalizeMonHocIds($validated['mon_hoc_ids'] ?? []);
            }

            $toHopMonHoc->update($validated);

            return ApiResponse::success(
                $this->enrichItem($toHopMonHoc->fresh()),
                'Cập nhật tổ hợp môn học thành công.',
            );
        });
    }

    public function destroy(ToHopMonHoc $toHopMonHoc): JsonResponse
    {
        return $this->tryApi(function () use ($toHopMonHoc) {
            $toHopMonHoc->delete();

            return ApiResponse::success(null, 'Đã xóa tổ hợp môn học.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_to_hop_mon_hoc,id'],
            ]);

            $count = ToHopMonHoc::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} tổ hợp môn học.",
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'ten_to_hop' => [$required, 'string', 'max:255'],
            'mon_hoc_ids' => ['nullable', 'array'],
            'mon_hoc_ids.*' => ['integer', 'distinct', 'exists:danh_muc_mon_hoc,id'],
            'ghi_chu' => ['nullable', 'string'],
        ]);
    }

    /**
     * @param  list<int|string>|mixed  $ids
     * @return list<int>
     */
    private function normalizeMonHocIds(mixed $ids): array
    {
        if (! is_array($ids)) {
            return [];
        }

        $normalized = array_values(array_unique(array_map('intval', $ids)));

        if ($normalized === []) {
            return [];
        }

        $validIds = MonHoc::query()
            ->whereIn('id', $normalized)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $invalid = array_values(array_diff($normalized, $validIds));
        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'mon_hoc_ids' => ['Một số môn học không hợp lệ.'],
            ]);
        }

        return $validIds;
    }

    /**
     * @return array<string, mixed>
     */
    private function enrichItem(ToHopMonHoc $item): array
    {
        $ids = array_values(array_map('intval', $item->mon_hoc_ids ?? []));
        $monHocs = $ids === []
            ? collect()
            : MonHoc::query()
                ->whereIn('id', $ids)
                ->get(['id', 'ma_mon_hoc', 'ten_mon_hoc'])
                ->sortBy(fn (MonHoc $row) => array_search($row->id, $ids, true))
                ->values();

        $data = $item->toArray();
        $data['mon_hocs'] = $monHocs->all();

        return $data;
    }
}
