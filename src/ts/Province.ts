import { LineStock } from '../../bga-cards'
import { BgaCards } from './libs'
import { QuorumCard, QuorumGame, QuorumPlayer } from './types'

/**
 * Part of the board
 */
export class Province {
	private handStock: LineStock<QuorumCard> | null = null
	private element: HTMLElement

	constructor(
		private game: QuorumGame,
		province: number
	) {
		let html = `
            <div id="province-${province}" class="province" data-province="${province}">
				<div id="province-${province}-token-slot" class="province-token-slot"></div>
			</div>
        `
		$('board').insertAdjacentHTML('beforeend', html)
		this.element = $('province-' + province)

		this.element.insertAdjacentHTML(
			'beforeend',
			`
            <div class="province-slots">
                ${Array.from({ length: 16 }, (_, i) => `<div class="province-slot slot-${i} ${this.getSlotClasses(i)}"></div>`).join('')}
            </div>
        `
		)
	}

	private getSlotClasses(i: number) {
		if (i == 0) return ''
		if (i < 7) return 'left-column'
		if (i > 9) return 'right-column'
		return ''
	}
}
