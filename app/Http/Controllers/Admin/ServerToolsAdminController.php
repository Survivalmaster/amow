<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\Process\Process;
use Throwable;

class ServerToolsAdminController extends Controller
{
    private const ARTISAN_COMMANDS = [
        'migrate' => ['migrate', '--force'],
        'optimize' => ['optimize'],
        'optimize-clear' => ['optimize:clear'],
        'cache-clear' => ['cache:clear'],
        'config-cache' => ['config:cache'],
        'route-cache' => ['route:cache'],
        'view-cache' => ['view:cache'],
        'queue-restart' => ['queue:restart'],
        'storage-link' => ['storage:link'],
    ];

    private const GITHUB_ACTIONS = [
        'status' => [
            ['bin' => 'git', 'args' => ['status', '--short']],
        ],
        'pull' => [
            ['bin' => 'git', 'args' => ['pull', '--ff-only']],
        ],
        'deploy' => [
            ['bin' => 'git', 'args' => ['pull', '--ff-only']],
            ['bin' => 'composer', 'args' => ['install', '--no-dev', '--optimize-autoloader', '--no-interaction']],
            ['bin' => 'php', 'args' => ['artisan', 'migrate', '--force']],
            ['bin' => 'php', 'args' => ['artisan', 'optimize']],
        ],
        'npm-build' => [
            ['bin' => 'npm', 'args' => ['install']],
            ['bin' => 'npm', 'args' => ['run', 'build']],
        ],
    ];

    public function index(): View
    {
        return view('admin.server-tools', [
            'artisanCommands' => array_keys(self::ARTISAN_COMMANDS),
            'githubActions' => array_keys(self::GITHUB_ACTIONS),
            'projectPath' => config('server_tools.path'),
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'in:artisan,github'],
            'action' => ['required', 'string', 'max:80'],
        ]);

        $steps = $this->stepsFor($validated['section'], $validated['action']);

        if ($steps === []) {
            return back()->withErrors(['tools' => 'That server tool action is not available.']);
        }

        $results = [];

        foreach ($steps as $step) {
            $result = $this->runStep($step);
            $results[] = $result;

            if ($result['exit_code'] !== 0 || $result['timed_out']) {
                break;
            }
        }

        return back()->with('tool_result', [
            'title' => Str::title($validated['section']).': '.$validated['action'],
            'failed' => collect($results)->contains(fn (array $result): bool => $result['exit_code'] !== 0 || $result['timed_out']),
            'results' => $results,
        ]);
    }

    private function stepsFor(string $section, string $action): array
    {
        if ($section === 'artisan' && isset(self::ARTISAN_COMMANDS[$action])) {
            return [
                ['bin' => 'php', 'args' => ['artisan', ...self::ARTISAN_COMMANDS[$action]]],
            ];
        }

        if ($section === 'github' && isset(self::GITHUB_ACTIONS[$action])) {
            return self::GITHUB_ACTIONS[$action];
        }

        return [];
    }

    private function runStep(array $step): array
    {
        $startedAt = microtime(true);
        $binaries = config('server_tools.binaries', []);
        $binary = $binaries[$step['bin']] ?? $step['bin'];
        $process = new Process([$binary, ...$step['args']], config('server_tools.path'));
        $process->setTimeout(max(1, ((int) config('server_tools.timeout', 120000)) / 1000));
        $timedOut = false;

        try {
            $process->run();
            $output = trim($process->getOutput()."\n".$process->getErrorOutput());
            $exitCode = $process->getExitCode() ?? 1;
        } catch (Throwable $exception) {
            $timedOut = str_contains(strtolower($exception->getMessage()), 'timed out');
            $output = $exception->getMessage();
            $exitCode = 1;
        }

        return [
            'label' => $step['bin'].' '.implode(' ', $step['args']),
            'exit_code' => $exitCode,
            'timed_out' => $timedOut,
            'duration_seconds' => round(microtime(true) - $startedAt, 2),
            'output' => $output !== '' ? $output : '(no output)',
        ];
    }
}
