<?php

namespace qpi\guard\event;

use pocketmine\event\Cancellable;

class CreatingRegionEvent extends RegionEvent implements Cancellable {

    private bool $cancelled = false;
    private ?string $reason = null;

    public function cancel(string $reason): void {
        $this->reason = $reason;
        $this->cancelled = true;
    }

    public function isCancelled(): bool {
        return $this->cancelled;
    }

    public function getReason(): ?string {
        return $this->reason;
    }
}