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
            <div id="province-${province}" class="province" data-province="${province}"></div>
        `
		$('board').insertAdjacentHTML('beforeend', html)
		this.element = $('province-' + province)

		this.element.insertAdjacentHTML(
			'beforeend',
			`
            <div class="province-slots">
                ${Array.from({ length: 15 }, (_, i) => `<div class="province-slot slot-${i + 1} ${this.getSlotClasses(i+1)}"></div>`).join('')}
            </div>
        `
		)
	}

	private getSlotClasses(i: number) {
		if (i < 7) return 'left-column'
		if (i > 9) return 'right-column'
		return ''
	}
}
