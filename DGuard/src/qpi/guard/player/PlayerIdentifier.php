<?php

namespace qpi\guard\player;

use pocketmine\player\Player;

abstract class PlayerIdentifier {

    public abstract function getId(): string;

    public abstract function isOnline(): bool;

    public static function of(Player|string $player): PlayerIdentifier {
        if($player instanceof Player) return new OnlinePlayerIdentifier($player);
        return new OfflinePlayerIdentifier($player);
    }
}