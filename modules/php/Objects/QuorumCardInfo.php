<?php

namespace Bga\Games\Quorum\Objects;

/**
 * A QuorumCardInfo is the graphic representation of a card (informations on it : right effect, left effect…).
 */
class QuorumCardInfo {
    public int $power;
    public int $province;
    public int $scoringType;
    public int $influence;
    public array $tradeRessources;

    public function __construct(int $power, int $province, int $influence, int $scoringType,array $tradeRessources = []) {
        $this->power = $power;
        $this->province = $province;
        $this->tradeRessources = $tradeRessources;
        $this->influence = $influence;
        $this->scoringType = $scoringType;
    }
}
