<?php

namespace Darkflade\TicTacToe\AI;

class Computer
{
    private int $M; // win length (3 or 5)
    private int $N;
    private Board $board;
    private array $memo = []; // memoization: hash->score

    public function __construct(Board $board, int $M = 5)
    {
        $this->board = $board;
        $this->M = $M;
        $this->N = $board->N;
    }

    public function chooseMove(Board $board, int $depth = 3): array
    {
        $this->N = $board->N;
        $best = [-1,-1];
        $bestScore = PHP_INT_MIN;
        $cells = $board->getEmptyAdjacentCells();

        if (count($cells) < 1) {
            for ($i = 0; $i < $this->N; $i++) {
                for ($j = 0; $j < $this->N; $j++) {
                    if ($board->isEmpty($i, $j)) {
                        $cells[] = [$i,$j];
                    }
                }
            }
        }

        foreach ($cells as $c) {
            [$x,$y] = $c;
            $board->set($x, $y, $board->computerSymbol);
            if ($board->winAt($x, $y, $this->M)) {
                $board->set($x, $y, '.');
                return [$x,$y];
            }
            $score = $this->minimaxAlphaBeta($board, $depth - 1, false, PHP_INT_MIN, PHP_INT_MAX);
            $board->set($x, $y, '.');

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = [$x,$y];
            }
        }

        if ($best[0] === -1) {
            for ($i = 0; $i < $this->N; $i++) {
                for ($j = 0; $j < $this->N; $j++) {
                    if ($board->isEmpty($i, $j)) {
                        return [$i,$j];
                    }
                }
            }
        }

