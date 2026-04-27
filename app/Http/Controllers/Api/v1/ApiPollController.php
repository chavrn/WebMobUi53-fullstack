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

        return response()->json($poll);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                  => 'nullable|string|max:255',
            'question'               => 'required|string|max:255',
            'allow_multiple_choices' => 'nullable|boolean',
            'allow_vote_change'      => 'nullable|boolean',
            'results_public'         => 'nullable|boolean',
            'duration'               => 'nullable|integer|min:1',
            'options'                => 'required|array|min:2',
            'options.*'              => 'required|string|max:255',
        ]);

        $poll = $request->user()->polls()->create([
            'title'                  => $validated['title'] ?? null,
            'question'               => $validated['question'],
            'secret_token'           => Str::random(32),
            'is_draft'               => true,
            'allow_multiple_choices' => $validated['allow_multiple_choices'] ?? false,
            'allow_vote_change'      => $validated['allow_vote_change'] ?? false,
            'results_public'         => $validated['results_public'] ?? false,
            'duration'               => $validated['duration'] ?? null,
        ]);

        foreach ($validated['options'] as $label) {
            $poll->options()->create(['label' => $label]);
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
    }
}
