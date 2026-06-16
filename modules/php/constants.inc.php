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
    const MATERIAL_LOCATION_BOARD = "BOARD";

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
    const STATE_ID_GOD_EFFECT = 31;
    const STATE_ID_DEBUG_GAME_END = 97;
    const STATE_ID_END_SCORE = 100;

    const PROVINCE_NEUTRAL = 0;
    const PROVINCE_ASIA = 1;
    const PROVINCE_GALLIA = 2;
    const PROVINCE_GERMANIA = 3;
    const PROVINCE_AFRICA = 4;
    const PROVINCE_MACEDONIA = 5;
    const PROVINCE_HISPANIA = 6;

    const ALL_PROVINCES = [
        self::PROVINCE_GERMANIA,
        self::PROVINCE_MACEDONIA,
        self::PROVINCE_GALLIA,
        self::PROVINCE_ASIA,
        self::PROVINCE_AFRICA,
        self::PROVINCE_HISPANIA,
    ];

    const GLBL_ORDERED_PROVINCES = 'ORDERED_PROVINCES';
    const GLBL_TOOK_CARD = 'TOOK_CARD';
    const GLBL_DID_RESET_RIVER = 'DID_RESET_RIVER';
    const GLBL_CURRENT_GOD = 'CURRENT_GOD';

    const CARD_TYPE_MILITARY = 11;
    const CARD_TYPE_INTRIGUE = 12;
    const CARD_TYPE_ARCHITECTURE = 13;
    const CARD_TYPE_TRADE = 14;
    const CARD_TYPE_GOD = 15;

    const CARD_TYPE_ARCH_BATH = 20;
    const CARD_TYPE_ARCH_TEMPLE = 21;
    const CARD_TYPE_ARCH_THEATER = 22;
    const CARD_TYPE_ARCH_AQUEDUCT = 23;
    const CARD_TYPE_ARCH_COLISEUM = 24;
    const CARD_TYPE_ARCH_ARCH = 25;

    const TRADE_FISH = 1;
    const TRADE_WOOD = 2;
    const TRADE_SHEEP = 3;
    const TRADE_WINE = 4;
    const TRADE_WHEAT = 5;
    const TRADE_TOOL = 6;
}
