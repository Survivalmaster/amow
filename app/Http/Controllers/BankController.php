<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Support\CharacterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with(['faction', 'rank'])->firstOrFail();

        return view('bank.index', [
            'character' => $character,
            'marketplaceSection' => 'bank',
            'sameFactionCharacters' => $this->sameFactionRecipients($character)->get(),
            'recentTransfers' => $character
                ->transactions()
                ->whereIn('type', ['player_transfer_sent', 'player_transfer_received'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    public function transfer(Request $request): RedirectResponse
    {
        $character = $request->user()->character()->with('faction')->firstOrFail();

        $validated = $request->validate([
            'recipient_character_id' => [
                'required',
                'integer',
                Rule::exists('characters', 'id')->where(fn ($query) => $query
                    ->where('faction_id', $character->faction_id)
                    ->where('id', '!=', $character->id)
                ),
            ],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'note' => ['nullable', 'string', 'max:160'],
        ]);

        try {
            DB::transaction(function () use ($character, $validated): void {
                $sender = Character::query()
                    ->with('faction')
                    ->whereKey($character->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $recipient = Character::query()
                    ->whereKey($validated['recipient_character_id'])
                    ->where('faction_id', $sender->faction_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($sender->plastic_credits < $validated['amount']) {
                    throw new \RuntimeException('You do not have enough Plastic Credits to send that amount.');
                }

                $transferId = (string) Str::uuid();
                $amount = (int) $validated['amount'];
                $note = filled($validated['note'] ?? null) ? trim($validated['note']) : null;
                $senderBefore = (int) $sender->plastic_credits;
                $recipientBefore = (int) $recipient->plastic_credits;

                $sender->decrement('plastic_credits', $amount);
                $recipient->increment('plastic_credits', $amount);

                CharacterActivity::recordTransaction(
                    $sender->fresh(),
                    'player_transfer_sent',
                    -$amount,
                    "Sent {$amount} Plastic Credits to {$recipient->name}.",
                    [
                        'transfer_id' => $transferId,
                        'recipient_character_id' => $recipient->id,
                        'recipient_name' => $recipient->name,
                        'faction_id' => $sender->faction_id,
                        'faction_name' => $sender->faction?->name,
                        'credits_before' => $senderBefore,
                        'credits_after' => $senderBefore - $amount,
                        'note' => $note,
                    ]
                );

                CharacterActivity::recordTransaction(
                    $recipient->fresh(),
                    'player_transfer_received',
                    $amount,
                    "Received {$amount} Plastic Credits from {$sender->name}.",
                    [
                        'transfer_id' => $transferId,
                        'sender_character_id' => $sender->id,
                        'sender_name' => $sender->name,
                        'faction_id' => $sender->faction_id,
                        'faction_name' => $sender->faction?->name,
                        'credits_before' => $recipientBefore,
                        'credits_after' => $recipientBefore + $amount,
                        'note' => $note,
                    ]
                );
            });
        } catch (\RuntimeException $exception) {
            return back()->withInput()->withErrors(['amount' => $exception->getMessage()]);
        }

        return back()->with('status', 'Bank transfer sent.');
    }

    private function sameFactionRecipients(Character $character)
    {
        return Character::query()
            ->with(['rank', 'user'])
            ->where('faction_id', $character->faction_id)
            ->whereKeyNot($character->id)
            ->orderBy('name');
    }
}
