<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaoKeNganHang;
use App\Models\TracNghiemLichSuThanhToanEduCoin;
use App\Services\DuyetYeuCauNapEduCoin;
use App\Support\ApiResponse;
use App\Support\MaGiaoDich;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class SaoKeNganHangController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $this->logWebhook('info', '1. Nhận request webhook', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => $request->all(),
            ]);

            $unauthorized = $this->authorizeWebhook($request);
            if ($unauthorized !== null) {
                return $unauthorized;
            }

            try {
                $validated = $request->validate([
                    'gateway' => ['required', 'string', 'max:50'],
                    'transactionDate' => ['required', 'date'],
                    'accountNumber' => ['required', 'string', 'max:50'],
                    'subAccount' => ['nullable', 'string', 'max:50'],
                    'code' => ['nullable', 'string', 'max:100'],
                    'content' => ['nullable', 'string'],
                    'transferType' => ['required', 'string', 'in:in,out'],
                    'description' => ['nullable', 'string'],
                    'transferAmount' => ['required', 'integer', 'min:0'],
                    'referenceCode' => ['nullable', 'string', 'max:100'],
                    'accumulated' => ['nullable', 'integer', 'min:0'],
                    'id' => ['required', 'integer', 'min:1'],
                ], [
                    'gateway.required' => 'Thiếu gateway.',
                    'transactionDate.required' => 'Thiếu thời gian giao dịch.',
                    'accountNumber.required' => 'Thiếu số tài khoản.',
                    'transferType.in' => 'transferType phải là in hoặc out.',
                    'id.required' => 'Thiếu id giao dịch.',
                ]);
            } catch (ValidationException $e) {
                $this->logWebhook('warning', '3. Validate payload thất bại', [
                    'errors' => $e->errors(),
                    'payload' => $request->all(),
                ]);

                throw $e;
            }

            $itemId = (int) $validated['id'];
            $this->logWebhook('info', '3. Validate payload thành công', [
                'item_id' => $itemId,
                'gateway' => $validated['gateway'],
                'content' => $validated['content'],
                'transfer_type' => $validated['transferType'],
                'transfer_amount' => (int) $validated['transferAmount'],
                'account_number' => $validated['accountNumber'],
                'transaction_date' => $validated['transactionDate'],
                'reference_code' => $validated['referenceCode'] ?? null,
            ]);

            $existing = SaoKeNganHang::query()->where('item_id', $itemId)->first();
            if ($existing !== null) {
                $this->logWebhook('info', '4. Bỏ qua vì giao dịch đã tồn tại (item_id trùng)', [
                    'item_id' => $itemId,
                    'sao_ke_id' => $existing->id,
                ]);

                return ApiResponse::success(
                    $this->toPublicArray($existing),
                    'Giao dịch đã tồn tại.',
                );
            }

            $this->logWebhook('info', '4. item_id chưa tồn tại, tiếp tục ghi nhận', [
                'item_id' => $itemId,
            ]);

            $contentFromContent = MaGiaoDich::extractFromText($validated['content'] ?? null);
            $contentFromCode = MaGiaoDich::extractFromText($validated['code'] ?? null);
            $contentFromDescription = MaGiaoDich::extractFromText($validated['description'] ?? null);
            $content = $contentFromContent ?? $contentFromCode ?? $contentFromDescription;

            $this->logWebhook('info', '5. Trích mã giao dịch từ nội dung chuyển khoản', [
                'item_id' => $itemId,
                'raw_content' => $validated['content'] ?? null,
                'raw_code' => $validated['code'] ?? null,
                'raw_description' => $validated['description'] ?? null,
                'ma_tu_content' => $contentFromContent,
                'ma_tu_code' => $contentFromCode,
                'ma_tu_description' => $contentFromDescription,
                'ma_giao_dich' => $content,
            ]);

            try {
                $this->logWebhook('info', '6. Bắt đầu transaction ghi sao kê', [
                    'item_id' => $itemId,
                ]);

                $item = DB::transaction(function () use ($validated, $content, $itemId) {
                    $item = SaoKeNganHang::query()->create([
                        'gateway' => $validated['gateway'],
                        'transaction_date' => $validated['transactionDate'],
                        'account_number' => $validated['accountNumber'],
                        'sub_account' => $validated['subAccount'] ?? null,
                        'code' => $validated['code'] ?? null,
                        'content' => $content,
                        'transfer_type' => $validated['transferType'],
                        'description' => $validated['description'] ?? null,
                        'transfer_amount' => (int) $validated['transferAmount'],
                        'reference_code' => $validated['referenceCode'] ?? null,
                        'accumulated' => isset($validated['accumulated'])
                            ? (int) $validated['accumulated']
                            : null,
                        'item_id' => $itemId,
                    ]);

                    $this->logWebhook('info', '7. Đã lưu bản ghi sao kê', [
                        'item_id' => $itemId,
                        'sao_ke_id' => $item->id,
                        'transfer_type' => $item->transfer_type,
                        'content' => $item->content,
                        'transfer_amount' => (int) $item->transfer_amount,
                    ]);

                    $this->xuLyGiaoDichVao($item);

                    return $item;
                });
            } catch (QueryException $e) {
                $this->logWebhook('warning', '6. QueryException khi ghi sao kê', [
                    'item_id' => $itemId,
                    'sql_state' => $e->errorInfo[0] ?? null,
                    'error' => $e->getMessage(),
                ]);

                $existing = SaoKeNganHang::query()->where('item_id', $itemId)->first();
                if ($existing !== null) {
                    $this->logWebhook('info', '6. Race condition: giao dịch đã được ghi bởi request khác', [
                        'item_id' => $itemId,
                        'sao_ke_id' => $existing->id,
                    ]);

                    return ApiResponse::success(
                        $this->toPublicArray($existing),
                        'Giao dịch đã tồn tại.',
                    );
                }

                $this->logWebhook('error', '6. Ghi sao kê thất bại, không phải trùng item_id', [
                    'item_id' => $itemId,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            } catch (Throwable $e) {
                $this->logWebhook('error', '6. Lỗi không mong đợi khi ghi sao kê', [
                    'item_id' => $itemId,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);

                throw $e;
            }

            $this->logWebhook('info', '10. Hoàn tất webhook sao kê', [
                'item_id' => $itemId,
                'sao_ke_id' => $item->id,
                'ma_giao_dich' => $item->content,
                'transfer_type' => $item->transfer_type,
                'transfer_amount' => (int) $item->transfer_amount,
            ]);

            return ApiResponse::success(
                $this->toPublicArray($item),
                'Đã ghi nhận sao kê ngân hàng.',
            );
        });
    }

    private function xuLyGiaoDichVao(SaoKeNganHang $item): void
    {
        $this->logWebhook('info', '8. Xử lý giao dịch vào', [
            'sao_ke_id' => $item->id,
            'item_id' => $item->item_id,
            'transfer_type' => $item->transfer_type,
            'content' => $item->content,
            'transfer_amount' => (int) $item->transfer_amount,
        ]);

        if ($item->transfer_type !== 'in') {
            $this->logWebhook('info', '8. Bỏ qua: transfer_type không phải in', [
                'sao_ke_id' => $item->id,
                'item_id' => $item->item_id,
                'transfer_type' => $item->transfer_type,
            ]);

            return;
        }

        $maGiaoDich = MaGiaoDich::normalize($item->content);
        if ($maGiaoDich === '') {
            $this->logWebhook('warning', '8. Bỏ qua: không trích được mã NAP/PAY từ nội dung', [
                'sao_ke_id' => $item->id,
                'item_id' => $item->item_id,
                'content' => $item->content,
            ]);

            return;
        }

        if (MaGiaoDich::isValidNap($maGiaoDich)) {
            $this->logWebhook('info', '9. Nhận diện mã NAP, bắt đầu duyệt yêu cầu nạp EduCoin', [
                'sao_ke_id' => $item->id,
                'item_id' => $item->item_id,
                'ma_giao_dich' => $maGiaoDich,
                'so_tien' => (int) $item->transfer_amount,
            ]);

            $nap = (new DuyetYeuCauNapEduCoin)->duyetTheoMaVaSoTien(
                $maGiaoDich,
                (int) $item->transfer_amount,
            );

            if ($nap === null) {
                $this->logWebhook('warning', '9. Không duyệt được yêu cầu nạp (không khớp mã + số tiền + chờ duyệt)', [
                    'sao_ke_id' => $item->id,
                    'item_id' => $item->item_id,
                    'ma_giao_dich' => $maGiaoDich,
                    'so_tien' => (int) $item->transfer_amount,
                ]);

                return;
            }

            $this->logWebhook('info', '9. Đã duyệt yêu cầu nạp EduCoin', [
                'sao_ke_id' => $item->id,
                'item_id' => $item->item_id,
                'ma_giao_dich' => $maGiaoDich,
                'nap_edu_coin_id' => $nap->id,
                'nguoi_nap_id' => $nap->nguoi_nap_id,
                'so_luong_edu_coin' => $nap->so_luong_edu_coin,
                'trang_thai' => $nap->trang_thai?->value,
            ]);

            return;
        }

        if (MaGiaoDich::isValidPay($maGiaoDich)) {
            $this->logWebhook('info', '9. Nhận diện mã PAY, ghi lịch sử thanh toán trắc nghiệm', [
                'sao_ke_id' => $item->id,
                'item_id' => $item->item_id,
                'ma_giao_dich' => $maGiaoDich,
                'so_tien' => (int) $item->transfer_amount,
            ]);

            $thanhToan = TracNghiemLichSuThanhToanEduCoin::query()->create([
                'noi_dung' => $maGiaoDich,
                'so_tien' => (int) $item->transfer_amount,
                'thoi_gian' => $item->transaction_date,
                'mo_ta' => $item->description,
            ]);

            $this->logWebhook('info', '9. Đã ghi lịch sử thanh toán trắc nghiệm', [
                'sao_ke_id' => $item->id,
                'item_id' => $item->item_id,
                'ma_giao_dich' => $maGiaoDich,
                'thanh_toan_id' => $thanhToan->id,
            ]);

            return;
        }

        $this->logWebhook('warning', '9. Mã giao dịch không phải NAP hoặc PAY hợp lệ, bỏ qua', [
            'sao_ke_id' => $item->id,
            'item_id' => $item->item_id,
            'ma_giao_dich' => $maGiaoDich,
        ]);
    }

    private function authorizeWebhook(Request $request): ?JsonResponse
    {
        $token = (string) config('services.sao_ke_webhook.token', '');
        if ($token === '') {
            $this->logWebhook('info', '2. Bỏ qua xác thực webhook vì chưa cấu hình token', [
                'ip' => $request->ip(),
            ]);

            return null;
        }

        $provided = $this->extractWebhookToken($request);
        if ($provided === '' || ! hash_equals($token, $provided)) {
            $this->logWebhook('warning', '2. Xác thực webhook thất bại', [
                'ip' => $request->ip(),
                'has_token' => $provided !== '',
            ]);

            return ApiResponse::error('Webhook không hợp lệ.', null, [], 401);
        }

        $this->logWebhook('info', '2. Xác thực webhook thành công', [
            'ip' => $request->ip(),
        ]);

        return null;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logWebhook(string $level, string $message, array $context = []): void
    {
        Log::channel('sao_ke_ngan_hang')->log($level, '[sao-ke-ngan-hang] '.$message, $context);
    }

    private function extractWebhookToken(Request $request): string
    {
        $header = trim((string) $request->header('Authorization', ''));
        if (preg_match('/^(?:Bearer|Apikey)\s+(.+)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return trim((string) $request->header('X-Api-Key', ''));
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(SaoKeNganHang $item): array
    {
        return [
            'id' => $item->id,
            'item_id' => (int) $item->item_id,
            'gateway' => $item->gateway,
            'transaction_date' => $item->transaction_date?->format('Y-m-d H:i:s'),
            'account_number' => $item->account_number,
            'sub_account' => $item->sub_account,
            'code' => $item->code,
            'content' => $item->content,
            'transfer_type' => $item->transfer_type,
            'description' => $item->description,
            'transfer_amount' => (int) $item->transfer_amount,
            'reference_code' => $item->reference_code,
            'accumulated' => $item->accumulated !== null ? (int) $item->accumulated : null,
            'created_at' => $item->created_at?->toIso8601String(),
        ];
    }
}
