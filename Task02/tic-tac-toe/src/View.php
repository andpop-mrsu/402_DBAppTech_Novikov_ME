<?php
namespace Darkflade\Tic_tac_toe\View;

use function cli\line;

function showStartScreen() {
    $helloMessage = "Добро пожаловать в игру Tic-Tac-Toe!\n";
    line($helloMessage);
}
