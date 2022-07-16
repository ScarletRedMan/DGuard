<?php

namespace qpi\guard\forms;

use form\SimpleForm;
use http\Exception\InvalidArgumentException;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use qpi\guard\forms\defaults\CreateRegionOption;
use qpi\guard\forms\defaults\ViewInfoAboutRegionOption;
use qpi\guard\region\Region;
use qpi\guard\region\RegionManager;

class RegionForms {
    use SingletonTrait;

    public const TYPE_MENU = 0;
    public const TYPE_CONTROL = 1;
    public const TYPE_VIEW = 2;

    private array $options = [];
    private array $keys = [];

    private function __construct() {
        $this->registerDefaultOptions();
    }

    public function sendMainForm(Player $player): void {
        $form = new SimpleForm();
        $form->setTitle("Меню");

        $this->appendMenuButtons($player, $form, RegionManager::getInstance()->findByPlayer($player), self::TYPE_MENU);

        $form->sendToPlayer($player);
    }

    public function appendMenuButtons(Player $player, SimpleForm $form, ?Region $region, int $type): void {
        $class = match ($type) {
            self::TYPE_MENU => RegionMenuOption::class,
            self::TYPE_CONTROL => RegionControlOption::class,
            self::TYPE_VIEW => RegionViewOption::class,
            default => null
        };
        if ($class === null) return;

        foreach ($this->options as $option) {
            if (!is_subclass_of($option, $class)) continue;

            if (!$option->canClick($player, $region)) continue;

            $form->addButton($option->getText(),
                SimpleForm::IMAGE_TYPE_PATH,
                $option->getIcon(),
                function (Player $player) use ($option) {
                    $option->click($player);
                });
        }
    }

    public function register(MenuOption $obj, ?string $key = null): void {
        $this->options[] = $obj;
        $this->keys[$key] = array_key_last($this->options);
    }

    public function get(string $key): MenuOption {
        if (!isset($this->keys[$key])) throw new InvalidArgumentException("Меню с ключем '{$key}' не существует");

        return $this->options[$this->keys[$key]];
    }

    private function registerDefaultOptions(): void {
        $this->register(new CreateRegionOption(), CreateRegionOption::SECTION_ID);
        $this->register(new ViewInfoAboutRegionOption(), ViewInfoAboutRegionOption::SECTION_ID);
    }
}