<?php


namespace qpi\guard\utils;


use pocketmine\item\Item;
use pocketmine\Player;
use qpi\guard\DGuard;

class Forms
{

    private static $instance;
    public static function getInstance(): Forms{
        return self::$instance;
    }

    public function __construct(){
        self::$instance = $this;
    }


    public function f_menu(Player $player){
        $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data){
            if($data !== null){
                switch($data){
                    case 0:
                        $this->f_control_list($player);
                        break;
                    case 1:
                        $this->f_private($player);
                        break;
                    case 2:
                        $this->f_regions_list($player);
                        break;
                    case 3:
                        $this->f_regions_info($player, Methods::getInstance()->getRegion($player->getX(), $player->getZ(), $player->getLevel()->getName()));
                        break;
                    case 4:
                        $this->f_guide($player);
                        break;
                }
            }
        });

        $form->setTitle('Регионы');
        $form->addButton('Управление регионами', 0, 'textures/items/book_writable');
        $form->addButton('Создать регион', 0, 'textures/items/campfire');
        $form->addButton('Мои регионы', 0, 'textures/items/book_normal');
        $form->addButton('Информация о регионе', 0, 'textures/items/map_empty');
        $form->addButton('Туториал', 0, 'textures/items/book_portfolio');
        $form->sendToPlayer($player);
    }

    public function f_guide(Player $player){
        $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data){
            if($data !== null){
                if($data == 0) $this->f_menu($player);
            }
        });

        $form->setContent(
            "§l§6Инструкция по созданию региона:§r§f\n".
            " Чтобы создать регион нужно сначала выделить крайние точки, которой будут служить границой региона. Отметить точки можно с помощью специального топорика, который можно получить из раздела §eСоздание региона§f в команде §b/rg§f или же отметив точки, введя команду §b/rg pos1§f и §b/rg pos2§f. ".
            "После выделения точек можно создавать регион в том же разделе меню. \n".
            " Регион создаются во всю высоту мира и рассчет блоков идет по площади. Можно создать всего 3 региона, в котором каждый может достигать площади до 10000 блоков или территорию 100 на 100 блоков.\n".
            "\n".
            "\n".
            "§l§6Инструкция по добавлению игрока в регион:§r§f\n".
            " Чтобы добавить в регион игрока нужно сначала выбрать регион в разделе §eМои регионы§f, где далее нужно выбрать §eДобавить игрока§f. Выбрав ник игрока из присутствующего онлайна на сервере и нажимаем §eОтправить§f. Готово! Игрок добавлен в регион и теперь имеет роль §bГость§f. Кстати, в регион можно добавить игрока, который сейчас онлайн на сервере, иначе он не отобразится в списке игроков.\n".
            " Чтобы изменить роль игроку нужно выбрать регион, в которого добавили игрока и выбрать раздел §eУправление игроками§f после чего выбираем нужного нам игрока и изменяем ему роль. В этом же разделе можно как и назначить роль, так и выгнать или передать регион другому игроку.\n".
            " Доступные роли для игроков в регионе:\n".
            " - §bГость§f - Может только взаимодействовать с печками, сундуками и дверьми. Хорошо подойдет для приюченных игроков.\n".
            " - §bЖитель§f - Может строить в привате, также взаимодействовать с сундуками, печками и дверями. Добавлять только на свой страх и риск, ведь администрация не несет ответственности за разрушенный дом.\n".
            " - §bВладелец§f - Полностью управляет регионом, выдает роли другим игрокам в регионе."
        );
        $form->setTitle('Туториал');
        $form->addButton('Назад');
        $form->addButton('Закрыть');
        $form->sendToPlayer($player);
    }

    public function f_regions_info(Player $player, $region){

        if($region != "") {
            $info = Methods::getInstance()->getRegionInfo($region);

            $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data) {});

            $form->setTitle('Регионы');
            $form->setContent(
                "Информация о регионе §e{$region}§f:\n".
                "\n".
                "Владелец: §b".$info['owner']."§f\n".
                "Жители: §b".((count($info['members']) > 0)? implode($info['members'], "§f, §b") : "§eОтсутствуют")."§f.\n".
                "Гости: §b".((count($info['guests']) > 0)? implode($info['guests'], "§f, §b") : "§eОтсутствуют")."§f.\n".
                "Площадь: §b".Methods::getInstance()->getSpace($info['minX'], $info['minZ'], $info['maxX'], $info['maxZ'])." Блоков§f."
            );
            $form->addButton('Выход');
            $form->sendToPlayer($player);
        }else $player->sendMessage("§l§c>§f В данной точке нет региона.§r");
    }


    public function f_regions_list(Player $player){
        if(Methods::getInstance()->getRegions($player->getName()) > 0){
            $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data) {
                if($data !== null){
                    $regions = Methods::getInstance()->getRegions($player->getName());
                    if($data != count($regions)) {
                        $tmp = [];

                        foreach ($regions as $name => $region) {
                            $tmp[] = $name;
                        }
                        $this->f_regions_info($player, $tmp[$data]);
                    }else{
                        $this->f_menu($player);
                    }
                }
            });
            $form->setContent("Выберите регион, которого вы хотите посмотреть информацию.");
            $regions = Methods::getInstance()->getRegions($player->getName());

            foreach($regions as $name => $body){
                $form->addButton($body['name'], 0, 'textures/items/campfire');
            }
        }else{
            $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data) {});
            $form->setContent("У вас нет еще регионов.");
        }
        $form->setTitle('Ваши регионы');
        $form->addButton('Назад');
        $form->sendToPlayer($player);
    }

    public function f_private(Player $player){
        $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data) {
            if($data !== null){
                switch($data){
                    case 0:
                        if(isset($this->pos1[strtolower($player->getName())], $this->pos2[strtolower($player->getName())])){
                            $this->f_create_region($player);
                        }else $player->sendMessage("§l§c>§e Для начала отметьте крайние точки региона.§r");
                        break;
                    case 1:
                        $item = Item::get(271, 0, 1)->setCustomName("§r§r§r§l§dМаркер выделения точек§r");
                        $inv = $player->getInventory();

                        $inv->addItem($item);

                        $player->sendMessage("§l§c>§f Вы получили инструмент для выделения. Нажмите им по блокам для выделения территории.§r");
                        break;
                    case 2:
                        $this->f_menu($player);
                        break;
                }
            }
        });
        $form->setTitle('Создание региона');
        $form->setContent(
            "Установите 2 точки, которые будут выделять территорию для создания региона. Далее можно будет создавать регион.\n".
            "\n".
            "Просто нажимайте деревянным топором по блоам чтобы устанавливать точки.\n".
            "\n".
            "Также можно с помощью команд §b/rg pos1§f и §b/rg pos2§f.".
            "\n".
            "Примечание: §3Регион создается во всю высоту, а блоки расчитываются по площади территории.§f"
        );
        $form->addButton("Создать регион", 0, 'textures/items/campfire');
        $form->addButton("Получить инструменты для выделения", 0, 'textures/items/wood_axe');
        $form->addButton("Назад");
        $form->sendToPlayer($player);
    }


    public function f_create_region(Player $player){
        $form = DGuard::getInstance()->api->createCustomForm(function (Player $player, $data){
            if(isset($data[1])){
                if(strlen($data[1]) > 3 and strlen($data[1]) < 15){
                    $pos1 = DGuard::getInstance()->pos1[strtolower($player->getName())];
                    $pos2 = DGuard::getInstance()->pos2[strtolower($player->getName())];
                    if(isset($this->wand[strtolower($player->getName())])) unset(DGuard::getInstance()->wand[strtolower($player->getName())]);

                    $result = Methods::getInstance()->createRegion($data[1], $player->getName(), $pos1['x'], $pos1['z'], $pos2['x'], $pos2['z'], $player->getLevel()->getName(), $player->isOp());

                    if($result == 0) $player->sendMessage("§l§c> §fРегион был успешно создан!§r");
                    else if($result == 1) $player->sendMessage("§l§c> §eРегион с таким названием уже существует.§r");
                    else if($result == 2) $player->sendMessage("§l§c> §eРегион пересекает другие регионы.§r");
                    else if($result == 3) $player->sendMessage("§l§c> §eРегион занимает слишком огромную площадь.§r");
                    else if($result == 5) $player->sendMessage("§l§c> §eВ данном мире нельзя создавать регионы.§r");
                    else if($result == 4) $player->sendMessage("§l§c> §eДостигнут лимит регионов.§r");


                }else $player->sendMessage("§l§c>§e Допустимая длина названия региона от 4 до 14 символов.§r");
            }
        });
        $form->setTitle("Создание региона");
        $form->addLabel("Укажите желаемое название региона. Использовать можно только латинские буквы и цыфры. Не использовать пробелы!");
        $form->addInput("Название региона", "Название региона. Например: ".$player->getName());
        $form->sendToPlayer($player);
    }

    public function f_control_list(Player $player){
        $regions = [];
        foreach(Methods::getInstance()->getRegions($player->getName()) as $name => $body){
            $regions[] = $name;
        }

        if(count($regions) > 0){
            $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data) {
                if($data !== null) {
                    $regions = [];
                    foreach (Methods::getInstance()->getRegions($player->getName()) as $name => $body) {
                        $regions[] = $name;
                    }

                    if (count($regions) == $data) {
                        $this->f_menu($player);
                    } else {
                        $this->f_edit_menu($player, $regions[$data]);
                    }
                }
            });
            $form->setContent("Выберите регион чтобы настроить его.");

            foreach($regions as $name){
                $form->addButton($name, 0, 'textures/items/campfire');
            }
        }else{
            $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data) {
                if($data !== null) $this->f_menu($player);
            });
            $form->setContent("У вас еще нет регионов.");
        }
        $form->setTitle("Управление регионами");
        $form->addButton("Назад");
        $form->sendToPlayer($player);
    }

    public function f_edit_menu(Player $player, $region){
        DGuard::getInstance()->region[strtolower($player->getName())] = $region;
        $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data) {
            if($data !== null){
                $region = DGuard::getInstance()->region[strtolower($player->getName())];

                switch($data){
                    case 0: //Флаги
                        $this->f_edit_flag($player, $region);
                        break;
                    case 1: //Управление игроками
                        $this->f_edit_players($player, $region);
                        break;
                    case 2: //Добавить игрока
                        $this->f_add_user($player, $region);
                        break;
                    case 3: //Удалить регион
                        $this->f_remove($player, $region);
                        break;
                    case 4:
                        $this->f_control_list($player);
                        break;
                }
            }
        });
        $form->setTitle("Управление регионом {$region}");
        $form->setContent("Выберите нужное вам действие, которое хотите применить к данному региону.");

        $form->addButton("Флаги региона", 0, 'textures/items/repeater');
        $form->addButton("Управление игроками", 0, 'textures/items/name_tag');
        $form->addButton("Добавить игрока", 0, 'textures/items/cake');
        $form->addButton("Удалить регион", 0, 'textures/items/blaze_powder');

        $form->addButton("Назад");
        $form->sendToPlayer($player);
    }


    public function f_edit_flag(Player $player, $region){
        DGuard::getInstance()->region[strtolower($player->getName())] = $region;
        $form = DGuard::getInstance()->api->createCustomForm(function (Player $player, $data){
            if(isset($data[1])){
                $region = DGuard::getInstance()->region[strtolower($player->getName())];

                Methods::getInstance()->setFlag($region, 'pvp', $data[1]? 'allow' : 'deny');
                Methods::getInstance()->setFlag($region, 'chest', $data[2]? 'allow' : 'deny');
                Methods::getInstance()->setFlag($region, 'furnace', $data[3]? 'allow' : 'deny');
                Methods::getInstance()->setFlag($region, 'pve', $data[4]? 'allow' : 'deny');

                $player->sendMessage("§l§c>§f Флаги региона былы успешно изменены.§r");
            }
        });
        $form->setTitle("Управление регионом {$region}");
        $form->addLabel("Установите нужные параметры установки флагов для региона §b{$region}§f.");
        $form->addToggle("PvP", (Methods::getInstance()->getFlag($region, 'pvp') == 'allow')? true : false);
        $form->addToggle("Использование сундуков", (Methods::getInstance()->getFlag($region, 'chest') == 'allow')? true : false);
        $form->addToggle("Использование печей", (Methods::getInstance()->getFlag($region, 'furnace') == 'allow')? true : false);
        $form->addToggle("PvE", (Methods::getInstance()->getFlag($region, 'pve') == 'allow')? true : false);
        $form->sendToPlayer($player);
    }

    public function f_remove(Player $player, $region){
        DGuard::getInstance()->region[strtolower($player->getName())] = $region;
        $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data) {
            if($data !== null){
                $region = DGuard::getInstance()->region[strtolower($player->getName())];
                switch($data){
                    case 0:
                        Methods::getInstance()->removeRegion($region);
                        $player->sendMessage("§l§c> §fРегион был успешно удален!§r");
                        break;
                    case 1:
                        $this->f_menu($player);
                        break;
                }
            }
        });

        $form->setTitle("Удалить регион {$region}");
        $form->setContent("Подтвердите что вы точно хотите удалить регион §b{$region}§f.");
        $form->addButton("Удалить регион", 0, 'textures/blocks/barrier');
        $form->addButton("Назад");
        $form->sendToPlayer($player);
    }

    public function f_add_user(Player $player, $region){
        DGuard::getInstance()->region[strtolower($player->getName())] = $region;

        $tmp = [];

        foreach(DGuard::getInstance()->getServer()->getOnlinePlayers() as $p){
            if(Methods::getInstance()->getRole($p->getName(), $region) == 0) $tmp[] = $p->getName();
        }
        ksort($tmp);
        DGuard::getInstance()->tmp[strtolower($player->getName())] = $tmp;

        $form = DGuard::getInstance()->api->createCustomForm(function (Player $player, $data){
            if(isset($data[1])){
                $tmp = DGuard::getInstance()->tmp[strtolower($player->getName())];
                if(count($tmp) != 0) {
                    $region = DGuard::getInstance()->region[strtolower($player->getName())];
                    $p = $tmp[$data[1]];
                    Methods::getInstance()->setRole($p, 1, $region);

                    $player->sendMessage("§l§c>§f Вы успешно добавили игрока §3{$p}§f в регион §3{$region}§f.§r");
                }
            }
        });

        $form->setTitle("Добавить игрока");
        $form->addLabel("Выберите игрока, которого хотите добавить в ваш регион. После добавления игроку устанавливается роль §bГость§f.");
        $form->addDropdown("Игрок", $tmp);
        $form->sendToPlayer($player);
    }

    public function f_edit_players(Player $player, $region){
        DGuard::getInstance()->region[strtolower($player->getName())] = $region;

        $body = Methods::getInstance()->getRegionInfo($region);
        $tmp = [];
        foreach($body['members'] as $p){
            $tmp[] = $p;
        }
        foreach($body['guests'] as $p){
            $tmp[] = $p;
        }
        DGuard::getInstance()->tmp[strtolower($player->getName())] = $tmp;

        $form = DGuard::getInstance()->api->createSimpleForm(function (Player $player, $data) {
            if($data !== null){
                $region = DGuard::getInstance()->region[strtolower($player->getName())];
                $tmp = DGuard::getInstance()->tmp[strtolower($player->getName())];

                if($data == count($tmp)){
                    $this->f_edit_menu($player, $region);
                }else{
                    DGuard::getInstance()->tmp[strtolower($player->getName())] = $tmp[$data];

                    $this->f_edit_role($player, $tmp[$data], $region);
                }
            }
        });

        $form->setTitle("Управление игроками");
        $form->setContent(
            "Выберите нужного игрока для редактирования.\n".
            "\n".
            "Типы игроков:\n".
            "§bЗеленые§f - Жители региона.\n".
            "§bБирюзовые§f - Гости региона."
        );
        foreach($tmp as $p){
            $form->addButton($p, 0, 'textures/blocks/'.((Methods::getInstance()->getRole($p, $region) == 2)? 'concrete_lime' : 'concrete_light_blue'));
        }
        $form->addButton("Назад");
        $form->sendToPlayer($player);
    }

    public function f_edit_role(Player $player, $p, $region){
        DGuard::getInstance()->region[strtolower($player->getName())] = $region;


        $form = DGuard::getInstance()->api->createCustomForm(function (Player $player, $data){
            if(isset($data[1])){
                $region = DGuard::getInstance()->region[strtolower($player->getName())];
                $p = DGuard::getInstance()->tmp[strtolower($player->getName())];

                Methods::getInstance()->setRole($p, $data[1], $region);

                switch($data[1]){
                    case 0:
                        $player->sendMessage("§l§c>§f Вы успешно выгнали игрока §3{$p}§f из региона §3{$region}§f!§r");
                        break;
                    case 1:
                        $player->sendMessage("§l§c>§f Вы успешно установили роль §3Гость§f игроку §b{$p}§f в регионе §3{$region}§f!§r");
                        break;
                    case 2:
                        $player->sendMessage("§l§c>§f Вы успешно установили роль §3Житель§f игроку §b{$p}§f в регионе §3{$region}§f!§r");
                        break;
                    case 3:
                        $player->sendMessage("§l§c>§f Вы успешно передали регион §3{$region}§f игроку §3{$p}§f!§r");
                        break;
                }
            }
        });

        $form->setTitle("Изменение роли");
        $form->addLabel(
            "Выберите действие для игрока §b{$p}§f в регионе §b{$region}§f.".
            "\n".
            "§bВладельцы§f могут все делать в регионе. Может быть только 1 владелец. При передаче, бывшему владельцу устанавливается роль §bЖитель§f.\n".
            "§bЖильцы§f могут взаимодействовать с регионом в плане строительства, но не могут его настраивать.\n".
            "§bГости§f могут только взаимодействовать с дверьми, печками, вестаками и сундуками."
        );
        $form->addDropdown("Действие", ['Выгнать из региона', 'Назначить Гостем', 'Назначить Жителем', 'Передать регион']);
        $form->sendToPlayer($player);
    }
}