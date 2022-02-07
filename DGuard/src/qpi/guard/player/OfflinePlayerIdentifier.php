<?php

namespace qpi\guard\player;

class OfflinePlayerIdentifier extends PlayerIdentifier {

    public function __construct(private string $xuid) {

    }

    public function getId(): string {
        return $this->xuid;
    }

    public function isOnline(): bool {
        return false;
    }
}