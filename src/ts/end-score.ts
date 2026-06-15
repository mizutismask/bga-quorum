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
				headers.insertAdjacentHTML(
					'beforeend',
					`
			<th colspan="4" style="color: #${player.color}">${player.name}</th>
			`
				)
			})

			body.innerHTML = `
		<tr>
			<td></td>
			${players
				.map(
					() => `
				<td>${_('Rank')}</td>
				<td>${_('Cards count')}</td>
				<td>${_('Influence')}</td>
				<td>${_('Total')}</td>
			`
				)
				.join('')}
		</tr>
	`

			this.orderedProvinces.forEach((province) => {
				body.insertAdjacentHTML(
					'beforeend',
					`
			<tr id="province-score-${province}">
				<td class="province-score-name province-${province}">${_(`Province ${province}`)}</td>
				${players
					.map(
						(player) => `
					<td id="province-${province}-rank-${player.id}" class="score-number"></td>
					<td id="province-${province}-cards-${player.id}" class="score-number"></td>
					<td id="province-${province}-influence-${player.id}" class="score-number"></td>
					<td id="province-${province}-total-${player.id}" class="score-number total"></td>
				`
					)
					.join('')}
			</tr>
			`
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
