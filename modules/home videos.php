<?php
#@gui:Play home videos
#@gui:Play home videos and filter on Asia
#@gui:Play home videos and filter on _custom input_
#@gui:---
#@gui:Skip to the next home videos
#@gui:---
#@gui:Stop playing home videos

if (
    ( in_array('home', $spokenWords)   && in_array('videos', $spokenWords) ) ||
    ( in_array('home', $spokenWords)   && in_array('video', $spokenWords)  ) ||
    ( in_array('video', $spokenWords)  && in_array('filter', $spokenWords) ) ||
    ( in_array('video', $spokenWords)  && in_array('filters', $spokenWords)) ||
    ( in_array('video', $spokenWords)  && in_array('filtered',$spokenWords)) ||
    ( in_array('filter', $spokenWords) && in_array('home', $spokenWords)  )  ||
    ( in_array('filters', $spokenWords) && in_array('home', $spokenWords)  ) ||
    ( in_array('filtered', $spokenWords) && in_array('home', $spokenWords) ) ){

    // Setup
    $pathToVideos = '/mnt/router/Dropbox/Home\ Videos/*';
    $scriptHome = '/mnt/router/Dropbox/src/jarvis/modules/videos/';

    // Special case of skipping currently playing videos:
    if ( in_array('skip', $spokenWords) ) {

        sshShellCommand('killall mplayer');
        respond('Skipping to the next set of home videos', true);
    }

    //
    // Determine command/filters to apply to list of home videos
    //

    //
    // 'Keyword Asia': Newer than Nov 2012, Older than Feb01-2016, no basketball, no private journal:
    //
    if ( in_array('asia', $spokenWords) ) {

        $find = <<<EOF
find $pathToVideos -regextype posix-extended -regex '.*\.(mp4|MP4|mpg|MPG|mov|MOV|wmv|WMV|avi|AVI)$' -newermt 2012-11-01 -and -not -newermt 2016-02-01 | grep -vi "basketball" | grep -i "/Videos/" | grep -vi "Aloha-1980" | grep -vi "Aloha-2010" | shuf
EOF;
        $response = 'Jarvis will now shuffle your home videos taken in Asia';

    //
    // Filtered: no private journal
    //
    } elseif ( in_array('filter', $spokenWords) || in_array('filters', $spokenWords) || in_array('filtered', $spokenWords) ) {


        $filter = $spokenWords[count($spokenWords)-1];
        $find = <<<EOF
find $pathToVideos -regextype posix-extended -regex '.*\.(mp4|MP4|mpg|MPG|mov|MOV|wmv|WMV|avi|AVI)$' | grep -i "/Videos/" | grep -i "$filter" | shuf
EOF;
        $response = 'Jarvis will now shuffle your home videos, whose title contains the keyword ' . $filter;

    //
    // Stop playing home videos
    //
    } elseif ( in_array('stop', $spokenWords) ) {

        tv_killMplayers();
        respond('Jarvis has stopped playing home videos', true);

    //
    // No Filter: no private journal, no basketball:
    //
    } else {

        $find = <<<EOF
find $pathToVideos -regextype posix-extended -regex '.*\.(mp4|MP4|mpg|MPG|mov|MOV|wmv|WMV|avi|AVI)$' | grep -vi "basketball" | grep -i "/Videos/" | shuf
EOF;
        $response = 'Jarvis will now shuffle your home videos on the T.V.';
    }


    //
    // Initialize TV
    //
    tv_init();

    // Get the list of videos based on our filter above. False - don't background this task
    $videos = sshShellCommand($find, false);

    // Split videos into four separate arrays
    $videos = partition($videos, 4);

    // Define the 4 quadrants and geometries (could always change this)
    $quadrant[0]['geometry'] = '960x540+0+0';
    $quadrant[0]['name']     = 'upper-left';
    $quadrant[1]['geometry'] = '960x540+960+0';
    $quadrant[1]['name']     = 'upper-right';
    $quadrant[2]['geometry'] = '960x540+0+540';
    $quadrant[2]['name']     = 'lower-left';
    $quadrant[3]['geometry'] = '960x540+960+540';
    $quadrant[3]['name']     = 'lower-right';

    //$volume = '-nosound';
    $volume = '-volume 35';
    $decoder = '-vo vdpau -vc ffh264vdpau,ffmpeg12vdpau,ffodivxvdpau,ffwmv3vdpau,ffvc1vdpau,';

    for ($i = 0; $i <= count($quadrant)-1; ++$i) {

        $cmd = '';

        // Craft special mplayer command, taking into account geometries and hardware decoding
        // 1 command per video = hundreds of commands; linked as a single script
        // endpos = only play first x seconds
        foreach ($videos[$i] as $video)
            $cmd .= 'mplayer -endpos 120 --stop-xscreensaver "'.$video.'" '.$volume.' -noborder -geometry '.$quadrant[$i]['geometry'].' '.$decoder.' </dev/null >/dev/null 2>&1; ';

        // Remove old quardrant.sh's -- do not background this task to avoid race conditions
        sshShellCommand('rm '.$scriptHome.$quadrant[$i]['name'].'.sh', false);

        // Write the new command to a shell script
        file_put_contents($scriptHome.$quadrant[$i]['name'].'.sh',$cmd);

        // Execute the shell script we just created
        sshShellCommand('DISPLAY=:0.0 '.$scriptHome.$quadrant[$i]['name'].'.sh');
    }

    respond($response, true);
}
