# Discord Role Bot

A small Node.js Discord bot with admin-configurable button panels for self-assignable roles.

## Setup

1. Install dependencies:

   ```bash
   npm install
   ```

2. Copy `.env.example` to `.env` and fill in:

   ```env
   DISCORD_TOKEN=your_bot_token_here
   CLIENT_ID=your_application_client_id_here
   GUILD_ID=your_test_server_id_here
   WEBSITE_BASE_URL=https://your-site.example
   WEBSITE_DISCORD_SYNC_SECRET=match_your_laravel_DISCORD_BOT_SYNC_SECRET
   ```

   `GUILD_ID` is optional, but recommended while developing because command updates appear in that server almost instantly.
   The website sync values are optional for local bot-only testing, but required if you want Discord roles and role members to appear under **Admin > Discord Management**.

3. Register slash commands:

   ```bash
   npm run deploy:commands
   ```

4. Start the bot:

   ```bash
   npm start
   ```

## Bot Permissions

The bot needs:

- Manage Roles
- Manage Server
- Send Messages
- Embed Links
- Read Message History
- Use Application Commands
- View Audit Log

For any role panel role, the bot's highest role must be above that role in the server role list.

For deleted message logs to include message text, enable **Message Content Intent** in the Discord Developer Portal under the bot's Privileged Gateway Intents.
For website role/member sync, enable **Server Members Intent** in the same portal area.

## Role Panels

Create a panel:

```text
/role-panel create title:"Choose your colour" description:"Pick one colour role." exclusive:true
```

Create a panel with a cooldown:

```text
/role-panel create title:"Choose your colour" description:"Pick one colour role." exclusive:true cooldown:24h
```

Create a panel with public role-change logs:

```text
/role-panel create title:"Choose your colour" description:"Pick one colour role." exclusive:true log-channel:#role-logs
```

Add up to 10 role buttons:

```text
/role-panel add message-id:123456789 role:@Red label:"Red" style:Danger
/role-panel add message-id:123456789 role:@Blue label:"Blue" style:Primary
```

If `exclusive` is enabled, clicking a new role button removes any other role from that same panel before assigning the new one. If `exclusive` is disabled, users can take multiple roles from the panel, and clicking a role they already have removes it.

Other admin commands:

```text
/role-panel list message-id:123456789
/role-panel log-channel message-id:123456789 channel:#role-logs
/role-panel log-channel message-id:123456789 clear:true
/role-panel cooldown message-id:123456789 duration:7d
/role-panel cooldown message-id:123456789 duration:0
/role-panel refresh message-id:123456789
/role-panel remove message-id:123456789 role:@Red
/role-panel delete message-id:123456789
```

Deleting a panel removes the bot's panel message and deletes the saved config.
Role panels show live role counts and refresh after each button click.
Cooldown durations support minutes, hours, days, and weeks, such as `30m`, `24h`, `7d`, or `2w`.

## Leadership Rank Panels

Rank panels let a leadership role manage only the rank roles you add to that panel, and only for members with the configured team role.

Create a panel:

```text
/rank-panel create title:"Fire Department Ranks" description:"Select a team member, then choose their rank." leadership-role:@Fire Command team-role:@Fire Department channel:#fire-leadership log-channel:#rank-logs
```

Edit a panel title or description:

```text
/rank-panel edit message-id:123456789 title:"Fire Department Rank Manager"
/rank-panel edit message-id:123456789 description:"Manage the ranks of your department members."
```

Add up to 25 manageable rank roles:

```text
/rank-panel add-rank message-id:123456789 role:@Probationary Firefighter
/rank-panel add-rank message-id:123456789 role:@Firefighter
/rank-panel add-rank message-id:123456789 role:@Lieutenant
```

Add an optional second role for ranks that should grant an extra permission or leadership role:

```text
/rank-panel add-rank message-id:123456789 role:@Major General extra-role:@Green Nation Leadership
```

Edit a rank label or extra role without removing it:

```text
/rank-panel edit-rank message-id:123456789 role:@Major General label:"Major General"
/rank-panel edit-rank message-id:123456789 role:@Major General extra-role:@Green Nation Leadership
/rank-panel edit-rank message-id:123456789 role:@Major General clear-extra-role:true
```

Leaders use the panel by selecting a member from the team dropdown. If the configured team has more than 25 members, Discord's component limit means the panel falls back to a searchable user picker, and the bot validates the selected member has the team role. The bot replies privately with a rank dropdown for that selected member. When a rank is chosen, the bot removes the member's other configured rank roles and configured extra roles from that panel, then applies the new rank and its extra role if one is configured.

Safety checks:

- The user must have the configured leadership role and team role, unless they have Manage Roles.
- The selected member must have the configured team role.
- Leaders cannot change their own rank from the panel.
- Leaders cannot manage someone with a higher highest role, or an equal highest role that is not managed by that panel, unless they have Manage Roles.
- The bot can only assign rank roles and extra roles below its highest role.

Other admin commands:

```text
/rank-panel list message-id:123456789
/rank-panel refresh message-id:123456789
/rank-panel edit message-id:123456789 title:"Fire Department Rank Manager"
/rank-panel edit-rank message-id:123456789 role:@Firefighter label:"Firefighter"
/rank-panel remove-rank message-id:123456789 role:@Firefighter
/rank-panel delete message-id:123456789
```

## Automatic Server Logs

Set the server log channel:

```text
/logging set channel:#server-logs
```

Check logging status:

```text
/logging status
```

Set separate member join and leave log channels:

```text
/logging joins channel:#joins
/logging leaves channel:#leaves
```

Clear those channels:

```text
/logging joins clear:true
/logging leaves clear:true
```

Turn logging off:

```text
/logging off
```

The logger currently posts automatic embeds for:

- Deleted messages
- Roles added to members
- Roles removed from members
- Members joining the server
- Members leaving the server
- Channels created
- Channels deleted
- Channel name, topic, and @everyone send-message overwrite changes
