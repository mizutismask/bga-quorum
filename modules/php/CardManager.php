<?php

namespace Bga\Games\Quorum;

use Bga\Games\Quorum\DeckManager;

const TABLE_CARD = "action_card";

class CardManager extends DeckManager {

    public function dealHands($notify = false) {
        $qty = 3;
        $players = $this->game->loadPlayersBasicInfos();
        foreach ($players as $playerId => $player) {
            $this->addCardsToHand($qty, $playerId, $player["player_no"], $notify);
        }
    }

    public function pickInitialActionCards() {
        $this->initRiver(4);
    }

    public function moveActionCardToPlayerHand($cardId, $playerId, bool $faceDown = false) {
        $this->moveCardToPlayerHand($cardId, $playerId, $faceDown, clienttranslate('${player_name} takes an action card'));
    }
}
