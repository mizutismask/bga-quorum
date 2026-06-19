import { Game } from '../Game'
import { log } from '../base-game'
import { QuorumGamedatas, QuorumPlayer, PlayerTurnArgs, QuorumCard, GodEffectArgs } from '../types'
import { Utils } from '../utils'

/**
 * We create one State class per declared state on the PHP side, to handle all state specific code here.
 * onEnteringState, onLeavingState and onPlayerActivationChange are predefined names that will be called by the framework.
 * When executing code in this state, you can access the args using this.args
 */
export class GodEffect {
	private listeners: {
		element: HTMLElement
		enter: EventListener
		leave: EventListener
	}[] = []
	constructor(
		private game: Game,
		private bga: Bga<QuorumPlayer, QuorumGamedatas>
	) {}

	/**
	 * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
	 */
	onEnteringState(args: GodEffectArgs, isCurrentPlayerActive: boolean) {
		if (isCurrentPlayerActive) {
			const slots = Array.from($('board').querySelectorAll<HTMLElement>('.province-token-slot'))

			slots.forEach((slot, index) => {
				slot.classList.add('province-enabled')
				const token = slot.querySelector<HTMLElement>('[data-value]')
				if (token) {
					const enter = () => {
						this.removeInfluenceEffects()

						const previousSlot = slots[(index - 1 + slots.length) % slots.length]
						const nextSlot = slots[(index + 1) % slots.length]

						this.addInfluenceEffect(
							previousSlot.querySelector<HTMLElement>('[data-value]'),
							Math.max(1, Math.min(4, Number(token.dataset.value) + Number(args.leftEffect)))
						)
						this.addInfluenceEffect(
							nextSlot.querySelector<HTMLElement>('[data-value]'),
							Math.max(1, Math.min(4, Number(token.dataset.value) + Number(args.rightEffect)))
						)
					}

					const leave = () => {
						this.removeInfluenceEffects()
					}

					token.addEventListener('mouseenter', enter)
					token.addEventListener('mouseleave', leave)

					this.listeners.push({
						element: token,
						enter,
						leave
					})
				}
			})
		}
	}

	private addInfluenceEffect(token: HTMLElement, value?: number) {
		const tooltip = this.game.createCloneForAnimation(token)
		tooltip.classList.add('influence-effect')
		tooltip.dataset.value = value.toString()
		tooltip.style.scale = '1.4'

		const rect = token.getBoundingClientRect()
		tooltip.style.position = 'fixed'
		tooltip.style.left = `${rect.left}px`
		tooltip.style.top = `${rect.top}px`
		tooltip.style.width = `${rect.width}px`
		tooltip.style.height = `${rect.height}px`
		tooltip.style.pointerEvents = 'none'
		tooltip.style.filter = 'brightness(0.7) saturate(0.9)'//'grayscale(70%) brightness(1.15)'

		document.body.appendChild(tooltip)
	}

	private removeInfluenceEffects() {
		document.body.querySelectorAll('.influence-effect').forEach((elt) => elt.remove())
	}

	/**
	 * This method is called each time we are leaving the game state. You can use this method to perform some user interface changes at this moment.
	 */
	onLeavingState(args: GodEffectArgs, isCurrentPlayerActive: boolean) {
		this.listeners.forEach(({ element, enter, leave }) => {
			element.removeEventListener('mouseenter', enter)
			element.removeEventListener('mouseleave', leave)
		})

		this.listeners = []
		this.game.playerTables[this.game.getPlayerId()].handStock!.setSelectionMode('none')
		this.game.river.setSelectionMode('none')
	}
}
