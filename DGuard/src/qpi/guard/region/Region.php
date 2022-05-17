<?php

namespace qpi\guard\region;

use JsonSerializable;
use qpi\guard\region\flag\Flag;
use qpi\guard\utils\Area;

class Region implements JsonSerializable {

    private int $id;
    private string $world;
    private string $name;
    private string $owner;
    private array $members;
    private array $flags = [];
    private Area $area;

    public bool $removed = false;

    private function __construct(int $id) {
        $this->id = $id;
    }

    /**
     * Получение id региона
     * @return int id региона
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * Получение имени мира, регион в котором расположен
     * @return string Имя мира
     */
    public function getWorldName(): string {
        return $this->world;
    }

    /**
     * Плучение названия региона
     * @return string Название региона
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Установка нового названия для региона
     * @param string $name Новое название региона
     * @return void
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * Получение НикНейма игрока владельца региона
     * @return string НикНейм владельца
     */
    public function getOwner(): string {
        return $this->owner;
    }

    /**
     * Получение информации о территории региона
     * @return Area
     */
    public function getArea(): Area {
        return $this->area;
    }

    /**
     * Получение статуса активности флага в данном регионе
     * @param Flag $flag Флаг
     * @return bool Значение
     */
    public function getFlag(Flag $flag): bool {
        return $this->flags[$flag->getId()] ?? $flag->getDefaultValue();
    }

    /**
     * Установка нового значения для флага региона
     * @param Flag $flag Флаг
     * @param bool $value Новое значение
     * @return void
     */
    public function setFlag(Flag $flag, bool $value): void {
        $this->flags[$flag->getId()] = $value;
    }

    /**
     * Получение роли игрока по его НикНейму в данном регионе
     * @param string $playerName НикНейм игрока
     * @return int Роль игрока для данного региона. Список ролей можно увидеть в классе `qpi\dguard\Roles`
     */
    public function getRole(string $playerName): int {
        if($this->owner == $playerName) return Roles::OWNER;
        return $this->members[$playerName] ?? Roles::NOBODY;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'world' => $this->world,
            'name' => $this->name,
            'owner' => $this->owner,
            'members' => $this->members,
            'flags' => $this->flags,
            'area' => $this->area->jsonSerialize(),
        ];
    }

    public static function fromJson(string|array $json): Region {
        $data = is_array($json)? $json : json_decode($json, true);
        $region = new Region($data['id']);

        $region->world = $data['world'];
        $region->name = $data['name'];
        $region->owner = $data['owner'];
        $region->members = $data['members'];
        $region->flags = $data['flags'];
        $region->area = Area::fromJson($data['area']);

        return $region;
    }
}