import { TooltipElement } from '../tooltipable'
import { QuorumCard, QuorumGame } from '../types'
import { CardsManagerBase } from './cardsManagerBase'

// <reference path="../card-manager.ts"/>
export const IMAGE_ITEMS_PER_ROW = 10

const setupFrontDiv = (game: QuorumGame) => (card: QuorumCard, div: HTMLElement) => {
	game.cardsManager.setFrontBackground(div as HTMLDivElement, card.type_arg)
	const tokensId = `${game.cardsManager.getId(card)}-tokens`
	const textId = `${game.cardsManager.getId(card)}-text`

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

	//adds tokens locations
	if (!$(tokensId)) {
		const container: HTMLDivElement = document.createElement('div')
		container.id = tokensId
		container.classList.add('tokens-location-wrapper')
		div.appendChild(container)
	}

	if (!$(textId)) {
		const container: HTMLDivElement = document.createElement('div')
		container.id = tokensId
		container.classList.add('bga-autofit', 'card-text-wrapper')
		div.appendChild(container)
	}
}

export class CardsManager extends CardsManagerBase<QuorumCard> {
	constructor(public game: QuorumGame) {
		super({
			animationManager: game.animationManager,
			type: 'card',
			getId: (card) => `Quorum-card-${card.id}`,
			setupFrontDiv: setupFrontDiv(game),
			setupDiv: (card: QuorumCard, div: HTMLElement) => {
				div.classList.add('Quorum-card')
				div.dataset.cardId = '' + card.id
				div.dataset.cardType = '' + card.type
			},
			setupBackDiv: (card: QuorumCard, div: HTMLElement) => {
				div.style.backgroundImage = `url('${g_gamethemeurl}img/Quorum-card-background.jpg')`
			},
			cardHeight: undefined as unknown as number,
			cardWidth: undefined as unknown as number
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
		const imageUrl = `${g_gamethemeurl}img/Quorum-card-background.jpg`
		cardDiv.style.backgroundImage = `url('${imageUrl}')`
		const imagePosition = cardType - 1
		const row = Math.floor(imagePosition / IMAGE_ITEMS_PER_ROW)
		const xBackgroundPercent = (imagePosition - row * IMAGE_ITEMS_PER_ROW) * 100
		const yBackgroundPercent = row * 100
		cardDiv.style.backgroundPositionX = `-${xBackgroundPercent}%`
		cardDiv.style.backgroundPositionY = `-${yBackgroundPercent}%`
		cardDiv.style.backgroundSize = `${IMAGE_ITEMS_PER_ROW * 100}%`
	}
}
