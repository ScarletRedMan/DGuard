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

    /**
     * Сохранение региона. Использовать это стоит после каждого изменения настроек региона
     * @param Region $region Регион, который требуется сохранить
     * @return void
     */
    public function saveRegion(Region $region): void {
        file_put_contents(
            $this->path . self::REGIONS_DIR . $region->getId() . ".json",
            json_encode($region)
        );
    }

    /**
     * Удаление региона
     * @param Region $region Регион, который требуется удалить
     * @return void
     */
    public function removeRegion(Region $region): void {
        $region->removed = true;
        $this->regions->remove($region);
        unlink($this->path . self::REGIONS_DIR . $region->getId() . ".json");
    }

    /**
     * Создание нового региона
     * @param String $playerName Имя игрока, владельца региона
     * @param string $world Мир, в котором расположен регион
     * @param Area $area Территоррия
     * @param string $name Имя региона
     * @return Region Созданный регион
     */
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

    /**
     * Получение региона по его id
     * @param int $regionId id региона
     * @return Region Регион
     * @throws RegionException
     */
    public function getRegionById(int $regionId): Region {
        return ($this->regions)($regionId);
    }

    /**
     * Получение региона в точке.
     * @param World|string $world Мир
     * @param Vector3|Point $pos Точка, в которой будет искаться регион
     * @return Region|null Регион или null
     */
    public function findRegion(World|string $world, Vector3|Point $pos): ?Region {
        return $this->regions->findRegion($world, $pos);
    }

    /**
     * Метод для получения региона в точке с кешированием.
     * @param Player $player Игрок
     * @param Vector3|null $pos Точка, из которой нужно получить регион. Если не указывать, то точкой будет игрок
     * @return Region|null Регион или null
     */
    public function findByPlayer(Player $player, ?Vector3 $pos = null): ?Region {
        return $this->regions->findAndCacheRegion($player, $pos === null? $player->getPosition() : $pos);
    }

    /**
     * Возвращает список регионов, которыми владеет игрок
     * @param Player $player Игрок
     * @return Region[] Список регионов
     */
    public function getRegions(Player $player): array {
        return $this->regions->getRegions($player);
    }
}