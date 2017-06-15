<?php

namespace MarijnvdWerf\DisAsm\Thumb;

class HiRegisterOperation extends Instruction
{
    public $src;
    public $dest;
    public $operation;

    public const ADD = 0;
    public const CMP = 1;
    public const MOV = 2;

    public function __construct($operation, $dest, $destHi, $src, $srcHi)
    {
        $this->operation = $operation;
        $this->dest = $dest + ($destHi ? 8 : 0);
        $this->src = $src + ($srcHi ? 8 : 0);
    }
}
