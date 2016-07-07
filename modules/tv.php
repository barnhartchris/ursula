<?php
#@gui:Turn on the TV
#@gui:Turn off the TV
#@gui:---
#@gui:Mute the TV
#@gui:Turn up the volume on TV
#@gui:Turn down the volume on TV
#@gui:---
#@gui:Watch Discovery Kids
#@gui:Watch Disney Junior
#@gui:Watch Basketball TV

$alreadyMatchedAndExecuted = false;

// Tuning to Channels
if (  in_array('watch', $spokenWords)      ||
      in_array('t. v.', $spokenWords)      ||
      in_array('tv', $spokenWords)         ||
      in_array('television', $spokenWords) ||
      in_array('turn', $spokenWords)       ){

    if ( in_array('discovery', $spokenWords) || in_array('discovering', $spokenWords) || in_array('kid', $spokenWords) || in_array('kids', $spokenWords) ) {

        $response = 'Jarvis will now put Discovery Kids on T. V.';
        tv_init('AV1');
        tv_remote('skyph', 'KEY_1 KEY_2 KEY_0');

    } elseif ( in_array('disney', $spokenWords) || in_array('junior', $spokenWords) ) {

        $response = 'Jarvis will now put Disney Junior on T. V.';
        tv_init('AV1');
        tv_remote('skyph', 'KEY_3 KEY_8');

    } elseif ( in_array('basketball', $spokenWords) || in_array('basket', $spokenWords) ) {

        $response = 'Jarvis will now put BTV on television';
        tv_init('AV1');
        tv_remote('skyph', 'KEY_3 KEY_3');

    } elseif ( in_array('mute', $spokenWords) || (in_array('sound', $spokenWords) && in_array('off', $spokenWords)) ) {

        $response = 'Jarvis will now mute the T.V. ';
        tv_remote('vizsb', 'MUTE');

    } elseif ( in_array('volume', $spokenWords) && in_array('up', $spokenWords) ) {

        $response = 'Jarvis will now turn up the volume on the T.V.';
        tv_remote('vizsb', 'UP UP');

    } elseif ( in_array('volume', $spokenWords) && in_array('down', $spokenWords) ) {

        $response = 'Jarvis will now turn down the volume on the T.V.';
        tv_remote('vizsb', 'DOWN DOWN');

    } elseif ( in_array('off', $spokenWords) ) {

        $response = 'Jarvis will now turn off the T. V.';
        tv_off();

    } elseif ( in_array('on', $spokenWords) ) {

        $response = 'Jarvis will now turn on the T. V.';
        tv_init(); // Defaults to input HDMI4

    } else {

        $response = 'I believe you wanted Jarvis to do something with the T.V., but I did not quite catch it. Please repeat.';
        respond($response, false);

    }

    respond($response, true);
}
