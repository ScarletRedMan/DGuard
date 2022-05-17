<?php

namespace qpi\guard\forms;

use form\SimpleForm;
use pocketmine\player\Player;

abstract class MenuOption {

    private string $text;
    private string $icon;

    public function __construct(string $text, string $icon) {
        $this->text = $text;
        $this->icon = $icon;
    }

    /**
     * Получает текст, который будет отобраться на кнопке в форме меню плагина
     * @return string Название раздела
     */
    public function getText(): string {
        return $this->text;
    }

    /**
     * Возвращает иконку кнопки для формы в меню
     * @return string Иконка кнопки
     */
    public function getIcon(): string {
        return $this->icon;
    }

    /**
     * Проверка на то, что может ли игрок открыть дануую форму.
     * @param Player $player Школьник
     * @return bool Будет ли показан пункт в меню плагина
     */
    public function canClick(Player $player): bool {
        return true;
    }

    /**
     * Действие при нажатии на кнопку открытия меню
     * @param Player $player Игрок
     * @param SimpleForm|null $prev Предыдущая форма. Используется для возврата назад
     * @return void
     */
    public abstract function click(Player $player, ?SimpleForm $prev = null): void;
}