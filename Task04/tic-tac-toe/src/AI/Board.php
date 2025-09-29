<?php

namespace Darkflade\TicTacToe\AI;

class Board
{
    public int $N;
    public array $playerMask;
    public array $computerMask;
    public string $playerSymbol;
    public string $computerSymbol;

    public function getSize(): int
    {
        return $this->N;
    }

    public function __construct(int $N = 5, string $player = 'X', string $computer = 'O')
    {
        $this->N = $N;
        $this->playerSymbol = $player;
        $this->computerSymbol = $computer;
        $this->playerMask = array_fill(0, $N, 0);
        $this->computerMask = array_fill(0, $N, 0);
    }

    public function get(int $x, int $y): string
    {
        $bit = 1 << $y;
        if (($this->playerMask[$x] & $bit) !== 0) {
            return $this->playerSymbol;
        }
        if (($this->computerMask[$x] & $bit) !== 0) {
            return $this->computerSymbol;
        }
        return '.';
    }

    public function set(int $x, int $y, string $sym): void
    {
        $bit = 1 << $y;
        if ($sym === $this->playerSymbol) {
            $this->playerMask[$x] |= $bit;
            $this->computerMask[$x] &= (~$bit);
        } elseif ($sym === $this->computerSymbol) {
            $this->computerMask[$x] |= $bit;
            $this->playerMask[$x] &= (~$bit);
        } else {
            $this->playerMask[$x] &= (~$bit);
            $this->computerMask[$x] &= (~$bit);
        }
    }

    public function isEmpty(int $x, int $y): bool
    {
        $bit = 1 << $y;
        return (($this->playerMask[$x] | $this->computerMask[$x]) & $bit) === 0;
    }

    public function clone(): Board
    {
        $b = new Board($this->N, $this->playerSymbol, $this->computerSymbol);
        $b->playerMask = $this->playerMask;
        $b->computerMask = $this->computerMask;
        return $b;
    }

    public function getEmptyAdjacentCells(): array
    {
        $res = [];
        $seen = [];
        $adj = [[-1,-1], [-1,0], [-1,1], [0,1], [0,-1], [1,-1], [1,0], [1,1]];
        for ($i = 0; $i < $this->N; $i++) {
            $rowFilled = $this->playerMask[$i] | $this->computerMask[$i];
            if ($rowFilled === 0) {
                continue;
            }
            for ($j = 0; $j < $this->N; $j++) {
                if ((($rowFilled >> $j) & 1) === 1) {
                    foreach ($adj as $d) {
                        $x = $i + $d[0];
                        $y = $j + $d[1];
                        if ($x < 0 || $y < 0 || $x >= $this->N || $y >= $this->N) {
                            continue;
                        }
                        if ($this->isEmpty($x, $y)) {
                            $seen["$x,$y"] = [$x, $y];
                        }
                    }
                }
            }
        }
        if (empty($seen)) {
            $c = intdiv($this->N, 2);
            return [[$c,$c]];
        }
        return array_values($seen);
    }

    public function winAt(int $x, int $y, int $M): bool
    {
        $sym = $this->get($x, $y);
        if ($sym === '.') {
            return false;
        }
        $dirs = [[1,0],[0,1],[1,1],[1,-1]];

        $dirs = [[1,0],[0,1],[1,1],[1,-1]];
        foreach ($dirs as $d) {
            $cnt = 1;
            for ($k = 1; $k < $M; $k++) {
                $nx = $x + $d[0] * $k;
                $ny = $y + $d[1] * $k;
                if ($nx >= 0 && $ny >= 0 && $nx < $this -> N && $ny < $this -> N && $this -> get($nx, $ny) === $sym) {
                    $cnt++;
                } else {
                    break;
                }
            }
            for ($k = 1; $k < $M; $k++) {
                $nx = $x - $d[0] * $k;
                $ny = $y - $d[1] * $k;
                if ($nx >= 0 && $ny >= 0 && $nx < $this -> N && $ny < $this -> N && $this -> get($nx, $ny) === $sym) {
                    $cnt++;
                } else {
                    break;
                }
            }
            if ($cnt >= $M) {
                return true;
            }
        }
        return false;
    }

    public function isDraw(): bool
    {
        for ($i = 0; $i < $this->N; $i++) {
            $rowFilled = $this->playerMask[$i] | $this->computerMask[$i];
            for ($j = 0; $j < $this->N; $j++) {
                if ((($rowFilled >> $j) & 1) === 0) {
                    return false;
                }
            }
        }
        return true;
    }

    public function toString(): string
    {
        $s = '';
        for ($i = 0; $i < $this -> N; $i++) {
            for ($j = 0; $j < $this -> N; $j++) {
                $s .= $this -> get($i, $j);
            }
        }
        return $s;
    }
}
