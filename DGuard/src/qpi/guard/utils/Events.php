<?php


namespace qpi\guard\utils;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use qpi\guard\DGuard;

class Events implements Listener{

    public function onBreak(BlockBreakEvent $e){
        $p = $e->getPlayer();
        $n = $p->getName();
        $block = $e->getBlock();
        $region = Methods::getInstance()->getRegion($block->getPosition()->getX(), $block->getPosition()->getZ(), $p->getWorld()->getDisplayName());

        if($region !== ""){
            if(!Server::getInstance()->isOp($p->getName())){
                $role = Methods::getInstance()->getRole($n, $region);
                if($role === 0){
                    $e->cancel();
                    $p->sendTip("§c§lУ вас нет доступа к этой территории§r§f");
                }else if($role === 1){
                    $e->cancel();
                    $p->sendTip("§c§lВам не разрешено здесь строить§r§f");
                }
            }
        }
    }

    public function onPlace(BlockPlaceEvent $e){
        $p = $e->getPlayer();
        $n = $p->getName();
        $block = $e->getBlock();
        $region = Methods::getInstance()->getRegion($block->getPosition()->getX(), $block->getPosition()->getZ(), $p->getWorld()->getDisplayName());

        if($region !== ""){
            if(!Server::getInstance()->isOp($p->getName())){
                $role = Methods::getInstance()->getRole($n, $region);
                if($role === 0){
                    $e->cancel();
                    $p->sendTip("§c§lУ вас нет доступа к этой территории§r§f");
                }else if($role === 1){
                    $e->cancel();
                    $p->sendTip("§c§lВам не разрешено здесь строить§r§f");
                }
            }
        }
    }

    public function onTap(PlayerInteractEvent $e){
        $p = $e->getPlayer();
        $n = $p->getName();
        $block = $e->getBlock();
        $id = $block->getId();
        $itemHand = $p->getInventory()->getItemInHand()->getId();

        $blocked_items = [259, 325, 269, 273, 256, 284, 277, 290, 291, 292, 294, 293];
        $blocked_blocks = [96, 167, 64, 193, 194, 195, 196, 197, 71, 61, 62, 58, 54, 107, 183, 184, 185, 187, 186];

        if(($itemHand == 280 or $itemHand == 271) and PlayerInteractEvent::RIGHT_CLICK_BLOCK == $e->getAction()) {
            if($itemHand == 280){
                $region = Methods::getInstance()->getRegion($block->getPosition()->getX(), $block->getPosition()->getZ(), $p->getWorld()->getDisplayName());

                if($region !== ""){
                    Forms::getInstance()->f_regions_info($p, $region);
                }else $p->sendMessage("§c§l>§f  В данном месте нет регионов.§r");

                $e->cancel();
            }else if($itemHand == 271){
                $n = strtolower($n);
                if(isset(DGuard::getInstance()->wand[$n])){
                    if(DGuard::getInstance()->wand[$n]) {
                        DGuard::getInstance()->set_pos(true, $block->getPosition()->getX(), $block->getPosition()->getZ(), $p->getWorld()->getDisplayName(), $p);
                        //printf('1 точка');
                    }else{
                        DGuard::getInstance()->set_pos(false, $block->getPosition()->getX(), $block->getPosition()->getZ(), $p->getWorld()->getDisplayName(), $p);
                    }
                }else{
                    DGuard::getInstance()->set_pos(true, $block->getPosition()->getX(), $block->getPosition()->getZ(), $p->getWorld()->getDisplayName(), $p);
                }
                $e->cancel();

            }
        }else if((in_array($id, $blocked_blocks) or in_array($itemHand, $blocked_items)) and !Server::getInstance()->isOp($p->getName())){
            $region = Methods::getInstance()->getRegion($block->getPosition()->getX(), $block->getPosition()->getZ(), $p->getWorld()->getDisplayName());
            if($region !== "") {
                $role = Methods::getInstance()->getRole($n, $region);
                if($role === 0){
                    if(in_array($id, [64, 193, 195, 196, 197, 194, 71, 96, 167, 107, 183, 184, 185, 187, 186])){
                        $e->cancel();
                    }else if($id == 54){
                        if(Methods::getInstance()->getFlag($region, 'chest') == 'deny') $e->cancel();
                    }else if($id == 61 or $id == 62){
                        if(Methods::getInstance()->getFlag($region, 'furnace') == 'deny') $e->cancel();
                    }
                }

                if(in_array($itemHand, $blocked_items)){
                    if($role < 2) $e->cancel();
                }

            }
        }
    }

    public function onDamage(EntityDamageEvent $e){
        if($e instanceof EntityDamageByEntityEvent){
            $p = $e->getEntity();
            if($p instanceof Player){

                $region = Methods::getInstance()->getRegion($p->getPosition()->getX(), $p->getPosition()->getZ(), $p->getWorld()->getDisplayName());
                if($region !== ""){
                    if($e->getDamager() instanceof Player)
                        if(Methods::getInstance()->getFlag($region, 'pvp') == 'deny') $e->cancel();
                }
            }else{
                $p = $e->getEntity();

                $region = Methods::getInstance()->getRegion($p->getPosition()->getX(), $p->getPosition()->getZ(), $p->getWorld()->getDisplayName());
                if($region !== ""){
                    if(Methods::getInstance()->getFlag($region, 'pve') == 'deny') $e->cancel();
                }
            }
        }
    }

}
