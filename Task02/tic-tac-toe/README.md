# Tic-Tac-Toe

Консольная игра «Крестики-нолики» на PHP.
 - работает через командную строку;
   - использует символы "X" и "O" для отображения фигур;
   - координаты ячеек вводятся через два числа, разделённых пробелом (номер строки и номер столбца);
   - режимы работы приложения определяются через аргументы командной строки:
     - `--new` (`-n`) — новая игра (режим по умолчанию);
     - `--list` (`-l`) — вывод списка всех сохранённых партий;
     - `--replay id` (`-r id`) — воспроизведение игры с указанным идентификатором;
     - `--help` (`-h`) — краткая справка о формате ввода и доступных ключах.

Все ходы и результаты партий сохраняются в базу данных SQLite, что позволяет просматривать историю игр и воспроизводить их.

---

## Требования окружения

- **PHP** ≥ 8.4  
  Проверено на версии:
  PHP 8.4.12 (cli) (ZTS Visual C++ 2022 x64)
  Zend Engine v4.4.12

- **Composer** ≥ 2.8  
  Проверено на версии:
  Composer - Dependency Manager for PHP - version 2.8.11

---

## Установка и запуск

1. Клонировать репозиторий:
 ```bash
 git clone https://github.com/darkflade/tic-tac-toe.git
 cd tic-tac-toe
 ```

2. Установить зависимости:
```bash
composer install
```
3. Сгенерировать автозагрузку:
```bash
composer dump-autoload -o
```
4. Запустить игру:
```bash
php bin/tic-tac-toe
```
5. Возможен запуск напрямую через Packagist:
 * Локально c директорию
```bash
composer composer create-project darkflade/tic-tac-toe
```
 * Глобально в путь composer
```bash
composer global require wp-cli/wp-cli
composer global dump-autoload -o
```

## Используемые библиотеки

wp-cli/php-cli-tools

## Ссылка на packagit

https://packagist.org/packages/darkflade/tic-tac-toe