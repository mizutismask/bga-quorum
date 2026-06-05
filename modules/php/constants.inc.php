<?php

class Constants {
    /*
    * Custom framework constants
    */
    const MATERIAL_TYPE_CARD = "CARD";
    const MATERIAL_TYPE_TOKEN = "TOKEN";
    const MATERIAL_TYPE_TILE = "TILE";
    const MATERIAL_TYPE_ACTION_CARD = "ACTION_CARD";
    const MATERIAL_TYPE_FIRST_PLAYER_TOKEN = "FIRST_PLAYER_TOKEN";

    const MATERIAL_LOCATION_HAND = "HAND";
    const MATERIAL_LOCATION_DECK = "DECK";
    const MATERIAL_LOCATION_STOCK = "STOCK";
    const MATERIAL_LOCATION_DISCARD = "DISCARD";
    const MATERIAL_LOCATION_RIVER = "RIVER";

    /* 
    * Game constants 
    */
    const LAST_TURN = 'LAST_TURN';
    const CAN_RESET_TURN = "CAN_RESET_TURN";

    /**
     * Options
     */
    const EXPANSION = 0; // 0 => base game

    /*
    * State constants
    */
    const STATE_ID_BGA_GAME_SETUP = 1;

    const STATE_ID_NEXT_PLAYER = 2;
    const STATE_ID_NEXT_ROUND = 3;
    const STATE_ID_END_OF_ROUND = 4;
    const STATE_ID_PLAYER_TURN = 30;
    const STATE_ID_DEBUG_GAME_END = 97;
    const STATE_ID_END_SCORE = 100;
}
