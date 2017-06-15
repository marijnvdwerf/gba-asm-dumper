<?php

namespace MarijnvdWerf\DisAsm\Thumb;

class PopRegisters extends Instruction
{
    public $registers = [];

    public function __construct()
    {
        foreach (func_get_args() as $arg) {
            if (is_array($arg)) {
                $this->registers = array_merge($this->registers, $arg);
                continue;
            }

            $this->registers[] = $arg;
        }

        sort($this->registers);
    }
}
