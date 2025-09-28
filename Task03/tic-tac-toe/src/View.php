<?php

namespace Darkflade\TicTacToe;

use function cli\line;
use function cli\prompt;
use function cli\err;

class View
{
    public static function showStartScreen(): void
    {
        line("%GДобро пожаловать в игру Tic-Tac-Toe!%n\n"); // зелёный текст
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
        line(""); // пустая строка
        for ($i = 0; $i < $n; $i++) {
            $row = [];
            for ($j = 0; $j < $n; $j++) {
                $row[] = $board->get($i, $j) ?: ".";
            }
            line(implode(" | ", $row));
            if ($i < $n - 1) {
                line(str_repeat("---+", $n - 1) . "---");
            }
        }
        line("");
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

            $x = (int)$x - 1; // приводим к 0-индексации
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
}
