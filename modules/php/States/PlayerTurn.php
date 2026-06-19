<?php

declare(strict_types=1);

namespace Bga\Games\Quorum\States;

use Bga\GameFramework\Actions\CheckAction;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Quorum\Game;
use Bga\Games\Quorum\Objects\QuorumCard;
use Constants;

class PlayerTurn extends GameState {

    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: Constants::STATE_ID_PLAYER_TURN,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must move the Oshax'),
            descriptionMyTurn: clienttranslate('You must select an action'),
        );
    }

    function onEnteringState(int $activePlayerId, array $args) {
    }

    /**
     * Game state arguments, example content.
     *
     * This method returns some additional information that is very specific to the `PlayerTurn` game state.
     */
    public function getArgs(int $activePlayerId): array {
        // Get some values from the current game situation from the database.
        return [
            "canTakeCard" => !$this->globals->get(Constants::GLBL_TOOK_CARD),
            "canResetRiver" => !$this->globals->get(Constants::GLBL_DID_RESET_RIVER) && $this->game->cardManager->riverContainsEnoughGods(),
            "selectableRiverCards" => $this->getSelectableRiverCards($activePlayerId),
            "selectableHandCards" => $this->getSelectableHandCards($activePlayerId),
        ];
    }

    #[PossibleAction]
    public function actTakeCard(int $cardId, int $activePlayerId, array $args) {
        $nextState = PlayerTurn::class;
        // check input values
        $validMoves = array_map(fn($card) => $card->id, $args['selectableRiverCards']);
        if (!in_array($cardId, $validMoves)) {
            throw new UserException(clienttranslate('You can take a card only from the river and you should not have more than 3 gods in your hand'));
        }
        $card = $this->game->cardManager->getCard($cardId);
        $this->globals->set(Constants::GLBL_TOOK_CARD, true);
        if ($card->isGod) {
            //reveal the card and move it to player hand
            $this->game->cardManager->moveCardToPlayerHand($cardId, $activePlayerId, false, clienttranslate('${player_name} takes a god card'));
            $this->game->globals->set(Constants::GLBL_CURRENT_GOD, $cardId);
            $nextState = GodEffect::class;
        } else {
            $this->game->cardManager->moveCardToPlayerHand($cardId, $activePlayerId, true, "");
        }
        $this->game->cardManager->refillRiver();
        //$this->notify->all('materialMove', "", [ 'type' => Constants::MATERIAL_TYPE_CARD, 'from' => Constants::MATERIAL_LOCATION_DECK, 'to' => Constants::MATERIAL_LOCATION_TOP_OF_DECK, 'material' => [QuorumCard::stripSecretInfo($this->game->cardManager->getTopOfLocation("deck"))]]);
        return $nextState;
    }

    #[PossibleAction]
    public function actPlayCard(int $cardId, int $activePlayerId, array $args) {
        // check input values
        $validMoves = array_map(fn($card) => $card->id, $args['selectableHandCards']);
        if (!in_array($cardId, $validMoves)) {
            throw new UserException(clienttranslate('You can’t play this card'));
        }
        $card = $this->game->cardManager->getCard($cardId);
        //move tokens accordingly
        $moved = $this->game->tokenManager->moveNationToken($card->province, $activePlayerId,  $card->influence);
        $this->game->nationValueCounters[$card->province]->set($activePlayerId, intval($moved->location));
        $this->updateRanks();

        $this->game->cardManager->insertCardOnExtremePosition($cardId, "played-$activePlayerId", true);

        return NextPlayer::class;
    }

    private function updateRanks() {
        $tokens = $this->game->tokenManager->getAll("card_location desc, card_location_arg");
        foreach (Constants::ALL_PROVINCES as $province) {
            $provinceTokens = array_values(array_filter($tokens, fn($token) => $token->type == $province));
            $this->game->dump('****************province***', $province);
            $this->game->dump('****************provinceTokens***', json_encode($provinceTokens));
            $rank = $this->game->getPlayerCount() + 1;

            foreach ($provinceTokens as $token) {
                if ((int) $token->location === 0) {
                    $this->game->nationRankCounters[$province]->set($token->type_arg, $this->game->getPlayerCount());
                    continue;
                }
                $rank = 1;

                foreach ($provinceTokens as $otherToken) {
                    if (
                        (int) $otherToken->location > (int) $token->location ||
                        ((int) $otherToken->location == (int) $token->location && (int) $otherToken->location_arg < (int) $token->location_arg)
                    ) {
                        ++$rank;
                    }
                }

                $this->game->nationRankCounters[$province]->set($token->type_arg, $rank);
            }
        }
    }

    #[PossibleAction]
    public function actResetRiver(int $activePlayerId) {
        $river = $this->game->cardManager->getRiverCards();
        $this->game->cardManager->replaceRiver(false);
        //put the old river cards back in the deck and shuffle
        foreach ($river as $card) {
            $this->game->cardManager->moveCardToLocation($card, "deck", 0, false);
        }
        $this->game->cardManager->shuffle();

        $this->globals->set(Constants::GLBL_DID_RESET_RIVER, true);

        $this->game->notify->all('riverChange', "", [
            'type' => Constants::MATERIAL_TYPE_CARD,
            'from' => Constants::MATERIAL_LOCATION_DECK,
            'to' => Constants::MATERIAL_LOCATION_RIVER,
            'material' => $this->game->cardManager->getRiverCards(),
            "newTopCard" => QuorumCard::stripSecretInfo($this->game->cardManager->getTopOfLocation("deck"))
        ]);
        return PlayerTurn::class;
    }

    /**
     * Player action, example content.
     *
     * In this scenario, each time a player pass, this method will be called. This method is called directly
     * by the action trigger on the front side with `bgaPerformAction`.
     */
    #[PossibleAction]
    public function actPass(int $activePlayerId) {
        $end = $this->game->hasReachedEndOfGameRequirements();
        if ($end) {
            if ($end && $this->globals->get(Constants::LAST_TURN) == 0) {
                $this->globals->set(Constants::LAST_TURN, $this->game->getLastPlayer()); //we play until the last player to finish the round
                if (!$this->game->isLastPlayer($activePlayerId)) {
                    $this->notify->all('lastTurn', clienttranslate('${player_name} triggered the end of the game, finishing round !'), ['player_name' => $this->game->getPlayerNameById($activePlayerId)]);
                    return NextPlayer::class;
                } else {
                    return EndOfRound::class;
                }
            }
        } else {
            return NextPlayer::class;
        }
    }

    private function getSelectableRiverCards(int $playerId): array {
        $cards = $this->game->cardManager->getRiverCards();
        $playerHand = $this->game->cardManager->getPlayerHand($playerId);
        $godInHand = array_filter($playerHand, fn($card) => $card->isGod);
        if (count($godInHand) == 3) {
            //remove gods
            $cards = array_filter($cards, fn($card) => !$card->isGod);
        }
        return $cards;
    }

    private function getSelectableHandCards(int $playerId): array {
        $playerHand = $this->game->cardManager->getPlayerHand($playerId);
        $cards = array_filter($playerHand, fn($card) => !$card->isGod);
        return $cards;
    }

    #[CheckAction(false)]
    function actResetPlayerTurn() {
        $possible = $this->globals->get(Constants::CAN_RESET_TURN);
        if (!$possible) {
            throw new UserException(clienttranslate("Undo is not available"));
        }
        $this->game->undoRestorePoint();
        //$this->toggleResetTurn(false);
        $this->gamestate->reloadState();
    }

    /**
     * This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
     * You can do whatever you want in order to make sure the turn of this player ends appropriately
     * (ex: play a random card).
     * 
     * See more about Zombie Mode: https://en.doc.boardgamearena.com/Zombie_Mode
     *
     * Important: your zombie code will be called when the player leaves the game. This action is triggered
     * from the main site and propagated to the gameserver from a server, not from a browser.
     * As a consequence, there is no current player associated to this action. In your zombieTurn function,
     * you must _never_ use `getCurrentPlayerId()` or `getCurrentPlayerName()`, 
     * but use the $playerId passed in parameter and $this->game->getPlayerNameById($playerId) instead.
     */
    function zombie(int $playerId) {
        //zombie level 1 (random action)
        $args = $this->getArgs($playerId);
        if ($args['canTakeCard'] && $args['selectableRiverCards']) {
            $validMoves = $args['selectableRiverCards'];
            $card = $this->game->getRandomValue($validMoves);
            return $this->actTakeCard($card->id, $playerId, $args);
        } else {
            $validMoves = $args['selectableHandCards'];
            $card = $this->game->getRandomValue($validMoves);
            return $this->actPlayCard($card->id, $playerId, $args);
        }
    }
}
