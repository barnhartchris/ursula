<?php
// "repeat Matt is a great father!"
// "-> Jarvis heard, Matt is a great father"
//
if ($spokenWords[0] == 'repeat') {
    $response = str_replace('repeat', '...', $command);
    respond('Jarvis heard the following.... ' . $response);
}
