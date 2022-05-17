<?php

namespace qpi\guard\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use qpi\guard\forms\Forms;

class RegionCommand extends Command {

    public function __construct() {
        parent::__construct("rg", "Управление регионами", "/rg", ['region', 'dguard']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Данную команду можно использовать только в игре");
            return false;
        }

        Forms::getInstance()->sendMainForm($sender);

        return true;
    }
}