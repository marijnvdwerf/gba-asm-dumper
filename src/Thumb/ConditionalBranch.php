<?php

namespace MarijnvdWerf\DisAsm\Thumb;

use ReflectionClass;

class ConditionalBranch extends Instruction
{
    const BEQ = 0b0000;
    const BNE = 0b0001;
    const BCS = 0b0010;
    const BCC = 0b0011;
    const BMI = 0b0100;
    const BPL = 0b0101;
    const BVS = 0b0110;
    const BVC = 0b0111;
    const BHI = 0b1000;
    const BLS = 0b1001;
    const BGE = 0b1010;
    const BLT = 0b1011;
    const BGT = 0b1100;
    const BLE = 0b1101;

    public $address;
    public $condition;

    public function __construct($condition, $address)
    {
        $this->address = $address;
        $this->condition = $condition;
    }

    function __toString()
    {
        $oClass = new ReflectionClass($this);
        $array = $oClass->getConstants();
        $array = array_flip($array);

        return sprintf('[%s %x]', $array[$this->condition], $this->address);
    }


}
