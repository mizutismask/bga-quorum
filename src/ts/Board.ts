import { LineStock } from '../../bga-cards'
import { BgaCards } from './libs'
import { Province } from './Province'
import { QuorumCard, QuorumGame, QuorumPlayer } from './types'

/**
 * Central board
 */
export class Board {
	private handStock: LineStock<QuorumCard> | null = null
	private provinces: Province[] = []
	
	constructor(
		private game: QuorumGame,
		orderedProvinces: number[]
	) {
		const provincesWithGhosts = [
			orderedProvinces[orderedProvinces.length - 1],
			...orderedProvinces,
			orderedProvinces[0]
		]
		
		provincesWithGhosts.forEach((color, index) => {
			if (index === 0) {
				$('board').insertAdjacentHTML(
					'afterbegin',
					`
					<div id="before-first-province" class="ghost-province"><div class="province" data-province="${color}"></div></div>
					`
				)
			} else if (index === provincesWithGhosts.length - 1) {
				$('board').insertAdjacentHTML(
					'beforeend',
					`
					<div id="after-last-province" class="ghost-province"><div class="province" data-province="${color}"></div>
					`
				)
			} else {
				const province = new Province(this.game, color)
                this.provinces[color] = province
                if(index === 1) {
					$(`province-${color}`).insertAdjacentHTML("beforeend",
                        `<div class="province-arrow fa fa-chevron-left">`
                    )
                }else if(index === provincesWithGhosts.length - 2) {
					$(`province-${color}`).insertAdjacentHTML("beforeend",
                        `<div class="province-arrow province-arrow-right fa fa-chevron-right">`
                    )
                }
			}
		})
	}
	setInfluenceToken(nation: number, value: number) {
		return this.provinces[nation].setInfluenceToken(value)
	}
}
