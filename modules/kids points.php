<?php
#@gui:Add one point for Mason
#@gui:Add one point for Michelle
#@gui:Add one point for both kids
#@gui:---
#@gui:Remove one point from Mason
#@gui:Remove one point from Michelle
#@gui:Remove one point from both kids
#@gui:---
#@gui:Report status of Mason's points
#@gui:Report status of Michelle's points
#@gui:Report status of both kids points

if (in_array("token",    $spokenWords)   ||
    in_array("token's",  $spokenWords)   ||
    in_array("tokens",   $spokenWords)   ||
    in_array("points",   $spokenWords)   ||
    in_array("point",    $spokenWords)   ||
    in_array("point's",  $spokenWords)   ||
    in_array("taupin",   $spokenWords)   ||
    in_array("taupin's", $spokenWords)   ||
    in_array("taupins",  $spokenWords)   ||
    in_array("tocen",    $spokenWords)   ||
    in_array("tocens",   $spokenWords)   ||
    in_array("tocen's",  $spokenWords)   ||
    in_array("tokens",   $spokenWords)     ) {

    //
    // Setup
    //
    $tokenFile = 'modules/tokens/tokens.log';
    $timestamp = time();
    $response = '';
    $addOrSubtract = false;
    $numTable[1] = 'one';
    $numTable[2] = 'two';
    $numTable[3] = 'three';
    $numTable[4] = 'four';
    $numTable[5] = 'five';
    $numTable[6] = 'six';
    $numTable[7] = 'seven';
    $numTable[8] = 'eight';
    $numTable[9] = 'nine';
    $numTable[10] = 'ten';

    //
    // Account for "to tokens" as "two tokens"
    //
    if (strstr($command, 'to tokens')) {
        $command = str_replace('to tokens', 'two tokens', $command);
        $spokenWords = explode(' ', $command);
    }

    //
    // Add/subtract tokens
    //
    if ( in_array('give',     $spokenWords)  ||
         in_array('add',      $spokenWords)  ||
         in_array('deposit',  $spokenWords)  ||
         in_array('increase', $spokenWords)  ||
         in_array('ad',       $spokenWords)    ) {

        $addOrSubtract = '+';
    }
    if ( in_array('subtract', $spokenWords) ||
         in_array('track',    $spokenWords) ||
         in_array('use',      $spokenWords) ||
         in_array('remove',   $spokenWords) ||
         in_array('delete',   $spokenWords) ||
         in_array('deduct',   $spokenWords) ||
         in_array('take',     $spokenWords) ||
         in_array('withdraw', $spokenWords) ||
         in_array('decrease', $spokenWords) ||
         in_array('redeem',   $spokenWords)   ) {

        $addOrSubtract = '-';
    }

    if ($addOrSubtract != false) {

        // Determine number of tokens to add/subtract:  (this code finds and converts six to 6)
        $numToAdd = array_intersect($numTable, $spokenWords);
        reset($numToAdd);
        $numToAdd = key($numToAdd);

        // If we didn't find a number, assume we're only adding 1
        if (!$numToAdd) $numToAdd = 1;

        // Tack on a negative to numToAdd if need be
        if ($addOrSubtract == '-') {

            $response .= 'Jarvis has removed ' . $numToAdd . ' point'.(($numToAdd!=1)?'s':'').' from ';
            $numToAdd = '-' . $numToAdd;

        } else {
            $response .= 'Jarvis has added ' . $numToAdd . ' point'.(($numToAdd!=1)?'s':'').' for ';
        }

        // Determine who to add them to
        if (in_array('both', $spokenWords) ||
            ( in_array('both', $spokenWords) && in_array('kids', $spokenWords) ) ||
            ( mentionsKalani($spokenWords) && mentionsMason($spokenWords) ) ){

            sshShellCommand('echo '.$numToAdd.',kalani,'.$timestamp.' >> /var/www/jarvis/' . $tokenFile);
            sshShellCommand('echo '.$numToAdd.',mason,'.$timestamp.' >> /var/www/jarvis/' . $tokenFile);

            $response .= 'both kids. ';

        } elseif (mentionsKalani($spokenWords)) {

            sshShellCommand('echo '.$numToAdd.',kalani,'.$timestamp.' >> /var/www/jarvis/' . $tokenFile);
            $response .= 'Michelle. ';

        } elseif (mentionsMason($spokenWords)) {

            sshShellCommand('echo '.$numToAdd.',mason,'.$timestamp.' >> /var/www/jarvis/' . $tokenFile);
            $response .= 'Mason. ';

        } else {

            $response = 'I think you wanted to add or remove tokens, but I did not understand who should receive them. Please restate your command. ';
            respond($response); // This will end execution

        }

        // Email parents token notification
        $shellCommand = 'email_via_sendEmail "matt@farleyfamily.net tia@farleyfamily.net" "Jarvis - Kids Points - ' . date('h:i A') . ' ' . time() . '" "' . $response . '" > /dev/null 2>&1 &';
        exec($shellCommand);

        // Notify network of change
        sshShellCommand('notify_network /mnt/documents/Icons/hourglass.png "" "Jarvis - Kids Points Update" "' . $response . '" "web"');

        // Start a countdown timer if someone used a token
        if ($addOrSubtract == '-') {

            // Start a network countdown timer
            sshShellCommand('notify_timer 30 "Jarvis points in use!"');

        }

    } // End if add/subtract found

    //
    // Reporting status:
    //
    if (in_array("report", $spokenWords)  ||
        in_array("status", $spokenWords)  ||
        in_array("how",    $spokenWords)  ||
        in_array("many",   $spokenWords)  ||
        in_array("reports",$spokenWords)  ) {

        // Sleep to allow ssh file operations to complete (500,000 = 0.5 seconds)
        usleep(500000);

        // Tally up each kid's tokens remaining, last add, last used
        $data = file($tokenFile);
        foreach ($data as $record) {
            list($numTokens, $name, $date) = explode(',', $record);

            // Add up / aggregate the number of tokens
            @$kids[$name]['numTokens'] += $numTokens;

            // A positive addition or subtraction
            if ($numTokens > 0) {
                $kids[$name]['lastAdd'] = humanTiming($date);
            } else {
                $kids[$name]['lastUsed'] = humanTiming($date);
            }
        }

        // Set blanks to 0 (for when we wipe the log file)
        $kids['kalani']['numTokens'] = $kids['kalani']['numTokens'] == '' ? 0 : $kids['kalani']['numTokens'];
        $kids['mason']['numTokens']  = $kids['mason']['numTokens']  == '' ? 0 : $kids['mason']['numTokens'];

        // Determine who to report on
        if (mentionsKalani($spokenWords)) {

            $response .= 'Michelle has ' . $kids['kalani']['numTokens'] . ' point'.(($kids['kalani']['numTokens']!=1)?'s':'').' left. And she last used a point ' . $kids['kalani']['lastUsed'] . ' ago... ';

        } elseif (mentionsMason($spokenWords)) {

            $response .= 'Mason has ' . $kids['mason']['numTokens'] . ' point'.(($kids['mason']['numTokens']!=1)?'s':'').' left. And he last used a point ' . $kids['mason']['lastUsed'] . ' ago... ';

        } else {

            $response .= 'Mason has ' . $kids['mason']['numTokens'] . ' point'.(($kids['mason']['numTokens']!=1)?'s':'').' left. And he last used a point ' . $kids['mason']['lastUsed'] . ' ago... ';
            $response .= 'Michelle has ' . $kids['kalani']['numTokens'] . ' point'.(($kids['kalani']['numTokens']!=1)?'s':'').' left. And she last used a point ' . $kids['kalani']['lastUsed'] . ' ago... ';

        }

    }

    respond($response);
}
