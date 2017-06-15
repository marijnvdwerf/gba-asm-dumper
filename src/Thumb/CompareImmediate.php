<?php

namespace MarijnvdWerf\DisAsm\Thumb;

class CompareImmediate extends Instruction
{
    public $value;
    public $register;

    public function __construct($value, $register)
    {
        $this->value = $value;
        $this->register = $register;
    }
}
