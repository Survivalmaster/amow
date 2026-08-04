<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Licence;
use App\Models\PlayerBusiness;
use App\Models\PlayerBusinessMember;
use App\Support\CharacterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlayerBusinessController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeDeveloper($request);

        $character = $request->user()->character()->with(['faction', 'licences'])->firstOrFail();
        $businesses = PlayerBusiness::query()
            ->with(['owner', 'faction', 'menuItems' => fn ($query) => $query->where('is_active', true)->latest()])
            ->orderBy('name')
            ->get();

        return view('businesses.index', [
            'businesses' => $businesses,
            'character' => $character,
            'creationLicences' => $character->licences->where('grants_business_creation', true)->values(),
            'businessTypes' => PlayerBusiness::TYPES,
            'businessIcons' => PlayerBusiness::ICONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeDeveloper($request);

        $character = $request->user()->character()->with(['faction', 'licences'])->firstOrFail();

        if (! $character->canCreatePlayerBusiness()) {
            return back()->withErrors(['business' => 'Your character needs a business creation licence first.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon_class' => ['required', 'string', Rule::in(array_keys(PlayerBusiness::ICONS))],
            'business_type' => ['required', 'string', Rule::in(array_keys(PlayerBusiness::TYPES))],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $licence = $character->licences->firstWhere('grants_business_creation', true);
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $suffix = 2;

        while (PlayerBusiness::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $business = null;

        DB::transaction(function () use (&$business, $character, $licence, $validated, $slug) {
            $business = PlayerBusiness::query()->create([
                'owner_character_id' => $character->id,
                'faction_id' => $character->faction_id,
                'licence_id' => $licence?->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'icon_class' => $validated['icon_class'],
                'business_type' => $validated['business_type'],
                'description' => $validated['description'] ?? null,
            ]);

            $ownerRole = $business->roles()->create([
                'name' => 'Owner',
                'hourly_wage' => 0,
            ]);

            $business->members()->create([
                'character_id' => $character->id,
                'player_business_role_id' => $ownerRole->id,
                'status' => PlayerBusinessMember::STATUS_ACTIVE,
                'joined_at' => now(),
            ]);

            $business->logs()->create([
                'actor_character_id' => $character->id,
                'type' => 'business_created',
                'description' => "{$character->name} founded {$business->name}.",
            ]);
        });

        return redirect()->route('businesses.show', $business)->with('status', 'Business created.');
    }

    public function show(Request $request, PlayerBusiness $playerBusiness): View
    {
        $this->authorizeDeveloper($request);

        $character = $request->user()->character()->with('faction')->firstOrFail();
        $playerBusiness->load([
            'owner',
            'faction',
            'menuItems' => fn ($query) => $query->latest(),
            'roles',
            'members.character',
            'members.role',
            'logs.actor',
        ]);

        return view('businesses.show', [
            'business' => $playerBusiness,
            'character' => $character,
            'businessTypes' => PlayerBusiness::TYPES,
            'isOwner' => $playerBusiness->owner_character_id === $character->id,
            'member' => $playerBusiness->members->firstWhere('character_id', $character->id),
        ]);
    }

    public function storeMenuItem(Request $request, PlayerBusiness $playerBusiness): RedirectResponse
    {
        $this->authorizeOwner($request, $playerBusiness);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'mode' => ['required', 'string', Rule::in(array_keys(PlayerBusiness::TYPES))],
            'price' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $playerBusiness->menuItems()->create([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->log($request, $playerBusiness, 'menu_updated', 'Added a business menu entry.');

        return back()->with('status', 'Menu item added.');
    }

    public function storeRole(Request $request, PlayerBusiness $playerBusiness): RedirectResponse
    {
        $this->authorizeOwner($request, $playerBusiness);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hourly_wage' => ['required', 'integer', 'min:0'],
        ]);

        $playerBusiness->roles()->create($validated);
        $this->log($request, $playerBusiness, 'role_created', "Created the {$validated['name']} role at {$validated['hourly_wage']} credits per hour.");

        return back()->with('status', 'Role created.');
    }

    public function invite(Request $request, PlayerBusiness $playerBusiness): RedirectResponse
    {
        $this->authorizeOwner($request, $playerBusiness);

        $validated = $request->validate([
            'character_name' => ['required', 'string', 'max:255'],
            'player_business_role_id' => ['nullable', 'exists:player_business_roles,id'],
        ]);

        $owner = $request->user()->character()->firstOrFail();
        $target = Character::query()->where('name', $validated['character_name'])->first();

        if (! $target || $target->faction_id !== $playerBusiness->faction_id) {
            return back()->withErrors(['invite' => 'Only characters in this business nation can be invited.']);
        }

        if (($validated['player_business_role_id'] ?? null) && ! $playerBusiness->roles()->whereKey($validated['player_business_role_id'])->exists()) {
            return back()->withErrors(['invite' => 'That role does not belong to this business.']);
        }

        $playerBusiness->members()->updateOrCreate(
            ['character_id' => $target->id],
            [
                'player_business_role_id' => $validated['player_business_role_id'] ?? null,
                'invited_by_character_id' => $owner->id,
                'status' => PlayerBusinessMember::STATUS_INVITED,
                'joined_at' => null,
            ]
        );

        $this->log($request, $playerBusiness, 'member_invited', "Invited {$target->name} to join.");

        return back()->with('status', 'Invite sent.');
    }

    public function join(Request $request, PlayerBusiness $playerBusiness): RedirectResponse
    {
        $this->authorizeDeveloper($request);

        $character = $request->user()->character()->firstOrFail();
        $member = $playerBusiness->members()->where('character_id', $character->id)->first();

        if (! $member || $member->status !== PlayerBusinessMember::STATUS_INVITED || $character->faction_id !== $playerBusiness->faction_id) {
            return back()->withErrors(['business' => 'You need an invite from this nation before joining.']);
        }

        $member->update([
            'status' => PlayerBusinessMember::STATUS_ACTIVE,
            'joined_at' => now(),
            'last_paid_at' => now(),
        ]);

        $this->log($request, $playerBusiness, 'member_joined', "{$character->name} joined the business.");

        return back()->with('status', 'You joined the business.');
    }

    public function deposit(Request $request, PlayerBusiness $playerBusiness): RedirectResponse
    {
        $this->authorizeOwner($request, $playerBusiness);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $character = $request->user()->character()->firstOrFail();

        if ($character->plastic_credits < $validated['amount']) {
            return back()->withErrors(['bank' => 'Your character does not have enough credits.']);
        }

        DB::transaction(function () use ($character, $playerBusiness, $validated) {
            $character->decrement('plastic_credits', $validated['amount']);
            $playerBusiness->increment('bank_credits', $validated['amount']);
            $playerBusiness->logs()->create([
                'actor_character_id' => $character->id,
                'type' => 'bank_deposit',
                'amount' => $validated['amount'],
                'description' => "{$character->name} deposited {$validated['amount']} credits.",
            ]);
            CharacterActivity::recordTransaction($character, 'business_bank_deposit', -$validated['amount'], "Deposited credits into {$playerBusiness->name}.");
        });

        return back()->with('status', 'Business bank funded.');
    }

    public function withdraw(Request $request, PlayerBusiness $playerBusiness): RedirectResponse
    {
        $this->authorizeOwner($request, $playerBusiness);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        if ($playerBusiness->bank_credits < $validated['amount']) {
            return back()->withErrors(['bank' => 'The business bank does not have enough credits.']);
        }

        $character = $request->user()->character()->firstOrFail();

        DB::transaction(function () use ($character, $playerBusiness, $validated) {
            $playerBusiness->decrement('bank_credits', $validated['amount']);
            $character->increment('plastic_credits', $validated['amount']);
            $playerBusiness->logs()->create([
                'actor_character_id' => $character->id,
                'type' => 'bank_withdrawal',
                'amount' => -$validated['amount'],
                'description' => "{$character->name} withdrew {$validated['amount']} credits.",
            ]);
            CharacterActivity::recordTransaction($character, 'business_bank_withdrawal', $validated['amount'], "Withdrew credits from {$playerBusiness->name}.");
        });

        return back()->with('status', 'Credits withdrawn.');
    }

    protected function authorizeDeveloper(Request $request): void
    {
        abort_unless($request->user()?->loadMissing('permissions')->hasPermission('developer'), 403);
    }

    protected function authorizeOwner(Request $request, PlayerBusiness $playerBusiness): void
    {
        $this->authorizeDeveloper($request);

        $character = $request->user()->character()->firstOrFail();
        abort_unless($playerBusiness->owner_character_id === $character->id, 403);
    }

    protected function log(Request $request, PlayerBusiness $playerBusiness, string $type, string $description): void
    {
        $character = $request->user()->character()->firstOrFail();

        $playerBusiness->logs()->create([
            'actor_character_id' => $character->id,
            'type' => $type,
            'description' => $description,
        ]);
    }
}
