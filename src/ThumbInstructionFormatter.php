<?php

namespace MarijnvdWerf\DisAsm;

use Exception;
use MarijnvdWerf\DisAsm\Thumb\Branch;
use MarijnvdWerf\DisAsm\Thumb\CompareImmediate;
use MarijnvdWerf\DisAsm\Thumb\ConditionalBranch;
use MarijnvdWerf\DisAsm\Thumb\HiRegisterOperation;
use MarijnvdWerf\DisAsm\Thumb\Instruction;
use MarijnvdWerf\DisAsm\Thumb\LongBranch;
use MarijnvdWerf\DisAsm\Thumb\MoveShiftedRegister;
use MarijnvdWerf\DisAsm\Thumb\PCRelativeLoad;
use MarijnvdWerf\DisAsm\Thumb\PopRegisters;
use MarijnvdWerf\DisAsm\Thumb\Register;
use MarijnvdWerf\DisAsm\Thumb\UnconditionalBranch;
use RomMap;

class ThumbInstructionFormatter
{

    private $conditions = [
        ConditionalBranch::BEQ => 'beq',
        ConditionalBranch::BNE => 'bne',
        ConditionalBranch::BCS => 'bcs',
        ConditionalBranch::BCC => 'bcc',
        ConditionalBranch::BMI => 'bmi',
        ConditionalBranch::BPL => 'bpl',
        ConditionalBranch::BVS => 'bvs',
        ConditionalBranch::BVC => 'bvc',
        ConditionalBranch::BHI => 'bhi',
        ConditionalBranch::BLS => 'bls',
        ConditionalBranch::BGE => 'bge',
        ConditionalBranch::BLT => 'blt',
        ConditionalBranch::BGT => 'bgt',
        ConditionalBranch::BLE => 'ble'
    ];

    /**
     * @param $opcode string|Instruction
     * @param RomMap $map
     * @return string
     * @throws Exception
     */
    public function format($opcode, $map = null, $labels = [])
    {
        if (is_string($opcode)) {
            return $opcode;
        }

        if ($opcode instanceof LongBranch) {
            $lbl = sprintf("#0x%x", $opcode->address);
            if ($map !== null) {
                $lbl = $map->getLabel($opcode->address);
                if ($lbl === null) {
                    //throw new Exception('Unknown jump');
                    $lbl = sprintf("#0x%x", $opcode->address);
                }
            }
            return sprintf("bl\t%s", $lbl);
        }

        if ($opcode instanceof PCRelativeLoad) {
            $lbl = sprintf("#0x%x", $opcode->address);
            if (count($labels) > 0) {
                $offset = 0;
                while (true) {
                    if (isset($labels[$opcode->address - $offset])) {
                        break;
                    }
                    $offset += 4;
                }
                $lbl = '_' . $labels[$opcode->address - $offset] . ($offset == 0 ? '' : ' + ' . $offset);
            }

            return sprintf("ldr\t%s, %s", $this->formatRegister($opcode->register), $lbl);
        }

        if ($opcode instanceof ConditionalBranch) {
            $lbl = sprintf("#0x%x", $opcode->address);
            if (count($labels) > 0) {
                $lbl = '_' . $labels[$opcode->address];
            }
            return sprintf("%s\t%s\t@cond_branch", $this->conditions[$opcode->condition], $lbl);
        }

        if ($opcode instanceof UnconditionalBranch) {
            $lbl = sprintf("#0x%x", $opcode->address);
            if (count($labels) > 0) {
                $lbl = '_' . $labels[$opcode->address];
            }
            return sprintf("b\t%s", $lbl);
        }

        if ($opcode instanceof LocalLongBranch) {
            $lbl = sprintf("#0x%x", $opcode->address);
            if (count($labels) > 0) {
                $lbl = '_' . $labels[$opcode->address];
            }
            return sprintf("bl\t%s", $lbl);
        }

        if ($opcode instanceof Branch) {
            return sprintf("bx\t%s", $this->formatRegister($opcode->register));
        }

        if ($opcode instanceof HiRegisterOperation) {
            $cmds = [
                HiRegisterOperation::ADD => 'add',
                HiRegisterOperation::CMP => 'cmp',
                HiRegisterOperation::MOV => 'mov'
            ];

            return sprintf("%s\t%s, %s", $cmds[$opcode->operation], $this->formatRegister($opcode->dest), $this->formatRegister($opcode->src));
        }

        if ($opcode instanceof PopRegisters) {
            return sprintf("pop\t{%s}", $this->formatRegisterList($opcode->registers));
        }

        if ($opcode instanceof MoveShiftedRegister) {
            $cmds = [
                MoveShiftedRegister::LSL => 'lsl',
                MoveShiftedRegister::LSR => 'lsr',
                MoveShiftedRegister::ASR => 'asr'
            ];

            return sprintf("%s\t%s, %s, #0x%x", $cmds[$opcode->operation], $this->formatRegister($opcode->dest), $this->formatRegister($opcode->src), $opcode->amount);
        }

        if ($opcode instanceof CompareImmediate) {
            $numberStr = '#0';
            if ($opcode->value != 0) {
                $numberStr = sprintf('#0x%x', $opcode->value);
            }
            return sprintf("cmp\t%s, %s", $this->formatRegister($opcode->register), $numberStr);
        }

        throw new Exception(sprintf("Unformatted opcode: %s", get_class($opcode)));
    }

    public function formatRegister($register)
    {
        return $this->registers[$register];
    }

    public function formatRegisterList($list)
    {
        $registers = array_map(function ($i) {
            return $this->registers[$i];
        }, $list);

        return implode(', ', $registers);
    }

    private $registers = [
        Register::R0 => 'r0',
        Register::R1 => 'r1',
        2 => 'r2',
        3 => 'r3',
        4 => 'r4',
        5 => 'r5',
        6 => 'r6',
        7 => 'r7',
        8 => 'r8',
        9 => 'r9',
        10 => 'sl',
        11 => 'fp',
        12 => 'ip',
        13 => 'sp',
        Register::LR => 'lr',
        Register::PC => 'pc',
    ];
}
