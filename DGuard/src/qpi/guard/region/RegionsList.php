<?php

namespace qpi\guard\region;

use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use qpi\guard\utils\Area;
use qpi\guard\utils\Point;

/**
 * Это специальный скрытый слой, доступ к которому есть только у менеджера регионов.
 * Сделан специально для того чтобы какой-нибудь фаршмачник не угандошил случайно регионы.
 */
class RegionsList {

    private array $data = [];

    /** @var Region[] */
    private array $cache = [];

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

        foreach ($this->cache as $key => $value) {
            if ($value->getId() === $region->getId()) unset($this->cache[$key]);
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

    public function findAndCacheRegion(Player $player, Vector3 $pos): ?Region {
        $point = Point::fromVector($pos);

        if (isset($this->cache[$player->getId()])) {
            $region = $this->cache[$player->getId()];
            if ($region->getArea()->isInside($point)) return $region;

            unset($this->cache[$player->getId()]);
        }

        $region = $this->findRegion($player->getWorld(), $pos);
        if ($region !== null) $this->cache[$player->getId()] = $region;

        return $region;
    }

    public function removeCache(Player $player): void {
        unset($this->cache[$player->getId()]);
    }

    public function isPrivateArea(string|World $world, Area $area): bool {
        $worldName = $world instanceof World? strtolower($world->getFolderName()) : $world;

        foreach ($this->data[$worldName] as $region) {
            if ($area->isCollide($region->getArea())) return true;
        }
        return false;
    }

    public function getRegions(Player $player): array {
        $list = [];

        foreach ($this->data as $regions) {
            foreach ($regions as $region) {
                if ($region->getOwner() !== $player->getName()) continue;

                $list[] = $region;
            }
        }

        return $list;
    }
}