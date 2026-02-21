<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatworkService;
use Illuminate\Http\JsonResponse;

class ChatworkMemberController extends Controller
{
    public function index(string $roomId): JsonResponse
    {
        $chatwork = new ChatworkService();
        $members = $chatwork->getRoomMembers($roomId);

        if (empty($members)) {
            return response()->json(['error' => 'メンバーを取得できませんでした。Room IDとAPIトークンを確認してください。'], 422);
        }

        return response()->json($members);
    }
}
