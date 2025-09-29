<?php

namespace Darkflade\TicTacToe\DB;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;
    private string $file;

    public function __construct(string $file = null)
    {
        $this->file = $file ?? __DIR__ . '/database.sqlite';
        $this->pdo = new PDO('sqlite:' . $this->file);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function init(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS games (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                board_size INTEGER NOT NULL,
                date TEXT NOT NULL,
                player_name TEXT NOT NULL,
                human_symbol TEXT NOT NULL,
                winner_symbol TEXT,
                moves_formatted TEXT,   
                moves_json TEXT         
            );
        SQL;
        $this->pdo->exec($sql);
    }

    public function saveGame(array $data): void
    {
        $sql = "INSERT INTO games 
            (board_size, date, player_name, human_symbol, winner_symbol, moves_formatted, moves_json)
            VALUES (:board_size, :date, :player_name, :human_symbol, :winner_symbol, :moves_formatted, :moves_json)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    public function listGames(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, board_size, date, player_name, human_symbol, winner_symbol 
            FROM games ORDER BY date DESC
            ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGame(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM games WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);
        return $game ?: null;
    }

    private function coordFromString(string $s): array
    {
        [$x, $y] = explode(',', $s);
        return [(int)$x, (int)$y];
    }
}
