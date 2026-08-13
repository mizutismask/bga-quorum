import { BgaCards, BgaAnimations, BgaAutofit } from './libs'
import { BaseGame, log, isDebug, ANIMATION_MS, ACTION_TIMER_DURATION, SCORE_MS } from './base-game'
import { GameFeatureConfig } from './gamefeatureconfig'
import { PlayerTable } from './player-table'
import { CardStock, Deck, LineStock } from '../../bga-cards'
import {
	QuorumCard,
	QuorumGamedatas,
	QuorumPlayer,
	NotifMaterialMove,
	NotifScoreArgs,
	NotifWinnerArgs,
	Token,
	NotifRiverChange
} from './types'
import { ScoreBoard } from './end-score'
import { Utils } from './utils'
import { CardsManager } from './cards/cards'
import { PlayerTurn } from './States/PlayerTurn'
import { NextPlayer } from './States/NextPlayer'
import { Board } from './Board'
import { GodEffect } from './States/GodEffect'
import { BgaHelpPopinButton, HelpManager } from './libs/help-manager/help-manager'

export const PROVINCE_NEUTRAL = 0
export const PROVINCE_ASIA = 1
export const PROVINCE_GALLIA = 2
export const PROVINCE_GERMANIA = 3
export const PROVINCE_AFRICA = 4
export const PROVINCE_MACEDONIA = 5
export const PROVINCE_HISPANIA = 6

const ALL_PROVINCES = [
	PROVINCE_ASIA,
	PROVINCE_GALLIA,
	PROVINCE_GERMANIA,
	PROVINCE_AFRICA,
	PROVINCE_MACEDONIA,
	PROVINCE_HISPANIA
]

export const CARD_TYPE_MILITARY = 11
export const CARD_TYPE_INTRIGUE = 12
export const CARD_TYPE_ARCHITECTURE = 13
export const CARD_TYPE_TRADE = 14

export const ALL_SCORING_TYPES = [CARD_TYPE_MILITARY, CARD_TYPE_INTRIGUE, CARD_TYPE_ARCHITECTURE, CARD_TYPE_TRADE]

export const CARD_TYPE_ARCH_BATH = 20
export const CARD_TYPE_ARCH_TEMPLE = 21
export const CARD_TYPE_ARCH_THEATER = 22
export const CARD_TYPE_ARCH_AQUEDUCT = 23
export const CARD_TYPE_ARCH_COLISEUM = 24
export const CARD_TYPE_ARCH_ARCH = 25

export class Game extends BaseGame {
	public cardsManager!: CardsManager

	private scoreBoard!: ScoreBoard
	public nationRankCounters: Counter[] = []
	public nationValueCounters: Counter[] = []

	private displayedTooltip: any //dijit.Tooltip

	private board: Board
	public river: LineStock<QuorumCard>
	private riverDeck: Deck<QuorumCard>

	constructor(bga: Bga<QuorumPlayer, QuorumGamedatas>) {
		super()
		this.bga = bga
		this.bga.states.register('PlayerTurn', new PlayerTurn(this, this.bga))
		this.bga.states.register('NextPlayer', new NextPlayer(this, this.bga))
		this.bga.states.register('GodEffect', new GodEffect(this, this.bga))
		this.bga.userPreferences.onChange = (pref_id, pref_value) => this.customPreferenceChanged(pref_id, pref_value)
	}

