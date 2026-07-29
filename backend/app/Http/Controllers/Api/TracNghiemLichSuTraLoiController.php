<?php

namespace App\Http\Controllers\Api;

use App\Enums\TrangThaiLoaiCauHoi;
use App\Enums\TrangThaiTracNghiemCauHoi;
use App\Http\Controllers\Controller;
use App\Models\LoaiCauHoi;
use App\Models\TracNghiemCauHoi;
use App\Models\TracNghiemCauTraLoi;
use App\Models\TracNghiemLichSuTraLoi;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TracNghiemLichSuTraLoiController extends Controller
{
    private const QUESTIONS_PER_LOAI = 10;

    /**
     * Bắt đầu phiên làm bài:
     * - Tạo ssid
     * - Random đủ câu hỏi cho mọi loại đang dùng
     * - Ghi đầy đủ vào lịch sử (chưa có đáp án)
     */
    public function start(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            ]);

            $limit = (int) ($validated['limit'] ?? self::QUESTIONS_PER_LOAI);
            $userId = $request->user('sanctum')?->id;

            $loaiList = LoaiCauHoi::query()
                ->where('trang_thai', TrangThaiLoaiCauHoi::DangSuDung)
                ->orderBy('thu_tu_uu_tien')
                ->orderBy('id')
                ->get(['id', 'ma_loai_cau_hoi', 'ten_loai_cau_hoi', 'thu_tu_uu_tien']);

            if ($loaiList->isEmpty()) {
                return ApiResponse::error('Chưa có loại câu hỏi khả dụng.');
            }

            $ssid = (string) Str::uuid();
            $now = now();
            $historyRows = [];
            $missingLoai = [];

            foreach ($loaiList as $loai) {
                $questions = TracNghiemCauHoi::query()
                    ->where('loai_cau_hoi_id', $loai->id)
                    ->where('trang_thai', TrangThaiTracNghiemCauHoi::DangSuDung)
                    ->inRandomOrder()
                    ->limit($limit)
                    ->get([
                        'id',
                        'loai_cau_hoi_id',
                        'nganh_hoc_id',
                        'chuyen_nganh_id',
                        'noi_dung_cau_hoi',
                    ]);

                if ($questions->isEmpty()) {
                    $missingLoai[] = $loai->ten_loai_cau_hoi ?: $loai->ma_loai_cau_hoi;
                    continue;
                }

                foreach ($questions as $question) {
                    $historyRows[] = [
                        'ssid' => $ssid,
                        'ma_loai_cau_hoi' => strtolower(trim((string) $loai->ma_loai_cau_hoi)),
                        'nguoi_dung_id' => $userId,
                        'cau_hoi_id' => $question->id,
                        'cau_tra_loi_id' => null,
                        'nganh_hoc_id' => $question->nganh_hoc_id,
                        'chuyen_nganh_id' => $question->chuyen_nganh_id,
                        'diem_so' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (! count($historyRows)) {
                return ApiResponse::error(
                    $missingLoai
                        ? 'Không có câu hỏi khả dụng cho các loại: '.implode(', ', $missingLoai).'.'
                        : 'Không tạo được bộ câu hỏi cho phiên làm bài.',
                );
            }

            DB::transaction(function () use ($historyRows) {
                foreach (array_chunk($historyRows, 500) as $chunk) {
                    TracNghiemLichSuTraLoi::query()->insert($chunk);
                }
            });

            $payload = $this->buildSessionPayload($ssid);

            return ApiResponse::success(
                [
                    'ssid' => $ssid,
                    'nguoi_dung_id' => $userId,
                    'question_count' => count($historyRows),
                    ...$payload,
                ],
                'Bắt đầu phiên trắc nghiệm thành công.',
                httpStatus: 201,
            );
        });
    }

    /**
     * Lấy lịch sử trả lời theo ssid — kèm câu hỏi đã gán cho phiên.
     * Có thể lọc theo ma_loai_cau_hoi (vd: stdm).
     */
    public function show(Request $request, string $ssid): JsonResponse
    {
        return $this->tryApi(function () use ($request, $ssid) {
            $ssid = trim($ssid);
            $validated = $request->validate([
                'ma_loai_cau_hoi' => ['nullable', 'string', 'max:50'],
            ]);
            $maLoai = isset($validated['ma_loai_cau_hoi'])
                ? strtolower(trim($validated['ma_loai_cau_hoi']))
                : null;

            $sessionExists = TracNghiemLichSuTraLoi::query()
                ->where('ssid', $ssid)
                ->exists();

            if (! $sessionExists) {
                return ApiResponse::error('Không tìm thấy phiên trắc nghiệm.');
            }

            return ApiResponse::success(
                [
                    'ssid' => $ssid,
                    ...$this->buildSessionPayload($ssid, $maLoai ?: null),
                ],
                'Lấy lịch sử trả lời thành công.',
            );
        });
    }

    /**
     * Tổng hợp điểm theo nganh_hoc_id / chuyen_nganh_id của một phiên (ssid).
     * Chỉ tính các câu đã có đáp án (diem_so không null).
     */
    public function tongHop(string $ssid): JsonResponse
    {
        return $this->tryApi(function () use ($ssid) {
            $ssid = trim($ssid);

            $sessionExists = TracNghiemLichSuTraLoi::query()
                ->where('ssid', $ssid)
                ->exists();

            if (! $sessionExists) {
                return ApiResponse::error('Không tìm thấy phiên trắc nghiệm.');
            }

            return ApiResponse::success(
                [
                    'ssid' => $ssid,
                    ...$this->buildTongHopPayload($ssid),
                ],
                'Tổng hợp ngành / chuyên ngành phù hợp thành công.',
            );
        });
    }

    /**
     * Lưu các câu đã trả lời của bước hiện tại vào lịch sử (theo ssid).
     */
    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ssid' => ['required', 'string', 'max:64'],
                'answers' => ['required', 'array', 'min:1'],
                'answers.*.cau_hoi_id' => ['required', 'integer', 'exists:trac_nghiem_cau_hoi,id'],
                'answers.*.cau_tra_loi_id' => ['required', 'integer', 'exists:trac_nghiem_cau_tra_loi,id'],
            ]);

            $ssid = $validated['ssid'];
            $sessionExists = TracNghiemLichSuTraLoi::query()
                ->where('ssid', $ssid)
                ->exists();

            if (! $sessionExists) {
                return ApiResponse::error('Không tìm thấy phiên trắc nghiệm.');
            }

            $userId = $request->user('sanctum')?->id;
            $saved = [];

            DB::transaction(function () use ($validated, $ssid, $userId, &$saved) {
                foreach ($validated['answers'] as $item) {
                    $cauHoiId = (int) $item['cau_hoi_id'];
                    $cauTraLoiId = (int) $item['cau_tra_loi_id'];

                    $assigned = TracNghiemLichSuTraLoi::query()
                        ->where('ssid', $ssid)
                        ->where('cau_hoi_id', $cauHoiId)
                        ->exists();

                    if (! $assigned) {
                        continue;
                    }

                    $cauHoi = TracNghiemCauHoi::query()
                        ->whereKey($cauHoiId)
                        ->with('loaiCauHoi:id,ma_loai_cau_hoi')
                        ->first(['id', 'nganh_hoc_id', 'chuyen_nganh_id', 'loai_cau_hoi_id']);

                    if (! $cauHoi) {
                        continue;
                    }

                    $cauTraLoi = TracNghiemCauTraLoi::query()
                        ->whereKey($cauTraLoiId)
                        ->where('cau_hoi_id', $cauHoiId)
                        ->first(['id', 'diem']);

                    if (! $cauTraLoi) {
                        continue;
                    }

                    $maLoai = strtolower(trim((string) ($cauHoi->loaiCauHoi?->ma_loai_cau_hoi ?? '')));

                    $record = TracNghiemLichSuTraLoi::query()->updateOrCreate(
                        [
                            'ssid' => $ssid,
                            'cau_hoi_id' => $cauHoiId,
                        ],
                        [
                            'cau_tra_loi_id' => $cauTraLoi->id,
                            'nguoi_dung_id' => $userId,
                            'nganh_hoc_id' => $cauHoi->nganh_hoc_id,
                            'chuyen_nganh_id' => $cauHoi->chuyen_nganh_id,
                            'diem_so' => $cauTraLoi->diem,
                            'ma_loai_cau_hoi' => $maLoai !== '' ? $maLoai : null,
                        ],
                    );

                    $saved[] = [
                        'id' => $record->id,
                        'cau_hoi_id' => $record->cau_hoi_id,
                        'cau_tra_loi_id' => $record->cau_tra_loi_id,
                        'nganh_hoc_id' => $record->nganh_hoc_id,
                        'chuyen_nganh_id' => $record->chuyen_nganh_id,
                        'diem_so' => $record->diem_so,
                    ];
                }
            });

            if (! count($saved)) {
                return ApiResponse::error('Không lưu được câu trả lời hợp lệ.');
            }

            return ApiResponse::success(
                [
                    'ssid' => $ssid,
                    'saved_count' => count($saved),
                    'answers' => $saved,
                ],
                'Lưu lịch sử trả lời thành công.',
            );
        });
    }

    /**
     * @return array{
     *   tong_diem: float,
     *   so_cau_da_tra_loi: int,
     *   nganh_hoc: list<array<string, mixed>>,
     *   chuyen_nganh: list<array<string, mixed>>
     * }
     */
    private function buildTongHopPayload(string $ssid): array
    {
        $records = TracNghiemLichSuTraLoi::query()
            ->where('ssid', $ssid)
            ->whereNotNull('cau_tra_loi_id')
            ->whereNotNull('diem_so')
            ->with([
                'nganhHoc:id,ma_nganh,ten_nganh',
                'chuyenNganh:id,ma_chuyen_nganh,ten_chuyen_nganh,nganh_hoc_id',
            ])
            ->get([
                'id',
                'nganh_hoc_id',
                'chuyen_nganh_id',
                'diem_so',
            ]);

        $byNganh = [];
        $byChuyenNganh = [];
        $tongDiem = 0.0;

        foreach ($records as $record) {
            $diem = (float) ($record->diem_so ?? 0);
            $tongDiem += $diem;

            $nganhId = $record->nganh_hoc_id ? (int) $record->nganh_hoc_id : null;
            if ($nganhId) {
                if (! isset($byNganh[$nganhId])) {
                    $byNganh[$nganhId] = [
                        'nganh_hoc_id' => $nganhId,
                        'ma_nganh' => $record->nganhHoc?->ma_nganh,
                        'ten_nganh' => $record->nganhHoc?->ten_nganh,
                        'tong_diem' => 0.0,
                        'so_cau' => 0,
                    ];
                }
                $byNganh[$nganhId]['tong_diem'] += $diem;
                $byNganh[$nganhId]['so_cau']++;
            }

            $chuyenId = $record->chuyen_nganh_id ? (int) $record->chuyen_nganh_id : null;
            if ($chuyenId) {
                if (! isset($byChuyenNganh[$chuyenId])) {
                    $byChuyenNganh[$chuyenId] = [
                        'chuyen_nganh_id' => $chuyenId,
                        'nganh_hoc_id' => $record->chuyenNganh?->nganh_hoc_id
                            ? (int) $record->chuyenNganh->nganh_hoc_id
                            : $nganhId,
                        'ma_chuyen_nganh' => $record->chuyenNganh?->ma_chuyen_nganh,
                        'ten_chuyen_nganh' => $record->chuyenNganh?->ten_chuyen_nganh,
                        'ma_nganh' => $record->nganhHoc?->ma_nganh,
                        'ten_nganh' => $record->nganhHoc?->ten_nganh,
                        'tong_diem' => 0.0,
                        'so_cau' => 0,
                    ];
                }
                $byChuyenNganh[$chuyenId]['tong_diem'] += $diem;
                $byChuyenNganh[$chuyenId]['so_cau']++;
            }
        }

        $sortByScore = static function (array $a, array $b): int {
            $scoreCmp = $b['tong_diem'] <=> $a['tong_diem'];
            if ($scoreCmp !== 0) {
                return $scoreCmp;
            }

            return $b['so_cau'] <=> $a['so_cau'];
        };

        $nganhHoc = array_values($byNganh);
        usort($nganhHoc, $sortByScore);

        $chuyenNganh = array_values($byChuyenNganh);
        usort($chuyenNganh, $sortByScore);

        foreach ($nganhHoc as &$item) {
            $item['tong_diem'] = round((float) $item['tong_diem'], 2);
        }
        unset($item);

        foreach ($chuyenNganh as &$item) {
            $item['tong_diem'] = round((float) $item['tong_diem'], 2);
        }
        unset($item);

        return [
            'tong_diem' => round($tongDiem, 2),
            'so_cau_da_tra_loi' => $records->count(),
            'nganh_hoc' => $nganhHoc,
            'chuyen_nganh' => $chuyenNganh,
        ];
    }

    /**
     * @return array{
     *   by_loai: array<string, mixed>,
     *   completed_loai: list<string>,
     *   total_answered: int,
     *   total_questions: int,
     *   expected_per_loai: int
     * }
     */
    private function buildSessionPayload(string $ssid, ?string $maLoai = null): array
    {
        $query = TracNghiemLichSuTraLoi::query()
            ->where('ssid', $ssid)
            ->whereNotNull('cau_hoi_id');

        if ($maLoai) {
            $query->where(function ($q) use ($maLoai) {
                $q->whereRaw('LOWER(ma_loai_cau_hoi) = ?', [$maLoai])
                    ->orWhereHas('cauHoi.loaiCauHoi', function ($loaiQuery) use ($maLoai) {
                        $loaiQuery->whereRaw('LOWER(ma_loai_cau_hoi) = ?', [$maLoai]);
                    });
            });
        }

        $records = $query
            ->with([
                'cauHoi' => fn ($q) => $q->select([
                    'id',
                    'loai_cau_hoi_id',
                    'nganh_hoc_id',
                    'chuyen_nganh_id',
                    'noi_dung_cau_hoi',
                ]),
                'cauHoi.cauTraLois' => fn ($q) => $q
                    ->orderBy('diem')
                    ->orderBy('id')
                    ->select(['id', 'cau_hoi_id', 'noi_dung_cau_tra_loi', 'diem']),
                'cauHoi.loaiCauHoi' => fn ($q) => $q->select([
                    'id',
                    'ma_loai_cau_hoi',
                    'ten_loai_cau_hoi',
                ]),
            ])
            ->orderBy('id')
            ->get();

        return $this->formatRecordsByLoai($records);
    }

    /**
     * @param  Collection<int, TracNghiemLichSuTraLoi>  $records
     * @return array{
     *   by_loai: array<string, mixed>,
     *   completed_loai: list<string>,
     *   total_answered: int,
     *   total_questions: int,
     *   expected_per_loai: int
     * }
     */
    private function formatRecordsByLoai(Collection $records): array
    {
        $byLoai = [];
        $totalAnswered = 0;

        foreach ($records as $record) {
            $cauHoi = $record->cauHoi;
            $loai = $cauHoi?->loaiCauHoi;
            if (! $cauHoi) {
                continue;
            }

            $ma = strtolower(trim((string) (
                $record->ma_loai_cau_hoi
                ?: $loai?->ma_loai_cau_hoi
                ?: ''
            )));
            if ($ma === '') {
                continue;
            }

            if (! isset($byLoai[$ma])) {
                $byLoai[$ma] = [
                    'loai_cau_hoi_id' => $cauHoi->loai_cau_hoi_id,
                    'ma_loai_cau_hoi' => $ma,
                    'question_count' => 0,
                    'answered_count' => 0,
                    'questions' => [],
                    'answers' => [],
                ];
            }

            $questionExists = collect($byLoai[$ma]['questions'])
                ->contains(fn ($item) => (int) $item['id'] === (int) $cauHoi->id);

            if (! $questionExists) {
                $byLoai[$ma]['questions'][] = [
                    'id' => $cauHoi->id,
                    'loai_cau_hoi_id' => $cauHoi->loai_cau_hoi_id,
                    'nganh_hoc_id' => $cauHoi->nganh_hoc_id,
                    'chuyen_nganh_id' => $cauHoi->chuyen_nganh_id,
                    'noi_dung_cau_hoi' => $cauHoi->noi_dung_cau_hoi,
                    'cau_tra_lois' => $cauHoi->cauTraLois
                        ->map(fn ($answer) => [
                            'id' => $answer->id,
                            'cau_hoi_id' => $answer->cau_hoi_id,
                            'noi_dung_cau_tra_loi' => $answer->noi_dung_cau_tra_loi,
                            'diem' => $answer->diem,
                        ])
                        ->values()
                        ->all(),
                ];
                $byLoai[$ma]['question_count'] = count($byLoai[$ma]['questions']);
            }

            if ($record->cau_tra_loi_id == null) {
                continue;
            }

            $byLoai[$ma]['answers'][] = [
                'cau_hoi_id' => $record->cau_hoi_id,
                'cau_tra_loi_id' => $record->cau_tra_loi_id,
                'diem_so' => $record->diem_so,
                'nganh_hoc_id' => $record->nganh_hoc_id,
                'chuyen_nganh_id' => $record->chuyen_nganh_id,
            ];
            $byLoai[$ma]['answered_count'] = count($byLoai[$ma]['answers']);
            $totalAnswered++;
        }

        $completedLoai = [];
        foreach ($byLoai as $ma => $group) {
            $questionCount = (int) $group['question_count'];
            if ($questionCount > 0 && (int) $group['answered_count'] >= $questionCount) {
                $completedLoai[] = $ma;
            }
        }

        return [
            'by_loai' => $byLoai,
            'completed_loai' => $completedLoai,
            'total_answered' => $totalAnswered,
            'total_questions' => $records->whereNotNull('cau_hoi_id')->count(),
            'expected_per_loai' => self::QUESTIONS_PER_LOAI,
        ];
    }
}
