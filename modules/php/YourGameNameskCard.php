<?php

namespace Bga\Games\Quorum;

/**
 * A QuorumCard is a physical card. It contains informations from matching QuorumCardInfo, with technical informations like id and location.
 * Location : deck or hand
 * Location arg : order (in deck), playerId (in hand)
 * Type : the Wizard type
 * Type arg :  player color
 */
class QuorumCard extends QuorumCardInfo {
    public int $id;
    public string $location;
    public int $location_arg;
    public int $type;
    public int $type_arg;

    public function __construct($dbCard, array $additionalParameters) {
        array_key_exists('id', $dbCard) ? $this->id = intval($dbCard['id']) : null;
        array_key_exists('location', $dbCard) ? $this->location = $dbCard['location'] : null;
        array_key_exists('location_arg', $dbCard) ? $this->location_arg = intval($dbCard['location_arg']) : null;
        array_key_exists('type', $dbCard) ? $this->type = intval($dbCard['type']) : null;
        array_key_exists('type_arg', $dbCard) ? $this->type_arg = intval($dbCard['type_arg']) : null;
        $materialInfo = $additionalParameters["material"];
        $cardInfo = $materialInfo[$this->type][$this->type_arg];
        $this->value = $cardInfo->value;
        $this->power = $cardInfo->power;
        $this->name = $cardInfo->name;
    }
}
