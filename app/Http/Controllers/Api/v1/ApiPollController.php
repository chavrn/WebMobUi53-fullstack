<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiPollController extends Controller
{
    public function index(Request $request)
    {
        $polls = $request->user()->polls()->with('options')->orderBy('created_at', 'desc')->get();
        return response()->json($polls);
    }

    public function show(string $token)
    {
        $poll = Poll::with(['options' => function ($query) {
            $query->withCount('votes');
        }])->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }


        return response()->json([
            'id' => $poll->id,
            'title' => $poll->title,
            'question' => $poll->question,
            'options' => $poll->options,
            'is_draft' => $poll->is_draft,
            'started_at' => $poll->started_at,
            'ends_at' => $poll->ends_at,
            'results_public' => $poll->results_public,
            'has_ended' => $poll->ends_at ? now()->gt($poll->ends_at) : false,
        ]);

    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'question' => 'required|string|max:255',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
            'allow_multiple_choices' => 'boolean',
            'results_public' => 'boolean',
            'ends_at' => 'nullable|date',
            'isDraft' => 'boolean',
        ]);

        $isDraft = $validated['isDraft'] ?? true;

        $poll = Poll::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'question' => $validated['question'],
            'allow_multiple_choices' => $validated['allow_multiple_choices'] ?? false,
            'results_public' => $validated['results_public'] ?? false,
            'is_draft' => $isDraft,
            'started_at' => $isDraft ? null : now(),
            'ends_at' => $validated['ends_at'] ?? null,
            'secret_token' => Str::random(32),
        ]);

        foreach ($validated['options'] as $label) {
            $poll->options()->create([
                'label' => $label,
            ]);
        }

        return response()->json($poll->load('options'), 201);
    }


    public function edit(Poll $poll)
    {
    }

    public function update(Request $request, Poll $poll)
    {
    }

    public function destroy(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $poll->delete();
        return response()->json(['message' => 'success'], 200);
    }

    public function start(Poll $poll)
    {
        // Sécurité : seul le propriétaire peut lancer
        if ($poll->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You are not allowed to start this poll.'
            ], 403);
        }

        // Le sondage ne doit pas déjà être lancé
        if (!$poll->is_draft) {
            return response()->json([
                'message' => 'This poll has already been started.'
            ], 422);
        }

        // Un sondage doit avoir au moins 2 options
        if ($poll->options()->count() < 2) {
            return response()->json([
                'message' => 'A poll must have at least 2 options before being started.'
            ], 422);
        }

        // Lancer le sondage
        $poll->update([
            'is_draft' => false,
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Poll successfully started.',
            'poll' => $poll
        ]);
    }
}
