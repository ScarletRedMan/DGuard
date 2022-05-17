<?php

namespace qpi\guard\utils;

use JsonSerializable;

class Area implements JsonSerializable {

    private Point $min;
    private Point $max;

    public function __construct(Point $p1, Point $p2) {
        $this->min = Point::min($p1, $p2);
        $this->max = Point::max($p1, $p2);
    }

    /**
     * Получение точки с минимальными координатами
     * @return Point Точка
     */
    public function getMin(): Point {
        return $this->min;
    }

    /**
     * Получение точки с максимальными координатами
     * @return Point Точка
     */
    public function getMax(): Point {
        return $this->max;
    }

    /**
     * Получение длинны территории по оси X
     * @return int Длинна по оси X
     */
    public function getXLength(): int {
        return $this->max->getX() - $this->min->getX();
    }

    /**
     * Получение длинны территории по оси Z
     * @return int Длинна по оси Z
     */
    public function getZLength(): int {
        return $this->max->getZ() - $this->min->getZ();
    }

    /**
     * Получение площади территории
     * @return int Площадь территории
     */
    public function getSpace(): int {
        return $this->getXLength() * $this->getZLength();
    }

    /**
     * Проверка на вхождение точки в данную местность
     * @param Point $point Точка
     * @return bool Результат проверки
     */
    public function isInside(Point $point): bool {
        if($this->min->getX() > $point->getX() || $this->min->getZ() > $point->getZ()) return false;
        if($this->max->getX() < $point->getX() || $this->max->getZ() < $point->getZ()) return false;
        return true;
    }

    /**
     * Проверка на коллизию двух территорий
     * @param Area $other Территория
     * @return bool Результат проверки
     */
    public function isCollide(Area $other): bool {
        if($this->min->getX() > $other->getMax()->getX() || $this->min->getZ() > $other->getMax()->getZ()) return false;
        if($other->min->getX() > $this->getMax()->getX() || $other->min->getZ() > $this->getMax()->getZ()) return false;
        return true;
    }

    public function jsonSerialize(): array {
        return [
            'min' => $this->min->jsonSerialize(),
            'max' => $this->max->jsonSerialize(),
        ];
    }

    public static function fromJson(string|array $json): Area {
        $data = is_array($json)? $json : json_decode($json, true);
        return new Area(Point::fromJson($data['min']), Point::fromJson($data['max']));
    }
}