<?php

namespace qpi\guard;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use qpi\guard\region\flag\DefaultFlagListener;
use qpi\guard\region\flag\FlagManager;
use qpi\guard\region\RegionManager;

class DGuard extends PluginBase implements Listener{

    private FlagManager $flagManager;
    private RegionManager $regionManager;

    protected function onLoad(): void {
        $this->flagManager = FlagManager::getInstance()->init($this);
        $this->regionManager = RegionManager::getInstance()->init($this);
    }

    protected function onEnable(): void {
        $pluginManager = $this->getServer()->getPluginManager();
        $pluginManager->registerEvents($this->regionManager->prepareRegionManagementListener(), $this);
        $pluginManager->registerEvents(new DefaultFlagListener($this->flagManager), $this);
    }

    protected function onDisable(): void {

    }
}
