import { EmbedBuilder } from 'discord.js';

export function buildProfileEmbed(payload) {
    const { user, character } = payload;

    if (!character) {
        return new EmbedBuilder()
            .setTitle('AMOW Profile')
            .setDescription(`Linked to **${user.name}**, but no in-game character exists yet.`)
            .setColor(0xc2a84f);
    }

    const licences = character.licences.length > 0 ? character.licences.join(', ') : 'None';
    const holdings = character.stock_holdings.length > 0
        ? character.stock_holdings.map((holding) => `${holding.company}: ${holding.shares}`).join('\n')
        : 'None';

    return new EmbedBuilder()
        .setTitle(character.name)
        .setDescription(`${character.faction} | ${character.rank}`)
        .setColor(0x7ead59)
        .addFields(
            { name: 'Role', value: `${character.role_type} / ${character.occupation}`, inline: true },
            { name: 'Credits', value: character.credits.toLocaleString(), inline: true },
            { name: 'Health', value: `${character.health_points} HP / ${character.armor_points} Armor`, inline: true },
            { name: 'Business Owner', value: character.business_owner ? 'Yes' : 'No', inline: true },
            { name: 'Licences', value: licences, inline: false },
            { name: 'Holdings', value: holdings, inline: false },
        )
        .setFooter({ text: `Linked Discord: ${user.discord_username || 'Unknown'}` });
}

export function buildLinkHelpText(linkUrl) {
    if (linkUrl) {
        return `Generate a link code here first: ${linkUrl}`;
    }

    return 'Generate a link code from your AMOW profile page first, then run `/amowlink` again.';
}
