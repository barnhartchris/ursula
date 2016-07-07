<?php
//
// Fail - die() replacement with error logging
//
function fail($message) {

    error_log($message);
    die();

}

//
// This function is called when all logic is complete and we're ready to "exit",
// providing with our oral response to be spoken by the Echo device
//
function respond($jarvisResponse, $endSession = false) {

    // Provide JSON response
    header('Content-Type: application/json;charset=UTF-8');

    // Determine whether or not to end this interaction with Alexa
    $shouldEndSession = $endSession ? 'true' : 'false';

    $text = '{
"version" : "1.0",
"response" : {
"outputSpeech" : {
"type" : "PlainText",
"text" : "'.$jarvisResponse.'"
},
"shouldEndSession" : '.$shouldEndSession.'
}
}';

    // Response do Amazon Web Service (or GUI)
    header('Content-Length: ' . strlen($text));
    echo $text;
    exit;
}

//
// Determine if the spoken command mentions Kalani Michelle
//
function mentionsKalani($spokenWords) {
    if (  in_array("michelle", $spokenWords) ||
          in_array("michelles", $spokenWords) ||
          in_array("kalani", $spokenWords) ||
          in_array("kalani's", $spokenWords) ||
          in_array("meshell", $spokenWords) ||
          in_array("meshell's", $spokenWords) ||
          in_array("michelle's", $spokenWords) ||
         (in_array("my", $spokenWords) && in_array("shelf", $spokenWords)) ||
         (in_array("k", $spokenWords) && in_array("train", $spokenWords)) ||
         (in_array("k", $spokenWords) && in_array("trains", $spokenWords)) ||
         (in_array("k", $spokenWords) && in_array("train's", $spokenWords)) ||
         (in_array("ok", $spokenWords) && in_array("train", $spokenWords)) ||
         (in_array("ok", $spokenWords) && in_array("trains", $spokenWords)) ||
         (in_array("ok", $spokenWords) && in_array("train's", $spokenWords)) ||
         (in_array("k.", $spokenWords) && in_array("train", $spokenWords)) ||
         (in_array("k.", $spokenWords) && in_array("trains", $spokenWords)) ||
         (in_array("k.", $spokenWords) && in_array("train's", $spokenWords)) ) {
        return true;
    }
    return false;
}

//
// Determine if the spoken command mentions Mason Makaio
//
function mentionsMason($spokenWords) {
    if ( in_array("mason", $spokenWords)    ||
         in_array("mason's", $spokenWords)  ||
         in_array("mesa", $spokenWords)     ||
         in_array("mesa's", $spokenWords)   ||
         in_array("masons", $spokenWords) ) {
        return true;
    }
    return false;
}

//
// Convert a unixtimestamp to Human "days ago" "minutes ago" etc
//
function humanTiming ($time) {

    $time = time() - $time; // to get the time since that moment

    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
    }

}

//
// Control the TV
//
function tv_remote($remote, $command) {

    sshShellCommand('irsend SEND_ONCE '.$remote.' '.$command);

}

//
// Init TV (power on, volume up, set input)
//
function tv_init($input = 'HDMI4', $remote = 'lgtv') {

    // Power on (2x), and set input to HDMI4
    sshShellCommand('irsend SEND_ONCE '.$remote.' POWER_ON POWER_ON; sleep 7; irsend SEND_ONCE '.$remote.' '.$input.' '.$input.' '.$input);

    // Set vol to 15
    tv_remote('vizsb', 'DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN DOWN UP UP UP UP UP UP UP UP UP UP UP UP UP UP'); // Set vol to 15

    // Cancel / clear any video mosiacs
    tv_killMplayers();

    // Kill any running apps
    tv_killApps();

    // Wake up the HTPC with a keystroke
    sshShellCommand('DISPLAY=:0.0 xdotool key Left');
}

//
// Turn TV off -- remove panels, kill mosiacs and mplayers
//
function tv_off() {

    // Send remote power off
    tv_remote('lgtv','POWER_OFF');

    // Cancel / clear any mosiacs
    tv_killMplayers();

    // Kill any running apps
    tv_killApps();
}

//
// Cancel / clear mosaics and mplayers
//
function tv_killMplayers() {

    // Hide Panels
    sshShellCommand('DISPLAY=:0.0 hide_mate_panels');

    // Kill any mosiac scripts
    sshShellCommand('ps -ef | grep "jarvis/modules/videos" | awk \'{print \$2}\' | xargs kill -9');

    // Kill mplayers
    sshShellCommand('killall mplayer');

}

//
// Kill running apps
//
function tv_killApps() {

    // XBMC
    sshShellCommand('pkill -9 xbmc.bin');

    // Chrome / Browser
    sshShellCommand('killall chrome');

    // Firefox (Scriptures)
    sshShellCommand('killall firefox');

}


//
// Route commands via user matt via self-ssh
//
// (I have to masquarade as matt because dropbox is mounted via USB with a umask not allowing world/other writes)
//
function sshShellCommand($shellCommand, $background = true, $server = false) {

    // If the user didn't specify an ssh server (e.g. htpc or server) default to HTPC
    if ($server == false)
        $server = '192.168.1.6';

    $newCommand  = 'ssh matt@' . $server;

    // Add slashes before " in ssh command (escape)
    $newCommand .= ' "'.addcslashes($shellCommand,'"').'" ';

    // If this task is to be run in the background so PHP can continue processing
    if ($background)
        $newCommand .= ' > /dev/null 2>&1 &';

    // Place the results into array $results
    exec($newCommand, $results);

    // Return the output
    return $results;
}

//
// Used in the videos.php module for breaking an array of videos into 4 quadrants
//
/**
 * For chunking arrays into equal parts
 * @param Array $list
 * @param int $p
 * @return multitype:multitype:
 * @link http://www.php.net/manual/en/function.array-chunk.php#75022
 */
function partition(Array $list, $p) {
    $listlen = count($list);
    $partlen = floor($listlen / $p);
    $partrem = $listlen % $p;
    $partition = array();
    $mark = 0;
    for($px = 0; $px < $p; $px ++) {
        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
        $partition[$px] = array_slice($list, $mark, $incr);
        $mark += $incr;
    }
    return $partition;
}

//
// Validate keychainUri is proper (from Amazon)
//
function validateKeychainUri($keychainUri){

    $uriParts = parse_url($keychainUri);

    if (strcasecmp($uriParts['host'], 's3.amazonaws.com') != 0)
        fail('The host for the Certificate provided in the header is invalid');

    if (strpos($uriParts['path'], '/echo.api/') !== 0)
        fail('The URL path for the Certificate provided in the header is invalid');

    if (strcasecmp($uriParts['scheme'], 'https') != 0)
        fail('The URL is using an unsupported scheme. Should be https');

    if (array_key_exists('port', $uriParts) && $uriParts['port'] != '443')
        fail('The URL is using an unsupported https port');

}
