const { EmbedBuilder, PermissionFlagsBits } = require('discord.js');
const { execFile } = require('node:child_process');
const path = require('node:path');

const DEFAULT_TIMEOUT_MS = Number(process.env.AMOW_TOOLS_TIMEOUT_MS || 120000);
const MAX_OUTPUT_LENGTH = 3500;

const PROJECT_PATH = process.env.AMOW_DEPLOY_PATH
  ? path.resolve(process.env.AMOW_DEPLOY_PATH)
  : path.resolve(__dirname, '..', '..');
const GIT_REPO_PATH = process.env.AMOW_GIT_REPO_PATH
  ? path.resolve(process.env.AMOW_GIT_REPO_PATH)
  : '';
const GIT_BRANCH = process.env.AMOW_GIT_BRANCH || 'main';
const GIT_SSH_COMMAND = process.env.AMOW_GIT_SSH_COMMAND || '';

const BINARIES = {
  php: process.env.AMOW_PHP_BINARY || 'php',
  git: process.env.AMOW_GIT_BINARY || 'git',
  composer: process.env.AMOW_COMPOSER_BINARY || 'composer',
  npm: process.env.AMOW_NPM_BINARY || 'npm'
};

const ARTISAN_COMMANDS = {
  'migrate': ['migrate', '--force'],
  'optimize': ['optimize'],
  'optimize-clear': ['optimize:clear'],
  'cache-clear': ['cache:clear'],
  'config-cache': ['config:cache'],
  'route-cache': ['route:cache'],
  'view-cache': ['view:cache'],
  'queue-restart': ['queue:restart'],
  'storage-link': ['storage:link']
};

const GITHUB_ACTIONS = {
  'npm-build': [
    { bin: 'npm', args: ['install'] },
    { bin: 'npm', args: ['run', 'build'] }
  ]
};

async function handleToolsCommand(interaction) {
  ensureAdministrator(interaction);

  const group = interaction.options.getSubcommandGroup();
  const subcommand = interaction.options.getSubcommand();

  await interaction.deferReply({ ephemeral: true });

  if (group === 'artisan') {
    await handleArtisan(interaction, subcommand);
    return;
  }

  if (group === 'github') {
    await handleGithub(interaction, subcommand);
    return;
  }

  await interaction.editReply('Unknown tools section.');
}

async function handleArtisan(interaction, subcommand) {
  const args = ARTISAN_COMMANDS[subcommand];

  if (!args) {
    await interaction.editReply('That Artisan command is not configured.');
    return;
  }

  const result = await runStep({ bin: 'php', args: ['artisan', ...args] });
  await replyWithResult(interaction, `Artisan: ${subcommand}`, [result]);
}

async function handleGithub(interaction, subcommand) {
  const steps = githubSteps(subcommand);

  if (!steps) {
    await interaction.editReply('That GitHub action is not configured.');
    return;
  }

  const results = [];

  for (const step of steps) {
    const result = await runStep(step);
    results.push(result);

    if (result.exitCode !== 0) {
      break;
    }
  }

  await replyWithResult(interaction, `GitHub: ${subcommand}`, results);
}

