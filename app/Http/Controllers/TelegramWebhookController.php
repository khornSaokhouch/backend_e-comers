<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Api;
use App\Models\ShopOrder;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        try {
            // Get update object from Telegram
            $update = $telegram->getWebhookUpdate();

            $message = $update->getMessage();

            // If no text message, ignore
            if (!$message || !$message->has('text')) {
                return response()->json(['status' => 'no text message'], 200);
            }

            $chatId = $message->getChat()->getId();
            $text = trim($message->getText());

            // Start command: ask for user ID
            if ($text === '/start') {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "👋 Welcome! Please enter your numeric User ID to view your orders:",
                ]);
                return response()->json(['status' => 'asked user id'], 200);
            }

            // If numeric text, treat as user ID
            if (is_numeric($text)) {
                $userId = (int) $text;

                $orders = ShopOrder::where('user_id', $userId)
                    ->with('orderLines')
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($orders->isEmpty()) {
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "❌ No orders found for User ID: {$userId}",
                    ]);
                    return response()->json(['status' => 'no orders'], 200);
                }

                // Format orders info
                $reply = "🛒 Orders for User ID {$userId}:\n\n";

                foreach ($orders as $order) {
                    $reply .= "🧾 Order #{$order->id} - Date: {$order->order_date}\n";
                    foreach ($order->orderLines as $line) {
                        $reply .= "  • Product Item ID: {$line->product_item_id}, Qty: {$line->quantity}, Price: {$line->price}\n";
                    }
                    $reply .= "------------------------\n";
                }

                // Split messages if too long for Telegram (max ~4096 chars)
                $chunks = str_split($reply, 3500);
                foreach ($chunks as $chunk) {
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $chunk,
                    ]);
                }

                return response()->json(['status' => 'sent orders'], 200);
            }

            // If input not recognized
            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❓ Sorry, I didn't understand that. Please send your numeric User ID.",
            ]);

            return response()->json(['status' => 'unknown input'], 200);

        } catch (\Exception $e) {
            Log::error('Telegram Webhook Error: ' . $e->getMessage());

            // Send error message to user if possible
            if (isset($chatId)) {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "⚠️ An error occurred. Please try again later.",
                ]);
            }

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
