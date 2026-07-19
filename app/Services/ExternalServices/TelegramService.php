<?php

namespace App\Services\ExternalServices;

use App\Models\TransactionDetail;
use App\Services\TransactionDetailService;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;

class TelegramService
{
    private string $botToken;
    private string $chatId;
    private string $outsiderKey;

    public function __construct(
    ) {
        $this->botToken = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
        $this->outsiderKey = config('services.telegram.outsider_api_key');
    }

    public function telegramSendRequestDetail(string $transactionDetailId): void{
        // For demonstration, let's assume an internal system creates a random tracking ID
        // Format a highly scannable message for Telegram
        $transactionDetail = TransactionDetail::findOrFail($transactionDetailId);
        $transaction = $transactionDetail->transaction;
        $payload = [
            'id' => $transactionDetail->id,
            'no_rekening' => $transaction->bank_account_num_full,
            'datetime' => $transactionDetail->created_at->timezone('Asia/Jakarta')->format('Y-m-d'),
            'local_timezone' => 'Asia/Jakarta',
            'amount' => $transactionDetail->amount,
            'type' => 'pengajuan',
        ];
        $telegramMessage = "```json\n". json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE). "\n```";
        $this->sendToTelegram($telegramMessage);
    }

    public function telegramSendMessageFeedback(bool $status, string $message, string $id): void{
        $payload = [
            'id' => $id,
            'success' => $status,
            'message' => $message,
        ];
        $telegramMessage = "```json\n". json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE). "\n```";
        $this->sendToTelegram($telegramMessage);
    }

    private function sendToTelegram(string $telegramMessage): JsonResponse
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $client = new Client();

        try {
            // Execute request using application/json context
            $response = $client->post($url, [
                'json' => [
                    'chat_id' => $this->chatId,
                    'text' => $telegramMessage,
                    'parse_mode' => 'Markdown',
                ]
            ]);

            // Check if status code matches 200 OK
            if ($response->getStatusCode() === 200) {
                return response()->json([
                    'status' => 'dispatched',
                ], 200);
            }

        } catch (GuzzleException $e) {
            // Catches connection timeouts, 4xx errors, or 5xx server issues
            Log::error("[WORKFLOW] Guzzle failed sending to Telegram: " . $e->getMessage());
        }

        return response()->json(['error' => 'Failed to reach Telegram API'], 500);
    }
}