function githubSteps(action) {
  if (!GIT_REPO_PATH) {
    return {
      status: [
        { bin: 'git', args: ['status', '--short'] }
      ],
      pull: [
        { bin: 'git', args: ['pull', '--ff-only'] }
      ],
      deploy: [
        { bin: 'git', args: ['pull', '--ff-only'] },
        { bin: 'composer', args: ['install', '--no-dev', '--optimize-autoloader', '--no-interaction'] },
        { bin: 'php', args: ['artisan', 'migrate', '--force'] },
        { bin: 'php', args: ['artisan', 'optimize'] }
      ],
      ...GITHUB_ACTIONS
    }[action];
  }

  const gitDir = `--git-dir=${GIT_REPO_PATH}`;
  const workTree = `--work-tree=${PROJECT_PATH}`;
  const fetchBranch = `${GIT_BRANCH}:refs/heads/${GIT_BRANCH}`;

  return {
    status: [
      { bin: 'git', args: [gitDir, 'remote', '-v'] },
      { bin: 'git', args: [gitDir, 'log', '--oneline', '--decorate', '-5', GIT_BRANCH] }
    ],
    pull: [
      { bin: 'git', args: [gitDir, 'fetch', 'origin', fetchBranch] }
    ],
    deploy: [
      { bin: 'git', args: [gitDir, 'fetch', 'origin', fetchBranch] },
      { bin: 'git', args: [gitDir, workTree, 'checkout', '-f', GIT_BRANCH, '--', '.'] },
      { bin: 'composer', args: ['install', '--no-dev', '--optimize-autoloader', '--no-interaction'] },
      { bin: 'php', args: ['artisan', 'migrate', '--force'] },
      { bin: 'php', args: ['artisan', 'optimize'] }
    ],
    ...GITHUB_ACTIONS
  }[action];
}

function ensureAdministrator(interaction) {
  if (!interaction.memberPermissions?.has(PermissionFlagsBits.Administrator)) {
    const error = new Error('Only Discord administrators can use AMOW tools.');
    error.publicReply = true;
    throw error;
  }
}

function runStep(step) {
  const binary = BINARIES[step.bin] || step.bin;
  const label = `${step.bin} ${step.args.join(' ')}`;

  return new Promise((resolve) => {
    const startedAt = Date.now();

    execFile(binary, step.args, {
      cwd: PROJECT_PATH,
      env: processEnvironment(step),
      timeout: DEFAULT_TIMEOUT_MS,
      windowsHide: true,
      maxBuffer: 1024 * 1024
    }, (error, stdout, stderr) => {
      const errorText = error && typeof error.code !== 'number'
        ? `${error.code || 'ERROR'}: ${error.message}`
        : '';
      const output = [stdout, stderr, errorText].filter(Boolean).join('\n').trim();

      resolve({
        label,
        exitCode: error ? (typeof error.code === 'number' ? error.code : 1) : 0,
        timedOut: Boolean(error?.killed),
        durationMs: Date.now() - startedAt,
        output: output || '(no output)'
      });
    });
  });
}

function processEnvironment(step) {
  return {
    ...process.env,
    ...(step.bin === 'git' && GIT_SSH_COMMAND ? { GIT_SSH_COMMAND } : {})
  };
}

async function replyWithResult(interaction, title, results) {
  const failed = results.some((result) => result.exitCode !== 0 || result.timedOut);
  const description = results
    .map(formatResult)
    .join('\n\n')
    .slice(0, MAX_OUTPUT_LENGTH);

  const embed = new EmbedBuilder()
    .setTitle(title)
    .setDescription(description || '(no output)')
    .setColor(failed ? 0xc65b3f : 0x7ead59)
    .setFooter({ text: `Path: ${PROJECT_PATH}${GIT_REPO_PATH ? ` | Git: ${GIT_REPO_PATH}` : ''}` })
    .setTimestamp(new Date());

  await interaction.editReply({ embeds: [embed] });
}

function formatResult(result) {
  const status = result.timedOut
    ? 'Timed out'
    : result.exitCode === 0
      ? 'OK'
      : `Exit ${result.exitCode}`;
  const output = truncate(result.output, 900);

  return `**${result.label}**\n${status} in ${Math.round(result.durationMs / 1000)}s\n\`\`\`\n${sanitizeCodeBlock(output)}\n\`\`\``;
}

function truncate(value, maxLength) {
  if (value.length <= maxLength) {
    return value;
  }

  return `${value.slice(0, maxLength - 20)}\n...output truncated`;
}

function sanitizeCodeBlock(value) {
  return value.replaceAll('```', "'''");
}

module.exports = {
  handleToolsCommand
};
