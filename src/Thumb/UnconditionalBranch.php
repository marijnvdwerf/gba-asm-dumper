<?php

namespace MarijnvdWerf\DisAsm\Thumb;

class UnconditionalBranch extends Instruction
{
    public $address;

    public function __construct($address)
    {
        $this->address = $address;
    }
}
