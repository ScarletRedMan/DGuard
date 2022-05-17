<?php

namespace qpi\guard\region\flag;

use pocketmine\player\Player;
use qpi\guard\region\Region;
use qpi\guard\region\Roles;

class Flag {

    private string $id;
    private string $name;
    private string $description;

    public function __construct(string $id, string $name, string $description) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * Получение id флага
     * @return string id флага
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * Получение названия флага
     * @return string Название флага
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Получение описания флага
     * @return string Описание флага
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * Получение стандартного значения флага
     * @return bool Стандартное значение флага
     */
    public function getDefaultValue(): bool {
        return false;
    }

    /**
     * Проверка на доступ к региону с помощью текущего флага
     * @param Player $player Игрок
     * @param Region $region Регион
     * @return bool Есть ли доступ на воздействие на регион
     */
    public final function check(Player $player, Region $region): bool {
        return $this->checkForPlayer($player, $region->getFlag($this), $region);
    }

    /**
     * Метод, с помощью которого определяется результат доступа на воздействие на регион
     * @param Player $player Игрок
     * @param bool $flagValue Значение флага
     * @param Region $region Регион
     * @return bool Есть ли доступ на воздействие на регион
     */
    protected function checkForPlayer(Player $player, bool $flagValue, Region $region): bool {
        return $flagValue || $region->getRole($player->getName()) > Roles::NOBODY;
    }
}