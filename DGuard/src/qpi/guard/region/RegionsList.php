<?php

namespace qpi\guard\region;

use pocketmine\math\Vector3;
use pocketmine\world\World;
use qpi\guard\utils\Point;

class RegionsList {

    private array $data = [];

    public function __construct(private RegionManager $regionManager) {

    }

    public function getRegionManager(): RegionManager {
        return $this->regionManager;
    }

    public function initWorld(World $world){
        $worldName = strtolower($world->getFolderName());
        if(!isset($this->data[$worldName])) $this->data[$worldName] = [];
    }

    public function add(Region $region): void {
        $worldName = strtolower($region->getWorldName());
        if(!isset($this->data[$worldName])) $this->data[$worldName] = [];
        $this->data[$worldName][$region->getId()] = $region;
    }

    public function remove(Region $region): void {
        foreach ($this->data as $worldName => $regions){
            unset($this->data[$worldName][$region->getId()]);
        }
    }

    public function __invoke(int $regionId): Region {
        foreach($this->data as $regions){
            if(isset($regions[$regionId])) return $regions[$regionId];
        }
        throw new RegionException("Региона #{$regionId} не существует!");
    }

    public function findRegion(World|string $world, Vector3|Point $pos): ?Region {
        $worldName = $world instanceof World? strtolower($world->getFolderName()) : $world;
        $point = $pos instanceof Point? $pos : Point::fromVector($pos);

        foreach ($this->data[$worldName] as $region){
            /** @var $region Region */
            if($region->getArea()->isInside($point)) return $region;
        }
        return null;
    }
}