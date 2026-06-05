<?php

namespace Bga\Games\Quorum;

enum PowerType: string {
    case IMMEDIATE = 'I';
    case DORMANT = 'D';
    case PERMANENT = 'P';
    case NONE = 'N';
}
