<?php

namespace qpi\guard\event\listener;

use pocketmine\event\Listener;
use pocketmine\event\world\WorldInitEvent;
use qpi\guard\region\RegionsList;

class RegionManagementListener implements Listener {

    public function __construct(private RegionsList $regions) {

    }

    public function onWorldInit(WorldInitEvent $event){
        $this->regions->initWorld($event->getWorld());
    }

}