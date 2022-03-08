<?php

namespace qpi\guard\region\flag\defaults;

use qpi\guard\region\flag\Flag;

class ChestFlag extends Flag {

    public function __construct() {
        parent::__construct(DefaultFlagIds::CHEST, "Свободное использование сундуков", "Позволяет всем игрокам открывать сундуки и бочки внутри региона");
    }
}