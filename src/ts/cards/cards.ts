import { TooltipElement } from '../tooltipable'
import { QuorumCard, QuorumGame } from '../types'
import { CardsManagerBase } from './cardsManagerBase'

// <reference path="../card-manager.ts"/>
export const IMAGE_ITEMS_PER_ROW = 9
export const BACK_IMAGE_ITEMS_PER_ROW = 7

const setupFrontDiv = (game: QuorumGame) => (card: QuorumCard, div: HTMLElement) => {
	game.cardsManager.setFrontBackground(div as HTMLDivElement, card.type_arg)

	//add help
	const helpId = `${game.cardsManager.getId(card)}-front-info`
	if (!$(helpId)) {
		const info: HTMLDivElement = document.createElement('div')
		info.id = helpId
		info.innerText = '?'
		info.classList.add('css-icon', 'card-info')
		div.appendChild(info)
		const tooltipContent = game.cardsManager.getTooltip(card)
		//game.addTooltipHtml(div.id, tooltipContent)
		game.addTooltipOnClickHelpButton(info.id, tooltipContent)
	}
}

const multiplier = .75
export class CardsManager extends CardsManagerBase<QuorumCard> {
	constructor(public game: QuorumGame) {
		super({
			animationManager: game.animationManager,
			type: 'quorum-card',
			setupFrontDiv: setupFrontDiv(game),
			setupDiv: (card: QuorumCard, div: HTMLElement) => {
				div.classList.add('quorum-card')
				div.dataset.cardId = '' + card.id
				div.dataset.cardType = '' + card.type
				div.dataset.cardTypeArg = '' + card.type_arg
			},
			setupBackDiv: (card: QuorumCard, div: HTMLElement) => {
				game.cardsManager.setBackBackground(div as HTMLDivElement, card)
			},
			cardWidth: 248 * multiplier,
			cardHeight: 347 * multiplier,
		})
	}

	public getCardName(card: QuorumCard) {
		return `<div class="cstm-card-name">${card.name}</div>`
	}

	public getTooltipContent(): TooltipElement<QuorumCard>[] {
		return [{ title: _('Objective'), contentProvider: (c: QuorumCard) => this.getDesc(c) }]
	}

	public getDesc(card: QuorumCard) {
		return 'todo'
	}

	public setFrontBackground(cardDiv: HTMLDivElement, cardType: number) {
		const imageUrl = `${g_gamethemeurl}img/cards.jpg`
		cardDiv.style.backgroundImage = `url('${imageUrl}')`
		const imagePosition = cardType - 1
		const row = Math.floor(imagePosition / IMAGE_ITEMS_PER_ROW)
		const xBackgroundPercent = (imagePosition - row * IMAGE_ITEMS_PER_ROW) * 100
		const yBackgroundPercent = row * 100
		cardDiv.style.backgroundPositionX = `-${xBackgroundPercent}%`
		cardDiv.style.backgroundPositionY = `-${yBackgroundPercent}%`
		cardDiv.style.backgroundSize = `${IMAGE_ITEMS_PER_ROW * 100}%`
	}

	public setBackBackground(cardDiv: HTMLDivElement, card: QuorumCard) {
		const cardType = card.province?? 7
		const imageUrl = `${g_gamethemeurl}img/backs.jpg`
		cardDiv.style.backgroundImage = `url('${imageUrl}')`
		const imagePosition = cardType - 1
		const row = Math.floor(imagePosition / BACK_IMAGE_ITEMS_PER_ROW)
		const xBackgroundPercent = (imagePosition - row * BACK_IMAGE_ITEMS_PER_ROW) * 100
		const yBackgroundPercent = row * 100
		cardDiv.style.backgroundPositionX = `-${xBackgroundPercent}%`
		cardDiv.style.backgroundPositionY = `-${yBackgroundPercent}%`
		cardDiv.style.backgroundSize = `${BACK_IMAGE_ITEMS_PER_ROW * 100}%`
	}
}
