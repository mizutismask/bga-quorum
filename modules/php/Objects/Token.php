<?php

namespace Bga\Games\Quorum\Objects;


/**
 * A Token is a physical card. It contains informations from matching TokenInfo, with technical informations like id and location.
 * Location : deck, river or hand
 * Location arg : order (in deck or river), player (in hand)
 * Type : god or normal
 * Type arg : the Token type (TokenInfo id)
 */
class Token{
    public int $id;
    public string $location;
    public int $location_arg;
    public int $type;
    public int $type_arg;

    public function __construct($dbCard) {
        $this->id = intval($dbCard['id']);
        $this->location = $dbCard['location'];
        $this->location_arg = intval($dbCard['location_arg']);
        $this->type = intval($dbCard['type']);
        $this->type_arg = intval($dbCard['type_arg']);
    }
}
