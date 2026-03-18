<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NationRequisition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NationRequisitionAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.nation-requisitions', [
            'requisitions' => NationRequisition::query()
                ->with(['faction', 'submitter', 'reviewer'])
                ->latest()
                ->get(),
        ]);
    }

    public function update(Request $request, NationRequisition $nationRequisition): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                NationRequisition::STATUS_SUBMITTED,
                NationRequisition::STATUS_BEING_REVIEWED,
                NationRequisition::STATUS_ACCEPTED,
                NationRequisition::STATUS_DENIED,
            ])],
            'admin_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if (in_array($validated['status'], [NationRequisition::STATUS_ACCEPTED, NationRequisition::STATUS_DENIED], true) && blank($validated['admin_reason'] ?? null)) {
            return back()->withErrors(['admin_reason' => 'A reason is required when accepting or denying a requisition.']);
        }

        $nationRequisition->update([
            'status' => $validated['status'],
            'admin_reason' => $validated['admin_reason'] ?? null,
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Nation requisition updated.');
    }
}
