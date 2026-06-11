<?php

namespace Bga\Games\Quorum;

use Bga\GameFramework\Table;
use Bga\Games\Quorum\DeckManager;

const TABLE_TOKEN = "token";

class TokenManager extends DeckManager {
    public function getAll() {
        $query = new QueryBuilder($this->game, TABLE_TOKEN);
        $res = $query
            ->select($this->game->getTypicalTableFields())
            ->get();
        return $this->cast($res);
    }
}
