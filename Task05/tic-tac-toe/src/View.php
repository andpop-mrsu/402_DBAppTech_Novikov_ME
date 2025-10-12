<?php

namespace Darkflade\TicTacToe;

use function cli\line;
use function cli\prompt;
use function cli\err;

class View
{
    public static function showStartScreen(): void
    {
        line("%GДобро пожаловать в смертельную битву в игре Tic-Tac-Toe!%n\n"); // зелёный текст
    }

    public static function askBoardSize(): int
    {
        $size = (int) prompt("Введите размер поля (от 3 до 10)", 3);
        if ($size < 3) {
            $size = 3;
        }
        if ($size > 10) {
            $size = 10;
        }
        return $size;
    }

    public static function renderBoard($board): void
    {
        $n = $board->getSize();
        $lineLength = 4 * $n - 1;
        line("+" . str_repeat('-', $lineLength) . "+");

        for ($i = 0; $i < $n; $i++) {
            $row = [];
            for ($j = 0; $j < $n; $j++) {
                $cell = $board->get($i, $j);
                switch ($cell) {
                    case 'X':
                        $row[] = "\e[0;92;49mX\e[0m";
                        break;
                    case 'O':
                        $row[] = "\e[0;36;49mO\e[0m";
                        break;
                    default:
                        $row[] = "\e[37m.\e[0m";
                }
            }
            line("| " . implode(" | ", $row) . " |");

            if ($i < $n - 1) {
                $sep = [];
                for ($k = 0; $k < $n; $k++) {
                    $sep[] = "---";
                }
                line("+" . implode("+", $sep) . "+");
            }
        }

        line("+" . str_repeat('-', $lineLength) . "+");
        line("\n");
    }

    public static function askMove($board): array
    {
        $N = $board->getSize();
        while (true) {
            $input = trim(prompt("Введите координаты через пробел (строка столбец), от 1 до $N"));
            $parts = preg_split('/\s+/', $input);

            if (count($parts) !== 2) {
                err("%RНужно ввести два числа через пробел.%n");
                continue;
            }

            [$x, $y] = $parts;

            if (!ctype_digit($x) || !ctype_digit($y)) {
                err("%RКоординаты должны быть числами.%n");
                continue;
            }

            $x = (int)$x - 1;
            $y = (int)$y - 1;

            if ($x < 0 || $x >= $N || $y < 0 || $y >= $N) {
                err("%RКоординаты вне диапазона 1..$N.%n");
                continue;
            }

            if (!$board->isEmpty($x, $y)) {
                err("%RЭта клетка уже занята.%n");
                continue;
            }

            return [$x, $y];
        }
    }

    public static function renderResult(string $msg): void
    {
        line("%B$msg%n\n");
    }

    public static function askPlayerName(): string
    {
        $name = trim(prompt("Как тебя величать, Герой?", "Hero"));
        return $name !== "" ? $name : "Hero";
    }
}
