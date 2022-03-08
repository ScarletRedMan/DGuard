<?php

namespace qpi\guard\region\flag\defaults;

use qpi\guard\region\flag\Flag;
use qpi\guard\region\Region;

class PvpFlag extends Flag {

    public function __construct() {
        parent::__construct(DefaultFlagIds::PVP, "PvP режим", "Разрешает атаковать других игроков в регионе.");
    }

    public function checkForPlayer(string $playerName, bool $flagValue, Region $region): bool {
        return $flagValue;
    }
}