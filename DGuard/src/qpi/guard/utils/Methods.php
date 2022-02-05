<?php


namespace qpi\guard\utils;


use qpi\guard\DGuard;
use qpi\guard\elements\Flag;

class Methods{
    
    private static $instance;
    public static function getInstance(): Methods{
        return self::$instance;
    }
    
    public function __construct(){
        self::$instance = $this;
    }

    public function isPrivated($x, $z, $level): bool{

        $areas = DGuard::getInstance()->areas->getAll();

        foreach($areas as $name => $body){
            if($level == $body['level']) {
                if ($body['minX'] <= $x and $x <= $body['maxX']) {
                    if ($body['minZ'] <= $z and $z <= $body['maxZ']) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    //Проверка: Запривачен ли промежуток территории?
    public function isPrivatedArea($x1, $z1, $x2, $z2, $level): bool{

        $tmp = [];

        if($x1 > $x2){
            $tmp['x'] = [
                'min' => $x2,
                'max' => $x1,
            ];
        }else{
            $tmp['x'] = [
                'min' => $x1,
                'max' => $x2,
            ];
        }

        if($z1 > $z2){
            $tmp['z'] = [
                'min' => $z2,
                'max' => $z1,
            ];
        }else{
            $tmp['z'] = [
                'min' => $z1,
                'max' => $z2,
            ];
        }

        $x1 = $tmp['x']['min'];
        $x2 = $tmp['x']['max'];
        $z1 = $tmp['z']['min'];
        $z2 = $tmp['z']['max'];
        unset($tmp);

        $areas = DGuard::getInstance()->areas->getAll();

        foreach($areas as $name => $body){
            if($level == $body['level']) {
                if(!($body['minX'] > $x2 or $body['maxX'] < $x1 or $body['minZ'] > $z2 or $body['maxZ'] < $z1)) return true;
            }
        }
        return false;
    }

    //Устанавливает флаг
    public function setFlag($region, $flag, $value): void{
        $areas = DGuard::getInstance()->areas->getAll();

        $areas[strtolower($region)]['flags'][strtolower($flag)] = $value;

        DGuard::getInstance()->areas->setAll($areas);
        DGuard::getInstance()->areas->save();
    }

    //Проверка флага
    public function getFlag($region, $flag): string{
        $areas = DGuard::getInstance()->areas->getAll();

        if(!isset($areas[strtolower($region)]['flags'][strtolower($flag)])){
            $areas[strtolower($region)]['flags'][strtolower($flag)] = 'deny';
            DGuard::getInstance()->areas->setAll($areas);
            DGuard::getInstance()->areas->save();
        }

        return $areas[strtolower($region)]['flags'][strtolower($flag)];
    }

    //Получает флаг по координатам
    public function getFlagByCoords($x, $z, $level, $flag): string{
        $areas = DGuard::getInstance()->areas->getAll();

        foreach($areas as $name => $body){
            if($level == $body['level']) {
                if ($body['minX'] <= $x and $x <= $body['maxX']) {
                    if ($body['minZ'] <= $z and $z <= $body['maxZ']) {
                        $region = $body;
                        unset($areas);
                        break;
                    }
                }
            }
        }

        if(isset($region)){
            return isset($region['flags'][strtolower($flag)])? $region['flags'][strtolower($flag)] : 'allow';
        }else return '';
    }

    //Получает площадь
    public function getSpace($x1, $z1, $x2, $z2): int{
        $tmp = [];

        if($x1 > $x2){
            $tmp['x'] = [
                'min' => $x2,
                'max' => $x1,
            ];
        }else{
            $tmp['x'] = [
                'min' => $x1,
                'max' => $x2,
            ];
        }

        if($z1 > $z2){
            $tmp['z'] = [
                'min' => $z2,
                'max' => $z1,
            ];
        }else{
            $tmp['z'] = [
                'min' => $z1,
                'max' => $z2,
            ];
        }

        $x1 = $tmp['x']['min'];
        $x2 = $tmp['x']['max'];
        $z1 = $tmp['z']['min'];
        $z2 = $tmp['z']['max'];
        unset($tmp);

        $x = (int)($x2 - $x1);
        $z = (int)($z2 - $z1);

        return $x * $z;
    }

    //Получает все регионы игрока
    public function getRegions($player): array{
        $areas = DGuard::getInstance()->areas->getAll();

        $player = strtolower($player);
        $regions = [];
        foreach($areas as $name => $body){
            if($body['owner'] == $player) $regions[$name] = $body;
        }
        return $regions;
    }

    //Возвращает роль игрока в регионе
    public function getRole($player, $region): int{
        $areas = DGuard::getInstance()->areas->getAll();

        $player = strtolower($player);
        $area = $areas[strtolower($region)];

        if($area['owner'] == $player) return 3; //Владелец
        else if(in_array($player, $area['members'])) return 2; //Жилец
        else if(in_array($player, $area['guests'])) return 1; //Квартирант
        else return 0; //Никто
    }

    //Создание региона
    public function createRegion($region, $owner, $x1, $z1, $x2, $z2, $level, $op = false): int{
        $owner = strtolower($owner);

        $tmp = [];

        if($x1 > $x2){
            $tmp['x'] = [
                'min' => $x2,
                'max' => $x1,
            ];
        }else{
            $tmp['x'] = [
                'min' => $x1,
                'max' => $x2,
            ];
        }

        if($z1 > $z2){
            $tmp['z'] = [
                'min' => $z2,
                'max' => $z1,
            ];
        }else{
            $tmp['z'] = [
                'min' => $z1,
                'max' => $z2,
            ];
        }

        $x1 = $tmp['x']['min'];
        $x2 = $tmp['x']['max'];
        $z1 = $tmp['z']['min'];
        $z2 = $tmp['z']['max'];
        unset($tmp);

        $areas = DGuard::getInstance()->areas->getAll();

        if(!isset($areas[strtolower($region)])){
            if(!$this->isPrivatedArea($x1, $z1, $x2, $z2, $level)){
                $config = DGuard::getInstance()->config->getAll();

                if($this->getSpace($x1, $z1, $x2, $z2) <= $config['default-space'] or $op){
                    if(count($this->getRegions($owner)) <= $config['default-regions'] or $op){
                        if($level == $config['main-level'] or $op){
                            $pk = [
                                'name' => strtolower($region),
                                'owner' => $owner,
                                'level' => $level,
                                'members' => [],
                                'guests' => [],
                                'minX' => $x1,
                                'maxX' => $x2,
                                'minZ' => $z1,
                                'maxZ' => $z2,
                                'flags' => [],
                            ];

                            foreach(DGuard::$flags as $tag => $flag){
                                /* @var $flag Flag*/

                                $pk['flags'][$tag] = $flag->getDefault();
                            }

                            $areas[strtolower($region)] = $pk;
                            unset($pk);

                            DGuard::getInstance()->areas->setAll($areas);
                            DGuard::getInstance()->areas->save();

                            return 0; //Приват успешно создан.
                        }else return 5; //В данном мире нельзя приватить территории.
                    }else return 4; //Достигнут лимит регионов.
                }else return 3; //Регион занимает слишком огромную площадь.
            }else return 2; //Регион пересекает другие регионы.
        }else return 1; //Регион с таким названием уже существует.
    }

    //Удаление региона
    public function removeRegion($region): void{
        $areas = DGuard::getInstance()->areas->getAll();

        unset($areas[strtolower($region)]);

        DGuard::getInstance()->areas->setAll($areas);
        DGuard::getInstance()->areas->save();
    }

    //Проверяет существование региона
    public function isPrivatedName($region): bool{
        $areas = DGuard::getInstance()->areas->getAll();
        return isset($areas[strtolower($region)]);
    }

    //Получение полной информации о регионе
    public function getRegionInfo($region): array{
        $areas = DGuard::getInstance()->areas->getAll();
        return $areas[strtolower($region)];
    }

    //Получает название региона
    public function getRegion($x, $z, $level): string{

        $areas = DGuard::getInstance()->areas->getAll();

        foreach($areas as $name => $body){
            if($level == $body['level']) {
                if ($body['minX'] <= $x and $x <= $body['maxX']) {
                    if ($body['minZ'] <= $z and $z <= $body['maxZ']) {
                        return $name;
                    }
                }
            }
        }
        return "";
    }

    //Установка роли для игрока
    public function setRole($player, $role, $region): void{
        $areas = DGuard::getInstance()->areas->getAll();

        $player = strtolower($player);
        $region = strtolower($region);
        $old_role = $this->getRole($player, $region);

        switch($old_role){
            case 0: //Не был в регионе ранее
                break;
            case 1: //Был гостем
                foreach($areas[$region]['guests'] as $id => $p){
                    if($p == $player) unset($areas[$region]['guests'][$id]);
                }
                break;
            case 2: //Был Жителем
                foreach($areas[$region]['members'] as $id => $p){
                    if($p == $player) unset($areas[$region]['members'][$id]);
                }
                break;
            case 3: //Был Владельцем
                break;
        }

        switch($role){
            case 0: //Выгнан из региона
                break;
            case 1: //Стал участником
                $areas[$region]['guests'][] = $player;
                break;
            case 2: //Стал жителем
                $areas[$region]['members'][] = $player;
                break;
            case 3: //Стал владельцем
                $areas[$region]['members'][] = $areas[$region]['owner'];
                $areas[$region]['owner'] = $player;
                break;
        }

        DGuard::getInstance()->areas->setAll($areas);
        DGuard::getInstance()->areas->save();
    }


}