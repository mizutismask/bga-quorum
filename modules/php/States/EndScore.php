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

        foreach (Constants::ALL_PROVINCES as $province) {
            foreach ($players as $playerId => $player) {
                $this->scoreProvince($province, $playerId, $playedCards);
            }
        }

        foreach ([Constants::CARD_TYPE_MILITARY, Constants::CARD_TYPE_TRADE, Constants::CARD_TYPE_ARCHITECTURE, Constants::CARD_TYPE_INTRIGUE] as $scoringType) {
            $this->scoreCardType($scoringType, $playedCards);
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
     * @return void 
     */
    private function scoreProvince(int $playerId, int $province, array $playedCards) {
        $scoringMatch = [
            Constants::PROVINCE_GERMANIA => Constants::CARD_TYPE_INTRIGUE,
            Constants::PROVINCE_GALLIA => Constants::CARD_TYPE_MILITARY,
            Constants::PROVINCE_HISPANIA => Constants::CARD_TYPE_TRADE,
            Constants::PROVINCE_MACEDONIA => Constants::CARD_TYPE_ARCHITECTURE
        ];
        $value = $this->game->nationValueCounters[$province]->get($playerId);
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
                    break;
                case Constants::PROVINCE_AFRICA:
                    $cardsCount = count(array_filter($playedCards, fn($card) => $card->power === 1));
                    $rank = $this->game->nationRankCounters[$province]->get($playerId);
                    $influence = $this->game->nationInfluenceCounters[$province]->get();
                    $multiplier = max(0, $influence - $rank + 1);
                    $score = $cardsCount * $multiplier;
                    $this->game->playerScore->inc($playerId, $score, new NotificationMessage(""));
                case Constants::PROVINCE_ASIA:
                    $cardsCount = count(array_filter($playedCards, fn($card) => $card->power === 2));
                    $rank = $this->game->nationRankCounters[$province]->get($playerId);
                    $influence = $this->game->nationInfluenceCounters[$province]->get();
                    $multiplier = max(0, $influence - $rank + 1);
                    $score = $cardsCount * $multiplier;
                    $this->game->playerScore->inc($playerId, $score, new NotificationMessage(""));
            }
        }
    }

    /**
     * 
     * @param int $scoringType 
     * @param array<array<QuorumCard>> $playedCards 
     * @return void 
     */
    private function scoreCardType(int $scoringType, array $playedCards) {
        foreach ($this->game->getPlayers() as $playerId => $player) {
            $cardsOfType = array_filter($playedCards[$playerId], fn($card) => $card->scoringType === $scoringType);
            switch ($scoringType) {
                case Constants::CARD_TYPE_MILITARY:
                    $this->scoreMilitary($playerId, $cardsOfType);
                case Constants::CARD_TYPE_TRADE:
                    $this->scoreTrade($playerId, $cardsOfType);
                case Constants::CARD_TYPE_ARCHITECTURE:
                    $this->scoreArchitecture($playerId, $cardsOfType);
                case Constants::CARD_TYPE_INTRIGUE:
                    $this->scoreIntrigue($playerId, $playedCards[$playerId]);
            }
        }
    }

    /**
     * @param array<QuorumCard> $playedCards 
     * @return void 
     */
    private function scoreIntrigue(int $playerId, $playedCards) {
        $threes = array_filter($playedCards, fn($card) => $card->power === 3);
        $intrigues = array_filter($playedCards, fn($card) => $card->scoringType === Constants::CARD_TYPE_INTRIGUE);
        $score = count($threes) * count($intrigues);
        $this->game->playerScore->inc($playerId, $score, new NotificationMessage(""));
    }

    /**
     * @param array<QuorumCard> $cardsOfType 
     * @return void 
     */
    private function scoreTrade(int $playerId, $cardsOfType) {
        $resourceCounts = [];

        foreach ($cardsOfType as $card) {
            foreach ($card->tradeRessources as $resource) {
                $resourceCounts[$resource] = ($resourceCounts[$resource] ?? 0) + 1;
            }
        }

        $score = 0;

        foreach ($resourceCounts as $count) {
            if ($count >= 4) {
                $score += 6;
            } elseif ($count === 3) {
                $score += 4;
            } elseif ($count === 2) {
                $score += 2;
            }
        }
        $this->game->playerScore->inc($playerId, $score, new NotificationMessage(""));
    }

    /**
     * @param array<QuorumCard> $cardsOfType 
     * @return void 
     */
    private function scoreArchitecture(int $playerId, $cardsOfType) {
        $pointsByCount = [0 => 0, 1 => 1, 2 => 4, 3 => 8, 4 => 12, 5 => 18, 6 => 24];
        return $pointsByCount[count($cardsOfType)];
    }

    /**
     * @param array<QuorumCard> $cardsOfType 
     * @return void 
     */
    private function scoreMilitary(int $playerId, $cardsOfType) {
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

        $powerCounts[1] = ($powerCounts[1] ?? 0) - $groupsOfThree;
        $powerCounts[2] = ($powerCounts[2] ?? 0) - $groupsOfThree;
        $powerCounts[3] = ($powerCounts[3] ?? 0) - $groupsOfThree;

        /** Consecutive pairs 1+2 */
        $pairs12 = min(
            $powerCounts[1] ?? 0,
            $powerCounts[2] ?? 0
        );

        $score += $pairs12 * 5;

        $powerCounts[1] -= $pairs12;
        $powerCounts[2] -= $pairs12;

        /** Consecutive pairs 2+3 */
        $pairs23 = min(
            $powerCounts[2] ?? 0,
            $powerCounts[3] ?? 0
        );

        $score += $pairs23 * 5;
        $this->game->playerScore->inc($playerId, $pairs23 * 5, new NotificationMessage(""));
    }
}
