<?php

namespace MarijnvdWerf\DisAsm\Dumper;

class RomMapEntry
{
    public $label;
    public $type;
    public $data = null;
    public $arguments = [];
    public $size = 0;

    public function __construct($label, $type = null)
    {
        $this->label = $label;
        $this->type = $type;
    }
}