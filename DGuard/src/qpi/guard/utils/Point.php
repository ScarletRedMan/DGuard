<?php

namespace qpi\guard\utils;

use JsonSerializable;
use pocketmine\math\Vector3;

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

    /**
     * Преобразование вектора в точку
     * @param Vector3 $pos Вектор
     * @return Point Точка
     */
    public static function fromVector(Vector3 $pos): Point {
        return new Point((int) ($pos->getX() + 0.5), (int) ($pos->getZ() + 0.5));
    }

    /**
     * Получение точки с минимальными координатами из двух точек
     * @param Point $p1 Точка 1
     * @param Point $p2 Точка 2
     * @return Point Новая точка
     */
    public static function min(Point $p1, Point $p2): Point {
        return new Point(min($p1->x, $p2->x), min($p1->z, $p2->z));
    }

    /**
     * Получение точки с максимальными координатами из двух точек
     * @param Point $p1 Точка 1
     * @param Point $p2 Точка 2
     * @return Point Новая точка
     */
    public static function max(Point $p1, Point $p2): Point {
        return new Point(max($p1->x, $p2->x), max($p1->z, $p2->z));
    }
}