	public setup(gamedatas: QuorumGamedatas) {
		this.gamedatas = gamedatas
		log('Starting game setup')
		this.dontPreloadUselessAssets()

		this.includeHtmlBasicTemplate()
		this.gameFeatures = new GameFeatureConfig()
		log('gamedatas', gamedatas)

		this.animationManager = new BgaAnimations.Manager({
			animationsActive: () => this.gameui.bgaAnimationsActive()
		})
		this.cardsManager = new CardsManager(this)

		this.river = new BgaCards.LineStock<QuorumCard>(this.cardsManager, document.getElementById('river-content'), {
			wrap: 'wrap'
		})
		//this.river.setSelectionMode('single')
		this.river.addCards(this.gamedatas.river)

		this.riverDeck = new BgaCards.Deck<QuorumCard>(this.cardsManager, document.getElementById('river-deck'), {})
		this.riverDeck.addCard(this.gamedatas.riverTopCard, {})

		if (gamedatas.lastTurn) {
			this.notif_lastTurn()
		}
		if (Number(gamedatas.gamestate.id) >= 90) {
			// score or end
			this.onEnteringEndScore()
		}

		Object.values(this.gamedatas.playerOrderWorkingWithSpectators).forEach((p) => {
			this.setupPlayer(this.gamedatas.players[p])
		})

		$('overall-content').classList.add(`player-count-${this.getPlayersCount()}`)

		this.board = new Board(this, this.gamedatas.orderedProvinces)
		this.showProvinceInfluence()
		this.createTokens()

		this.setupTooltips()
		this.setupHelpPopin()

		this.scoreBoard = new ScoreBoard(this, this.getPlayersInOrder(), this.gamedatas.orderedProvinces)
		this.gamedatas.scoreProvinceDetails?.forEach(
			(s) => s != null && this.scoreBoard.updateScore(s.playerId, s.scoreType, s.score)
		)
		this.gamedatas.scoreTypeDetails?.forEach((s) => this.scoreBoard.updateScore(s.playerId, s.scoreType, s.score))
		if (this.gamedatas.winners) {
			this.gamedatas.winners.forEach((pId) => this.scoreBoard.highlightWinnerScore(pId))
		}
		Utils.removeClass('animatedScore')

		if (!this.isCustomSoundsOn()) {
			this.bga.sounds.dontPreloadSounds(this.customSounds)
		}
		this.setupNotifications()
		BgaAutofit.init()

		log('Ending game setup')
	}

	private setupTooltips() {
		this.setTooltipToClass('province-counters', _('The rank and value you reached for each province'))
		this.setTooltipToClass(
			'province-token-slot',
			_('The influence of that province, will be used as a base multiplier according to your rank')
		)

		$('board')
			.querySelectorAll<HTMLElement>('.ghost-province')
			.forEach((element) => {
				this.setTooltip(
					element.id,
					`
				<div class="ghost-tooltip">
					<img src="${this.bga.images.getImgUrl('roundLayout.jpg')}"</img>
					<div>${_('Provinces are laid out in circle')}</div>
					<br/>
					<div style="font-size: 0.8em">${_('(You can hide this in the preferences menu on the top right corner)')}</div>
				</div>`
				)
			})
	}

	private setupPlayer(player: QuorumPlayer) {
		this.setupMiniPlayerBoard(player)
		this.playerTables[player.id] = new PlayerTable(
			this,
			player,
			parseInt(player.id) === this.getPlayerId() ? this.gamedatas.hand : player.hand,
			player.playedCards
		)
	}

	private createTokens() {
		this.gamedatas.tokens.forEach((t) => {
			const player = Object.values(this.gamedatas.players).find((p) => Number(p.id) == t.type_arg)
			const tokenDiv = document.createElement('div')
			tokenDiv.id = `token-${t.type}-${t.type_arg}`
			tokenDiv.classList.add('token', 'token-' + t.type)
			tokenDiv.dataset.color = '' + player.color
			tokenDiv.dataset.playerOrder = '' + player.playerNo
			tokenDiv.title = player.name
			const dest = document.querySelector<HTMLElement>(`#province-${t.type} .slot-${t.location}`)
			dest.appendChild(tokenDiv)
			if (t.location != '0') {
				dest.dataset.childCount = dest.children.length.toString()
			}
		})
	}

