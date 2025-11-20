const DB_NAME = 'TicTacToeDB';
const DB_VERSION = 1;
const STORE_NAME = 'games';

let db;

function openDB() {
    return new Promise((resolve, reject) => {
        if (db) {
            return resolve(db);
        }

        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onerror = (event) => {
            console.error("Database error:", event.target.error);
            reject("Database error");
        };

        request.onsuccess = (event) => {
            db = event.target.result;
            resolve(db);
        };

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

export async function saveGame(gameData) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(STORE_NAME, 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.add(gameData);

        request.onsuccess = () => resolve(request.result);
        request.onerror = (event) => {
            console.error("Error saving game:", event.target.error);
            reject("Error saving game");
        };
    });
}

export async function listGames() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(STORE_NAME, 'readonly');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result.reverse());
        request.onerror = (event) => {
            console.error("Error listing games:", event.target.error);
            reject("Error listing games");
        };
    });
}

export async function getGameById(id) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(STORE_NAME, 'readonly');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.get(id);

        request.onsuccess = () => resolve(request.result);
        request.onerror = (event) => {
            console.error(`Error getting game with id ${id}:`, event.target.error);
            reject("Error getting game");
        };
    });
}