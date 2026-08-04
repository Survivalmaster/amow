<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CharacterLogAdminController extends Controller
{
    private const VISIBLE_LOG_TYPES = [
        'work',
        'item_purchase',
        'licence_purchase',
        'stock_buy',
        'stock_sell',
        'job_change',
        'rank_change',
    ];

    public function index(Request $request): View
    {
        $characters = Character::query()
            ->with(['user', 'faction', 'rank', 'currentJob'])
            ->orderBy('name')
            ->get();

        $selectedCharacter = null;
        $transactions = null;
        $logStats = null;

        if ($request->integer('character_id')) {
            $selectedCharacter = $characters->firstWhere('id', $request->integer('character_id'));
        }

        if ($selectedCharacter) {
            $selectedCharacter->loadMissing(['user', 'faction', 'rank', 'currentJob']);

            $baseTransactionsQuery = $selectedCharacter
                ->transactions()
                ->whereIn('type', self::VISIBLE_LOG_TYPES);

            $logStats = $this->buildLogStats($selectedCharacter, (clone $baseTransactionsQuery)->latest()->get());

            $transactions = (clone $baseTransactionsQuery)
                ->latest()
                ->paginate(50)
                ->withQueryString();
        }

        return view('admin.character-logs', [
            'characters' => $characters,
            'selectedCharacter' => $selectedCharacter,
            'transactions' => $transactions,
            'logStats' => $logStats,
        ]);
    }

    private function buildLogStats(Character $character, Collection $logs): array
    {
        $counts = $logs->countBy('type');
        $earnedCredits = (int) $logs->where('amount', '>', 0)->sum('amount');
        $spentCredits = abs((int) $logs->where('amount', '<', 0)->sum('amount'));
        $workLogs = $logs->where('type', 'work');
        $xpEarned = (int) $workLogs->sum(fn ($transaction) => (int) data_get($transaction->metadata, 'xp_earned', 0));
        $staminaSpent = (int) $workLogs->sum(function ($transaction) {
            $before = data_get($transaction->metadata, 'stamina_before');
            $after = data_get($transaction->metadata, 'stamina_after');

            return is_numeric($before) && is_numeric($after) ? max(0, (int) $before - (int) $after) : 0;
        });
        $recentDays = collect(range(6, 0))->map(fn ($daysAgo) => now()->subDays($daysAgo)->startOfDay());
        $dailyLogs = $recentDays->map(function ($day) use ($logs) {
            return [
                'label' => $day->format('D'),
                'count' => $logs->filter(fn ($transaction) => $transaction->created_at->isSameDay($day))->count(),
            ];
        });
        $maxDailyLogs = max(1, $dailyLogs->max('count') ?? 0);

        return [
            'total_logs' => $logs->count(),
            'work_count' => (int) ($counts['work'] ?? 0),
            'purchase_count' => (int) (($counts['item_purchase'] ?? 0) + ($counts['licence_purchase'] ?? 0)),
            'market_count' => (int) (($counts['stock_buy'] ?? 0) + ($counts['stock_sell'] ?? 0)),
            'change_count' => (int) (($counts['job_change'] ?? 0) + ($counts['rank_change'] ?? 0)),
            'earned_credits' => $earnedCredits,
            'spent_credits' => $spentCredits,
            'net_credits' => $earnedCredits - $spentCredits,
            'xp_earned' => $xpEarned,
            'stamina_spent' => $staminaSpent,
            'level_progress_percent' => min(100, (int) round(($character->experience_points / max(1, $character->experienceRequiredForNextLevel())) * 100)),
            'stamina_percent' => max(0, min(100, (int) ($character->stamina_points ?? 100))),
            'activity_days' => $dailyLogs->map(fn ($day) => [
                ...$day,
                'percent' => (int) round(($day['count'] / $maxDailyLogs) * 100),
            ]),
        ];
    }
}
