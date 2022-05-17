<?php

namespace qpi\guard\forms;

use form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class Forms {
    use SingletonTrait;

    private array $options = [];

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

    public function register(MenuOption $obj) {
        $this->options[] = $obj;
    }

    private function registerDefaultOptions(): void {

    }
}