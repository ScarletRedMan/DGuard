<?php

namespace qpi\guard\player;

use pocketmine\player\Player;

class OnlinePlayerIdentifier extends PlayerIdentifier {

    public function __construct(private Player $player) {

    }

    public function getId(): string {
        return $this->player->getXuid();
    }

    public function isOnline(): bool {
        return $this->getPlayer()->isOnline();
    }

    public function getPlayer(): Player {
        return $this->player;
    }
}