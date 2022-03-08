<?php

namespace qpi\guard\region\flag\defaults;

use qpi\guard\region\flag\Flag;

class BuildFlag extends Flag {

    public function __construct() {
        parent::__construct(DefaultFlagIds::BUILD, "Свободное строительство", "Позволяет всем игрокам строить внутри региона");
    }
}