import { LineStock } from '../../bga-cards'
import { BgaCards } from './libs'
import { QuorumCard, QuorumGame, QuorumPlayer } from './types'

/**
 * Player table.
 */
export class PlayerTable {
	private handStock: LineStock<QuorumCard> | null = null

	constructor(
		private game: QuorumGame,
		player: QuorumPlayer,
		cards: QuorumCard[]
	) {
		const isMyTable = Number(player.id) === game.getPlayerId()
		const ownClass = isMyTable ? 'own' : ''
		let html = `
		<div id="player-table-${player.id}" class="player-order${player.playerNo} player-table ${ownClass}">
				<a id="anchor-player-${player.id}"></a>
				<span class="player-name" style="color:#${player.color}">${player.name}</span>
            </div>
        `
		dojo.place(html, 'player-tables')

		const handHtml = `
			<div id="hand-${player.id}" class="cstm-player-hand"></div>
        `
		dojo.place(handHtml, `player-table-${player.id}`, 'last')
		this.initHand(player, cards)
	}

	private initHand(player: QuorumPlayer, cards: QuorumCard[] = []) {
		this.handStock = new BgaCards.LineStock<QuorumCard>(this.game.cardsManager, $('hand-' + player.id), {})
		this.handStock.setSelectionMode('single')
		if (cards) {
			this.handStock.addCards(cards)
		}
	}
}
