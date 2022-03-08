<?php

namespace qpi\guard\region\flag;

use qpi\guard\region\Region;

abstract class Flag {

    private string $id;
    private string $name;
    private string $description;

    public function __construct(string $id, string $name, string $description) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getDefaultValue(): bool {
        return true;
    }

    public abstract function checkForPlayer(string $playerName, bool $flagValue, Region $region): bool;
}