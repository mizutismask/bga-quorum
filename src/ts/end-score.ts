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
		const headers = document.getElementById('scoretr')
		if (!headers) throw new Error('scoretr not found to populate the scoreboard')

		const body = document.getElementById('score-table-body')
		if (!body) throw new Error('score-table-body not found to populate the scoreboard')

		if (!headers.childElementCount) {
			headers.innerHTML = '<th></th>'

			players.forEach((player) => {
				const playerId = Number(player.id)

				dojo.place(
					`
		<table id="score-${playerId}" class="score-player-table">
			<thead>
				<tr>
					<th colspan="5" style="color: #${player.color}">
						<span id="score-winner-${playerId}"></span>
						${player.name}
					</th>
				</tr>
				<tr>
					<th></th>
					<th>${_('Rank')}</th>
					<th>${_('Cards count')}</th>
					<th>${_('Influence')}</th>
					<th>${_('Total')}</th>
				</tr>
			</thead>
			<tbody>
				${this.orderedProvinces
					.map(
						(province) => `
						<tr>
							<td class="province-${province}">${this.game.getProvinceName(province)}</td>
							<td id="province-${province}-rank-${playerId}" class="score-number"></td>
							<td id="province-${province}-cards-${playerId}" class="score-number"></td>
							<td id="province-${province}-influence-${playerId}" class="score-number"></td>
							<td id="province-${province}-total-${playerId}" class="score-number total"></td>
						</tr>
					`
					)
					.join('')}
				<tr>
					<td colspan="4">${_('Total')}</td>
					<td id="total-${playerId}" class="score-number total">${player.score}</td>
				</tr>
			</tbody>
		</table>
		`,
					'score-table-body'
				)
			})
		}
	}

	public updateScores(players: QuorumPlayer[]) {
		/*players.forEach((p) => {
            document.getElementById(`destination-reached${p.id}`).innerHTML = (
                p.completedDestinations.length + p.sharedCompletedDestinationsCount
            ).toString();
            document.getElementById(`revealed-tokens-back${p.id}`).innerHTML = p.revealedTokensBackCount.toString();
            document.getElementById(`destination-unreached${p.id}`).innerHTML = this.preventMinusZero(
                p.uncompletedDestinations?.length
            );
            document.getElementById(`revealed-tokens-left${p.id}`).innerHTML = this.preventMinusZero(
                p.revealedTokensLeftCount
            );
            document.getElementById(`total${p.id}`).innerHTML = p.score.toString();
        });*/
	}

	private preventMinusZero(score: number) {
		if (score === 0) {
			return '0'
		}
		return '-' + score.toString()
	}

	public updateScore(playerId: number, scoreType: string, score: number, animate: boolean = true) {
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
