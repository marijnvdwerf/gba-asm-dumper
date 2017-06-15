<?php

namespace MarijnvdWerf\DisAsm;

use Exception;
use MarijnvdWerf\DisAsm\Thumb\Branch;
use MarijnvdWerf\DisAsm\Thumb\CompareImmediate;
use MarijnvdWerf\DisAsm\Thumb\ConditionalBranch;
use MarijnvdWerf\DisAsm\Thumb\HiRegisterOperation;
use MarijnvdWerf\DisAsm\Thumb\LongBranch;
use MarijnvdWerf\DisAsm\Thumb\MoveShiftedRegister;
use MarijnvdWerf\DisAsm\Thumb\PCRelativeLoad;
use MarijnvdWerf\DisAsm\Thumb\PopRegisters;
use MarijnvdWerf\DisAsm\Thumb\Register;
use MarijnvdWerf\DisAsm\Thumb\UnconditionalBranch;
use PhpBinaryReader\BinaryReader;

class ThumbOpCodeReader
{

    private function printOpcode(int $opcode, $ln = 16)
    {
        $string = '';

        while ($opcode !== 0) {
            $string .= $opcode & 1 ? '1' : '0';
            $opcode = $opcode >> 1;
        }

        $string = str_pad($string, $ln, '0', STR_PAD_RIGHT);
        return '0b' . strrev($string);
    }

    /** @var Fn[] */
    private $readFunctions = [];

    private function readRList($data)
    {
        $registers = $this->parseRlist($data);

        return $this->formatter->formatRegisterList($registers);
    }


    public function formatRegister($register, $hs = false)
    {
        if ($hs) {
            $register += 8;
        }

        return $this->formatter->formatRegister($register);
    }

    public function __construct()
    {
        $this->formatter = new ThumbInstructionFormatter();

        $this->readFunctions[] = new Fn('00011._._.___.___.___', [$this, 'readOpcode2']);
        $this->readFunctions[] = new Fn('000__._____.___.___', [$this, 'readOpcode1']);
        $this->readFunctions[] = new Fn('001.__.___.________', [$this, 'readOpcode3']);
        $this->readFunctions[] = new Fn('010000____.___.___', [$this, 'readOpcode4']);
        $this->readFunctions[] = new Fn('010001__._._.___.___', [$this, 'readOpcode5']);
        $this->readFunctions[] = new Fn('01001___.________', [$this, 'readOpcode6']);
        $this->readFunctions[] = new Fn('0101_._0___.___.___', [$this, 'readOpcode7']);
        $this->readFunctions[] = new Fn('0101_._1___.___.___', [$this, 'readOpcode8']);
        $this->readFunctions[] = new Fn('011_._._____.___.___', [$this, 'readOpcode9']);
        $this->readFunctions[] = new Fn('1000_._____.___.___', [$this, 'readOpcode10']);
        $this->readFunctions[] = new Fn('1001_.___.________', [$this, 'readOpcode11']);
        $this->readFunctions[] = new Fn('1010_.___.________', [$this, 'readOpcode12']);

        $this->readFunctions[] = new Fn('10110000_._______', [$this, 'readOpcode13']);
        $this->readFunctions[] = new Fn('1011_10_.________', [$this, 'readOpcode14']);

        $this->readFunctions[] = new Fn('1100_.___.________', [$this, 'readOpcode15']);
        $this->readFunctions[] = new Fn('11011111.________', [$this, 'readOpcode17']);
        $this->readFunctions[] = new Fn('1101____.________', [$this, 'readOpcode16']);
        $this->readFunctions[] = new Fn('11100.___________', [$this, 'readFormat18']);
        $this->readFunctions[] = new Fn('1111_.___________', [$this, 'readOpcode19']);
    }

    public function readOpcode(BinaryReader $br)
    {
        $opcode = $br->readUInt16();
        $pc = $br->getPosition();

        if (!is_int($opcode)) {
            throw new \InvalidArgumentException('Expected integer opcode');
        }

        foreach ($this->readFunctions as $fn) {
            if (($opcode & $fn->mask) == $fn->pattern) {
                return call_user_func($fn->fn, $opcode, $pc, $br);
            }
        }

        throw new Exception(sprintf('Unrecognised opcode: %s', self::printOpcode($opcode)));
    }

