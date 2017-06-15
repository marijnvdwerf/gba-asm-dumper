<?php

namespace MarijnvdWerf\DisAsm;

class Fn
{
    public $mask = 0;
    public $pattern = 0;
    public $fn;

    public function __construct($pattern, $fn)
    {
        $this->fn = $fn;

        $pattern = str_replace('.', '', $pattern);
        assert(strlen($pattern) === 16);

        $pattern = strrev($pattern);
        for ($i = 0; $i < 16; $i++) {
            $char = $pattern[$i];

            if ($char === '_') {
                continue;
            }

            $this->mask |= 1 << $i;
            $this->pattern |= intval($char, 10) << $i;
        }
    }
}
