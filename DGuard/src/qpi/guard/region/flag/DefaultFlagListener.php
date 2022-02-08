<?php

namespace qpi\guard\region\flag;

use pocketmine\event\Listener;

class DefaultFlagListener implements Listener {

    public function __construct(private FlagManager $manager) {

    }

}