<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\CharacterLandBuilding;
use App\Models\CharacterLandTile;
use App\Models\City;
use App\Models\Company;
use App\Models\DirectChatMessage;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\GlobalChatMessage;
use App\Models\Item;
use App\Models\Licence;
use App\Models\Location;
use App\Models\MapHex;
use App\Models\MapMarker;
use App\Models\MapPolygon;
use App\Models\Message;
use App\Models\NationChatMessage;
use App\Models\NationRequisition;
use App\Models\Skirmish;
use App\Models\StockHolding;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatisticsAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.statistics', [
            'statistics' => $this->payload(),
        ]);
    }

    public function state(): JsonResponse
    {
        return response()->json($this->payload());
    }

    private function payload(): array
    {
        $characters = Character::query();
        $transactions = Transaction::query();
        $totalMapHexes = MapHex::query()->count();
        $claimedMapHexes = MapHex::query()->whereNotNull('faction_id')->count();
        $onlineUsers = User::query()->where('last_seen_at', '>=', now()->subHour())->count();

        return [
            'generated_at' => now()->timezone(config('app.timezone'))->format('H:i:s'),
            'summary' => [
                ['label' => 'Users', 'value' => User::query()->count(), 'icon' => 'fa-users', 'tone' => 'blue'],
                ['label' => 'Characters', 'value' => (clone $characters)->count(), 'icon' => 'fa-id-card', 'tone' => 'green'],
                ['label' => 'Online Now', 'value' => $onlineUsers, 'icon' => 'fa-signal', 'tone' => 'lime'],
                ['label' => 'Factions', 'value' => Faction::query()->count(), 'icon' => 'fa-flag', 'tone' => 'gold'],
                ['label' => 'Player Credits', 'value' => (int) (clone $characters)->sum('plastic_credits'), 'icon' => 'fa-coins', 'tone' => 'gold'],
                ['label' => 'Nation Banks', 'value' => (int) Faction::query()->sum('nation_bank_credits'), 'icon' => 'fa-building-columns', 'tone' => 'blue'],
                ['label' => 'Transactions', 'value' => (clone $transactions)->count(), 'icon' => 'fa-receipt', 'tone' => 'slate'],
                ['label' => 'Territory Hexes', 'value' => $totalMapHexes, 'icon' => 'fa-map', 'tone' => 'green'],
            ],
            'activity' => $this->activitySeries(),
            'economy' => $this->economy(),
            'world' => $this->world(),
            'factions' => $this->factionBreakdown(),
            'territory' => [
                'total' => $totalMapHexes,
                'claimed' => $claimedMapHexes,
                'claimed_percent' => $totalMapHexes > 0 ? round(($claimedMapHexes / $totalMapHexes) * 100, 1) : 0,
                'types' => MapHex::query()
                    ->select('tile_type', DB::raw('COUNT(*) as total'))
                    ->groupBy('tile_type')
                    ->orderBy('tile_type')
                    ->get()
                    ->map(fn ($row) => [
                        'label' => str($row->tile_type)->replace('_', ' ')->title()->toString(),
                        'value' => (int) $row->total,
                    ])
                    ->values(),
            ],
            'content' => [
                ['label' => 'Items', 'value' => Item::query()->count(), 'icon' => 'fa-boxes-stacked'],
                ['label' => 'Licences', 'value' => Licence::query()->count(), 'icon' => 'fa-id-card'],
                ['label' => 'Jobs', 'value' => GameJob::query()->count(), 'icon' => 'fa-briefcase'],
                ['label' => 'Companies', 'value' => Company::query()->count(), 'icon' => 'fa-chart-line'],
                ['label' => 'Skirmishes', 'value' => Skirmish::query()->count(), 'icon' => 'fa-crosshairs'],
                ['label' => 'Units', 'value' => Unit::query()->count(), 'icon' => 'fa-shield-halved'],
            ],
        ];
    }

    private function economy(): array
    {
        $transactions = Transaction::query();

        return [
            'earned' => (int) (clone $transactions)->where('amount', '>', 0)->sum('amount'),
            'spent' => abs((int) (clone $transactions)->where('amount', '<', 0)->sum('amount')),
            'work_earned' => (int) Transaction::query()->where('type', 'work')->sum('amount'),
            'refunds' => (int) Transaction::query()->where('type', 'refund')->sum('amount'),
            'marketplace_spend' => abs((int) Transaction::query()->whereIn('type', ['item_purchase', 'licence_purchase'])->sum('amount')),
            'stock_volume' => abs((int) Transaction::query()->whereIn('type', ['stock_buy', 'stock_sell'])->sum('amount')),
            'bank_transfers' => abs((int) Transaction::query()->where('type', 'player_transfer_sent')->sum('amount')),
            'nation_donations' => abs((int) Transaction::query()->where('type', 'nation_donation')->sum('amount')),
        ];
    }

    private function world(): array
    {
        return [
            ['label' => 'Cities', 'value' => City::query()->count(), 'icon' => 'fa-city'],
            ['label' => 'Locations', 'value' => Location::query()->count(), 'icon' => 'fa-location-dot'],
            ['label' => 'Map Markers', 'value' => MapMarker::query()->count(), 'icon' => 'fa-map-location-dot'],
            ['label' => 'Map Polygons', 'value' => MapPolygon::query()->count(), 'icon' => 'fa-draw-polygon'],
            ['label' => 'Land Tiles', 'value' => CharacterLandTile::query()->count(), 'icon' => 'fa-border-all'],
            ['label' => 'Land Buildings', 'value' => CharacterLandBuilding::query()->count(), 'icon' => 'fa-house-chimney'],
            ['label' => 'Stock Holdings', 'value' => StockHolding::query()->sum('shares'), 'icon' => 'fa-chart-pie'],
            ['label' => 'Requisitions', 'value' => NationRequisition::query()->count(), 'icon' => 'fa-file-signature'],
            ['label' => 'Local Messages', 'value' => Message::query()->count(), 'icon' => 'fa-message'],
            ['label' => 'World Chat', 'value' => GlobalChatMessage::query()->count(), 'icon' => 'fa-comments'],
            ['label' => 'Nation Chat', 'value' => NationChatMessage::query()->count(), 'icon' => 'fa-people-group'],
            ['label' => 'Direct Chat', 'value' => DirectChatMessage::query()->count(), 'icon' => 'fa-paper-plane'],
        ];
    }

    private function factionBreakdown()
    {
        return Faction::query()
            ->withCount(['characters', 'mapHexes'])
            ->orderByDesc('characters_count')
            ->get()
            ->map(fn (Faction $faction) => [
                'label' => $faction->name,
                'color' => $faction->color ?: '#7ead59',
                'characters' => (int) $faction->characters_count,
                'bank' => (int) $faction->nation_bank_credits,
                'territory' => (int) $faction->map_hexes_count,
                'credits' => (int) Character::query()->where('faction_id', $faction->id)->sum('plastic_credits'),
            ])
            ->values();
    }

    private function activitySeries(): array
    {
        return collect(range(6, 0))
            ->map(fn (int $daysAgo) => now()->subDays($daysAgo)->startOfDay())
            ->map(function (Carbon $day) {
                $nextDay = $day->copy()->addDay();

                return [
                    'label' => $day->format('D'),
                    'transactions' => Transaction::query()->whereBetween('created_at', [$day, $nextDay])->count(),
                    'users' => User::query()->whereBetween('created_at', [$day, $nextDay])->count(),
                    'characters' => Character::query()->whereBetween('created_at', [$day, $nextDay])->count(),
                    'messages' => Message::query()->whereBetween('created_at', [$day, $nextDay])->count()
                        + GlobalChatMessage::query()->whereBetween('created_at', [$day, $nextDay])->count()
                        + NationChatMessage::query()->whereBetween('created_at', [$day, $nextDay])->count()
                        + DirectChatMessage::query()->whereBetween('created_at', [$day, $nextDay])->count(),
                ];
            })
            ->values()
            ->all();
    }
}
