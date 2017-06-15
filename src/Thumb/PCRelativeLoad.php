<?php

namespace MarijnvdWerf\DisAsm\Thumb;

class PCRelativeLoad extends Instruction
{
    public $register;
    public $address;

    public function __construct($register, $address)
    {
        $this->register = $register;
        $this->address = $address;
    }
}
