<?php

namespace qpi\guard\forms;

use form\SimpleForm;
use http\Exception\InvalidArgumentException;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class Forms {
    use SingletonTrait;

    private array $options = [];
    private array $keys = [];

    private function __construct() {
        $this->registerDefaultOptions();
    }

    public function sendMainForm(Player $player) {
        $form = new SimpleForm();
        $form->setTitle("Меню");

        foreach ($this->options as $option) {
            if (!$option->canClick($player)) continue;

            $form->addButton($option->getText(),
                SimpleForm::IMAGE_TYPE_PATH,
                $option->getIcon(),
                function (Player $player) use ($option) {
                    $option->click($player);
                });
        }

        $form->sendToPlayer($player);
    }

    public function register(MenuOption $obj, ?string $key = null) {
        $this->options[] = $obj;
        $this->keys[$key] = array_key_last($this->options);
    }

    public function get(string $key): MenuOption {
        if (!isset($this->keys[$key])) throw new InvalidArgumentException("Меню с ключем '{$key}' не существует");

        return $this->options[$this->keys[$key]];
    }

    private function registerDefaultOptions(): void {

    }
}