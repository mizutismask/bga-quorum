<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Quorum implementation : © Séverine Kamycki <mizutismask@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */

declare(strict_types=1);

namespace Bga\Games\Quorum;

use Bga\GameFramework\Components\Counters\PlayerCounter;
use Bga\GameFramework\Components\Deck;
use Bga\Games\Quorum\ExpansionManager;
use Bga\Games\Quorum\States\NextPlayer;
use Constants;

require_once("constants.inc.php");

class Game extends \Bga\GameFramework\Table {
    use UtilTrait;
    use PlayerUtilTrait;
    use DBUtilTrait;
    use GameUtilTrait;
    use DebugUtilTrait;

    private Deck $cards;
    private CardManager $cardManager;
    public PlayerCounter $ticketsCounter;
    private ContextManager $contextManager;
    public ExpansionManager $expansionManager;

    function __construct() {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        $this->initGameStateLabels(array(
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ));

        $this->expansionManager = new ExpansionManager($this);

        $this->ticketsCounter = $this->counterFactory->createPlayerCounter("tickets");

        $this->cards = $this->deckFactory->createDeck("card");
        $this->cards->autoreshuffle = true;
        $this->expansionManager = new ExpansionManager($this);
        $this->cardManager = new CardManager($this, TABLE_CARD, $this->cards, "QuorumCard", Constants::MATERIAL_TYPE_CARD, ["material" => Material::getCards()]);
        $this->contextManager = new ContextManager($this);
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array()): string {
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("(%s, '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                addslashes($player["player_name"]),
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                "INSERT INTO `player` (`player_id`, `player_color`, `player_name`) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //$this->setGameStateInitialValue( 'my_first_global_variable', 0 );
        //initialize everything to be compliant with undo framework
        //foreach ($this->GAMESTATELABELS as $value_label => $ID) if ($ID >= 10 && $ID < 90) $this->setGameStateInitialValue($value_label, 0);

        $this->initStats();
        $this->ticketsCounter->initDb(array_keys($players));

        // TODO: setup the initial game situation here
        $this->globals->set(Constants::LAST_TURN, 0); // last turn is the id of the last player, 0 if it's not last turn
        $this->setupTable($players);

        // does not activate player since it’s done within stNextPlayer
        return NextPlayer::class;

        /************ End of the game initialization *****/
    }

    function setupTable(array $players) {
        $this->setupSharedItems();
        $this->cardManager->dealHands();
        $this->cardManager->initRiver(5);
        $this->cardManager->createCards($this->expansionManager->getGodCardsToGenerate());
        foreach ($players as $playerId => $player) {
        }
    }

    function setupSharedItems() {
        $allProvinces = Constants::ALL_PROVINCES;
        $randomizedProvinces = array_values($this->getRandomSlice($allProvinces, count(Constants::ALL_PROVINCES)));
        $this->globals->set(Constants::GLBL_ORDERED_PROVINCES, $randomizedProvinces);
        
        $this->cardManager->createCards($this->expansionManager->getNormalCardsToGenerate());
    }

    function hasReachedEndOfGameRequirements(): bool {
        //TODO
        return $this->globals->get("round") == 4;
    }

    /**
     * Activates next player, also giving him extra time.
     */
    function activateNextPlayerCustom() {
        $player_id = $this->activeNextPlayer();
        $this->giveExtraTime($player_id);
        $this->playerStats->inc('turns_number', 1, $player_id);
        $this->tableStats->inc('turns_number', 1);
        $this->notify->all('msg', clienttranslate('&#10148; Start of ${player_name}\'s turn'), ['player_name' => $this->getPlayerNameById($player_id)]);
        //$this->makeSavepoint();
        return $player_id;
    }


    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas(int $currentPlayerId): array {
        $stateName = $this->getStateName();
        $isEnd = $stateName === 'endScore' || $stateName === 'gameEnd' || $stateName === 'debugGameEnd';

        $result = [];
        $result['expansion'] = $this->expansionManager->getExpansion();
        $result['version'] = $this->getGameVersion();
        $result['orderedProvinces'] = $this->globals->get(Constants::GLBL_ORDERED_PROVINCES);
        $this->dump('****************orderedProvinces***', $result['orderedProvinces']);

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score, player_no playerNo FROM player ";
        $result['players'] = $this->getCollectionFromDb($sql);
        $result['playerOrderWorkingWithSpectators'] = $this->getPlayerIdsInOrder($currentPlayerId);
        $result['turnOrderClockwise'] = true;

        //counters
        $this->ticketsCounter->fillResult($result);

        $result['hand'] = $this->cardManager->getPlayerHand($currentPlayerId);

        foreach ($result['players'] as $playerId => &$player) {
            $currentPlayerOrder = intval($player['playerNo']);
            $player['playerNo'] = $currentPlayerOrder;
            //$player['discard'] = $this->cardManager->getCardsOfTypeArgFromLocation(TABLE_CARD, $currentPlayerOrder, MATERIAL_LOCATION_DISCARD);
            //$player['hand'] = $this->cardManager->getCardsOfTypeArgFromLocation(TABLE_CARD, $currentPlayerOrder, MATERIAL_LOCATION_HAND);

            // $player['cardsCount'] = intval($this->actionCards->countCardInLocation("hand", $playerId));
        }

        // TODO: Gather all information about current game situation (visible by player $current_player_id).
        if ($isEnd) {
            $maxScore = $this->playerScore->getMax();
            $result['winners'] = array_keys(array_filter($result['players'], fn($player) => intval($player['score'] == $maxScore)));
            if (count($result['winners']) > 1) {
                $tieWinners =  array_filter($result['players'], fn($player) => in_array($player["id"], $result['winners']));
                $maxScore = max(array_map(fn($player) => intval($player['scoreAux']), $tieWinners));
                $result['winners'] = array_keys(array_filter($tieWinners, fn($player) => intval($player['scoreAux'] == $maxScore)));
            }
        } else {
            $result['lastTurn'] = $this->globals->get(Constants::LAST_TURN) > 0;
        }
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression() {
        $stateName = $this->getStateName();
        if ($stateName === 'EndScore' || $stateName === 'GameEnd' || $stateName === 'DebugGameEnd') {
            // game is over
            return 100;
        }
        /*$roundProgression = 100 * count($this->cardManager->getGridCards()) / 12;
        
        $round = intval($this->globals->get(GLB_ROUND));
        return (100 * $this->getMaxScore() / 2) + $roundProgression / ($round == 3 ? 3 : 2);*/
        return 0;
    }

    function getGameVersion(): int {
        return $this->bga->tableOptions->get(300);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////    
    function makeSavepoint($player_id = null) {
        $this->undoSavepoint();
    }

    function toggleResetTurn(bool $value) {
        $this->globals->set(Constants::CAN_RESET_TURN, $value);
    }
    /*
        In this space, you can put any utility methods useful for your game logic
    */

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */

    function upgradeTableDb($from_version) {
        $changes = [
            // [2307071828, "INSERT INTO DBPREFIX_global (`global_id`, `global_value`) VALUES (24, 0)"], 
        ];

        foreach ($changes as [$version, $sql]) {
            if ($from_version <= $version) {
                try {
                    $this->warn("upgradeTableDb apply 1: from_version=$from_version, change=[ $version, $sql ]");
                    $this->applyDbUpgradeToAllDB($sql);
                } catch (\Exception $e) {
                    // See https://studio.boardgamearena.com/bug?id=64
                    // BGA framework can produce invalid SQL with non-existant tables when using DBPREFIX_.
                    // The workaround is to retry the query on the base table only.
                    $this->error("upgradeTableDb apply 1 failed: from_version=$from_version, change=[ $version, $sql ]");
                    $sql = str_replace("DBPREFIX_", "", $sql);
                    $this->warn("upgradeTableDb apply 2: from_version=$from_version, change=[ $version, $sql ]");
                    $this->applyDbUpgradeToAllDB($sql);
                }
            }
        }
        $this->warn("upgradeTableDb complete: from_version=$from_version");
    }
}