    private function parseRlist($data)
    {
        $registers = [];

        for ($i = 0; $i < 16; $i++) {
            if ($data & (1 << $i)) {
                $registers[] = $i;
            }
        }

        return $registers;
    }


    private function readOpcode1($opcode)
    {

        assert((($opcode >> 31) & 0b111) == 0b000);

        $dest = ($opcode >> 0) & 0b111;
        $src = ($opcode >> 3) & 0b111;
        $offset = ($opcode >> 6) & 0b11111;
        $operation = ($opcode >> 11) & 0b11;

        switch ($operation) {
            case 0b00:
                return new MoveShiftedRegister(MoveShiftedRegister::LSL, $dest, $src, $offset);

            case 0b01:
                return new MoveShiftedRegister(MoveShiftedRegister::LSR, $dest, $src, $offset);

            case 0b10:
                return new MoveShiftedRegister(MoveShiftedRegister::ASR, $dest, $src, $offset);
        }

        return null;
    }

    private function readOpcode2($opcode)
    {

        assert((($opcode >> 11) & 0b11111) == 0b00011);

        $dest = ($opcode >> 0) & 0b111;
        $src = ($opcode >> 3) & 0b111;
        $value = ($opcode >> 6) & 0b111;
        $operation = ($opcode >> 9) & 0b1;
        $immediate = ($opcode >> 10) & 0b1;

        if ($operation == 0 && $immediate == 0) {
            return sprintf("add\t%s, %s, %s", $this->formatRegister($dest), $this->formatRegister($src), $this->formatRegister($value));
        } else if ($operation == 0 && $immediate == 1) {
            return sprintf("add\t%s, %s, #%s", $this->formatRegister($dest), $this->formatRegister($src), $value);
        } else if ($operation == 1 && $immediate == 0) {
            return sprintf("sub\t%s, %s, %s", $this->formatRegister($dest), $this->formatRegister($src), $this->formatRegister($value));
        } else if ($operation == 1 && $immediate == 1) {
            return sprintf("sub\t%s, %s, #%s", $this->formatRegister($dest), $this->formatRegister($src), $value);
        }

        return null;
    }

    private function readOpcode3($opcode)
    {

        assert((($opcode >> 13) & 0b111) == 0b001);

        $value = ($opcode >> 0) & 0b11111111;
        $dest = ($opcode >> 8) & 0b111;
        $operation = ($opcode >> 11) & 0b11;

        switch ($operation) {
            case 0b00:
                return sprintf("mov\t%s, #0x%x", $this->formatRegister($dest), $value);

            case 0b01:
                return new CompareImmediate($value, $dest);

            case 0b10:
                return sprintf("add\t%1\$s, %1\$s, #0x%2\$x", $this->formatRegister($dest), $value);

            case 0b11:
                return sprintf("sub\t%1\$s, %1\$s, #0x%2\$x", $this->formatRegister($dest), $value);
        }

        return null;
    }

    private $doubleOperations = [
        0b0000 => 'and',
        0b0001 => 'eor',
        0b1100 => 'orr',
        0b1101 => 'mul',
    ];

    private $operations = [
        0b0010 => 'LSL',
        0b0011 => 'LSR',
        0b0100 => 'ASR',
        0b0101 => 'ADC',
        0b0110 => 'SBC',
        0b0111 => 'ROR',
        0b1000 => 'TST',
        0b1001 => 'neg',
        0b1010 => 'cmp',
        0b1011 => 'CMN',
        0b1110 => 'BIC',
        0b1111 => 'MVN',
    ];

    private function readOpcode4($opcode)
    {
        $dest = ($opcode >> 0) & 0b111;
        $src = ($opcode >> 3) & 0b111;
        $op = ($opcode >> 6) & 0b1111;

        if (isset($this->doubleOperations[$op])) {
            return sprintf("%1\$s\t%2\$s, %2\$s, %3\$s", $this->doubleOperations[$op], $this->formatRegister($dest), $this->formatRegister($src));
        }

        return sprintf("%1\$s\t%2\$s, %3\$s", $this->operations[$op], $this->formatRegister($dest), $this->formatRegister($src));
    }

