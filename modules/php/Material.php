<?php

namespace Bga\Games\Quorum;

use Bga\Games\Quorum\Objects\QuorumCardInfo;
use Constants;

class Material {

  /**
   * 
   * @return array<QuorumCardInfo> 
   */
  public static function getCards(): array {

    return [
      1 => [ //base cards
        //yellow cards
        1 => new QuorumCardInfo(0, Constants::PROVINCE_GALLIA, 2, Constants::CARD_TYPE_INTRIGUE),
        2 => new QuorumCardInfo(1, Constants::PROVINCE_GALLIA, 3, Constants::CARD_TYPE_INTRIGUE),
        3 => new QuorumCardInfo(1, Constants::PROVINCE_GALLIA, 2, Constants::CARD_TYPE_INTRIGUE),
        4 => new QuorumCardInfo(2, Constants::PROVINCE_GALLIA, 1, Constants::CARD_TYPE_INTRIGUE),
        5 => new QuorumCardInfo(0, Constants::PROVINCE_MACEDONIA, 2, Constants::CARD_TYPE_INTRIGUE),
        6 => new QuorumCardInfo(1, Constants::PROVINCE_MACEDONIA, 1, Constants::CARD_TYPE_INTRIGUE),
        7 => new QuorumCardInfo(2, Constants::PROVINCE_MACEDONIA, 1, Constants::CARD_TYPE_INTRIGUE),
        8 => new QuorumCardInfo(2, Constants::PROVINCE_MACEDONIA, 3, Constants::CARD_TYPE_INTRIGUE),
        9 => new QuorumCardInfo(0, Constants::PROVINCE_HISPANIA, 3, Constants::CARD_TYPE_INTRIGUE),
        10 => new QuorumCardInfo(0, Constants::PROVINCE_HISPANIA, 2, Constants::CARD_TYPE_INTRIGUE),
        11 => new QuorumCardInfo(1, Constants::PROVINCE_HISPANIA, 1, Constants::CARD_TYPE_INTRIGUE),
        12 => new QuorumCardInfo(2, Constants::PROVINCE_HISPANIA, 3, Constants::CARD_TYPE_INTRIGUE),
        13 => new QuorumCardInfo(0, Constants::PROVINCE_ASIA, 3, Constants::CARD_TYPE_INTRIGUE),
        14 => new QuorumCardInfo(1, Constants::PROVINCE_ASIA, 1, Constants::CARD_TYPE_INTRIGUE),
        15 => new QuorumCardInfo(1, Constants::PROVINCE_ASIA, 2, Constants::CARD_TYPE_INTRIGUE),
        16 => new QuorumCardInfo(0, Constants::PROVINCE_AFRICA, 2, Constants::CARD_TYPE_INTRIGUE),
        17 => new QuorumCardInfo(2, Constants::PROVINCE_AFRICA, 1, Constants::CARD_TYPE_INTRIGUE),
        18 => new QuorumCardInfo(2, Constants::PROVINCE_AFRICA, 3, Constants::CARD_TYPE_INTRIGUE),

        //brown cards
        19 => new QuorumCardInfo(3, Constants::PROVINCE_GERMANIA, 3, Constants::CARD_TYPE_ARCHITECTURE),
        20 => new QuorumCardInfo(2, Constants::PROVINCE_AFRICA, 2, Constants::CARD_TYPE_ARCHITECTURE),
        21 => new QuorumCardInfo(3, Constants::PROVINCE_GALLIA, 1, Constants::CARD_TYPE_ARCHITECTURE),
        22 => new QuorumCardInfo(2, Constants::PROVINCE_AFRICA, 1, Constants::CARD_TYPE_ARCHITECTURE),
        23 => new QuorumCardInfo(1, Constants::PROVINCE_GERMANIA, 1, Constants::CARD_TYPE_ARCHITECTURE),
        24 => new QuorumCardInfo(2, Constants::PROVINCE_HISPANIA, 2, Constants::CARD_TYPE_ARCHITECTURE),
        25 => new QuorumCardInfo(1, Constants::PROVINCE_GALLIA, 1, Constants::CARD_TYPE_ARCHITECTURE),
        26 => new QuorumCardInfo(3, Constants::PROVINCE_HISPANIA, 1, Constants::CARD_TYPE_ARCHITECTURE),
        27 => new QuorumCardInfo(2, Constants::PROVINCE_GALLIA, 2, Constants::CARD_TYPE_ARCHITECTURE),
        28 => new QuorumCardInfo(2, Constants::PROVINCE_GERMANIA, 2, Constants::CARD_TYPE_ARCHITECTURE),
        29 => new QuorumCardInfo(3, Constants::PROVINCE_ASIA, 1, Constants::CARD_TYPE_ARCHITECTURE),
        30 => new QuorumCardInfo(1, Constants::PROVINCE_HISPANIA, 3, Constants::CARD_TYPE_ARCHITECTURE),
        31 => new QuorumCardInfo(3, Constants::PROVINCE_GERMANIA, 3, Constants::CARD_TYPE_ARCHITECTURE),
        32 => new QuorumCardInfo(1, Constants::PROVINCE_ASIA, 3, Constants::CARD_TYPE_ARCHITECTURE),
        33 => new QuorumCardInfo(2, Constants::PROVINCE_HISPANIA, 2, Constants::CARD_TYPE_ARCHITECTURE),
        34 => new QuorumCardInfo(3, Constants::PROVINCE_AFRICA, 3, Constants::CARD_TYPE_ARCHITECTURE),
        35 => new QuorumCardInfo(1, Constants::PROVINCE_GALLIA, 3, Constants::CARD_TYPE_ARCHITECTURE),
        36 => new QuorumCardInfo(1, Constants::PROVINCE_ASIA, 2, Constants::CARD_TYPE_ARCHITECTURE),

        //blue cards
        37 => new QuorumCardInfo(1, Constants::PROVINCE_GALLIA, 2, Constants::CARD_TYPE_TRADE, [Constants::TRADE_FISH, Constants::TRADE_FISH, Constants::TRADE_WHEAT]),
        38 => new QuorumCardInfo(2, Constants::PROVINCE_AFRICA, 1, Constants::CARD_TYPE_TRADE, [Constants::TRADE_WINE, Constants::TRADE_WINE, Constants::TRADE_TOOL]),
        39 => new QuorumCardInfo(3, Constants::PROVINCE_AFRICA, 3, Constants::CARD_TYPE_TRADE, [Constants::TRADE_WOOD, Constants::TRADE_WOOD, Constants::TRADE_SHEEP]),
        40 => new QuorumCardInfo(1, Constants::PROVINCE_ASIA, 1, Constants::CARD_TYPE_TRADE, [Constants::TRADE_SHEEP, Constants::TRADE_SHEEP, Constants::TRADE_WINE]),
        41 => new QuorumCardInfo(2, Constants::PROVINCE_MACEDONIA, 1, Constants::CARD_TYPE_TRADE, [Constants::TRADE_TOOL, Constants::TRADE_TOOL, Constants::TRADE_FISH]),
        42 => new QuorumCardInfo(3, Constants::PROVINCE_GALLIA, 1, Constants::CARD_TYPE_TRADE, [Constants::TRADE_WHEAT, Constants::TRADE_WHEAT, Constants::TRADE_WOOD]),
        43 => new QuorumCardInfo(1, Constants::PROVINCE_ASIA, 3, Constants::CARD_TYPE_TRADE, [Constants::TRADE_SHEEP, Constants::TRADE_WHEAT, Constants::TRADE_WINE]),
        44 => new QuorumCardInfo(2, Constants::PROVINCE_AFRICA, 2, Constants::CARD_TYPE_TRADE, [Constants::TRADE_TOOL, Constants::TRADE_WHEAT, Constants::TRADE_WOOD]),
        45 => new QuorumCardInfo(3, Constants::PROVINCE_GERMANIA, 2, Constants::CARD_TYPE_TRADE, [Constants::TRADE_WINE, Constants::TRADE_WOOD, Constants::TRADE_FISH]),
        46 => new QuorumCardInfo(1, Constants::PROVINCE_MACEDONIA, 2, Constants::CARD_TYPE_TRADE, [Constants::TRADE_FISH, Constants::TRADE_SHEEP, Constants::TRADE_WOOD]),
        47 => new QuorumCardInfo(2, Constants::PROVINCE_GALLIA, 3, Constants::CARD_TYPE_TRADE, [Constants::TRADE_WINE, Constants::TRADE_WHEAT, Constants::TRADE_TOOL]),
        48 => new QuorumCardInfo(2, Constants::PROVINCE_GERMANIA, 3, Constants::CARD_TYPE_TRADE, [Constants::TRADE_TOOL, Constants::TRADE_SHEEP, Constants::TRADE_FISH]),
        49 => new QuorumCardInfo(3, Constants::PROVINCE_ASIA, 2, Constants::CARD_TYPE_TRADE, [Constants::TRADE_SHEEP, Constants::TRADE_TOOL]),
        50 => new QuorumCardInfo(3, Constants::PROVINCE_MACEDONIA, 3, Constants::CARD_TYPE_TRADE, [Constants::TRADE_WINE, Constants::TRADE_WHEAT]),
        51 => new QuorumCardInfo(1, Constants::PROVINCE_GERMANIA, 1, Constants::CARD_TYPE_TRADE, [Constants::TRADE_FISH, Constants::TRADE_WOOD]),
        52 => new QuorumCardInfo(2, Constants::PROVINCE_GERMANIA, 1, Constants::CARD_TYPE_TRADE, [Constants::TRADE_WHEAT, Constants::TRADE_WOOD]),
        53 => new QuorumCardInfo(3, Constants::PROVINCE_MACEDONIA, 2, Constants::CARD_TYPE_TRADE, [Constants::TRADE_SHEEP, Constants::TRADE_FISH]),
        54 => new QuorumCardInfo(1, Constants::PROVINCE_GALLIA, 3, Constants::CARD_TYPE_TRADE, [Constants::TRADE_TOOL, Constants::TRADE_WINE]),

        //green cards
        55 => new QuorumCardInfo(1, Constants::PROVINCE_ASIA, 2, Constants::CARD_TYPE_MILITARY),
        56 => new QuorumCardInfo(1, Constants::PROVINCE_ASIA, 3, Constants::CARD_TYPE_MILITARY),
        57 => new QuorumCardInfo(3, Constants::PROVINCE_ASIA, 1, Constants::CARD_TYPE_MILITARY),
        58 => new QuorumCardInfo(1, Constants::PROVINCE_HISPANIA, 1, Constants::CARD_TYPE_MILITARY),
        59 => new QuorumCardInfo(2, Constants::PROVINCE_HISPANIA, 1, Constants::CARD_TYPE_MILITARY),
        60 => new QuorumCardInfo(2, Constants::PROVINCE_HISPANIA, 3, Constants::CARD_TYPE_MILITARY),
        61 => new QuorumCardInfo(3, Constants::PROVINCE_HISPANIA, 2, Constants::CARD_TYPE_MILITARY),
        62 => new QuorumCardInfo(2, Constants::PROVINCE_AFRICA, 2, Constants::CARD_TYPE_MILITARY),
        63 => new QuorumCardInfo(3, Constants::PROVINCE_AFRICA, 1, Constants::CARD_TYPE_MILITARY),
        64 => new QuorumCardInfo(2, Constants::PROVINCE_AFRICA, 3, Constants::CARD_TYPE_MILITARY),
        65 => new QuorumCardInfo(1, Constants::PROVINCE_MACEDONIA, 3, Constants::CARD_TYPE_MILITARY),
        66 => new QuorumCardInfo(2, Constants::PROVINCE_MACEDONIA, 1, Constants::CARD_TYPE_MILITARY),
        67 => new QuorumCardInfo(3, Constants::PROVINCE_MACEDONIA, 2, Constants::CARD_TYPE_MILITARY),
        68 => new QuorumCardInfo(3, Constants::PROVINCE_MACEDONIA, 3, Constants::CARD_TYPE_MILITARY),
        69 => new QuorumCardInfo(1, Constants::PROVINCE_GERMANIA, 1, Constants::CARD_TYPE_MILITARY),
        70 => new QuorumCardInfo(1, Constants::PROVINCE_GERMANIA, 2, Constants::CARD_TYPE_MILITARY),
        71 => new QuorumCardInfo(2, Constants::PROVINCE_GERMANIA, 3, Constants::CARD_TYPE_MILITARY),
        72 => new QuorumCardInfo(3, Constants::PROVINCE_GERMANIA, 2, Constants::CARD_TYPE_MILITARY),
      ],
      2 => [
        //gods
        73 => new QuorumCardInfo(0, Constants::PROVINCE_NEUTRAL, 1, Constants::CARD_TYPE_GOD, [], 1, 1,),
        74 => new QuorumCardInfo(0, Constants::PROVINCE_NEUTRAL, 1, Constants::CARD_TYPE_GOD, [], -1, -1,),
        75 => new QuorumCardInfo(0, Constants::PROVINCE_NEUTRAL, 1, Constants::CARD_TYPE_GOD, [], 1, 1,),
        76 => new QuorumCardInfo(0, Constants::PROVINCE_NEUTRAL, 1, Constants::CARD_TYPE_GOD, [], -1, 1,),
        77 => new QuorumCardInfo(0, Constants::PROVINCE_NEUTRAL, 1, Constants::CARD_TYPE_GOD, [], 1, -1,),
        78 => new QuorumCardInfo(0, Constants::PROVINCE_NEUTRAL, 1, Constants::CARD_TYPE_GOD, [], 1, -1,),
        79 => new QuorumCardInfo(0, Constants::PROVINCE_NEUTRAL, 1, Constants::CARD_TYPE_GOD, [], -1, -1,),
      ]
    ];
  }

