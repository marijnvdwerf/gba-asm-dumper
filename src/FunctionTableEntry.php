<?php

namespace MarijnvdWerf\DisAsm;

class FunctionTableEntry
{
    public $name;
    public $segment;
    public $address;
    public $size;
    public $instructions = [];
    public $instructionSet = InstructionSet::THUMB;
}
