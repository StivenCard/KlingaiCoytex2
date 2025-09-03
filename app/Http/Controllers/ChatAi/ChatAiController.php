<?php

namespace App\Http\Controllers\ChatAi;

use App\Http\Controllers\Controller;
use App\Services\ChatAi\ChatAiService;
use Illuminate\Http\Request;

class ChatAiController extends Controller
{
    public function index()
    {
        // Página del chat
        return view('openai.chat');
    }

    public function send(Request $request, ChatAiService $openai)
    {
        $data = $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        $reply = $openai->enviarMensaje($data['message']);
        return response()->json(['user' => $data['message'], 'assistant' => $reply]);
    }
}
