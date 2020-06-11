<?php

namespace qpi\guard;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use qpi\guard\elements\Flag;
use qpi\guard\utils\Events;
use qpi\guard\utils\Forms;
use qpi\guard\utils\Methods;

class DGuard extends PluginBase implements Listener{

    /* @var $areas Config */
    public $areas;

    /* @var $config Config */
    public $config;

    public $pos1, $pos2, $wand, $region, $tmp;
    public static $flags;
    private static $instance;

    const VERSION = 1;

    public function onEnable(){

        self::$instance = $this;
        new Methods();
        new Forms();

        $this->getServer()->getPluginManager()->registerEvents(new Events(), $this);


        $this->areas = new Config($this->getDataFolder().'areas.json', Config::JSON, []);
        $this->config = new Config($this->getDataFolder().'settings.json', Config::JSON, [
            'version' => self::VERSION, //Версия
            'default-space' => 10000, //Допустимая площадь привата
            'default-regions' => 3, //Попустимое кол-во привата
            'main-level' => 'world', //Название основного мира в котором можно приватить
        ]);

        //Переменные
        $this->pos1 = $this->pos2 = $this->wand = $this->region = $this->tmp = [];

        //Регистрация флагов
        Flag::registerFlag('pvp', 'Разрешает PvP.', 'deny');
        Flag::registerFlag('chest', 'Разрешает всем открывать сундуки.', 'deny');
        Flag::registerFlag('furnace', 'Разрешает всем использовать печки.', 'deny');
        Flag::registerFlag('pve', 'Разрешает PvE.', 'allow');
    }


    public static function getInstance(): DGuard{
        return self::$instance;
    }


    public function onCommand(CommandSender $s, Command $cmd, $label, array $args): bool{
        if (strtolower($cmd->getName()) == 'rg'){
            if ($s instanceof Player){
                if(isset($args[0])){
                    switch(strtolower($args[0])){

                        case 'pos1':
                            $this->set_pos(true, $s->getX(), $s->getZ(), $s->getLevel()->getName(), $s);
                            break;

                        case 'pos2':
                            $this->set_pos(false, $s->getX(), $s->getZ(), $s->getLevel()->getName(), $s);
                            break;

                        default:
                            $s->sendMessage("§l§c>§e Не найдена суб-команда.§r");
                    }
                }else Forms::getInstance()->f_menu($s);
            } else {

                if(isset($args[0])){
                    switch(strtolower($args[0])){
                        case 'claim':
                            if(isset($args[1], $args[2], $args[3], $args[4], $args[5], $args[6])){
                                $x1 = (int) $args[1];
                                $x2 = (int) $args[2];
                                $z1 = (int) $args[3];
                                $z2 = (int) $args[4];
                                $region = $args[5];
                                $player = $args[6];

                                $result = Methods::getInstance()->createRegion($region, $player, $x1, $z1, $x2, $z2, 'world',true);

                                if($result === 0) $s->sendMessage('Регион успешно создан!');
                                else if($result === 1) $s->sendMessage('Название региона занято!');
                                else if($result === 2) $s->sendMessage('Регион пересекает другие регионы!');

                            }else $s->sendMessage('Использование: /rg claim <x1> <x2> <z1> <z2> <Регион> <Владелец>');
                            break;

                        case 'remove':
                            if(isset($args[1])){
                                $region = $args[1];

                                if(Methods::getInstance()->isPrivatedName($region)){
                                    Methods::getInstance()->removeRegion($region);
                                    $s->sendMessage('Регион успешно удален.');
                                }else $s->sendMessage('Регион не найден!');

                            }else $s->sendMessage('Использование: /rg remove <Регион>');
                            break;

                        case 'reowner':
                            if(isset($args[1], $args[2])){
                                $region = strtolower($args[1]);
                                $player = strtolower($args[2]);

                                if(Methods::getInstance()->isPrivatedName($region)){
                                    $areas = $this->areas->getAll();

                                    $areas[$region]['owner'] = $player;

                                    $this->areas->setAll($areas);
                                    $this->areas->save();
                                    $s->sendMessage('Владелец был успешно изменен!');
                                }else $s->sendMessage('Регион не найден!');

                            }else $s->sendMessage('Использование: /rg reowner <Регион> <Новый владелец>');
                            break;

                        case 'help':
                            $s->sendMessage('/rg claim - Заприватить регион.');
                            $s->sendMessage('/rg help - Помощь.');
                            $s->sendMessage('/rg remove - Удалить регион.');
                            $s->sendMessage('/rg reowner - Передать регион.');
                            break;
                    }
                }else $s->sendMessage('Введите /rg help для просмотра списка команд.');
            }
        }
        return true;
    }

    public function set_pos(bool $firstPos, $x, $z, $level, Player $player){
        if(Methods::getInstance()->isPrivated($x, $z, $level)){
            $player->sendMessage("§l§c>§e Невозможно здесь установить точку, тк здесь находится регион.§r");
        }else{
            if($firstPos){
                if(isset($this->pos2[strtolower($player->getName())])){
                    $temp = $this->pos2[strtolower($player->getName())];

                    if($temp['x'] == (int) $x && $temp['z'] == (int) $z) return;
                }

                if(isset($this->pos1[strtolower($player->getName())])){
                    $temp = $this->pos1[strtolower($player->getName())];

                    if($temp['x'] == (int) $x && $temp['z'] == (int) $z) return;
                }

                $player->sendMessage("§c§l>§f §3Первая точка§f была установлена. Нажмите еще раз для установки §3второй точки§f.§r");
                $this->pos1[strtolower($player->getName())] = [
                    'x' => (int) $x,
                    'z' => (int) $z,
                ];

                $this->wand[strtolower($player->getName())] = false;
            }else{
                if(isset($this->pos1[strtolower($player->getName())])){
                    $temp = $this->pos1[strtolower($player->getName())];

                    if($temp['x'] == (int) $x && $temp['z'] == (int) $z) return;
                }

                if(isset($this->pos2[strtolower($player->getName())])){
                    $temp = $this->pos2[strtolower($player->getName())];

                    if($temp['x'] == (int) $x && $temp['z'] == (int) $z) return;
                }

                $player->sendMessage("§c§l>§f §3Вторая точка§f была установлена. Теперь можно создать регион.§r");
                $this->pos2[strtolower($player->getName())] = [
                    'x' => (int) $x,
                    'z' => (int) $z,
                ];

                $this->wand[strtolower($player->getName())] = true;
            }
        }
    }
}