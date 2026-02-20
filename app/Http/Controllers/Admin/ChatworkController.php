<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatworkService;
use Illuminate\Http\Request;

class ChatworkController extends Controller
{
    public function send(Request $request, User $user)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if (!$user->chatwork_room_id) {
            return back()->with('error', 'このユーザーにはChatwork Room IDが設定されていません。');
        }

        $chatwork = new ChatworkService();
        $message = $validated['message'];

        if ($user->chatwork_id) {
            $message = "[To:{$user->chatwork_id}]{$user->name}さん\n{$message}";
        }

        $success = $chatwork->sendMessage($user->chatwork_room_id, $message);

        if ($success) {
            return back()->with('success', "{$user->name}さんにChatworkメッセージを送信しました。");
        }

        return back()->with('error', 'Chatworkメッセージの送信に失敗しました。APIトークンとRoom IDを確認してください。');
    }
}
