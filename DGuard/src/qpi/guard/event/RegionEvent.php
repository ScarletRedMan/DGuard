<?php

namespace qpi\guard\event;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
use qpi\guard\region\Region;

abstract class RegionEvent extends PlayerEvent {

    private ?Region $region;

    public function __construct(Player $player, ?Region $region) {
        $this->player = $player;
        $this->region = $region;
    }

    public function getRegion(): ?Region {
        return $this->region;
    }
}