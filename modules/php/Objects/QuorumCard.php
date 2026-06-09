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
        //$cardInfo = $cardsDescription[$this->type][$this->type_arg];
        //$this->isGod = $this->type > 72;
    }
}
