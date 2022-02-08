<?php

namespace qpi\guard\region\flag;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class FlagManager {
    use SingletonTrait;

    private PluginBase $plugin;
    private static array $flags = [];

    public function init(PluginBase $plugin): FlagManager {
        $this->plugin = $plugin;

        $this->registerDefaultFlags();

        return $this;
    }

    private function registerDefaultFlags(): void {
        //...
    }

    public function registerFlag(Flag $flag): void {
        $flagId = $flag->getId();
        if(isset(self::$flags[$flagId])){
            throw new \InvalidArgumentException("Флаг '${flagId}' уже существует");
        }
        self::$flags[$flagId] = $flag;
        $this->plugin->getLogger()->info("Флаг '${flagId}' был успешно зарегистрирован!");
    }

    public function getAllFlags(): array {
        return array_values(self::$flags);
    }

    public static function of(string $flagId): Flag {
        if(isset(self::$flags[$flagId])) return self::$flags[$flagId];
        throw new \InvalidArgumentException("Флага '${flagId}' не существует");
    }
}