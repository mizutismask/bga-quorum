import { LineStock, SlotStock } from '../../bga-cards'
import { BgaCards } from './libs'
import { generateSlotsIds } from './stock-utils'
import { QuorumCard, QuorumGame, QuorumPlayer } from './types'

/**
 * Player table.
 */
export class PlayerTable {
	public handStock: LineStock<QuorumCard> | null = null
	private playedCardsStock: SlotStock<QuorumCard> | null = null

	constructor(
		private game: QuorumGame,
		player: QuorumPlayer,
		handCards: QuorumCard[],
		playedCards: QuorumCard[]
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
			<div id="played-cards-${player.id}" class="played-cards"></div>
        `
		dojo.place(handHtml, `player-table-${player.id}`, 'last')
		this.initHand(player, handCards)
		this.initPlayedCards(player, playedCards)
	}

	private initHand(player: QuorumPlayer, cards: QuorumCard[] = []) {
		this.handStock = new BgaCards.LineStock<QuorumCard>(this.game.cardsManager, $('hand-' + player.id), {wrap:"nowrap"})
		//this.handStock.setSelectionMode('single')
		if (cards) {
			this.handStock.addCards(cards)
		}
	}

	private initPlayedCards(player: QuorumPlayer, cards: QuorumCard[] = []) {
		this.playedCardsStock = new BgaCards.SlotStock<QuorumCard>(
			this.game.cardsManager,
			$(`played-cards-${player.id}`),
			{ slotsIds: generateSlotsIds('slot-', 12), mapCardToSlot: (card) => 'slot-' + card.location_arg }
		)
		$(`played-cards-${player.id}`).querySelectorAll<HTMLElement>('.slot').forEach((element) => {
			element.dataset.slotNumber = element.dataset.slotId.replace('slot-', '')
		})

		this.playedCardsStock.setSelectionMode('none')
		if (cards) {
			this.playedCardsStock.addCards(cards)
		}
	}
}
