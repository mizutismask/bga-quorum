<?php

declare(strict_types=1);

namespace Bga\Games\Quorum\States;

use Bga\GameFramework\NotificationMessage;
use Bga\GameFramework\StateType;
use Bga\Games\Quorum\Game;
use Bga\Games\Quorum\Objects\QuorumCard;
use Constants;

const ST_END_GAME = 99;

const SCORE_GAIN_PER_RARE_TREASURE = 3;
const SCORE_LOSE_PER_RATS = 1;
const SCORE_LOSE_PER_UNFILLED_ROOMS = 5;
const SCORE_SOLO_COLOR = 5;


class EndScore extends \Bga\GameFramework\States\GameState {

    function __construct(
        protected Game $game,
    ) {
        parent::__construct(
            $game,
            id: Constants::STATE_ID_END_SCORE,
            type: StateType::GAME,
        );
    }

    /**
     * Game state action, example content.
     *
     * The onEnteringState method of state `EndScore` is called just before the end of the game.
     */
    public function onEnteringState() {
        // Here, we would compute scores if they are not updated live, and compute average statistics
        $this->game->cardManager->sortPlayedCards();
        $this->scorePoints();
        $this->scoreTieBreaker();

        if ($this->game->isStudio()) {
            $this->game->stMakeEveryoneActive();
            return DebugGameEnd::class;
        } else {
            return ST_END_GAME;
        }
    }


    public function scorePoints() {
        $playedCards = [];
        $players = $this->game->getPlayers();
        foreach ($players as $playerId => $player) {
            $playedCards[$playerId] = $this->game->cardManager->getCardsInLocation("played-$playerId");
        }

        $provinceTotalByPlayer = array_fill_keys(array_keys($this->game->getPlayers()), 0);
        foreach (Constants::ALL_PROVINCES as $province) {
            foreach ($players as $playerId => $player) {
                $total = $this->scoreProvince($playerId, $province,  $playedCards[$playerId]);
                $provinceTotalByPlayer[$playerId] += $total;
            }
        }

        foreach ($players as $playerId => $player) {
            $this->game->notify->all("score", "", ["playerId" => $playerId, "score" => $provinceTotalByPlayer[$playerId], "scoreType" => "total"]);
        }

        $typeTotalByPlayer = array_fill_keys(array_keys($this->game->getPlayers()), 0);
        foreach ([Constants::CARD_TYPE_MILITARY, Constants::CARD_TYPE_TRADE, Constants::CARD_TYPE_ARCHITECTURE, Constants::CARD_TYPE_INTRIGUE] as $scoringType) {
            $total = $this->scoreCardType($scoringType, $playedCards);
            foreach ($players as $p => $player) {
                $typeTotalByPlayer[$p] += $total[$p];
            }
        }
        foreach ($players as $playerId => $player) {
            $this->game->notify->all("score", "", ["playerId" => $playerId, "score" => $typeTotalByPlayer[$playerId], "scoreType" => "type-total"]);
        }
    }

    private function scoreTieBreaker() {
        foreach ($this->game->loadPlayersBasicInfos() as $playerId => $playerInfo) {
            //$this->game->playerScoreAux->set($playerId, $this->game->playerFishCounter->get($playerId), new NotificationMessage(""));
        }
    }

    private function getPoints($playerId) {
        return 0;
    }

