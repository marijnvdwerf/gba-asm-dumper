<?php

namespace MarijnvdWerf\DisAsm;

class RomMap
{
    private $invalidate = true;
    public $procedures = [];
    private $fnIndexed = [];

    public function addFunction(FunctionTableEntry $fn)
    {
        $this->procedures[$fn->address] = $fn;
        $this->invalidate = true;
    }

    public function getFunction($index)
    {
        if ($this->invalidate) {
            ksort($this->procedures);
            $this->fnIndexed = array_keys($this->procedures);
            $this->invalidate = false;
        }

        return $this->procedures[$this->fnIndexed[$index]];
    }

    public function getFunctions()
    {
        if ($this->invalidate) {
            ksort($this->procedures);
        }

        return $this->procedures;
    }

    private $data;

    public function addData($addr, $name)
    {
        $this->data[$addr] = $name;
        ksort($this->data);
    }

    public function getLabel($address)
    {
        if (isset($this->data[$address])) {
            return $this->data[$address];
        }

        if (isset($this->procedures[$address])) {
            return $this->procedures[$address]->name;
        }


        if (isset($this->procedures[$address - 1])) {
            return $this->procedures[$address - 1]->name . ' + 1';
        }

        if (($address >= 0x2000000 && $address < 0x4000000) || ($address >= 0x8000000 && $address < 0x9000000)) {
            $lastName = null;
            $lastAddr = 0;
            foreach ($this->data as $mAddr => $mName) {
                if ($mAddr > $address) {
                    break;
                }
                $lastName = $mName;
                $lastAddr = $mAddr;
            }

            if ($lastName !== null) {
                $offset = $address - $lastAddr;
                if ($offset == 0) {
                    return $lastName;
                }
                return sprintf('%s+0x%x', $lastName, $offset);
            }
        }

        return null;
    }
}
