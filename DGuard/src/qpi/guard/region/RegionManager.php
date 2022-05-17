<?php

namespace qpi\guard\region;

use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;
use qpi\guard\event\listener\RegionManagementListener;
use qpi\guard\utils\Area;
use qpi\guard\utils\Point;

class RegionManager {
    use SingletonTrait;

    private const REGIONS_DIR = "regions/";
    private const SAVED_FREE_ID_PATH = ".free_id";

    private PluginBase $plugin;
    private string $path;
    private int $freeId;
    private RegionsList $regions;

    public function init(PluginBase $plugin): RegionManager {
        $this->plugin = $plugin;
        $this->path = $plugin->getDataFolder();
        $this->regions = new RegionsList($this);

        $this->initConfigs();
        $this->loadRegions();

        return $this;
    }

    private function initConfigs(): void {
        @mkdir($this->path . self::REGIONS_DIR);

        $savedFreeIdPath = $this->path . self::SAVED_FREE_ID_PATH;
        if(!file_exists($savedFreeIdPath)){
            $this->freeId = 0;
            $this->saveFreeId();
        } else {
            $this->freeId = (int) file_get_contents($savedFreeIdPath);
        }
    }

    public function prepareRegionManagementListener(): RegionManagementListener {
        return new RegionManagementListener($this->regions);
    }

    private function saveFreeId(): void {
        file_put_contents($this->path . self::SAVED_FREE_ID_PATH, $this->freeId);
    }

    private function useFreeId(): int {
        $id = $this->freeId;
        $this->saveFreeId();
        return $id;
    }

    private function loadRegions(): void {
        $files = scandir($this->path . self::REGIONS_DIR);
        foreach ($files as $file) {
            if (!is_file($file)) continue;

            $region = Region::fromJson(file_get_contents($file));
            $this->regions->add($region);
        }
    }

    public function saveRegion(Region $region): void {
        file_put_contents(
            $this->path . self::REGIONS_DIR . $region->getId() . ".json",
            json_encode($region)
        );
    }

    public function removeRegion(Region $region): void {
        $region->removed = true;
        $this->regions->remove($region);
        unlink($this->path . self::REGIONS_DIR . $region->getId() . ".json");
    }

    public function createNewRegion(String $playerName, string $world, Area $area, string $name): Region {
        $regionData = [
            'id' => $this->useFreeId(),
            'world' => $world,
            'name' => $name,
            'owner' => $playerName,
            'members' => [],
            'flags' => [],
            'area' => $area->jsonSerialize(),
        ];

        $region = Region::fromJson($regionData);
        $this->regions->add($region);
        $this->saveRegion($region);

        return $region;
    }

    public function getRegionById(int $regionId): Region {
        return ($this->regions)($regionId);
    }

    public function findRegion(World|string $world, Vector3|Point $pos): ?Region {
        return $this->regions->findRegion($world, $pos);
    }

    public function findByPlayer(Player $player, ?Vector3 $pos = null): ?Region {
        return $this->regions->findAndCacheRegion($player, $pos === null? $player->getPosition() : $pos);
    }

    public function getRegions(Player $player): array {
        return $this->regions->getRegions($player);
    }
}