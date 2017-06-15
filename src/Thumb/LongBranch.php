<?php

namespace MarijnvdWerf\DisAsm\Thumb;

class LongBranch extends Instruction
{
    public $address;

    public function __construct($address)
    {
        $this->address = $address;
    }
}
