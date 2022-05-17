<?php

namespace qpi\guard\forms;

use pocketmine\player\Player;

abstract class MenuOption {

    private string $text;
    private string $icon;

    public function __construct(string $text, string $icon) {
        $this->text = $text;
        $this->icon = $icon;
    }

    public function getText(): string {
        return $this->text;
    }

    public function getIcon(): string {
        return $this->icon;
    }

    public function canClick(Player $player): bool {
        return true;
    }

    public abstract function click(Player $player): void;
}