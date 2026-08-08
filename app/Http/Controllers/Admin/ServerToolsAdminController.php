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
            'gitRepoPath' => config('server_tools.git_repo_path'),
            'gitBranch' => config('server_tools.git_branch'),
            'gitSshCommand' => config('server_tools.git_ssh_command'),
            'binaries' => config('server_tools.binaries', []),
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
            return $this->githubSteps($action);
        }

        return [];
    }

    private function githubSteps(string $action): array
    {
        $repoPath = config('server_tools.git_repo_path');
        $branch = config('server_tools.git_branch', 'main');

        if (! is_string($repoPath) || trim($repoPath) === '') {
            return self::GITHUB_ACTIONS[$action];
        }

        $gitDir = '--git-dir='.trim($repoPath);
        $workTree = '--work-tree='.config('server_tools.path');
        $fetchBranch = $branch.':refs/heads/'.$branch;

        return match ($action) {
            'status' => [
                ['bin' => 'git', 'args' => [$gitDir, 'remote', '-v']],
                ['bin' => 'git', 'args' => [$gitDir, 'log', '--oneline', '--decorate', '-5', $branch]],
            ],
            'pull' => [
                ['bin' => 'git', 'args' => [$gitDir, 'fetch', 'origin', $fetchBranch]],
            ],
            'deploy' => [
                ['bin' => 'git', 'args' => [$gitDir, 'fetch', 'origin', $fetchBranch]],
                ['bin' => 'git', 'args' => [$gitDir, $workTree, 'checkout', '-f', $branch, '--', '.']],
                ['bin' => 'composer', 'args' => ['install', '--no-dev', '--optimize-autoloader', '--no-interaction']],
                ['bin' => 'php', 'args' => ['artisan', 'migrate', '--force']],
                ['bin' => 'php', 'args' => ['artisan', 'optimize']],
            ],
            default => self::GITHUB_ACTIONS[$action],
        };
    }

    private function runStep(array $step): array
    {
        $startedAt = microtime(true);
        $binaries = config('server_tools.binaries', []);
        [$binary, $args] = $this->commandFor($step, $binaries);
        $process = new Process([$binary, ...$args], config('server_tools.path'));
        $process->setEnv($this->processEnvironment($step));
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

    private function commandFor(array $step, array $binaries): array
    {
        $binary = $binaries[$step['bin']] ?? $step['bin'];

        if ($step['bin'] === 'composer') {
            $phpBinary = $binaries['php'] ?? null;

            if (
                is_string($phpBinary)
                && trim($phpBinary) !== ''
                && is_string($binary)
                && str_contains($binary, DIRECTORY_SEPARATOR)
            ) {
                return [trim($phpBinary), [trim($binary), ...$step['args']]];
            }
        }

        return [$binary, $step['args']];
    }

    private function processEnvironment(array $step): array
    {
        $environment = [];
        $binaries = config('server_tools.binaries', []);
        $phpBinary = $binaries['php'] ?? null;
        $gitSshCommand = config('server_tools.git_ssh_command');

        if (is_string($phpBinary) && str_contains($phpBinary, DIRECTORY_SEPARATOR)) {
            $phpDirectory = dirname($phpBinary);

            if (is_dir($phpDirectory)) {
                $currentPath = getenv('PATH') ?: getenv('Path') ?: '';
                $environment['PATH'] = $phpDirectory.($currentPath !== '' ? PATH_SEPARATOR.$currentPath : '');
            }
        }

        if ($step['bin'] === 'git' && is_string($gitSshCommand) && trim($gitSshCommand) !== '') {
            $environment['GIT_SSH_COMMAND'] = trim($gitSshCommand);
        }

        return $environment;
    }
}
