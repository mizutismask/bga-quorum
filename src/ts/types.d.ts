import { CardsManager } from './cards/cards'
import { BgaAnimations } from './libs'

/**
 * Your game interfaces
 */
type LandType = 'M' | 'W' | 'D' | 'N'

// remove this if you don't use cards. If you do, make sure the types are correct . By default, some number are send as string, I suggest to cast to right type in PHP.
interface Card {
	id: number
	location: string
	location_arg: number
	type: number
	type_arg: number
}
interface QuorumCard extends Card {
	name: string //translated
	province: number
	power: number
	influence: number
	isGod: boolean
}
interface Token extends Card {}
interface NationTile extends Card {}
interface TreasureCard extends Card {}
interface LegacyCard extends Card {}

interface QuorumPlayer extends Player {
	playerNo: number
	tiles: number
	fairies: number
	hammers: number
	hand: QuorumCard[]
	playedCards: QuorumCard[]
}

interface QuorumGamedatas {
	current_player_id: string
	teamMate?: number
	decision: { decision_type: string }
	game_result_neutralized: string
	gamestate: Gamestate
	gamestates: { [gamestateId: number]: Gamestate }
	neutralized_player_id: string
	notifications: { last_packet_id: string; move_nbr: string }
	playerorder: (string | number)[]
	playerOrderWorkingWithSpectators: number[] //starting with current player
	players: { [playerId: number]: QuorumPlayer }
	tablespeed: string
	lastTurn: boolean
	turnOrderClockwise: boolean
	expansion: number
	// counters
	scoreProvinceDetails?: Array<NotifScoreArgs>
	scoreTypeDetails?: Array<NotifScoreArgs>
	winners: number[]
	version: string
	counters: Map<string, CounterValue>
	// Add here variables you set up in getAllDatas
	hand: Array<QuorumCard>
	treasures: TreasureCard[]
	boardContent: NationTile[]
	selectableHandCards: NationTile[]
	legacyCards: LegacyCard[]
	orderedProvinces: number[]
	river: Array<QuorumCard>
	riverTopCard: QuorumCard
	tokens: Token[]
	nationInfluenceCounter_1: number
	nationInfluenceCounter_2: number
	nationInfluenceCounter_3: number
	nationInfluenceCounter_4: number
	nationInfluenceCounter_5: number
	nationInfluenceCounter_6: number
}

interface CounterValue {
	counter_name: string
	counter_value: number
}

interface QuorumGame /*extends Game*/ {
	cardsManager: CardsManager
	animationManager: InstanceType<typeof BgaAnimations.Manager>
	getCurrentPlayer(): QuorumPlayer
	getPlayerId(): number
	getPlayerScore(playerId: number): number
	setTooltip(id: string, html: string): void
	setTooltipToClass(className: string, html: string): void
	clientActionData: ClientActionData
	resetClientActionData(): void
	addTooltipOnClickHelpButton(idButton: string, tooltipContent: string, delay?: number): void
	handSelectionChange(selection: NationTile[], lastChange: NationTile | null): void
	takeAction(action: string, data?: any, options?: { lock: boolean; checkAction: boolean }): Promise<void>
	getScoringTypeName(type: number): string
	getProvinceName(province: number): string
	onProvinceClick(province: number): void
}

interface BoardConfig {
	width: number
	height: number
	name: string
	squares: Array<{ landType: LandType; content: NationTile | null }>
}

interface PlayerTurnArgs {
	canTakeCard: boolean
	canResetRiver: boolean
	selectableRiverCards: QuorumCard[]
	selectableHandCards: QuorumCard[]
}

interface LegacyEffectArgs {
	nationName: string
	elementToSteal: number
	playerTargets: number[]
	possibleTilesToSteal: NationTile[]
	victimId: number
}

interface TeamProposalArgs {
	_private: { possibleNationsToOffer: number[] }
}

interface TeamProposalReactionArgs {
	title: string
	player_name: string
	nationTypeTr: string
	isWant: boolean
}

interface TeamProposalConclusionArgs {}

interface NotifPointsArgs {
	playerId: number
	points: number
	delta: number
	scoreType: string
}

interface NotifScoreArgs {
	playerId: number
	score: number | string
	scoreType: string
}

interface NotifScoreSegment {
	squareScores: Record<number, number>
	multiplier: number
}

interface NotifCounter {
	counterName: string
	counterValue: number
	playerId: number
}

interface NotifUpdateCounters {
	counters: [{ [name: string]: CounterValue }]
}

interface NotifWinnerArgs {
	playerId: number
}

interface NotifScorePointArgs {
	playerId: number
	points: number
}
interface NotifPossibleSquares {
	possibleSquares: number[]
}

interface NotifImportantMessageArgs {
	message: string
	type: 'POSITIVE' | 'NEGATIVE' | 'WARNING' | 'WIN'
	temporary: boolean
	args: Array<any>
}

type MoveLocation = 'HAND' | 'DECK' | 'STOCK' | 'TABLE' | 'DISCARD' | 'RIVER' | 'BOARD'

interface NotifMaterialMove {
	type: MaterialType
	from: MoveLocation
	to: MoveLocation
	fromArg: number
	toArg: number
	material: Array<any | string> //elements (cards for exemple), or tokenIds
}

interface SwappedMaterial {
	from: 'HAND' | 'DECK' | 'FESTIVAL'
	to: 'HAND' | 'DECK' | 'FESTIVAL'
	fromArg: number
	toArg: number
	material: any | string
}

type MaterialType = 'CARD' | 'TOKEN' | 'FIRST_PLAYER_TOKEN' | 'TILE' | 'TREASURE'

interface NotifMaterialSwap {
	type: MaterialType
	material1: SwappedMaterial
	material2: SwappedMaterial
}

interface ClientActionData {
	placedCardId: string | null
	destinationSquare: string | null
	previousCardParentInHand: HTMLElement | null
}
