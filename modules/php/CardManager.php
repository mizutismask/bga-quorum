<?php

namespace Bga\Games\Quorum;

use Bga\Games\Quorum\DeckManager;
use Constants;

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

    public function riverContainsEnoughGods(): bool {
        return $this->countCardsOfTypeFromLocation(TABLE_CARD, 2, "river") >= 3;
    }

    public function refillRiver() {
        $newCard = $this->castSingle($this->deck->pickCardForLocation('deck', 'river'));

        $this->game->notify->all('materialMove', "", [
            'type' => Constants::MATERIAL_TYPE_CARD,
            'from' => Constants::MATERIAL_LOCATION_DECK,
            'to' => Constants::MATERIAL_LOCATION_RIVER,
            'material' => [$newCard],
        ]);
    }

    public function sortPlayedCards() { //'province', "power"
        foreach ($this->game->getPlayers() as $playerId => $player) {
            $cards = $this->getCardsInLocation("played-$playerId");
            usort($cards, function ($a, $b) {
                return [$a->scoringType, $a->power] <=> [$b->scoringType, $b->power];
            });
            foreach ($cards as $i => $card) {
                $this->deck->moveCard($card->id, "played-$playerId", $i + 1);
            }
            $refreshed = $this->getCardsInLocation("played-$playerId", null, "location_arg");
            $this->game->notify->all("materialMove",  "",  [
                'playerId' => $playerId,
                'type' => $this->materialType,
                'from' => "played-$playerId",
                'to' => "played-$playerId",
                'material' => $refreshed,
            ]);
        }
    }
}
