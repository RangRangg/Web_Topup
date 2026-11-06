<?php

namespace App\Http\Controllers;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\JokiService;
use App\Services\AI\AIServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AIServiceInterface $aiService
    ) {}

    /**
     * Dedicated AI Assistant page.
     */
    public function index(): View
    {
        $recommendations = JokiService::with('game')
            ->where('is_active', true)
            ->where('is_popular', true)
            ->take(4)
            ->get();

        $initialMessages = [];
        $user = auth()->user();

        if ($user) {
            $conversation = AIConversation::where('user_id', $user->id)
                ->where('is_active', true)
                ->latest()
                ->first();

            if ($conversation) {
                $initialMessages = $conversation->aiMessages()
                    ->latest()
                    ->take(10)
                    ->get()
                    ->reverse()
                    ->map(fn ($m) => [
                        'role' => $m->role === 'assistant' ? 'ai' : 'user',
                        'text' => $m->content,
                        'recommendations' => $m->meta['recommendations'] ?? [],
                        'navigation' => $m->meta['navigation'] ?? [],
                    ])
                    ->values()
                    ->toArray();
            }
        }

        return view('ai-assistant.index', compact('recommendations', 'initialMessages'));
    }

    /**
     * Handle AI chat consultation prompt with rate-limiting, error handling, and conversation storage.
     */
    public function consult(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|min:2|max:500',
        ], [
            'prompt.required' => 'Pertanyaan atau kebutuhan tidak boleh kosong.',
            'prompt.min' => 'Pertanyaan minimal 2 karakter.',
            'prompt.max' => 'Pertanyaan maksimal 500 karakter.',
        ]);

        $prompt = trim($validated['prompt']);
        $user = $request->user();

        $conversation = null;
        $history = [];

        if ($user) {
            $conversation = AIConversation::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'is_active' => true,
                ],
                [
                    'title' => 'Konsultasi Aethereal AI',
                    'session_id' => $request->session()->getId(),
                ]
            );

            // Fetch last 4 turns for conversational context
            $history = $conversation->aiMessages()
                ->latest()
                ->take(6)
                ->get()
                ->reverse()
                ->map(fn ($msg) => [
                    'role' => $msg->role,
                    'content' => $msg->content,
                ])
                ->values()
                ->toArray();
        }

        try {
            $result = $this->aiService->ask($prompt, $user, $history);
        } catch (\Throwable $e) {
            $result = [
                'reply' => 'Maaf, terjadi kendala saat memproses permintaan Anda. Silakan coba kembali beberapa saat lagi.',
                'recommendations' => [],
                'navigation' => [
                    ['label' => 'Katalog Top Up', 'url' => route('topup')],
                    ['label' => 'Layanan Joki', 'url' => route('joki')],
                ],
                'source' => 'error_fallback',
            ];
        }

        // Clean any markdown formatting artifacts (* or `)
        if (isset($result['reply']) && is_string($result['reply'])) {
            $result['reply'] = str_replace(['*', '`'], '', $result['reply']);
        }

        // Persist conversation messages for authenticated users
        if ($user && $conversation) {
            AIMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $prompt,
            ]);

            AIMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $result['reply'],
                'meta' => [
                    'recommendations' => $result['recommendations'] ?? [],
                    'navigation' => $result['navigation'] ?? [],
                ],
            ]);
        }

        return response()->json([
            'reply' => $result['reply'],
            'recommendations' => $result['recommendations'] ?? [],
            'navigation' => $result['navigation'] ?? [],
            'source' => $result['source'] ?? 'local',
        ]);
    }

    /**
     * Fetch conversation history for authenticated user.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['messages' => []]);
        }

        $conversation = AIConversation::where('user_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        if (! $conversation) {
            return response()->json(['messages' => []]);
        }

        $messages = $conversation->aiMessages()
            ->latest()
            ->take(15)
            ->get()
            ->reverse()
            ->map(fn ($m) => [
                'role' => $m->role === 'assistant' ? 'ai' : 'user',
                'text' => $m->content,
                'recommendations' => $m->meta['recommendations'] ?? [],
                'navigation' => $m->meta['navigation'] ?? [],
            ])
            ->values();

        return response()->json(['messages' => $messages]);
    }

    /**
     * Clear active conversation for authenticated user.
     */
    public function clear(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            AIConversation::where('user_id', $user->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        return response()->json(['success' => true]);
    }
}
