<?php

namespace qpi\guard;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use qpi\guard\region\RegionManager;

class DGuard extends PluginBase implements Listener{

    private RegionManager $regionManager;

    protected function onLoad(): void {
        $this->regionManager = RegionManager::getInstance()->init($this);
    }

    protected function onEnable(): void {
        $pluginManager = $this->getServer()->getPluginManager();
        $pluginManager->registerEvents($this->regionManager->prepareRegionManagementListener(), $this);
    }

    protected function onDisable(): void {

    }
}
