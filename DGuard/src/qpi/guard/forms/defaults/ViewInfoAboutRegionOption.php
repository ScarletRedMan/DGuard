<?php

namespace qpi\guard\forms\defaults;

use form\SimpleForm;
use pocketmine\player\Player;
use qpi\guard\forms\MenuOption;
use qpi\guard\forms\RegionForms;
use qpi\guard\forms\RegionMenuOption;
use qpi\guard\region\flag\FlagManager;
use qpi\guard\region\Region;
use qpi\guard\region\RegionManager;
use qpi\guard\region\Roles;

class ViewInfoAboutRegionOption extends MenuOption implements RegionMenuOption {

    public const SECTION_ID = "infoAboutThisRegion";

    public function __construct() {
        parent::__construct("Информация о текущем регионе", "textures/items/map_empty");
    }

    public function canClick(Player $player, ?Region $region): bool {
        return $region !== null;
    }

    public function click(Player $player, ?SimpleForm $prev = null): void {
        self::sendInfoAboutRegion($player, RegionManager::getInstance()->findByPlayer($player));
    }

    public static function sendInfoAboutRegion(Player $player, Region $region): void {
        if ($region->removed) return;

        $members = [];
        $guests = [];
        foreach ($region->getMembers() as $target) {
            if ($region->getRole($target) === Roles::OWNER) continue;
            $name = "§3{$target}§f";

            if ($region->getRole($target) === Roles::MEMBER) $members[] = $name;
            else $guests[] = $name;
        }

        $flags = [];
        foreach (FlagManager::getInstance()->getAllFlags() as $flag) {
            $flags[] = " ". ($region->getFlag($flag)? "" : ""). " {$flag->getName()}";
        }

        $form = new SimpleForm();
        $form->setTitle("Информация о регионе");
        $form->setContent(
            "§lИнформация о регионе §d{$region->getName()}§f(id: §7{$region->getId()}§f):§r\n".
            " §fВладелец региона: §e{$region->getOwner()}§f\n".
            " §fЖители региона: ". (empty($members)? "§eОтсутствуют§f" : implode(', ', $members)) ."§f.\n" .
            " §fГости: ". (empty($guests)? "§eОтсутствуют§f" : implode(', ', $guests)) ."§f.\n" .
            " §fПлощадь региона: §b{$region->getArea()->getSpace()}§f(§e{$region->getArea()->getXLength()}§6x§e{$region->getArea()->getZLength()})\n" .
            "\n§l§fФлаги:§r\n\n". implode("\n\n", $flags));

        RegionForms::getInstance()->appendMenuButtons($player, $form, $region, RegionForms::TYPE_VIEW);

        $form->sendToPlayer($player);
    }
}