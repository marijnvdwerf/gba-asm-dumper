<?php

namespace MarijnvdWerf\DisAsm\Thumb;

class Branch extends Instruction
{
    public $register;

    public function __construct($register, $hi)
    {
        $this->register = $register + ($hi ? 8 : 0);
    }
}
