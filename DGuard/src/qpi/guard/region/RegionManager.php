<?php

namespace qpi\guard\region;

use http\Exception\InvalidArgumentException;
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

    public const FIRST_POINT = 0;
    public const SECOND_POINT = 1;

    private PluginBase $plugin;
    private string $path;
    private int $freeId;
    private RegionsList $regions;
    private array $points = [];

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
        return new RegionManagementListener($this, $this->regions);
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
     * Проверка на коллизию привата в территории
     * @param string|World $world Мир или его название
     * @param Area $area Территория
     * @return bool Присутствует ли приват данной территории
     */
    public function isPrivateArea(string|World $world, Area $area): bool {
        return $this->regions->isPrivateArea($world, $area);
    }

    /**
     * Возвращает список регионов, которыми владеет игрок
     * @param Player $player Игрок
     * @return Region[] Список регионов
     */
    public function getRegions(Player $player): array {
        return $this->regions->getRegions($player);
    }

    /**
     * Ставит маркер-точку для отметки границ региона
     * @param Player $player Игрок
     * @param Vector3 $pos Точка
     * @param int $number Номер точки. Использовать константы FIRST_POINT и SECOND_POINT
     * @return void
     */
    public function placePoint(Player $player, Vector3 $pos, int $number): void {
        if ($number !== self::FIRST_POINT || $number !== self::SECOND_POINT) throw new InvalidArgumentException();

        $this->points[$player->getId()][$number] = Point::fromVector($pos);
    }

    /**
     * Проверка наличие отметки точек территории игроком
     * @param Player $player Игрок
     * @return bool Результат проверки
     */
    public function isSelectedArea(Player $player): bool {
        return isset($this->points[$player->getId()]) && count($this->points) === 2;
    }

    /**
     * Получение территории по выделенным точкам
     * @param Player $player Игрок
     * @return Area Выделенная территория
     * @throws RegionException
     */
    public function getSelectedArea(Player $player): Area {
        if (!$this->isSelectedArea($player)) throw new RegionException("Не выстановлены точки границ территории");

        return new Area($this->points[$player->getId()][self::FIRST_POINT], $this->points[$player->getId()][self::SECOND_POINT]);
    }

    public function removePoints(Player $player): void {
        unset($this->points[$player->getId()]);
    }
}