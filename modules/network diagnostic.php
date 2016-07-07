<?php
#@gui:Perform network diagnostic

if ( (in_array("network", $spokenWords)  && in_array("diagnostic", $spokenWords))  ||
     (in_array("networks", $spokenWords) && in_array("diagnostic", $spokenWords))  ||
     (in_array("work", $spokenWords)     && in_array("diagnostic", $spokenWords))  ||
     (in_array("works", $spokenWords)    && in_array("diagnostic", $spokenWords))  ||
     (in_array("networks", $spokenWords) && in_array("diagnostics", $spokenWords)) ||
     (in_array("work", $spokenWords)     && in_array("diagnostics", $spokenWords)) ||
     (in_array("works", $spokenWords)    && in_array("diagnostics", $spokenWords)) ||
     (in_array("works", $spokenWords)    && in_array("diagnostics", $spokenWords)) ){

    // Machines to test
    // TODO: Merge with mfp network monitoring tool into Jarvis GUI utilities
    $hosts['ubuntu']        = "Matt's computer";
    $hosts['tia-laptop']    = "Tia's laptop";
    $hosts['neuros']        = 'Livingroom P.C.';
    $hosts['kalani-laptop'] = 'Master Bedroom Laptop';

    $hosts['router']        = 'Wi-Fi Router';
    $hosts['192.168.2.1']   = 'DSL Modem';
    $hosts['comcast.com']   = 'Comcast';

    $hosts['4.2.2.99']      = 'Test Host';

    $hosts['images.farleyfamily.net']   = 'Image Server';
    $hosts['houston.farleyfamily.net']  = 'Houston Server';

    // Clear log file / start over
    sshShellCommand('echo -n "" > /tmp/network_diagnostic.log');

    // Ping each host asynchronously and store results in log file
    foreach ($hosts as $host=>$name) {

        $cmd = 'if [ "$(ping -q -w2 -c1 '.$host.' | grep received | cut -d \',\' -f2 | cut -d \' \' -f2)" -eq 1 ];then echo "'.$name.'.. online...." >> /tmp/network_diagnostic.log; else echo "'.$name.'.. offline... no response...." >> /tmp/network_diagnostic.log; fi';
        sshShellCommand($cmd);

    }

    // Wait to give async requests a chance to finish, then read log file
    sleep(3);
    $response = file_get_contents('/tmp/network_diagnostic.log');
    $response = trim(preg_replace('/\s+/', ' ', $response));

    respond($response, true);
}
