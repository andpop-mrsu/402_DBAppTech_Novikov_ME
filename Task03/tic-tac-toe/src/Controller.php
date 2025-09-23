<?php

namespace Darkflade\TicTacToe\Controller;

use Darkflade\TicTacToe\AI\Board;
use Darkflade\TicTacToe\AI\Computer;
use Darkflade\TicTacToe\View;

function startGame()
{

    $N = View::askBoardSize();
    $M = min(($N <= 3) ? 3 : 5, $N);


    $board = new Board($N);
    $ai = new Computer($board, $M);

    $playerSymbol = rand(0, 1) ? 'X' : 'O';
    $computerSymbol = ($playerSymbol === 'X') ? 'O' : 'X';

    $turn = ($playerSymbol === 'X') ? $playerSymbol : $computerSymbol;

    while (true) {
        View::renderBoard($board);

        if ($turn === $playerSymbol) {
            [$x, $y] = View::askMove($board);
            $board->set($x, $y, $playerSymbol);
        } else {
            [$x, $y] = $ai->chooseMove($board);
            $board->set($x, $y, $computerSymbol);
        }

        if ($board->winAt($x, $y, $M)) {
            View::renderBoard($board);
            View::renderResult("$turn победил!");
            break;
        }

        if ($board->isDraw()) {
            View::renderBoard($board);
            View::renderResult("Ничья!");
            break;
        }

        $turn = ($turn === $playerSymbol) ? $computerSymbol : $playerSymbol;
    }
}
