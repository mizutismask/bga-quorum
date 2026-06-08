import { LineStock } from '../../bga-cards'
import { BgaCards } from './libs'
import { QuorumCard, QuorumGame, QuorumPlayer } from './types'

/**
 * Part of the board
 */
export class Province {
	private handStock: LineStock<QuorumCard> | null = null

	constructor(
		private game: QuorumGame,
		province: number
	) {
		let html = `
            <div id="province-${province}" class="province" data-province="${province}"></div>
        `
		$('board').insertAdjacentHTML('beforeend', html)
	}
}
