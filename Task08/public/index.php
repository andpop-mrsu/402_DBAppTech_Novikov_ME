<?php
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|html)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.html') {
    readfile(__DIR__ . '/index.html');
    exit;
}

$dbFile = __DIR__ . '/../db/database.sqlite';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $dbDir = dirname($dbFile);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0777, true);
    }

    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS games (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        date TEXT,
        player_name TEXT,
        board_size INTEGER,
        human_symbol TEXT,
        winner_symbol TEXT DEFAULT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS moves (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        game_id INTEGER,
        move_num INTEGER,
        x INTEGER,
        y INTEGER,
        symbol TEXT,
        FOREIGN KEY(game_id) REFERENCES games(id)
    )");

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

$inputData = json_decode(file_get_contents('php://input'), true);

try {
    if ($method === 'GET' && isset($pathParts[0]) && $pathParts[0] === 'games' && !isset($pathParts[1])) {
        $stmt = $pdo->query("SELECT * FROM games ORDER BY id DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    elseif ($method === 'GET' && $pathParts[0] === 'games' && isset($pathParts[1])) {
        $gameId = (int)$pathParts[1];
        
        $stmtGame = $pdo->prepare("SELECT * FROM games WHERE id = ?");
        $stmtGame->execute([$gameId]);
        $game = $stmtGame->fetch(PDO::FETCH_ASSOC);

        if ($game) {
            $stmtMoves = $pdo->prepare("SELECT move_num, x, y, symbol FROM moves WHERE game_id = ? ORDER BY move_num ASC");
            $stmtMoves->execute([$gameId]);
            $moves = $stmtMoves->fetchAll(PDO::FETCH_ASSOC);
            
            $game['moves'] = $moves;
            echo json_encode($game);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Game not found']);
        }
    }

    elseif ($method === 'POST' && $pathParts[0] === 'games') {
        if (!$inputData) throw new Exception("No input data");
        
        $sql = "INSERT INTO games (date, player_name, board_size, human_symbol, winner_symbol) VALUES (:date, :player_name, :board_size, :human_symbol, :winner_symbol)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':date' => $inputData['date'],
            ':player_name' => $inputData['player_name'],
            ':board_size' => $inputData['board_size'],
            ':human_symbol' => $inputData['human_symbol'],
            ':winner_symbol' => null
        ]);
        
        echo json_encode(['id' => $pdo->lastInsertId()]);
    }

    elseif ($method === 'POST' && $pathParts[0] === 'step' && isset($pathParts[1])) {
        $gameId = (int)$pathParts[1];
        if (!$inputData) throw new Exception("No input data");

        if ($inputData['x'] == -1 && isset($inputData['winner'])) {
        } else {
            $sql = "INSERT INTO moves (game_id, move_num, x, y, symbol) VALUES (:game_id, :move_num, :x, :y, :symbol)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':game_id' => $gameId,
                ':move_num' => $inputData['num'],
                ':x' => $inputData['x'],
                ':y' => $inputData['y'],
                ':symbol' => $inputData['symbol']
            ]);
        }

        if (isset($inputData['winner'])) {
            $stmtUpd = $pdo->prepare("UPDATE games SET winner_symbol = ? WHERE id = ?");
            $stmtUpd->execute([$inputData['winner'], $gameId]);
        }

        echo json_encode(['status' => 'ok']);
    }
    else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found', 'path' => $path]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}