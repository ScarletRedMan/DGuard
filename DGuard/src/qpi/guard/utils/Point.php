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

    public static function fromJson(string|array $json): Point {
        $data = is_array($json)? $json : json_decode($json, true);
        return new Point($data['x'], $data['z']);
    }

    public static function min(Point $p1, Point $p2): Point {
        return new Point(min($p1->x, $p2->x), min($p1->z, $p2->z));
    }

    public static function max(Point $p1, Point $p2): Point {
        return new Point(max($p1->x, $p2->x), max($p1->z, $p2->z));
    }
}