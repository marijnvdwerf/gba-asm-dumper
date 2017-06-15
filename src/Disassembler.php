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
use PhpBinaryReader\BinaryReader;

/**
 * @property ThumbOpCodeReader opcodeReader
 */
class Disassembler
{

    private function getCodeblock(&$lines, BinaryReader $br, $fnStart, $fnEnd)
    {
        while (true) {
            $address = $br->getPosition();

            if (isset($lines[$address])) {
                break;
            }

            $opcode = $this->opcodeReader->readOpcode($br);
            if ($opcode === null) {
                throw new Exception("Unhandled opcode");
            }

            $lines[$address] = $opcode;

            if ($opcode instanceof Branch) {
                break;
            }

            if ($opcode instanceof UnconditionalBranch) {
                break;
            }

            if ($opcode instanceof PopRegisters && in_array(Register::PC, $opcode->registers)) {
                break;
            }

            if ($opcode instanceof LongBranch && $opcode->address > $fnStart && $opcode->address < $fnEnd - 2) {
                $lines[$address] = new LocalLongBranch($opcode->address);
                break;
            }

            if ($opcode instanceof HiRegisterOperation && $opcode->dest == Register::PC) {
                break;
            }
        }
    }

    /**
     * @param $lines Instruction[]
     *
     * @return int[]
     */
    private static function getMissingCodeBlocks($lines)
    {
        $codeAddresses = [];

        foreach ($lines as $instruction) {
            if ($instruction instanceof ConditionalBranch) {
                $codeAddresses[] = $instruction->address;
            }
            if ($instruction instanceof UnconditionalBranch) {
                $codeAddresses[] = $instruction->address;
            }
            if ($instruction instanceof LocalLongBranch) {
                $codeAddresses[] = $instruction->address;
            }

            if ($instruction instanceof OffsetData) {
                $codeAddresses[] = $instruction->value;
            }
        }

        $codeAddresses = array_unique($codeAddresses);
        sort($codeAddresses);

        $missing = [];
        foreach ($codeAddresses as $address) {
            if (isset($lines[$address])) {
                continue;
            }

            $missing[] = $address;
        }

        return $missing;
    }

    /**
     * @param $lines Instruction[]
     *
     * @return int[]
     */
    private static function getMissingDataOffsets($lines)
    {
        $dataAddresses = [];

        foreach ($lines as $instruction) {
            if ($instruction instanceof PCRelativeLoad) {
                $dataAddresses[] = $instruction->address;
            }
        }

        $dataAddresses = array_unique($dataAddresses);
        sort($dataAddresses);

        $missing = [];
        foreach ($dataAddresses as $address) {
            if (isset($lines[$address])) {
                continue;
            }

            $missing[] = $address;
        }

        return $missing;
    }

    private function findMissingLines(BinaryReader $br, $fnStart, $fnEnd, &$lines)
    {
        while (true) {
            $missingCode = self::getMissingCodeBlocks($lines);
            $missingData = self::getMissingDataOffsets($lines);

            if (count($missingCode) == 0 && count($missingData) == 0) {
                break;
            }

            foreach ($missingCode as $address) {
                $br->setPosition($address);
                $this->getCodeblock($lines, $br, $fnStart, $fnEnd);
            }

            foreach ($missingData as $address) {
                $br->setPosition($address);
                $lines[$address] = new Data($br->readUInt32());
            }
        }
    }

    private function findInstructionsLeadingTo($lines, $address)
    {
        $instructions = [];

        foreach ($lines as $l => $line) {
            if ($l === $address - 4 && $line instanceof LongBranch) {
                $instructions[$l] = $line;
                continue;
            }

            if ($l === $address - 2 && !($line instanceof UnconditionalBranch)) {
                $instructions[$l] = $line;
                continue;
            }

            if ($line instanceof ConditionalBranch && $line->address == $address) {
                $instructions[$l] = $line;
                continue;
            }

            if ($line instanceof UnconditionalBranch && $line->address == $address) {
                $instructions[$l] = $line;
                continue;
            }

            if ($line instanceof LocalLongBranch && $line->address == $address) {
                $instructions[$l] = $line;
                continue;
            }
        }

        return $instructions;
    }

    private function findJumpTable(&$lines, &$out)
    {
        /** @var HiRegisterOperation[] $operations */
        $operations = [];

        foreach ($lines as $a => $line) {
            if ($line instanceof HiRegisterOperation && $line->dest == Register::PC) {
                if ($line->src == Register::LR) {
                    continue;
                }

                $operations[$a] = $line;
            }
        }

        if (count($operations) == 0) {
            return false;
        }


        $tables = [];

        foreach ($operations as $address => $jumpOp) {
            $src = $jumpOp->src;

            Assert::equals($lines[$address - 2], "ldr\tr0, [r0]");
            Assert::equals($lines[$address - 4], "add\tr0, r0, r1");
            Assert::isInstanceOf($lines[$address - 6], PCRelativeLoad::class);

            /** @var PCRelativeLoad $load */
            $load = $lines[$address - 6];

            $shiftOpAddress = $address - 8;
            Assert::isInstanceOf($lines[$shiftOpAddress], MoveShiftedRegister::class);
            Assert::equals($lines[$shiftOpAddress]->operation, MoveShiftedRegister::LSL);
            Assert::equals($lines[$shiftOpAddress]->dest, Register::R0);
            Assert::equals($lines[$shiftOpAddress]->amount, 2);
            $caseRegister = $lines[$shiftOpAddress]->src;

            $instructions = $this->findInstructionsLeadingTo($lines, $shiftOpAddress);
            if (count($instructions) == 0) {
                throw new Exception(sprintf('Found no instructions leading into %x', $shiftOpAddress));
            } else if (count($instructions) > 1) {
                throw new Exception(sprintf('Found more than one instruction leading into %x', $shiftOpAddress));
            }

            $referringAddress = array_keys($instructions)[0];
            $referringInstruction = array_values($instructions)[0];

            /** @var CompareImmediate $comparison */
            $comparison = $lines[$referringAddress - 2];

            $count = -1;
            if ($referringInstruction->address == $shiftOpAddress) {
                switch ($referringInstruction->condition) {
                    case ConditionalBranch::BLS:
                        $count = $comparison->value + 1;
                        break;
                    default:
                        throw new Exception("Unhandled branch type");
                        break;
                }
            } else {
                switch ($referringInstruction->condition) {
                    case ConditionalBranch::BHI:
                        $count = $comparison->value + 1;
                        break;
                    default:
                        error_log("== " . $this->formatInstruction($referringInstruction));
                        break;
                }
            }

            $tables[] = new JumpTable($load->address, $count);
        }

        $out = $tables;

        return true;
    }

    public function disassemble(BinaryReader $br, $fnStart, $fnEnd)
    {
        $this->opcodeReader = new ThumbOpCodeReader();

        $lines = [];
        $this->getCodeblock($lines, $br, $fnStart, $fnEnd);

        $this->findMissingLines($br, $fnStart, $fnEnd, $lines);

        if ($this->findJumpTable($lines, $out)) {
            foreach ($out as $o) {
                $br->setPosition($o->address);
                $lines[$o->address] = new OffsetData($br->readUInt32());

                $tableStart = $lines[$o->address]->value;
                $br->setPosition($tableStart);
                for ($i = 0; $i < $o->count; $i++) {
                    $lines[$tableStart + $i * 4] = new OffsetData($br->readUInt32());
                }
            }

            $this->findMissingLines($br, $fnStart, $fnEnd, $lines);
        }

        ksort($lines);
        return $lines;
    }

}
