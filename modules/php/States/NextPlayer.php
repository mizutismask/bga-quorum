<?php

declare(strict_types=1);

namespace Bga\Games\Quorum\States;

use Bga\GameFramework\StateType;
use Bga\Games\Quorum\Game;
use Constants;

class NextPlayer extends \Bga\GameFramework\States\GameState {

    function __construct(
        protected Game $game,
    ) {
        parent::__construct(
            $game,
            id: Constants::STATE_ID_NEXT_PLAYER,
            type: StateType::GAME,
            updateGameProgression: true,
        );
    }

    /**
     * Game state action, example content.
     *
     * The onEnteringState method of state `nextPlayer` is called everytime the current game state is set to `nextPlayer`.
     */
    function onEnteringState() {

        $activePlayerId = $this->game->activateNextPlayerCustom();

        $this->game->globals->set(Constants::GLBL_TOOK_CARD, false);
        $this->game->globals->set(Constants::GLBL_DID_RESET_RIVER, false);
        $this->game->globals->delete(Constants::GLBL_CURRENT_GOD);
        //$this->game->setPlayerGlobal($activePlayerId, Constants::GLBL_DISCOVERY_TAKEN, false);

        //$this->game->contextMgr->reset();

        return PlayerTurn::class;
    }
}
