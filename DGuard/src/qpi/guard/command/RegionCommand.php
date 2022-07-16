<?php

namespace qpi\guard\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use qpi\guard\forms\RegionForms;
use qpi\guard\region\RegionManager;

class RegionCommand extends Command {

    private RegionForms $forms;

    public function __construct() {
        parent::__construct("rg", "Управление регионами", "/rg", ['region', 'dguard']);

        $this->forms = RegionForms::getInstance();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Данную команду можно использовать только в игре");
            return false;
        }

        if (empty($args)) {
            $this->forms->sendMainForm($sender);
            return true;
        }

        $firstArg = $args[0];

        $regionManager = RegionManager::getInstance();
        switch ($firstArg) {
            case "pos1":
                if ($regionManager->findByPlayer($sender) === null) {
                    RegionManager::getInstance()->placePoint($sender, $sender->getPosition(), RegionManager::FIRST_POINT);
                    $sender->sendMessage("§eПервая точка была успешно установлена!");
                } else $sender->sendMessage("§cДанная точка находится в чужом привате.");
                break;

            case "pos2":
                if ($regionManager->findByPlayer($sender) === null) {
                    RegionManager::getInstance()->placePoint($sender, $sender->getPosition(), RegionManager::SECOND_POINT);
                    $sender->sendMessage("§eВторая точка была успешно установлена!");
                } else $sender->sendMessage("§cДанная точка находится в чужом привате.");
                break;
        }
        return true;
    }
}