<?php

namespace qpi\guard\event\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\world\WorldInitEvent;
use pocketmine\event\world\WorldLoadEvent;
use qpi\guard\region\RegionsList;

class RegionManagementListener implements Listener {

    public function __construct(private RegionsList $regions) {

    }

    public function onWorldInit(WorldInitEvent $event): void {
        $this->regions->initWorld($event->getWorld());
    }

    public function onWorldCreated(WorldLoadEvent $event): void {
        $this->regions->initWorld($event->getWorld());
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $this->regions->removeCache($event->getPlayer());
    }
}