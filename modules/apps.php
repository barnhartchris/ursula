<?php
#@gui:Start X. B. M. C.
#@gui:Google Search _custom input_
#@gui:YouTube Search _custom input_
#@gui:Amazon Search _custom input_

if (
    ( in_array('x.', $spokenWords)      && in_array('b.', $spokenWords) ) ||
    ( in_array('x.', $spokenWords)      && in_array('m.', $spokenWords) ) ||
    ( in_array('x.', $spokenWords)      && in_array('c.', $spokenWords) ) ||
    ( in_array('x', $spokenWords)       && in_array('b', $spokenWords) )  ||
    ( in_array('x', $spokenWords)       && in_array('m', $spokenWords) )  ||
    ( in_array('x', $spokenWords)       && in_array('c', $spokenWords) )  ||
    ( in_array('farley', $spokenWords)  && in_array('family', $spokenWords) ) ||
    ( in_array('youtube', $firstThreeWords)) ||
    ( in_array('amazon', $firstThreeWords) ) ||
    ( in_array('google', $firstThreeWords) ) ){

    // Filter search word / phrase by removing non-search words:
    //$search = $spokenWords[count($spokenWords)-1];
    $search = str_replace('x.', '', $command);
    $search = str_replace('b.', '', $search);
    $search = str_replace('m.', '', $search);
    $search = str_replace('c.', '', $search);
    $search = str_replace('e.', '', $search);
    $search = str_replace('d.', '', $search);
    $search = str_replace('t.', '', $search);
    $search = str_replace('search ', '', $search);
    $search = str_replace('youtube', '', $search);
    $search = str_replace('amazon', '', $search);
    $search = str_replace('google', '', $search);
    $search = str_replace(' for ', '', $search);
    $search = str_replace(' on ', '', $search);
    $search = str_replace(' load ', '', $search);
    $search = str_replace(' start ', '', $search);
    $search = str_replace(' keyword ', '', $search);
    $search = str_replace('do ', '', $search);
    $search = str_replace(' do ', '', $search);
    $search = str_replace(' a ', '', $search);
    $search = str_replace('', '', $search);

    if (
        ( in_array('x.', $spokenWords)   && in_array('b.', $spokenWords) ) ||
        ( in_array('x.', $spokenWords)   && in_array('m.', $spokenWords) ) ||
        ( in_array('x.', $spokenWords)   && in_array('c.', $spokenWords) ) ||
        ( in_array('x', $spokenWords)   && in_array('b', $spokenWords) )   ||
        ( in_array('x', $spokenWords)   && in_array('m', $spokenWords) )   ||
        ( in_array('x', $spokenWords)   && in_array('c', $spokenWords) )   ){

        // Load XBMC
        $command = '/mnt/documents/bin/xbmc_launch_nmediapc';
        $response = 'Jarvis will now load XBMC';

    } elseif ( in_array('youtube', $firstThreeWords) ) {

        // Load YouTube Search
        $url      = 'https://www.youtube.com/results?search_query=' . $search;
        $command  = 'google-chrome "' . $url . '"';
        $response = 'Jarvis will now load YouTube and search on ' . $search;

    } elseif ( in_array('google', $firstThreeWords) ) {

        // Load Google Search
        $url     = 'https://www.google.com/search?q=' . $search;
        $command = 'google-chrome "' . $url . '"';
        $response = 'Jarvis will now load Google and search on ' . $search;

    } elseif ( in_array('amazon', $firstThreeWords) ) {

        // Load Amazon Search
        $url     = 'http://www.amazon.com/s/ref=nb_sb_noss?url=search-alias%3Daps&field-keywords=' . $search . '&x=0&y=0';
        $command = 'google-chrome "' . $url . '"';
        $response = 'Jarvis will now load Amazon and search on ' . $search;

    } elseif ( in_array('farley', $spokenWords)  && in_array('family', $spokenWords) ) {

        // Load FF.Net
        $url     = 'http://www.farleyfamily.net/';
        $command = 'google-chrome "' . $url . '"';
        $response = 'Jarvis will now load Farley Family dot Net';

    } else {

        $response = 'Jarvis thinks you wanted to start an application, but did not understand which one.';
        respond($response, false);

    }

    //
    // Initialize TV and Load App
    //
    tv_init();

    // Load app -- sleep 2 secs let the other apps die on init
    sshShellCommand('sleep 2; DISPLAY=:0.0 ' . $command);
    respond($response, true);
}
