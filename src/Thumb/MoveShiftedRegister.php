<?php

namespace MarijnvdWerf\DisAsm\Thumb;

class MoveShiftedRegister extends Instruction
{
    const LSL = 0;
    const LSR = 1;
    const ASR = 2;

    public $operation;
    public $src;
    public $dest;
    public $amount;

    public function __construct($operation, $dest, $src, $amount)
    {
        $this->operation = $operation;
        $this->dest = $dest;
        $this->amount = $amount;
        $this->src = $src;
    }
}
