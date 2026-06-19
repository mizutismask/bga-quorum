import { ALL_SCORING_TYPES } from './Game'
import { QuorumGame, QuorumPlayer } from './types'

/**
 * End score board.
 * No notifications.
 */
export class ScoreBoard {
	constructor(
		private game: QuorumGame,
		private players: QuorumPlayer[],
		private orderedProvinces: number[]
	) {
		const container = document.getElementById('score-tables')
		if (!container) {
			throw new Error('score-tables not found')
		}

		if (!container.childElementCount) {
			container.innerHTML = players
				.map((player) => {
					const playerId = Number(player.id)

					return `
				<div class="player-score-container whiteblock">
					

					<table id="score-${playerId}" class="score-player-table score-provinces-table">
						<thead>
							<tr>
								<th><h3 style="color: #${player.color}">
									<span id="score-winner-${playerId}"></span>
									${player.name}
								</h3></th>
								<th>${_('Rank')}</th>
								<th>${_('Influence')}</th>
								<th>${_('Cards count')}</th>
								<th>${_('Total')}</th>
							</tr>
						</thead>
						<tbody>
							${this.orderedProvinces
								.map(
									(province) => `
										<tr>
											<td class="province-${province}">
												${this.game.getProvinceName(province)}
											</td>
											<td id="province-${province}-rank-${playerId}"></td>
											<td id="province-${province}-influence-${playerId}"></td>
											<td id="province-${province}-cards-${playerId}"></td>
											<td id="province-${province}-total-${playerId}"></td>
										</tr>
									`
								)
								.join('')}
							<tr>
								<td colspan="4">${_('Total')}</td>
								<td id="total-${playerId}"></td>
							</tr>
						</tbody>
					</table>

					<table id="score-types-${playerId}" class="score-player-table score-types-table">
						<thead>
							<tr>
								<th></th>
								<th></th>
								<th>${_('Total')}</th>
							</tr>
						</thead>
						<tbody>
							${ALL_SCORING_TYPES.map(
								(type) => `
									<tr>
										<td class="type-${type}">
											${this.game.getScoringTypeName(type)}
										</td>
										<td id="type-${type}-computation-${playerId}"></td>
										<td id="type-${type}-total-${playerId}"></td>
									</tr>
								`
							).join('')}
							<tr>
								<td colspan="2">${_('Total')}</td>
								<td id="type-total-${playerId}"></td>
							</tr>
						</tbody>
					</table>
				</div>
			`
				})
				.join('')
		}
	}
	
	public updateScore(playerId: number, scoreType: string, score: number | string, animate: boolean = true) {
		let elt = dojo.byId(scoreType)
		if (!elt) {
			const playerVariant = `${scoreType}-${playerId}`
			elt = dojo.byId(playerVariant)
			scoreType = playerVariant
		}
		if (!elt) {
			console.error('updateScore : this element can not be displayed', scoreType)
		} else {
			elt.innerHTML = score.toString()
			if (animate) {
				dojo.addClass(scoreType, 'animatedScore')
			}
		}
	}
	/**
	 * Add trophee icon to top score player(s)
	 */
	public highlightWinnerScore(playerId: number | string) {
		document.getElementById(`score${playerId}`)?.classList.add('highlight')
		document.getElementById(`score-winner-${playerId}`)?.classList.add('fa', 'fa-trophy', 'fa-lg')
	}
}