    private function readOpcode5($opcode)
    {

        assert((($opcode >> 10) & 0b111111) == 0b010001);

        $dest = ($opcode >> 0) & 0b111;
        $src = ($opcode >> 3) & 0b111;
        $h2 = ($opcode >> 6) & 0b1;
        $h1 = ($opcode >> 7) & 0b1;
        $op = ($opcode >> 8) & 0b11;

        switch ($op) {
            case 0b00:
                return sprintf('add %1$s, %1$s, %2$s', $this->formatRegister($dest, $h1), $this->formatRegister($src, $h2));
            case 0b01:
                return sprintf("cmp\t%s, %s", $this->formatRegister($dest, $h1), $this->formatRegister($src, $h2));
            case 0b10:
                return new HiRegisterOperation(HiRegisterOperation::MOV, $dest, $h1, $src, $h2);
            case 0b11:
                return new Branch($src, $h2);
        }
        return null;
    }

    private function readOpcode6($opcode, $pc)
    {
        $value = ($opcode >> 0) & 0b11111111;
        $dest = ($opcode >> 8) & 0b111;

        // The value of the PC will be 4 bytes greater than the address of this instruction, but bit
        //1 of the PC is forced to 0 to ensure it is word aligned.
        $pc = (($pc + 2) & (0XFFFFFFFF ^ 2));
        return new PCRelativeLoad($dest, $pc + ($value << 2));
    }

    private function readOpcode7($opcode)
    {
        $dest = ($opcode >> 0) & 0b111;
        $base = ($opcode >> 3) & 0b111;
        $offset = ($opcode >> 6) & 0b111;
        $b = ($opcode >> 10) & 0b1;
        $l = ($opcode >> 11) & 0b1;

        $cmd = [
            ['str', 'ldr'],
            ['strb', 'ldrn']
        ];

        return sprintf("%s\t%s, [%s, %s]", $cmd[$l][$b], $this->formatRegister($dest), $this->formatRegister($base), $this->formatRegister($offset));
    }

    private function readOpcode8($opcode)
    {
        $dest = ($opcode >> 0) & 0b111;
        $base = ($opcode >> 3) & 0b111;
        $offset = ($opcode >> 6) & 0b111;
        $s = ($opcode >> 10) & 0b1;
        $h = ($opcode >> 11) & 0b1;

        $cmd = [
            ['strh', 'ldrh'],
            ['ldsb', 'ldsh']
        ];

        return sprintf("%s\t%s, [%s, %s]", $cmd[$s][$h], $this->formatRegister($dest), $this->formatRegister($base), $this->formatRegister($offset));

    }

    private function readOpcode9($opcode)
    {
        $dest = ($opcode >> 0) & 0b111;
        $base = ($opcode >> 3) & 0b111;
        $offset = ($opcode >> 6) & 0b11111;
        $load = ($opcode >> 11) & 0b1;
        $byte = ($opcode >> 12) & 0b1;

        $cmds = [
            ['str', 'ldr'],
            ['strb', 'ldrb'],
        ];

        $offsetStr = '';
        if ($offset > 0) {
            if (!$byte) {
                $offset *= 4;
            }
            $offsetStr = sprintf(', #0x%x', $offset);
        }

        return sprintf("%s\t%s, [%s%s]", $cmds[$byte][$load], $this->formatRegister($dest), $this->formatRegister($base), $offsetStr);
    }

    private function readOpcode10($opcode)
    {
        $dest = ($opcode >> 0) & 0b111;
        $base = ($opcode >> 3) & 0b111;
        $offset = ($opcode >> 6) & 0b11111;
        $l = ($opcode >> 11) & 0b1;

        $cmd = ['strh', 'ldrh'];

        $offsetStr = '';
        if ($offset > 0) {
            $offsetStr = sprintf(', #0x%x', $offset * 2);
        }

        return sprintf("%s\t%s, [%s%s]", $cmd[$l], $this->formatRegister($dest), $this->formatRegister($base), $offsetStr);
    }

    private function readOpcode11($opcode)
    {
        $offset = $opcode & 0b11111111;
        $dest = ($opcode >> 8) & 0b111;
        $l = ($opcode >> 11) & 0b1;


        $offsetStr = '';
        if ($offset > 0) {
            $offsetStr = sprintf(', #0x%x', $offset * 4);
        }

        switch ($l) {
            case 0:
                return sprintf("str\t%s, [sp%s]", $this->formatRegister($dest), $offsetStr);
            case 1:
                return sprintf("ldr\t%s, [sp%s]", $this->formatRegister($dest), $offsetStr);
        }
    }

