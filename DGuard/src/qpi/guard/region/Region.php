<?php

namespace qpi\guard\region;

use JsonSerializable;

class Region implements JsonSerializable {

    private int $id;
    private string $world;
    private string $name;
    private string $owner;
    private array $members;
    private array $flags = [];

    private function __construct(int $id) {
        $this->id = $id;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getWorldName(): string {
        return $this->world;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getOwner(): string {
        return $this->owner;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'world' => $this->world,
            'name' => $this->name,
            'owner' => $this->owner,
            'members' => $this->members,
            'flags' => $this->flags,
        ];
    }

    public static function fromJson(string $json): Region {
        $data = json_decode($json, true);
        $region = new Region($data['id']);

        $region->world = $data['world'];
        $region->name = $data['name'];
        $region->owner = $data['owner'];
        $region->members = $data['members'];
        $region->flags = $data['flags'];

        return $region;
    }
}