<?php

namespace qpi\guard\utils;

use JsonSerializable;

class Point implements JsonSerializable {

    public function __construct(private int $x, private int $z) {

    }

    public function getX(): int {
        return $this->x;
    }

    public function getZ(): int {
        return $this->z;
    }

    public function jsonSerialize(): array {
        return [
            'x' => $this->x,
            'z' => $this->z,
        ];
    }

    public static function fromJson(string $json): Point {
        $data = json_decode($json, true);
        return new Point($data['x'], $data['z']);
    }
}