    private function readOpcode12($opcode)
    {

        $offset = $opcode & 0b11111111;
        $dest = ($opcode >> 8) & 0b111;
        $sp = ($opcode >> 11) & 0b1;


        $offsetStr = '';
        if ($offset > 0) {
            $offsetStr = sprintf(', #0x%x', $offset * 4);
        }

        switch ($sp) {
            case 0:
                return sprintf("add\t%s, pc%s", $this->formatRegister($dest), $offsetStr);
            case 1:
                return sprintf("add\t%s, sp%s", $this->formatRegister($dest), $offsetStr);
        }
    }

    private function readOpcode13($opcode)
    {
        assert(($opcode & 0b1111111000000000) == 0b1011000000000000);

        $sword7 = $opcode & 0b1111111;
        $signed = ($opcode >> 7) & 0b1;

        switch ($signed) {
            case 0:
                return sprintf("add\tsp, sp, #0x%x", $sword7 * 4);
            case 1:
                return sprintf("add\tsp, sp, #0x%x", 0xFFFFFFFF + 1 - $sword7 * 4);
        }
    }

    private function readOpcode14($opcode)
    {
        assert(($opcode & 0b1111011000000000) == 0b1011010000000000);

        $rlist = $opcode & 0b11111111;
        $pclr = ($opcode >> 8) & 0b1;
        $loadStore = ($opcode >> 11) & 0b1;

        if ($loadStore == 0 && $pclr == 0) {
            return sprintf("push\t{%s}", $this->readRList($rlist));
        } else if ($loadStore == 0 && $pclr == 1) {
            if ($rlist === 0) {
                return "push\t{lr}";
            }

            return sprintf("push\t{%s, lr}", $this->readRList($rlist));
        } else if ($loadStore == 1 && $pclr == 0) {
            return new PopRegisters($this->parseRlist($rlist));
        } else if ($loadStore == 1 && $pclr == 1) {
            return new PopRegisters($this->parseRlist($rlist), Register::PC);
        }

        return null;
    }

    private function readOpcode15($opcode)
    {
        $registerList = $opcode & 0b11111111;
        $base = ($opcode >> 8) & 0b111;
        $loadStore = ($opcode >> 11) & 0b1;

        switch ($loadStore) {
            case 0:
                return sprintf("stmia\t%s!, {%s}", $this->formatRegister($base), $this->readRList($registerList));
            case 1:
                return sprintf("ldmia\t%s!, {%s}", $this->formatRegister($base), $this->readRList($registerList));
        }

        return null;
    }

    private function readOpcode16($opcode, $pc)
    {
        $offset = $opcode & 0b11111111;
        $cond = ($opcode >> 8) & 0b1111;

        if ($offset & (1 << 7)) {
            $offset = -((~$offset & 0b1111111) + 1);
        }

        return new ConditionalBranch($cond, $pc + 2 + ($offset << 1));
    }

    private function readFormat18($opcode, $pc)
    {
        $offset = $opcode & 0b11111111111;

        if ($offset & (1 << 10)) {
            $offset = -((~$offset & 0b1111111111) + 1);
        }

        return new UnconditionalBranch($pc + 2 + ($offset << 1));
    }

    private function readOpcode17($opcode, $pc)
    {
        $value = $opcode & 0b11111111;

        return sprintf("swi\t#0x%x", $value);
    }

    private function readOpcode19($opcode, $pc, BinaryReader $br)
    {
        $offset = $opcode & 0b11111111111;
        $part = $opcode >> 11 & 0b1;
        if ($part !== 0) {
            throw new Exception(sprintf("Unexpected long branch with H=1 at %x", $pc - 2));
        }

        $opcode2 = $br->readUInt16();
        assert(($opcode2 >> 11) == 0b11111);
        $offset2 = $opcode2 & 0b11111111111;

        $offset = ($offset << 12) + ($offset2 << 1);
        if ($offset & (1 << 22)) {
            $offset = -((~$offset & 0b11111111111111111111111) + 1);
        }

        return new LongBranch($pc + $offset + 2);
    }
}