	private setupMiniPlayerBoard(player: QuorumPlayer) {
		const playerId = Number(player.id)
		this.bga.playerPanels.getElement(playerId).insertAdjacentHTML(
			'afterbegin',
			`<div id="counters-${player.id}" class="counters province-counters">
			</div>
			<div id="additional-info-${player.id}" class="counters additional-info">
			<div id="additional-icons-${player.id}" class="additional-icons"></div> 
			</div>
			`
		)
		ALL_PROVINCES.forEach((province) => {
			$(`counters-${player.id}`).insertAdjacentHTML(
				'beforeend',
				`<div id="province-${province}-counter-${player.id}-wrapper" class="counter">
					<div class="province-icon icon province-${province}"></div> 
					<span id="province-${province}-player-counter-${player.id}"></span>
					(<span id="province-${province}-value-player-counter-${player.id}" style="padding:0px"></span>)
				</div>`
			)
			const nationCounter = new ebg.counter()
			nationCounter.create(`province-${province}-player-counter-${player.id}`, {
				value: player['nationRankCounter_' + province],
				playerCounter: 'nationRankCounter_' + province,
				playerId: playerId
			})
			this.nationRankCounters[playerId] = nationCounter

			const nationValueCounter = new ebg.counter()
			nationValueCounter.create(`province-${province}-value-player-counter-${player.id}`, {
				value: player['nationValueCounter_' + province],
				playerCounter: 'nationValueCounter_' + province,
				playerId: playerId
			})
			this.nationValueCounters[playerId] = nationValueCounter
		})

		/* const revealedTokensBackCounter = new ebg.counter();
		revealedTokensBackCounter.create(`revealed-tokens-back-counter-${player.id}`);
		revealedTokensBackCounter.setValue(player.revealedTokensBackCount);
		this.revealedTokensBackCounters[playerId] = revealedTokensBackCounter;
		*/
		const ticketsCounter = new ebg.counter()
		/*ticketsCounter.create(`tickets-player-counter-${player.id}`, {
			value: player.tickets,
			playerCounter: 'tickets',
			playerId: playerId
			})
			this.ticketsCounters[playerId] = ticketsCounter
			
			const cardsCounter = new ebg.counter()
			cardsCounter.create(`hand-cards-counter-${player.id}`)
			cardsCounter.setValue(player.cardsCount)
			this.handCardsCounters[playerId] = cardsCounter
			*/
		if (this.gameFeatures.showPlayerHelp && this.getPlayerId() === playerId) {
			//help
			dojo.place(`<div id="player-help" class="css-icon cstm-help-icon">?</div>`, `additional-icons-${player.id}`)
		}

		if (this.gameFeatures.showFirstPlayer && player.playerNo === 1) {
			dojo.place(
				`<div id="firstPlayerIcon" class="css-icon player-turn-order">1<span class="exponent">st<span></div>`,
				`additional-icons-${player.id}`,
				`last`
			)
		}

		if (this.gameFeatures.spyOnOtherPlayerBoard && this.getPlayerId() !== playerId) {
			//spy on other player
			dojo.place(
				`
					<div class="show-player-tableau"><a href="#anchor-player-${player.id}" classes="inherit-color">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 85.333343 145.79321">
                    <path fill="currentColor" d="M 1.6,144.19321 C 0.72,143.31321 0,141.90343 0,141.06039 0,140.21734 5.019,125.35234 11.15333,108.02704 L 22.30665,76.526514 14.626511,68.826524 C 8.70498,62.889705 6.45637,59.468243 4.80652,53.884537 0.057,37.810464 3.28288,23.775161 14.266011,12.727735 23.2699,3.6711383 31.24961,0.09115725 42.633001,0.00129225 c 15.633879,-0.123414 29.7242,8.60107205 36.66277,22.70098475 8.00349,16.263927 4.02641,36.419057 -9.54327,48.363567 l -6.09937,5.36888 10.8401,30.526466 c 5.96206,16.78955 10.84011,32.03102 10.84011,33.86992 0,1.8389 -0.94908,3.70766 -2.10905,4.15278 -1.15998,0.44513 -19.63998,0.80932 -41.06667,0.80932 -28.52259,0 -39.386191,-0.42858 -40.557621,-1.6 z M 58.000011,54.483815 c 3.66666,-1.775301 9.06666,-5.706124 11.99999,-8.735161 l 5.33334,-5.507342 -6.66667,-6.09345 C 59.791321,26.035633 53.218971,23.191944 43.2618,23.15582 33.50202,23.12041 24.44122,27.164681 16.83985,34.94919 c -4.926849,5.045548 -5.023849,5.323672 -2.956989,8.478106 3.741259,5.709878 15.032709,12.667218 24.11715,14.860013 4.67992,1.129637 13.130429,-0.477436 20,-3.803494 z m -22.33337,-2.130758 c -2.8907,-1.683676 -6.3333,-8.148479 -6.3333,-11.893186 0,-11.58942 14.57544,-17.629692 22.76923,-9.435897 8.41012,8.410121 2.7035,22.821681 -9,22.728685 -2.80641,-0.0223 -6.15258,-0.652121 -7.43593,-1.399602 z m 14.6667,-6.075289 c 3.72801,-4.100734 3.78941,-7.121364 0.23656,-11.638085 -2.025061,-2.574448 -3.9845,-3.513145 -7.33333,-3.513145 -10.93129,0 -13.70837,13.126529 -3.90323,18.44946 3.50764,1.904196 7.30574,0.765377 11,-3.29823 z m -11.36999,0.106494 c -3.74071,-2.620092 -4.07008,-7.297494 -0.44716,-6.350078 3.2022,0.837394 4.87543,-1.760912 2.76868,-4.29939 -1.34051,-1.615208 -1.02878,-1.94159 1.85447,-1.94159 4.67573,0 8.31873,5.36324 6.2582,9.213366 -1.21644,2.27295 -5.30653,5.453301 -7.0132,5.453301 -0.25171,0 -1.79115,-0.934022 -3.42099,-2.075605 z"></path>
					</svg>
					</a>
					</div>
					`,
				`additional-icons-${player.id}`
			)
		}
	}

