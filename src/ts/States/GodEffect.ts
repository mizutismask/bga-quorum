import { Game } from '../Game'
import { log } from '../base-game'
import { QuorumGamedatas, QuorumPlayer, PlayerTurnArgs, QuorumCard } from '../types'
import { Utils } from '../utils'

/**
 * We create one State class per declared state on the PHP side, to handle all state specific code here.
 * onEnteringState, onLeavingState and onPlayerActivationChange are predefined names that will be called by the framework.
 * When executing code in this state, you can access the args using this.args
 */
export class GodEffect {
	constructor(
		private game: Game,
		private bga: Bga<QuorumPlayer, QuorumGamedatas>
	) {}

	/**
	 * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
	 */
	onEnteringState(args: PlayerTurnArgs, isCurrentPlayerActive: boolean) {
		if (isCurrentPlayerActive) {
			$("board").querySelectorAll('.province-token-slot').forEach((p) => {
				p.classList.add('province-enabled')
			})
		} 
	}

	/**
	 * This method is called each time we are leaving the game state. You can use this method to perform some user interface changes at this moment.
	 */
	onLeavingState(args: PlayerTurnArgs, isCurrentPlayerActive: boolean) {
		this.game.playerTables[this.game.getPlayerId()].handStock!.setSelectionMode('none')
		this.game.river.setSelectionMode('none')
	}
}
