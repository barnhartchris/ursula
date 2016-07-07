<?php
#@gui:Email Matt _custom input_
#@gui:Email Tia _custom input_
#@gui:Email Michelle _custom input_

if (in_array("email", $firstThreeWords)     ||
    in_array("e-mail", $firstThreeWords)    ||
    in_array("mail", $firstThreeWords)      ||
    in_array("male", $firstThreeWords)      ) {

    // Determine recipient:
    $firstFiveWords = array_slice($spokenWords,0,5);
    if (in_array("tia", $firstFiveWords)     ||
        in_array("t", $firstFiveWords)       ||
        in_array("amber", $firstFiveWords)   ||
        in_array("wife", $firstFiveWords)      ) {

        $recipient = array('Tia','tia@farleyfamily.net');

    } elseif (in_array("matt", $firstFiveWords)     ||
        in_array("matthew", $firstFiveWords)        ||
        in_array("peter", $firstFiveWords)          ||
        in_array("husband", $firstFiveWords)      ) {

        $recipient = array('Matt','matt@farleyfamily.net');

    } elseif (mentionsKalani($firstFiveWords)) {

        $recipient = array('Kalani','kalani@farleyfamily.net');

    } else {
        respond('Jarvis failed to identify the e-mail recipient. Please repeat.');
    }

    // Determine message:
    $body = $command;

    // Send message
    $shellCommand = 'email_via_sendEmail ' . $recipient[1] . ' "Message from Jarvis" "' . $body . '" > /dev/null 2>&1 &';
    exec($shellCommand);

    respond('Jarvis has sent the following message to ' . $recipient[0] . '... ' . $command);
}
