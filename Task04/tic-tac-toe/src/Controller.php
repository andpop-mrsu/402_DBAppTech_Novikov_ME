<?php

namespace Darkflade\TicTacToe\Controller;

use Darkflade\TicTacToe\AI\Board;
use Darkflade\TicTacToe\AI\Computer;
use Darkflade\TicTacToe\View;
use Darkflade\TicTacToe\DB\Database;

function startGame()
{

    $db = new Database();
    $db->init();

    $playerName = View::askPlayerName();
    $N = View::askBoardSize();
    $M = min(($N <= 3) ? 3 : 5, $N);


    $board = new Board($N);
    $ai = new Computer($board, $M);

    $playerSymbol = rand(0, 1) ? 'X' : 'O';
    $computerSymbol = ($playerSymbol === 'X') ? 'O' : 'X';
    $turn = ($playerSymbol === 'X') ? $playerSymbol : $computerSymbol;

    // Variables for saving the game
    $moves = [];
    $moveNum = 1;
    $winner = null;

    while (true) {
        View::renderBoard($board);

        if ($turn === $playerSymbol) {
            [$x, $y] = View::askMove($board);
            $board->set($x, $y, $playerSymbol);
        } else {
            [$x, $y] = $ai->chooseMove($board);
            $board->set($x, $y, $computerSymbol);
        }

        $moves[] = [
            'num' => $moveNum,
            'x' => $x,
            'y' => $y,
            'symbol' => $turn
        ];
        $moveNum++;

        if ($board->winAt($x, $y, $M)) {
            View::renderBoard($board);
            View::renderResult("$turn победил!");
            $winner = $turn;
            break;
        }

        if ($board->isDraw()) {
            View::renderBoard($board);
            View::renderResult("Ничья!");
            $winner = "DRAW";
            break;
        }

        $turn = ($turn === $playerSymbol) ? $computerSymbol : $playerSymbol;
    }

    $formattedMoves = implode("\n", array_map(fn($m) => "{$m['num']}|{$m['x']}|{$m['y']}", $moves));
    $jsonMoves = json_encode($moves, JSON_UNESCAPED_UNICODE);

    $db->saveGame([
        'board_size' => $N,
        'date' => date('Y-m-d H:i:s'),
        'player_name' => $playerName,
        'human_symbol' => $playerSymbol,
        'winner_symbol' => $winner,
        'moves_formatted' => $formattedMoves,
        'moves_json' => $jsonMoves
    ]);
}