	private showProvinceInfluence() {
		ALL_PROVINCES.forEach((province) => {
			this.board.setInfluenceToken(province, this.gamedatas[`nationInfluenceCounter_${province}`], false)
		})
	}

	private setupHelpPopin() {
		new HelpManager(this, {
			buttons: [
				new BgaHelpPopinButton({
					title: _('Scoring card'),
					html: this.getHelpHtml(),
					buttonBackground: 'white',
					buttonColor: '#266059'
				})
			]
		})
	}

	private getHelpHtml() {
		let html = `
			<div id="help-popin"> `
		/*new Set(this.gamedatas.rolesInPlay).forEach((r) => {
				html += this.getRoleHtml(r, this.gamedatas.rolesInPlay.filter((allR) => allR === r).length)
				})*/
		html += `
				</div>
				`
		return html
	}

	/* This enable to inject translatable styled things to logs or action bar */
	/* @Override */
	public bgaFormatText(log: string, args: any): { log: string; args: any } {
		try {
			if (log && args && !args.processed) {
				args.processed = true

				//displays gems
				;['gemType'].forEach((field) => {
					if (typeof args[field] === 'number') {
						args[field] = `<span class="log-icon gem gem-${args[field]}"></span>`
					}
				})
			}
		} catch (e) {
			console.error(log, args, 'Exception thrown', e.stack)
		}
		return { log, args }
	}

	public customPreferenceChanged(prefId: number, prefValue: any): void {
		switch (prefId) {
			case 100:
				if (this.isCustomSoundsOn()) {
					this.bga.sounds.preloadSounds(this.customSounds)
				}
				break
		}
	}

	///////////////////////////////////////////////////
	//// Game & client states
	//
	onRiverSelectionChange(lastChange: QuorumCard | null): void {
		if (lastChange) {
			this.takeAction('actTakeCard', { cardId: lastChange.id })
		}
	}