    /**
     * @param array<QuorumCard> $playedCards 
     */
    private function scoreProvince(int $playerId, int $province, array $playedCards): int {
        $scoringMatch = [
            Constants::PROVINCE_GERMANIA => Constants::CARD_TYPE_INTRIGUE,
            Constants::PROVINCE_GALLIA => Constants::CARD_TYPE_MILITARY,
            Constants::PROVINCE_HISPANIA => Constants::CARD_TYPE_TRADE,
            Constants::PROVINCE_MACEDONIA => Constants::CARD_TYPE_ARCHITECTURE
        ];
        $score = 0;
        $value = $this->game->nationValueCounters[$province]->get($playerId);
        //throw new \Exception(json_encode($this->game->nationValueCounters));
        if ($value > 0) {
            switch ($province) {
                case Constants::PROVINCE_GERMANIA:
                case Constants::PROVINCE_GALLIA;
                case Constants::PROVINCE_HISPANIA;
                case Constants::PROVINCE_MACEDONIA;
                    $cardsCount = count(array_filter($playedCards, fn($card) => $card->scoringType === $scoringMatch[$province]));
                    $rank = $this->game->nationRankCounters[$province]->get($playerId);
                    $influence = $this->game->nationInfluenceCounters[$province]->get();
                    $multiplier = max(0, $influence - $rank + 1);
                    $score = $cardsCount * $multiplier;
                    $this->game->playerScore->inc($playerId, $score, new NotificationMessage(""));
                    $this->notifyProvinceScore($playerId, $province, $score, $influence, $rank, $multiplier, $cardsCount);
                    break;
                case Constants::PROVINCE_AFRICA:
                    $cardsCount = count(array_filter($playedCards, fn($card) => $card->power === 1));
                    $rank = $this->game->nationRankCounters[$province]->get($playerId);
                    $influence = $this->game->nationInfluenceCounters[$province]->get();
                    $multiplier = max(0, $influence - $rank + 1);
                    $score = $cardsCount * $multiplier;
                    $this->game->playerScore->inc($playerId, $score, new NotificationMessage(""));
                    $this->notifyProvinceScore($playerId, $province, $score, $influence, $rank, $multiplier, $cardsCount);
                    break;
                case Constants::PROVINCE_ASIA:
                    $cardsCount = count(array_filter($playedCards, fn($card) => $card->power === 2));
                    $rank = $this->game->nationRankCounters[$province]->get($playerId);
                    $influence = $this->game->nationInfluenceCounters[$province]->get();
                    $multiplier = max(0, $influence - $rank + 1);
                    $score = $cardsCount * $multiplier;
                    $this->game->playerScore->inc($playerId, $score, new NotificationMessage(""));
                    $this->notifyProvinceScore($playerId, $province, $score, $influence, $rank, $multiplier, $cardsCount);
                    break;
            }
        }
        return $score;
    }

    private function notifyProvinceScore(int $playerId, int $province, int $score, int $influence, int $rank, int $multiplier, int $cardsCount) {
        $this->game->notify->all("score", "", ["playerId" => $playerId, "score" => $rank, "scoreType" => "province-$province-rank"]);
        $this->game->notify->all("score", "", ["playerId" => $playerId, "score" => $cardsCount, "scoreType" => "province-$province-cards"]);
        $this->game->notify->all("score", "", ["playerId" => $playerId, "score" => $multiplier, "scoreType" => "province-$province-influence"]);
        $this->game->notify->all("score", "", ["playerId" => $playerId, "score" => $score, "scoreType" => "province-$province-total"]);
    }

    private function notifyCardTypeScore(int $playerId, int $type, int $score, string $computation) {
        $this->game->notify->all("score", "", ["playerId" => $playerId, "score" => $computation, "scoreType" => "type-$type-computation"]);
        $this->game->notify->all("score", "", ["playerId" => $playerId, "score" => $score, "scoreType" => "type-$type-total"]);
    }

    /**
     * 
     * @param int $scoringType 
     * @param array<array<QuorumCard>> $playedCards 
     */
    private function scoreCardType(int $scoringType, array $playedCards) {
        $scores = array_fill_keys(array_keys($this->game->getPlayers()), 0);
        foreach ($this->game->getPlayers() as $playerId => $player) {
            $cardsOfType = array_filter($playedCards[$playerId], fn($card) => $card->scoringType === $scoringType);
            switch ($scoringType) {
                case Constants::CARD_TYPE_MILITARY:
                    $scores[$playerId] = $this->scoreMilitary($playerId, $cardsOfType);
                    break;
                case Constants::CARD_TYPE_TRADE:
                    $scores[$playerId] = $this->scoreTrade($playerId, $cardsOfType);
                    break;
                case Constants::CARD_TYPE_ARCHITECTURE:
                    $scores[$playerId] = $this->scoreArchitecture($playerId, $cardsOfType);
                    break;
                case Constants::CARD_TYPE_INTRIGUE:
                    $scores[$playerId] = $this->scoreIntrigue($playerId, $playedCards[$playerId]);
                    break;
            }
        }
        return $scores;
    }

