<?php

namespace App\Http\Controllers;

use App\Models\Changelog;
use Illuminate\View\View;

class ChangelogController extends Controller
{
    public function index(): View
    {
        return view('changelogs.index', [
            'changelogs' => Changelog::query()
                ->released()
                ->latest('released_at')
                ->latest()
                ->get(),
        ]);
    }
}