	onHandSelectionChange(lastChange: QuorumCard | null): void {
		if (lastChange) {
			this.takeAction('actPlayCard', { cardId: lastChange.id })
		}
	}

	onProvinceClick(province: number): void {
		this.takeAction('actChooseProvince', { 'province': province }).then(() =>
			Utils.removeClass('province-enabled', $('board'))
		)
	}

	public onEnteringState(stateName: string, args: any) {
		log('Entering state: ' + stateName, args)

		if (this.gameFeatures.spyOnActivePlayerInGeneralActions) {
			this.addArrowsToActivePlayer(args)
		}
	}

	/**
	 * Show score board.
	 */
	public onEnteringEndScore() {
		this.bga.gameArea.removeLastTurnBanner()
	}
	///////////////////////////////////////////////////
	//// Utility methods
	///////////////////////////////////////////////////

	public getProvinceName(province: number): string {
		switch (province) {
			case PROVINCE_AFRICA:
				return _('Africa')
			case PROVINCE_GALLIA:
				return _('Gallia')
			case PROVINCE_ASIA:
				return _('Asia')
			case PROVINCE_GERMANIA:
				return _('Germania')
			case PROVINCE_HISPANIA:
				return _('Hispania')
			case PROVINCE_MACEDONIA:
				return _('Macedonia')
			default:
				return _('Neutral')
		}
	}

	public getScoringTypeName(scoringType: number): string {
		switch (scoringType) {
			case CARD_TYPE_ARCHITECTURE:
				return _('Architecture')
			case CARD_TYPE_INTRIGUE:
				return _('Intrigue')
			case CARD_TYPE_MILITARY:
				return _('Military')
			case CARD_TYPE_TRADE:
				return _('Trade')
			default:
				return _('God')
		}
	}

	public getFullScoringTypeName(card: QuorumCard): string {
		let mainType = this.getScoringTypeName(card.scoringType)
		if (card.scoringType != CARD_TYPE_ARCHITECTURE) return mainType
		mainType += ' ('
		switch (card.architectureSubType) {
			case CARD_TYPE_ARCH_AQUEDUCT:
				mainType += _('Aqueduct')
				break
			case CARD_TYPE_ARCH_ARCH:
				mainType += _('Arch')
				break
			case CARD_TYPE_ARCH_BATH:
				mainType += _('Bath')
				break
			case CARD_TYPE_ARCH_COLISEUM:
				mainType += _('Coliseum')
				break
			case CARD_TYPE_ARCH_TEMPLE:
				mainType += _('Temple')
				break
			case CARD_TYPE_ARCH_THEATER:
				mainType += _('Theater')
				break
			default:
				break
		}
		mainType += ')'
		return mainType
	}

	private getSelectedIdsAsParam(stock: CardStock<QuorumCard>) {
		return stock
			.getSelection()
			.map((c) => c.id)
			.join(',')
	}

	public isRealTime() {
		return this.gameui.bRealtime
	}

	public closeCurrentTooltip() {
		if (this.displayedTooltip == null) return
		else {
			this.displayedTooltip.close()
			this.displayedTooltip = null
		}
	}

	public addTooltipOnClickHelpButton(id, html, delay) {
		let tooltip = new dijit.Tooltip({
			label: html,
			showDelay: delay
		})

		dojo.connect($(id), 'click', (evt) => {
			evt.stopPropagation()

			if (tooltip.state == 'SHOWING') {
				this.closeCurrentTooltip()
			} else {
				this.closeCurrentTooltip()
				tooltip.open($(id))
				this.displayedTooltip = tooltip
			}
		})

		dojo.connect($(id), 'mouseleave', () => {
			tooltip.close()
		})
	}

	public dontPreloadUselessAssets() {
		if (this.getPlayersCount() == 1) {
			//this.bga.images.dontPreloadImage('centralBoard.png')//TODO
		} else {
			//this.bga.images.dontPreloadImage('centralBoardSolo.png')
		}
	}

