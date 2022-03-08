<?php

namespace qpi\guard\region\flag\defaults;

use qpi\guard\region\flag\Flag;

class FurnaceFlag extends Flag {

    public function __construct() {
        parent::__construct(DefaultFlagIds::FURNACE, "Свободное использование печей", "Позволяет всем игрокам использовать печки внутри региона");
    }
}