    /**
     * @param array<QuorumCard> $playedCards 
     */
    private function scoreIntrigue(int $playerId, $playedCards) {
        $threes = array_filter($playedCards, fn($card) => $card->power === 3);
        $intrigues = array_filter($playedCards, fn($card) => $card->scoringType === Constants::CARD_TYPE_INTRIGUE);
        $score = count($threes) * count($intrigues);
        $this->game->playerScore->inc($playerId, $score, new NotificationMessage(""));

        $this->notifyCardTypeScore($playerId, Constants::CARD_TYPE_INTRIGUE, $score, count($threes) . "x" . count($intrigues));
        return $score;
    }

    /**
     * @param array<QuorumCard> $cardsOfType 
     */
    private function scoreTrade(int $playerId, $cardsOfType) {
        $resourceCounts = [];

        foreach ($cardsOfType as $card) {
            foreach ($card->tradeRessources as $resource) {
                $resourceCounts[$resource] = ($resourceCounts[$resource] ?? 0) + 1;
            }
        }

        $score = 0;
        $computation = "";

        foreach ($resourceCounts as $count) {
            if ($count >= 4) {
                $score += 6;
                $computation .= "+6";
            } elseif ($count === 3) {
                $score += 4;
                $computation .= "+4";
            } elseif ($count === 2) {
                $score += 2;
                $computation .= "+2";
            }
        }
        $computation = ltrim($computation, '+');
        $this->game->playerScore->inc($playerId, $score, new NotificationMessage(""));

        $this->notifyCardTypeScore($playerId, Constants::CARD_TYPE_TRADE, $score, $computation);
        return $score;
    }

    /**
     * @param array<QuorumCard> $cardsOfType 
     */
    private function scoreArchitecture(int $playerId, $cardsOfType): int {
        $pointsByCount = [0 => 0, 1 => 1, 2 => 4, 3 => 8, 4 => 12, 5 => 18, 6 => 24];
        $points = $pointsByCount[count($cardsOfType)];
        $this->game->playerScore->inc($playerId, $points, new NotificationMessage(""));
        $this->notifyCardTypeScore($playerId, Constants::CARD_TYPE_ARCHITECTURE, $points, count($cardsOfType) . "->" . $points);
        return $points;
    }

    /**
     * @param array<QuorumCard> $cardsOfType 
     */
    private function scoreMilitary(int $playerId, $cardsOfType): int {
        $powerCounts = [];

        foreach ($cardsOfType as $card) {
            $powerCounts[$card->power] = ($powerCounts[$card->power] ?? 0) + 1;
        }

        $score = 0;

        /** Groups of 1+2+3 */
        $groupsOfThree = min(
            $powerCounts[1] ?? 0,
            $powerCounts[2] ?? 0,
            $powerCounts[3] ?? 0
        );

        $score += $groupsOfThree * 10;
        $this->game->playerScore->inc($playerId, $groupsOfThree * 10, new NotificationMessage(""));

        $powerCounts[1] = ($powerCounts[1] ?? 0) - $groupsOfThree;
        $powerCounts[2] = ($powerCounts[2] ?? 0) - $groupsOfThree;
        $powerCounts[3] = ($powerCounts[3] ?? 0) - $groupsOfThree;

        /** Consecutive pairs 1+2 */
        $pairs12 = min(
            $powerCounts[1] ?? 0,
            $powerCounts[2] ?? 0
        );

        $score += $pairs12 * 5;
        $this->game->playerScore->inc($playerId, $pairs12 * 5, new NotificationMessage(""));

        $powerCounts[1] -= $pairs12;
        $powerCounts[2] -= $pairs12;

        /** Consecutive pairs 2+3 */
        $pairs23 = min(
            $powerCounts[2] ?? 0,
            $powerCounts[3] ?? 0
        );

        $score += $pairs23 * 5;
        $this->game->playerScore->inc($playerId, $pairs23 * 5, new NotificationMessage(""));
        $this->notifyCardTypeScore($playerId, Constants::CARD_TYPE_MILITARY, $score, $groupsOfThree . "x10 + " . $pairs12 + $pairs23 . "x5");
        return $score;
    }
}