	public toggleActionButtonAbility(buttonId: string, enable: boolean, autoClickIfEnabled: boolean | null = null) {
		if (autoClickIfEnabled == null) {
			//autoClickIfEnabled= this.isConfirmOnlyOnPlacingTokensOn()
		}
		dojo.toggleClass(buttonId, 'disabled', !enable)
		if (autoClickIfEnabled && !dojo.hasClass(buttonId, 'disabled')) {
			$(buttonId).click()
		}
	}

	public resetClientActionData() {
		this.clientActionData = {
			placedCardId: null,
			destinationSquare: null,
			previousCardParentInHand: null
		}
	}

	public handSelectionChange(selection: QuorumCard[], lastChange: QuorumCard): void {
		if (this.bga.players.isCurrentPlayerActive()) {
			this.toggleActionButtonVisibility('btn-validate', selection.length > 0)
		}
	}

	///////////////////////////////////////////////////
	//// Player's action

	/*
    
        Here, you are defining methods to handle player's action (ex: results of mouse click on 
        game objects).
        
        Most of the time, these methods:
        _ check the action is possible at this game state.
        _ make a call to the game server
    
    */
	private ensureStockSelection(stocks: CardStock<QuorumCard>[], errorMsg: string, callback: Function) {
		if (stocks.every((s) => s.getSelection().length > 0)) {
			callback()
		} else {
			this.bga.dialogs.showMessage(errorMsg, 'error')
		}
	}

	///////////////////////////////////////////////////
	//// Reaction to cometD notifications

	/*
        setupNotifications:
        
        In this method, you associate each of your game notifications with your local method to handle it.
        
        Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                your Quorum.game.php file.
    
    */
	setupNotifications() {
		log('notifications subscriptions setup')

		// TODO: here, associate your game notifications with local methods

		// Example 1: standard notification handling
		// dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

		// Example 2: standard notification handling + tell the user interface to wait
		//            during 3 seconds after calling the method in order to let the players
		//            see what is happening in the game.
		// dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
		// this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
		//

		const manualNotifs = [
			{ name: 'scoreDetailTotal', duration: ANIMATION_MS * 2 },
			{ name: 'importantMessage', duration: 3000 },
			{ name: 'setTableCounter', duration: ANIMATION_MS * 3 }
		]
		this.bga.notifications.setupPromiseNotifications({
			minDuration: ANIMATION_MS,
			//minDurationNoText: ANIMATION_MS,
			logger: log,
			ignoreNotifications: manualNotifs.map((notif) => notif.name)
		})

		manualNotifs.forEach(({ name, duration }) => {
			dojo.subscribe(name, this, (notifDetails: Notif<any>) => {
				log(`notif_${name}`, notifDetails.args)

				const promise = this[`notif_${name}`](notifDetails.args)

				// tell the UI notification ends, if the function returned a promise
				promise?.then(() => (this as any).bga.gameui.notifqueue.onSynchronousNotificationEnd())
			})
			;(this as any).bga.gameui.notifqueue.setSynchronous(name, duration)
		})
	}

	async notif_setTableCounter(args) {
		const { name, value, oldValue, inc, absInc, playerId } = args
		if (name.startsWith('nationInfluenceCounter_')) {
			return this.board.setInfluenceToken(name.replace('nationInfluenceCounter_', ''), value)
		}
	}

	/**
	 * Updates a total or subtotal
	 * @param notif
	 */
	notif_scoreDetail(notif: NotifScoreArgs) {
		this.scoreBoard.updateScore(notif.playerId, notif.scoreType, notif.score)
	}
	notif_scoreDetailTotal(notif: NotifScoreArgs) {
		this.notif_scoreDetail(notif)
	}

