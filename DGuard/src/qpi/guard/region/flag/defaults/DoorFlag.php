<?php

namespace qpi\guard\region\flag\defaults;

use qpi\guard\region\flag\Flag;

class DoorFlag extends Flag {

    public function __construct() {
        parent::__construct(DefaultFlagIds::DOOR, "Свободное открытие дверей", "Позволяет всем игрокам свободно открывать двери, калитки и люки внутри региона");
    }
}