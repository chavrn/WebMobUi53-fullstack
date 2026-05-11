<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
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

        $hasEnded = $poll->ends_at ? now()->gt($poll->ends_at) : false;
        $totalVotes = $poll->options->sum('votes_count');

        $userHasVoted = false;
        if (auth()->check()) {
            $userHasVoted = PollVote::where('user_id', auth()->id())
                ->whereIn('poll_option_id', $poll->options->pluck('id'))
                ->exists();
        }

        return response()->json([
            'id'                   => $poll->id,
            'title'                => $poll->title,
            'question'             => $poll->question,
            'allow_multiple_choices' => $poll->allow_multiple_choices,
            'options'              => $poll->options->map(fn($opt) => [
                'id'          => $opt->id,
                'label'       => $opt->label,
                'votes_count' => $opt->votes_count,
                'percentage'  => $totalVotes > 0 ? round($opt->votes_count / $totalVotes * 100) : 0,
            ]),
            'is_draft'        => $poll->is_draft,
            'results_public'  => $poll->results_public,
            'started_at'      => $poll->started_at,
            'ends_at'         => $poll->ends_at,
            'has_ended'       => $hasEnded,
            'is_authenticated' => auth()->check(),
            'user_has_voted'  => $userHasVoted,
        ]);
    }

    public function vote(Request $request, string $token)
    {
        $poll = Poll::with('options')->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        if ($poll->is_draft) {
            return response()->json(['message' => 'This poll has not started yet.'], 422);
        }

        if ($poll->ends_at && now()->gt($poll->ends_at)) {
            return response()->json(['message' => 'This poll has ended.'], 422);
        }

        $validated = $request->validate([
            'poll_option_id' => 'required|integer|exists:poll_options,id',
        ]);

        // Vérifie que l'option appartient bien à ce sondage
        if (!$poll->options->contains('id', $validated['poll_option_id'])) {
            return response()->json(['message' => 'This option does not belong to this poll.'], 422);
        }

        if ($poll->allow_multiple_choices) {
            // Choix multiples : interdit de voter deux fois pour la même option
            $alreadyVoted = PollVote::where('user_id', $request->user()->id)
                ->where('poll_option_id', $validated['poll_option_id'])
                ->exists();
        } else {
            // Choix unique : interdit d'avoir voté pour n'importe quelle option du sondage
            $alreadyVoted = PollVote::where('user_id', $request->user()->id)
                ->whereIn('poll_option_id', $poll->options->pluck('id'))
                ->exists();
        }

        if ($alreadyVoted) {
            return response()->json(['message' => 'You have already voted in this poll.'], 422);
        }

        PollVote::create([
            'poll_id'        => $poll->id,
            'user_id'        => $request->user()->id,
            'poll_option_id' => $validated['poll_option_id'],
        ]);

        // Retourne les résultats mis à jour
        $poll->load(['options' => fn($q) => $q->withCount('votes')]);
        $totalVotes = $poll->options->sum('votes_count');

        return response()->json([
            'message' => 'Vote registered.',
            'options' => $poll->options->map(fn($opt) => [
                'id'          => $opt->id,
                'label'       => $opt->label,
                'votes_count' => $opt->votes_count,
                'percentage'  => $totalVotes > 0 ? round($opt->votes_count / $totalVotes * 100) : 0,
            ]),
        ], 201);
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
        if ($poll->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($poll->load('options'));
    }

    public function update(Request $request, Poll $poll)
    {
        if ($poll->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (!$poll->is_draft) {
            return response()->json(['message' => 'Cannot edit a poll that has already been started.'], 422);
        }

        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'question'               => 'required|string|max:255',
            'options'                => 'required|array|min:2',
            'options.*'              => 'required|string|max:255',
            'allow_multiple_choices' => 'boolean',
            'results_public'         => 'boolean',
            'ends_at'                => 'nullable|date',
        ]);

        $poll->update([
            'title'                  => $validated['title'],
            'question'               => $validated['question'],
            'allow_multiple_choices' => $validated['allow_multiple_choices'] ?? false,
            'results_public'         => $validated['results_public'] ?? false,
            'ends_at'                => $validated['ends_at'] ?? null,
        ]);

        // Supprime et recrée les options (sondage non encore démarré donc pas de votes)
        $poll->options()->delete();
        foreach ($validated['options'] as $label) {
            $poll->options()->create(['label' => $label]);
        }

        return response()->json($poll->load('options'));
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
