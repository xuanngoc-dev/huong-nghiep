<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OffsetPaginator
{
    /**
     * Đọc start/limit từ query string (dùng chung cho các API list).
     *
     * @return array{start: int, limit: int}
     */
    public static function fromRequest(Request $request, int $defaultLimit = 10, int $maxLimit = 100): array
    {
        $start = max(0, (int) $request->query('start', 0));
        $limit = (int) $request->query('limit', $defaultLimit);
        $limit = max(1, min($limit, $maxLimit));

        return [
            'start' => $start,
            'limit' => $limit,
        ];
    }

    /**
     * Phân trang offset-based: trả về data + total (+ start/limit).
     *
     * @return array{data: Collection, total: int, start: int, limit: int}
     */
    public static function paginate(Builder $query, Request $request, int $defaultLimit = 10, int $maxLimit = 100): array
    {
        ['start' => $start, 'limit' => $limit] = self::fromRequest($request, $defaultLimit, $maxLimit);

        $total = (clone $query)->count();
        $data = (clone $query)->skip($start)->take($limit)->get();

        return [
            'data' => $data,
            'total' => $total,
            'start' => $start,
            'limit' => $limit,
        ];
    }
}
