<?php

use Telegram\Bot\Api;
use Illuminate\Http\Request;
use App\Models\Company;

class TelegramController extends Controller
{
    public function setChatId(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $updates = $telegram->getUpdates();

        if (empty($updates)) {
            return response()->json(['message' => 'No Telegram messages received yet.'], 404);
        }

        $chat_id = end($updates)['message']['chat']['id'];

        Company::where('id', $request->company_id)->update([
            'telegram_chat_id' => $chat_id
        ]);

        return response()->json(['message' => 'Telegram chat ID saved successfully.']);
    }
}