        return $best;
    }

    // minimax с alpha-beta; isMax==true значит очередь компьютера
    private function minimaxAlphaBeta(Board $board, int $depth, bool $isMax, int $alpha, int $beta): int
    {
        $hash = $board->toString() . ($isMax ? '1' : '0') . $depth;
        if (isset($this->memo[$hash])) {
            return $this->memo[$hash];
        }

        for ($i = 0; $i < $this->N; $i++) {
            for ($j = 0; $j < $this->N; $j++) {
                if ($board->get($i, $j) !== '.' && $board->winAt($i, $j, $this->M)) {
                    $val = ($board->get($i, $j) === $board->computerSymbol) ? PHP_INT_MAX : PHP_INT_MIN;
                    $this->memo[$hash] = $val;
                    return $val;
                }
            }
        }
        if ($board->isDraw()) {
            $this->memo[$hash] = 0;
            return 0;
        }
        if ($depth <= 0) {
            $v = $this->evaluate($board);
            $this->memo[$hash] = $v;
            return $v;
        }

        $cells = $board->getEmptyAdjacentCells();
        if (empty($cells)) {
            for ($i = 0; $i < $this->N; $i++) {
                for ($j = 0; $j < $this->N; $j++) {
                    if ($board->isEmpty($i, $j)) {
                        $cells[] = [$i,$j];
                    }
                }
            }
        }

        if ($isMax) {
            $value = PHP_INT_MIN;
            foreach ($cells as $c) {
                [$x,$y] = $c;
                $board->set($x, $y, $board->computerSymbol);
                $val = $this->minimaxAlphaBeta($board, $depth - 1, false, $alpha, $beta);
                $board->set($x, $y, '.');
                if ($val > $value) {
                    $value = $val;
                }
                if ($value > $alpha) {
                    $alpha = $value;
                }
                if ($alpha >= $beta) {
                    break;
                }
            }
            $this->memo[$hash] = $value;
            return $value;
        } else {
            $value = PHP_INT_MAX;
            foreach ($cells as $c) {
                [$x,$y] = $c;
                $board->set($x, $y, $board->playerSymbol);
                $val = $this->minimaxAlphaBeta($board, $depth - 1, true, $alpha, $beta);
                $board->set($x, $y, '.');
                if ($val < $value) {
                    $value = $val;
                }
                if ($value < $beta) {
                    $beta = $value;
                }
                if ($alpha >= $beta) {
                    break;
                }
            }
            $this->memo[$hash] = $value;
            return $value;
        }
    }

    private function evaluate(Board $board): int
    {
        $M = $this->M;
        $N = $this->N;
        $comp = $board->computerSymbol;
        $pl = $board->playerSymbol;

        $countsComp = array_fill(0, $M + 1, array_fill(0, 3, 0));
        $countsPl = array_fill(0, $M + 1, array_fill(0, 3, 0));

        $dirs = [[1,0],[0,1],[1,1],[1,-1]];

        for ($i = 0; $i < $N; $i++) {
            for ($j = 0; $j < $N; $j++) {
                $c = $board->get($i, $j);
                if ($c === '.') {
                    continue;
                }
                foreach ($dirs as $d) {
                    $px = $i - $d[0];
                    $py = $j - $d[1];
                    if ($px >= 0 && $py >= 0 && $px < $N && $py < $N && $board->get($px, $py) === $c) {
                        continue;
                    }

                    $len = 0;
                    $x = $i;
                    $y = $j;
                    while ($x >= 0 && $y >= 0 && $x < $N && $y < $N && $board->get($x, $y) === $c) {
                        $len++;
                        $x += $d[0];
                        $y += $d[1];
                    }
                    if ($len <= 0) {
                        continue;
                    }
                    // endpoints
                    $leftFree = false;
                    $rightFree = false;
                    $lx = $i - $d[0];
                    $ly = $j - $d[1];
                    if (!($lx >= 0 && $ly >= 0 && $lx < $N && $ly < $N) || $board->get($lx, $ly) === $c) {
                        // left is blocked by boundary or same symbol => not free
                        $leftFree = false;
                    } else {
                        $leftFree = ($board->get($lx, $ly) === '.');
                    }
                    $rx = $i + $d[0] * $len;
                    $ry = $j + $d[1] * $len;
                    if (!($rx >= 0 && $ry >= 0 && $rx < $N && $ry < $N) || $board->get($rx, $ry) === $c) {
                        $rightFree = false;
                    } else {
                        $rightFree = ($board->get($rx, $ry) === '.');
                    }

                    $type = 0;
                    if ($leftFree && $rightFree) {
                        $type = 2;
                    } elseif ($leftFree || $rightFree) {
                        $type = 1;
                    } else {
                        $type = 0;
                    }

                    if ($len >= $M) {
                        // immediate win
                        return ($c === $comp) ? PHP_INT_MAX : PHP_INT_MIN;
                    }
                    // increment appropriate counter
                    if ($c === $comp) {
                        $countsComp[$len][$type]++;
                    } else {
                        $countsPl[$len][$type]++;
                    }
                }
            }
        }

        // weights: tuned heuristics
        // open (both ends) is much stronger than semi-open
        $score = 0;
        // base multiplier progression - exponential growth with len
        $base = 100;
        for ($len = 1; $len < $M; $len++) {
            $wOpen = intval(pow($base, $len - 1) * 50);    // both ends free
            $wSemi = intval(pow($base, $len - 1) * 10);    // one end free
            $wClosed = intval(pow($base, $len - 1) * 1);   // closed, minor value

            $score += $countsComp[$len][2] * $wOpen;
            $score += $countsComp[$len][1] * $wSemi;
            $score += $countsComp[$len][0] * $wClosed;

            $score -= $countsPl[$len][2] * intval($wOpen * 1.2); // opponent open is slightly more dangerous
            $score -= $countsPl[$len][1] * intval($wSemi * 1.1);
            $score -= $countsPl[$len][0] * $wClosed;
        }

        // small normalization to keep numbers sane
        if ($score > 0 && $score < 1000) {
            $score += 1;
        }
        if ($score < 0 && $score > -1000) {
            $score -= 1;
        }

        return $score;
    }
}
