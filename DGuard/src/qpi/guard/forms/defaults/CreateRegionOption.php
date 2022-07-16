<?php

namespace qpi\guard\forms\defaults;

use form\CustomForm;
use form\SimpleForm;
use pocketmine\player\Player;
use qpi\guard\event\CreatedRegionEvent;
use qpi\guard\event\CreatingRegionEvent;
use qpi\guard\forms\MenuOption;
use qpi\guard\forms\RegionMenuOption;
use qpi\guard\region\Region;
use qpi\guard\region\RegionException;
use qpi\guard\region\RegionManager;

class CreateRegionOption extends MenuOption implements RegionMenuOption {

    public const SECTION_ID = "regionCreate";

    public function __construct() {
        parent::__construct("Создание региона", "textures/items/campfire");
    }

    public function click(Player $player, ?SimpleForm $prev = null): void {
        $form = new SimpleForm();
        $form->setTitle("Создание региона");
        $form->setContent(
            "Для создания региона вам потребуется отметить 2 точки, которые будут являться границами региона. ".
            "Устанавливать их можно с помощью команд §6/rg pos1§f и §6/rg pos2§f. §dСтолбы при выделении строить не нужно!§f ".
            "Регион создается во всю высоту мира."
        );

        if (RegionManager::getInstance()->isSelectedArea($player)) {
            $form->addButton("Создать регион", SimpleForm::IMAGE_TYPE_PATH, "textures/items/campfire", function(Player $player) {
                $this->sendCreationForm($player);
            });
        } else {
            $form->addButton("Создать регион\n§4§lНе выделены точки", action: function(Player $player) {
                $player->sendMessage("§cОтметьте точки границ региона с помощью команд /rg pos1 и /rg pos2");
            });
        }

        $form->sendToPlayer($player);
    }

    public function sendCreationForm(Player $player): void {
        $form = new CustomForm(function (Player $player, array $data) {
            $regionManager = RegionManager::getInstance();
            try {
                $area = $regionManager->getSelectedArea($player);
            } catch (RegionException $ex) {
                $player->sendMessage('§c'. $ex->getMessage());
                return;
            }

            if ($regionManager->isPrivateArea($player->getWorld(), $area)) {
                $player->sendMessage("§cОтмеченная территория пересекает чужой регион");
                return;
            }

            $name = trim($data['name']);
            if (strlen($name) < 1 || strlen($name) > 32) {
                $player->sendMessage("§cНеверная длина названия региона. Должна быть от 1 до 32 символов.");
                return;
            }

            $rg = Region::fromJson([ //TODO: Убрать этот костыль
                'id' => -1,
                'world' => $player->getWorld()->getFolderName(),
                'name' => $name,
                'owner' => $player->getName(),
                'members' => [],
                'flags' => [],
                'area' => $area->jsonSerialize(),
            ]);
            $event = new CreatingRegionEvent($player, $rg);
            $event->call();
            if ($event->isCancelled()) {
                $player->sendMessage("§c{$event->getReason()}");
                return;
            }

            $rg = $regionManager->createNewRegion($player->getName(), $player->getWorld()->getFolderName(), $area, $name);
            $player->sendMessage("§eРегион '{$name}' был успешно создан!");

            $event = new CreatedRegionEvent($player, $rg);
            $event->call();
        });
        $form->setTitle("Создание региона");

        $form->addLabel(
            "Вы можете придумать любое название для региона. Но его длина должна быть от 1 до 32 символов."
        );

        $form->addInput("Название региона", "§8Мой приват", "", key: "name");

        $form->sendToPlayer($player);
    }
}