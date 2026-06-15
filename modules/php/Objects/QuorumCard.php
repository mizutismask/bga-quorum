<?php

namespace Bga\Games\Quorum\Objects;


/**
 * A QuorumCard is a physical card. It contains informations from matching QuorumCardInfo, with technical informations like id and location.
 * Location : deck, river or hand
 * Location arg : order (in deck or river), player (in hand)
 * Type : god or normal
 * Type arg : the quorumCard type (QuorumCardInfo id)
 */
class QuorumCard extends QuorumCardInfo {
    public int $id;
    public string $location;
    public int $location_arg;
    public int $type;
    public int $type_arg;

    public function __construct($dbCard, $cardsDescription) {
        $this->id = intval($dbCard['id']);
        $this->location = $dbCard['location'];
        $this->location_arg = intval($dbCard['location_arg']);
        $this->type = intval($dbCard['type']);
        $this->type_arg = intval($dbCard['type_arg']);


        $cardInfo = $cardsDescription["material"][$this->type][$this->type_arg];
        $this->isGod = $this->type == 2;

        $this->power = $cardInfo->power;
        $this->province = $cardInfo->province;
        $this->influence = $cardInfo->influence;
        $this->leftEffect = $cardInfo->leftEffect;
        $this->rightEffect = $cardInfo->rightEffect;
        $this->scoringType = $cardInfo->scoringType;
        $this->tradeRessources = $cardInfo->tradeRessources;
    }

    public static function stripSecretInfo(QuorumCard $card): QuorumCard {
        $copy = clone $card;
        $copy->type = 0;
        $copy->type_arg = 0;
        unset($copy->power);
        unset($copy->influence);
        unset($copy->leftEffect);
        unset($copy->rightEffect);
        unset($copy->scoringType);
        unset($copy->tradeRessources);
        return $copy;
    }
}
