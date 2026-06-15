<?php

declare(strict_types=1);

namespace Bga\Games\Quorum\States;

use Bga\GameFramework\Actions\CheckAction;
use Bga\GameFramework\NotificationMessage;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Quorum\Game;
use Bga\Games\Quorum\QuorumCard;
use Constants;

class GodEffect extends GameState {

    function __construct(protected Game $game) {
        parent::__construct(
            $game,
            id: Constants::STATE_ID_GOD_EFFECT,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must choose a province'),
            descriptionMyTurn: clienttranslate('You must choose a province to apply ${godName} effect (${leftEffect} on the left, ${rightEffect} on the right)'),
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
        $god =  $this->game->cardManager->getCard($this->globals->get(Constants::GLBL_CURRENT_GOD));
        return [
            "leftEffect" => $god->leftEffect < 0 ? $god->leftEffect : "+" . $god->leftEffect,
            "rightEffect" => $god->rightEffect < 0 ? $god->rightEffect : "+" . $god->rightEffect,
            "godName" => $this->getGodName($god->type_arg),
        ];
    }

    public function getGodName(int $cardType): string {
        return match ($cardType) {
            73 => clienttranslate('Minerva'),
            74 => clienttranslate('Neptunus'),
            75 => clienttranslate('Pluto'),
            76 => clienttranslate('Venus'),
            77 => clienttranslate('Vulcanus'),
            78 => clienttranslate('Mars'),
            79 => clienttranslate('Jupiter'),
            default => "Unknown god",
        };
    }

    #[PossibleAction]
    public function actChooseProvince(int $province, int $activePlayerId, array $args) {
        $nextState = PlayerTurn::class;
        $god =  $this->game->cardManager->getCard($this->globals->get(Constants::GLBL_CURRENT_GOD));

        $this->game->tokenManager->moveNationToken($province, $activePlayerId,  $god->influence);
        //effect on the left and right provinces
        $leftProvince = $this->getLeftProvince($province);
        $this->game->nationInfluenceCounters[$leftProvince]->inc($god->leftEffect, new NotificationMessage(
            $god->leftEffect < 0 ? clienttranslate('${province} loses ${amount} influence') : clienttranslate('${province} gains ${amount} influence'),
            ['amount' => abs($god->leftEffect), 'province' => $this->game->getProvinceName($leftProvince)]
        ));

        $rightProvince = $this->getRightProvince($province);
        $this->game->nationInfluenceCounters[$rightProvince]->inc($god->rightEffect, new NotificationMessage(
            $god->rightEffect < 0 ? clienttranslate('${province} loses ${amount} influence') : clienttranslate('${province} gains ${amount} influence'),
            ['amount' => abs($god->rightEffect), 'province' => $this->game->getProvinceName($rightProvince)]
        ));

        return $nextState;
    }

    private function getLeftProvince(int $province) {
        $provinces = $this->globals->get(Constants::GLBL_ORDERED_PROVINCES);
        $index = array_search($province, $provinces, true);

        if ($index === false) {
            throw new \BgaSystemException("Unknown province: $province");
        }

        return $provinces[($index - 1 + count($provinces)) % count($provinces)];
    }

    private function getRightProvince(int $province): int {
        $provinces = $this->globals->get(Constants::GLBL_ORDERED_PROVINCES);

        $index = array_search($province, $provinces, true);

        if ($index === false) {
            throw new \BgaSystemException("Unknown province: $province");
        }

        return $provinces[($index + 1) % count($provinces)];
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
        $possible = Constants::ALL_PROVINCES;
        return $this->actChooseProvince($this->game->getRandomValue($possible), $playerId, $this->getArgs($playerId));
    }
}
