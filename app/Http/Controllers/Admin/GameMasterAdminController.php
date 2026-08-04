<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use App\Models\GameEvent;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class GameMasterAdminController extends Controller
{
    public function index(): View
    {
        $events = GameEvent::query()->with(['faction', 'creator'])->latest()->get();

        return view('admin.game-master', [
            'events' => $events,
            'factions' => Faction::query()->orderBy('name')->get(),
            'eventParticipation' => $this->buildEventParticipation($events),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'faction_id' => ['nullable', 'exists:factions,id'],
            'is_enabled' => ['nullable', 'boolean'],
            'ends_at' => ['nullable', 'date'],
            'xp_multiplier_enabled' => ['nullable', 'boolean'],
            'xp_multiplier' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'credit_multiplier_enabled' => ['nullable', 'boolean'],
            'credit_multiplier' => ['nullable', 'numeric', 'min:1', 'max:5'],
        ]);

        GameEvent::query()->create([
            ...$validated,
            'created_by_user_id' => $request->user()->id,
            'is_enabled' => $request->boolean('is_enabled', true),
            'ends_at' => $request->date('ends_at'),
            'xp_multiplier_enabled' => $request->boolean('xp_multiplier_enabled') && filled($request->input('xp_multiplier')),
            'xp_multiplier' => $request->boolean('xp_multiplier_enabled') ? (float) $request->input('xp_multiplier') : null,
            'credit_multiplier_enabled' => $request->boolean('credit_multiplier_enabled') && filled($request->input('credit_multiplier')),
            'credit_multiplier' => $request->boolean('credit_multiplier_enabled') ? (float) $request->input('credit_multiplier') : null,
        ]);

        return back()->with('status', 'Game event created.');
    }

    public function update(Request $request, GameEvent $gameEvent): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'faction_id' => ['nullable', 'exists:factions,id'],
            'is_enabled' => ['nullable', 'boolean'],
            'ends_at' => ['nullable', 'date'],
            'xp_multiplier_enabled' => ['nullable', 'boolean'],
            'xp_multiplier' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'credit_multiplier_enabled' => ['nullable', 'boolean'],
            'credit_multiplier' => ['nullable', 'numeric', 'min:1', 'max:5'],
        ]);

        $gameEvent->update([
            ...$validated,
            'is_enabled' => $request->boolean('is_enabled'),
            'ends_at' => $request->date('ends_at'),
            'xp_multiplier_enabled' => $request->boolean('xp_multiplier_enabled') && filled($request->input('xp_multiplier')),
            'xp_multiplier' => $request->boolean('xp_multiplier_enabled') ? (float) $request->input('xp_multiplier') : null,
            'credit_multiplier_enabled' => $request->boolean('credit_multiplier_enabled') && filled($request->input('credit_multiplier')),
            'credit_multiplier' => $request->boolean('credit_multiplier_enabled') ? (float) $request->input('credit_multiplier') : null,
        ]);

        return back()->with('status', 'Game event updated.');
    }

    private function buildEventParticipation(Collection $events): Collection
    {
        if ($events->isEmpty()) {
            return collect();
        }

        $logs = Transaction::query()
            ->with('character')
            ->where('type', 'work')
            ->whereNotNull('metadata')
            ->where('created_at', '>=', $events->min('created_at')->copy()->subMinute())
            ->latest()
            ->get();

        return $events->mapWithKeys(function (GameEvent $event) use ($logs) {
            $eventLogs = $logs->filter(fn (Transaction $transaction) => $this->transactionIncludesEvent($transaction, $event));
            $participants = $eventLogs
                ->groupBy('character_id')
                ->map(function (Collection $participantLogs) {
                    $firstLog = $participantLogs->first();

                    return [
                        'character' => $firstLog?->character,
                        'shifts' => $participantLogs->count(),
                        'credits' => (int) $participantLogs->sum('amount'),
                        'xp' => (int) $participantLogs->sum(fn (Transaction $transaction) => (int) data_get($transaction->metadata, 'xp_earned', 0)),
                        'last_worked_at' => $participantLogs->max('created_at'),
                    ];
                })
                ->sortByDesc('shifts')
                ->values();

            return [
                $event->id => [
                    'participants' => $participants,
                    'participant_count' => $participants->count(),
                    'shift_count' => $eventLogs->count(),
                    'credits' => (int) $eventLogs->sum('amount'),
                    'xp' => (int) $eventLogs->sum(fn (Transaction $transaction) => (int) data_get($transaction->metadata, 'xp_earned', 0)),
                ],
            ];
        });
    }

    private function transactionIncludesEvent(Transaction $transaction, GameEvent $event): bool
    {
        $eventEntries = collect([
            ...collect(data_get($transaction->metadata, 'credit_multiplier_events', []))->all(),
            ...collect(data_get($transaction->metadata, 'xp_multiplier_events', []))->all(),
        ]);

        return $eventEntries->contains(function (array $entry) use ($event) {
            if ((int) ($entry['id'] ?? 0) === $event->id) {
                return true;
            }

            return ($entry['name'] ?? null) === $event->title;
        });
    }
}
