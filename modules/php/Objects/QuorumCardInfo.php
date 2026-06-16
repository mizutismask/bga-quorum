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
    public bool $isGod;
    public int $leftEffect;
    public int $rightEffect;
    public int $architectureSubType;

    public function __construct(int $power, int $province, int $influence, int $scoringType, array $tradeRessources = [], int $leftEffect = 0, int $rightEffect = 0) {
        $this->power = $power;
        $this->province = $province;
        $this->tradeRessources = $tradeRessources;
        $this->influence = $influence;
        $this->scoringType = $scoringType;
        $this->leftEffect = $leftEffect;
        $this->rightEffect = $rightEffect;
    }
}
