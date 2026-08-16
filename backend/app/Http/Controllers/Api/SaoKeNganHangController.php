<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaoKeNganHang;
use App\Support\ApiResponse;
use App\Support\MaGiaoDich;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaoKeNganHangController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $unauthorized = $this->authorizeWebhook($request);
            if ($unauthorized !== null) {
                return $unauthorized;
            }

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

            $itemId = (int) $validated['id'];
            $existing = SaoKeNganHang::query()->where('item_id', $itemId)->first();
            if ($existing !== null) {
                return ApiResponse::success(
                    $this->toPublicArray($existing),
                    'Giao dịch đã tồn tại.',
                );
            }

            $content = MaGiaoDich::extractFromText($validated['content'] ?? null)
                ?? MaGiaoDich::extractFromText($validated['code'] ?? null)
                ?? MaGiaoDich::extractFromText($validated['description'] ?? null);

            try {
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
            } catch (QueryException $e) {
                $existing = SaoKeNganHang::query()->where('item_id', $itemId)->first();
                if ($existing !== null) {
                    return ApiResponse::success(
                        $this->toPublicArray($existing),
                        'Giao dịch đã tồn tại.',
                    );
                }

                throw $e;
            }

            return ApiResponse::success(
                $this->toPublicArray($item),
                'Đã ghi nhận sao kê ngân hàng.',
            );
        });
    }

    private function authorizeWebhook(Request $request): ?JsonResponse
    {
        $token = (string) config('services.sao_ke_webhook.token', '');
        if ($token === '') {
            return null;
        }

        $provided = $this->extractWebhookToken($request);
        if ($provided === '' || ! hash_equals($token, $provided)) {
            return ApiResponse::error('Webhook không hợp lệ.', null, [], 401);
        }

        return null;
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
