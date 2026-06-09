<?php

namespace Bga\Games\Quorum;

use Bga\Games\Quorum\DeckManager;

const TABLE_CARD = "card";

class CardManager extends DeckManager {

    public function dealHands($notify = false) {
        $qty = 4;
        $players = $this->game->loadPlayersBasicInfos();
        foreach ($players as $playerId => $player) {
            $this->addCardsToHand($qty, $playerId, $notify);
        }
    }

    public function moveActionCardToPlayerHand($cardId, $playerId, bool $faceDown = false) {
        $this->moveCardToPlayerHand($cardId, $playerId, $faceDown, clienttranslate('${player_name} takes an action card'));
    }
}
