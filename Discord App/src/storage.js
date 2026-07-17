const fs = require('node:fs/promises');
const path = require('node:path');

const dataDir = path.join(__dirname, '..', 'data');
const panelsPath = path.join(dataDir, 'panels.json');
const rankPanelsPath = path.join(dataDir, 'rankPanels.json');
const settingsPath = path.join(dataDir, 'settings.json');
const cooldownsPath = path.join(dataDir, 'cooldowns.json');

async function ensureStore() {
  await fs.mkdir(dataDir, { recursive: true });

  try {
    await fs.access(panelsPath);
  } catch {
    await fs.writeFile(panelsPath, JSON.stringify({ panels: {} }, null, 2));
  }

  try {
    await fs.access(settingsPath);
  } catch {
    await fs.writeFile(settingsPath, JSON.stringify({ guilds: {} }, null, 2));
  }

  try {
    await fs.access(rankPanelsPath);
  } catch {
    await fs.writeFile(rankPanelsPath, JSON.stringify({ panels: {} }, null, 2));
  }

  try {
    await fs.access(cooldownsPath);
  } catch {
    await fs.writeFile(cooldownsPath, JSON.stringify({ panels: {} }, null, 2));
  }
}

async function readPanels() {
  await ensureStore();
  const raw = await fs.readFile(panelsPath, 'utf8');
  return JSON.parse(raw);
}

async function writePanels(data) {
  await ensureStore();
  await fs.writeFile(panelsPath, JSON.stringify(data, null, 2));
}

async function getPanel(messageId) {
  const data = await readPanels();
  return data.panels[messageId] ?? null;
}

async function savePanel(panel) {
  const data = await readPanels();
  data.panels[panel.messageId] = panel;
  await writePanels(data);
  return panel;
}

async function deletePanel(messageId) {
  const data = await readPanels();
  const existed = Boolean(data.panels[messageId]);
  delete data.panels[messageId];
  await writePanels(data);
  return existed;
}

async function readRankPanels() {
  await ensureStore();
  const raw = await fs.readFile(rankPanelsPath, 'utf8');
  return JSON.parse(raw);
}

async function writeRankPanels(data) {
  await ensureStore();
  await fs.writeFile(rankPanelsPath, JSON.stringify(data, null, 2));
}

async function getRankPanel(messageId) {
  const data = await readRankPanels();
  return data.panels[messageId] ?? null;
}

async function saveRankPanel(panel) {
  const data = await readRankPanels();
  data.panels[panel.messageId] = panel;
  await writeRankPanels(data);
  return panel;
}

async function deleteRankPanel(messageId) {
  const data = await readRankPanels();
  const existed = Boolean(data.panels[messageId]);
  delete data.panels[messageId];
  await writeRankPanels(data);
  return existed;
}

async function readSettings() {
  await ensureStore();
  const raw = await fs.readFile(settingsPath, 'utf8');
  return JSON.parse(raw);
}

async function writeSettings(data) {
  await ensureStore();
  await fs.writeFile(settingsPath, JSON.stringify(data, null, 2));
}

async function getGuildSettings(guildId) {
  const data = await readSettings();
  return data.guilds[guildId] ?? {};
}

async function saveGuildSettings(guildId, settings) {
  const data = await readSettings();
  data.guilds[guildId] = {
    ...(data.guilds[guildId] ?? {}),
    ...settings
  };
  await writeSettings(data);
  return data.guilds[guildId];
}

async function readCooldowns() {
  await ensureStore();
  const raw = await fs.readFile(cooldownsPath, 'utf8');
  return JSON.parse(raw);
}

async function writeCooldowns(data) {
  await ensureStore();
  await fs.writeFile(cooldownsPath, JSON.stringify(data, null, 2));
}

async function getPanelUserCooldown(messageId, userId) {
  const data = await readCooldowns();
  return data.panels[messageId]?.[userId] ?? null;
}

async function setPanelUserCooldown(messageId, userId, value) {
  const data = await readCooldowns();
  data.panels[messageId] = data.panels[messageId] ?? {};
  data.panels[messageId][userId] = value;
  await writeCooldowns(data);
  return value;
}

async function deletePanelCooldowns(messageId) {
  const data = await readCooldowns();
  delete data.panels[messageId];
  await writeCooldowns(data);
}

module.exports = {
  deletePanelCooldowns,
  deletePanel,
  deleteRankPanel,
  getGuildSettings,
  getPanelUserCooldown,
  getPanel,
  getRankPanel,
  readPanels,
  readRankPanels,
  readSettings,
  saveGuildSettings,
  setPanelUserCooldown,
  savePanel,
  saveRankPanel
};
