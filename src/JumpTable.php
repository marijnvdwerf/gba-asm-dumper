<?php

namespace MarijnvdWerf\DisAsm;

class JumpTable
{
    public $address;
    public $count;

    public function __construct($address, $count)
    {
        $this->address = $address;
        $this->count = $count;
    }
}