  static public function getSubArchitectureType(int $cardType): int {
    return match ($cardType) {
      19 => Constants::CARD_TYPE_ARCH_BATH,
      20 => Constants::CARD_TYPE_ARCH_BATH,
      21 => Constants::CARD_TYPE_ARCH_BATH,
      22 => Constants::CARD_TYPE_ARCH_TEMPLE,
      23 => Constants::CARD_TYPE_ARCH_TEMPLE,
      24 => Constants::CARD_TYPE_ARCH_TEMPLE,
      25 => Constants::CARD_TYPE_ARCH_COLISEUM,
      26 => Constants::CARD_TYPE_ARCH_COLISEUM,
      27 => Constants::CARD_TYPE_ARCH_COLISEUM,
      28 => Constants::CARD_TYPE_ARCH_THEATER,
      29 => Constants::CARD_TYPE_ARCH_THEATER,
      30 => Constants::CARD_TYPE_ARCH_THEATER,
      31 => Constants::CARD_TYPE_ARCH_ARCH,
      32 => Constants::CARD_TYPE_ARCH_ARCH,
      33 => Constants::CARD_TYPE_ARCH_ARCH,
      34 => Constants::CARD_TYPE_ARCH_AQUEDUCT,
      35 => Constants::CARD_TYPE_ARCH_AQUEDUCT,
      36 => Constants::CARD_TYPE_ARCH_AQUEDUCT,
      default => 0
    };
  }
}
