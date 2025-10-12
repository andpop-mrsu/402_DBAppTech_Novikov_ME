<?php

namespace Darkflade\TicTacToe\DB;

use RedBeanPHP\R;

class Database
{
    private string $file;

    // Singletone construct
    public function __construct(string $file = null)
    {
        $this->file = $file ?? __DIR__ . '/database.sqlite';

        if (!\RedBeanPHP\R::testConnection()) {
            \RedBeanPHP\R::setup('sqlite:' . $this->file);
            \RedBeanPHP\R::ext('xdispense', function ($type) {
                return \RedBeanPHP\R::getRedBean()->dispense($type);
            });
        }
    }

    public function saveGame(array $data): void
    {
        $game = R::xdispense('games');
        $game->board_size = $data['board_size'];
        $game->date = $data['date'];
        $game->player_name = $data['player_name'];
        $game->human_symbol = $data['human_symbol'];
        $game->winner_symbol = $data['winner_symbol'] ?? null;
        $game->moves_formatted = $data['moves_formatted'] ?? null;
        $game->moves_json = $data['moves_json'] ?? null;
        R::store($game);
    }

    public function listGames(): array
    {
        $games = R::findAll('games', ' ORDER BY date DESC ');
        $result = [];
        foreach ($games as $g) {
            $result[] = [
                'id' => $g->id,
                'board_size' => $g->board_size,
                'date' => $g->date,
                'player_name' => $g->player_name,
                'human_symbol' => $g->human_symbol,
                'winner_symbol' => $g->winner_symbol
            ];
        }
        return $result;
    }

    public function getGame(int $id): ?array
    {
        $game = R::load('games', $id);
        if ($game->id === 0) {
            return null;
        }
        return [
            'id' => $game->id,
            'board_size' => $game->board_size,
            'date' => $game->date,
            'player_name' => $game->player_name,
            'human_symbol' => $game->human_symbol,
            'winner_symbol' => $game->winner_symbol,
            'moves_formatted' => $game->moves_formatted,
            'moves_json' => $game->moves_json
        ];
    }

    private function coordFromString(string $s): array
    {
        [$x, $y] = explode(',', $s);
        return [(int)$x, (int)$y];
    }
}
