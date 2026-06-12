<?php

namespace Bga\Games\Quorum;

use Bga\GameFramework\Table;
use Bga\Games\Quorum\DeckManager;
use Constants;

const TABLE_TOKEN = "token";

class TokenManager extends DeckManager {
    public function getAll(string $orderBy) {
        $query = new QueryBuilder($this->game, TABLE_TOKEN);
        $res = $query
            ->select($this->game->getTypicalTableFields())
            ->orderBy($orderBy)
            ->get();
        return $this->cast($res);
    }

    public function moveNationToken(int $province, int $playerId, int $qty) {
        $token = $this->getNationToken($province, $playerId);
        $this->deck->insertCardOnExtremePosition($token->id, min(15,intval($token->location) + $qty), true);
        $refreshedToken = $this->getCard($token->id);
        //notify token move
        $this->game->notify->all('materialMove', clienttranslate('${player_name} reaches ${number} in ${provinceName}'), [
            'type' => Constants::MATERIAL_TYPE_TOKEN,
            'from' => Constants::MATERIAL_LOCATION_BOARD,
            'to' => Constants::MATERIAL_LOCATION_BOARD,
            'toArg' => $token->location_arg,
            'material' => [$refreshedToken],
            "player_name" => $this->game->getPlayerNameById($playerId),
            "number" => $refreshedToken->location,
            "provinceName" => $this->game->getProvinceName($province),
            "i18n" => ["provinceName"],
        ]);
        return $refreshedToken;
    }

    public function getNationToken(int $province, int $playerId) {
        return $this->getCardOfTypeAndTypeArg(TABLE_TOKEN, $province, $playerId);
    }
}
