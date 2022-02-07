<?php

namespace qpi\guard\region;

class Roles {

    public const NOBODY = 0;
    public const GUEST = 20;
    public const MEMBER = 60;
    public const OWNER = 98;
    public const NOT_ALLOW = 99;

    private function __construct() {

    }
}