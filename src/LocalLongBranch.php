<?php

namespace MarijnvdWerf\DisAsm;

use MarijnvdWerf\DisAsm\Thumb\Instruction;

class LocalLongBranch extends Instruction
{
    public $address;

    public function __construct($address)
    {
        $this->address = $address;
    }
}
