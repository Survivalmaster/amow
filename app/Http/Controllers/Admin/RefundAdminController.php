<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\IssueCharacterRefund;
use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RefundAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.refunds', [
            'characters' => Character::query()
                ->with(['user', 'faction', 'rank', 'currentJob'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, IssueCharacterRefund $issueRefund): RedirectResponse
    {
        $validated = $request->validate([
            'character_id' => ['required', 'integer', 'exists:characters,id'],
            'plastic_credits' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'experience_points' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $credits = (int) ($validated['plastic_credits'] ?? 0);
        $experience = (int) ($validated['experience_points'] ?? 0);

        if ($credits <= 0 && $experience <= 0) {
            throw ValidationException::withMessages([
                'refund' => 'Add XP, Plastic Credits, or both before issuing a refund.',
            ]);
        }

        $character = Character::query()->findOrFail($validated['character_id']);
        $issueRefund->execute($character, $request->user(), $credits, $experience, $validated['reason']);

        return redirect()
            ->route('admin.refunds.index')
            ->with('status', "Refund issued to {$character->name}.");
    }
}
