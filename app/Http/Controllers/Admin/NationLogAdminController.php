<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use App\Models\NationRequisition;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class NationLogAdminController extends Controller
{
    private const PER_PAGE_OPTIONS = [10, 50, 100];
    private const MAX_PER_PAGE = 500;

    private const MONEY_IN_TYPES = [
        'nation_donation',
    ];

    private const MONEY_OUT_TYPES = [
        'nation_withdrawal',
        'nation_bank_withdrawal',
        'nation_disbursement',
        'nation_requisition_payout',
    ];

    public function index(Request $request): View
    {
        $factions = Faction::query()
            ->withCount('characters')
            ->orderBy('name')
            ->get();

        $selectedFaction = null;
        $logs = null;
        $logStats = null;
        $perPage = $this->resolvePerPage($request);

        if ($request->integer('faction_id')) {
            $selectedFaction = $factions->firstWhere('id', $request->integer('faction_id'));
        }

        if ($selectedFaction) {
            $selectedFaction->loadMissing(['characters.user', 'requisitions']);
            $entries = $this->buildEntries($selectedFaction);
            $logStats = $this->buildLogStats($selectedFaction, $entries);
            $logs = $this->paginateEntries($entries, $perPage, $request);
        }

        return view('admin.nation-logs', [
            'factions' => $factions,
            'selectedFaction' => $selectedFaction,
            'logs' => $logs,
            'logStats' => $logStats,
            'perPage' => $request->query('per_page', '10'),
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'maxPerPage' => self::MAX_PER_PAGE,
        ]);
    }

    private function buildEntries(Faction $faction): Collection
    {
        $transactions = Transaction::query()
            ->with(['character.user', 'character.faction'])
            ->whereHas('character', fn ($query) => $query->where('faction_id', $faction->id))
            ->where(function ($query) {
                $query->whereIn('type', [...self::MONEY_IN_TYPES, ...self::MONEY_OUT_TYPES])
                    ->orWhere('type', 'like', 'nation\_%');
            })
            ->latest()
            ->get()
            ->map(fn (Transaction $transaction): array => [
                'kind' => 'transaction',
                'occurred_at' => $transaction->created_at,
                'type' => $transaction->type,
                'event' => match ($transaction->type) {
                    'nation_donation' => 'Donation',
                    default => str($transaction->type)->replace('_', ' ')->title()->toString(),
                },
                'actor' => $transaction->character?->name ?? 'Unknown character',
                'actor_user' => $transaction->character?->user,
                'description' => $transaction->description,
                'detail' => $this->transactionDetail($transaction),
                'bank_amount' => $this->bankAmountForTransaction($transaction),
                'metadata' => $transaction->metadata ?? [],
            ]);

        $requisitions = NationRequisition::query()
            ->with(['submitter.user', 'reviewer'])
            ->where('faction_id', $faction->id)
            ->latest()
            ->get()
            ->map(fn (NationRequisition $requisition): array => [
                'kind' => 'requisition',
                'occurred_at' => $requisition->reviewed_at ?? $requisition->created_at,
                'type' => 'nation_requisition',
                'event' => 'Requisition',
                'actor' => $requisition->submitter?->name ?? 'Unknown character',
                'actor_user' => $requisition->submitter?->user,
                'description' => $requisition->title,
                'detail' => collect([
                    str($requisition->status)->replace('_', ' ')->title()->toString(),
                    $requisition->reviewer ? 'Reviewed by '.$requisition->reviewer->name : null,
                    $requisition->admin_reason ? 'Reason: '.$requisition->admin_reason : null,
                ])->filter()->implode(' | '),
                'bank_amount' => 0,
                'metadata' => [
                    'status' => $requisition->status,
                    'details' => $requisition->details,
                    'admin_reason' => $requisition->admin_reason,
                ],
            ]);

        return $transactions
            ->concat($requisitions)
            ->sortByDesc(fn (array $entry) => $entry['occurred_at']?->timestamp ?? 0)
            ->values();
    }

    private function transactionDetail(Transaction $transaction): string
    {
        $metadata = collect($transaction->metadata ?? []);

        return collect([
            $metadata->get('reason') ? 'Reason: '.$metadata->get('reason') : null,
            $metadata->get('admin') ? 'Admin: '.$metadata->get('admin') : null,
            $metadata->get('credits_before') && $metadata->get('credits_after')
                ? 'Credits '.number_format((int) $metadata->get('credits_before')).' -> '.number_format((int) $metadata->get('credits_after'))
                : null,
        ])->filter()->implode(' | ') ?: $transaction->description;
    }

    private function bankAmountForTransaction(Transaction $transaction): int
    {
        $metadataAmount = data_get($transaction->metadata, 'nation_amount');

        if (is_numeric($metadataAmount)) {
            return (int) $metadataAmount;
        }

        if (in_array($transaction->type, self::MONEY_IN_TYPES, true)) {
            return abs((int) $transaction->amount);
        }

        if (in_array($transaction->type, self::MONEY_OUT_TYPES, true)) {
            return -abs((int) $transaction->amount);
        }

        return (int) $transaction->amount;
    }

    private function buildLogStats(Faction $faction, Collection $entries): array
    {
        $moneyEntries = $entries->where('kind', 'transaction');
        $moneyIn = (int) $moneyEntries->sum(fn (array $entry) => max(0, (int) $entry['bank_amount']));
        $moneyOut = abs((int) $moneyEntries->sum(fn (array $entry) => min(0, (int) $entry['bank_amount'])));

        return [
            'total_logs' => $entries->count(),
            'money_in' => $moneyIn,
            'money_out' => $moneyOut,
            'net_bank_movement' => $moneyIn - $moneyOut,
            'current_bank' => (int) $faction->nation_bank_credits,
            'requisition_count' => $entries->where('kind', 'requisition')->count(),
        ];
    }

    private function paginateEntries(Collection $entries, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $entries->forPage($page, $perPage)->values(),
            $entries->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function resolvePerPage(Request $request): int
    {
        $requested = $request->query('per_page', 10);

        if ($requested === 'max') {
            return self::MAX_PER_PAGE;
        }

        $requested = (int) $requested;

        return in_array($requested, self::PER_PAGE_OPTIONS, true) ? $requested : 10;
    }
}