	async notif_riverChange(notif: NotifRiverChange) {
		const cards = notif.material as Array<QuorumCard>
		const card = cards.at(0)
		if (cards.length == 5) {
			await this.river.removeAll({})
			//await this.animationManager.base.wait(2000)
			await this.river.addCards(cards, { bump: 1 })
			await this.riverDeck.shuffle({ animatedCardsMax: 20, pauseDelayAfterAnimation: 50 })
			return await this.riverDeck.addCard(notif.newTopCard, { animationsActive: false })
		} else {
			await this.riverDeck.addCard(card, {
				initialSide: 'back',
				finalSide: 'back',
				animationsActive: false
			})
			await this.riverDeck.flipCard(card, {})
			await this.river.addCard(card, { bump: 1 })
			return await this.riverDeck.addCard(notif.newTopCard, { animationsActive: false })
		}
	}

	notif_materialMove(notif: NotifMaterialMove) {
		switch (notif.type) {
			case 'CARD':
				const cards = notif.material as Array<QuorumCard>
				return this.notif_cardMove(cards, notif)
			case 'TOKEN':
				return this.notif_tokenMove(notif.material as Array<Token>, notif)
			default:
				console.error('Material type move not handled', notif)
				break
		}
	}

	async notif_cardMove(cards: QuorumCard[], notif: NotifMaterialMove) {
		const card = cards.at(0)
		if (notif.to.startsWith('played-')) {
			const pId = notif.to.replace('played-', '')
			if (cards.length == 1) {
				return await this.playerTables[pId].playedCardsStock.addCard(card)
			} else {
				return await this.playerTables[pId].playedCardsStock.addCards(cards, {})
			}
		} else {
			switch (notif.to) {
				case 'HAND':
					return Promise.all(cards.map((c) => this.addCardToHand(c, notif)))
				case 'RIVER':
					console.error('should not be called anymore')
				case 'DISCARD':
					return await this.cardsManager.getCardStock(card)?.removeCards(cards, { fadeOut: true })
				case 'DECK_TOP':
					return await this.riverDeck.addCards(cards)
				default:
					console.error('Card move destination not handled', notif)
					break
			}
		}
	}

	async addCardToHand(card: QuorumCard, notif: NotifMaterialMove) {
		if (card.isGod) {
			await this.river.flipCard(card, {})
			await this.animationManager.base.wait(1500) //let some time to see the card
			return await this.playerTables[notif.toArg].handStock.addCard(card, {})
		} else {
			if (notif.toArg == this.getPlayerId() && !this.cardsManager.isCardVisible(card)) {
				//nothing to do, my card has already been revealed and move privatly in another notif
			} else {
				return await this.playerTables[notif.toArg].handStock.addCard(card)
			}
		}
	}

	async notif_tokenMove(cards: Token[], notif: NotifMaterialMove) {
		const card = cards.at(0)
		switch (notif.to) {
			case 'BOARD':
				this.moveToken(card, notif)
				break
			default:
				console.error('Token move destination not handled', notif)
				break
		}
	}

	async moveToken(c: Token, notif: NotifMaterialMove) {
		const elmt = document.getElementById(`token-${c.type}-${c.type_arg}`)
		const source = elmt.parentElement
		const dest = document.querySelector<HTMLElement>(`#province-${c.type} .slot-${c.location}`)

		//since the real token styles are location dependent, they are lost during animation, hence animation is not visible
		//so we get a clone with all the styles and animate that clone
		const clone = this.createCloneForAnimation(elmt)

		//hide the real token
		elmt.style.visibility = 'hidden'
		//animate
		await this.animationManager.slideFloatingElement(clone, elmt, dest)
		//add to dest and show
		dest.appendChild(elmt)
		elmt.style.visibility = ''
		//clean
		clone.remove()

		source.dataset.childCount = source.children.length.toString()
		dest.dataset.childCount = dest.children.length.toString()
	}

	/**
	 * Highlight winner for end score.
	 */
	notif_highlightWinnerScore(notif: NotifWinnerArgs) {
		this.scoreBoard?.highlightWinnerScore(notif.playerId)
	}
}
