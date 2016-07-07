<?php
#@gui:Book of Mormon - Start family scripture study
#@gui:Book of Mormon - Start family scripture study on the previous chapter
#@gui:Book of Mormon - Which chapter are we on for scripture study?
#@gui:---
#@gui:Old Testament - Start family scripture study
#@gui:Old Testament - Start family scripture study on the previous chapter
#@gui:Old Testament - Which chapter are we on for scripture study?
#@gui:---
#@gui:New Testament - Start family scripture study
#@gui:New Testament - Start family scripture study on the previous chapter
#@gui:New Testament - Which chapter are we on for scripture study?
#@gui:---
#@gui:Doctrine & Covenants - Start family scripture study
#@gui:Doctrine & Covenants - Start family scripture study on the previous chapter
#@gui:Doctrine & Covenants - Which chapter are we on for scripture study?
#@gui:---
#@gui:Pearl of Great Price - Start family scripture study
#@gui:Pearl of Great Price - Start family scripture study on the previous chapter
#@gui:Pearl of Great Price - Which chapter are we on for scripture study?

if (in_array("scriptures", $spokenWords) ||
    in_array("scripture",  $spokenWords) ||
    in_array("study",      $spokenWords) ||
    in_array("studies",    $spokenWords) ){

    //
    // Determine which book we are to study
    //
    if ( in_array('pearl', $spokenWords) || in_array('price', $spokenWords) || in_array('great', $spokenWords) ) {

        // Pearl of Great Price
        $response  = 'Loading Pearl of Great Price. ';
        $thisBook = 'pgp';

    } elseif ( in_array('doctrine', $spokenWords) || in_array('covenants', $spokenWords) ) {

        // D&C
        $response  = 'Loading Doctrine and Covenants. ';
        $thisBook  = 'dc-testament';

    } elseif ( in_array('new', $spokenWords) ) {

        // Bible: New Testament
        $response  = 'Loading New Testament. ';
        $thisBook  = 'nt';

    } elseif ( in_array('old', $spokenWords) ) {

        // Bible: New Testament
        $response  = 'Loading Old Testament. ';
        $thisBook  = 'ot';

    } else {

        // Book of Mormon
        $response  = 'Loading Book of Mormon. ';
        $thisBook  = 'bofm';

    }

    $fileCurrentIndex = 'modules/scriptures/chapter_index_'.$thisBook.'.txt'; // Set to -1 to start over
    $fileAllChapters  = 'modules/scriptures/chapter_index_'.$thisBook.'.csv';

    // Start the TV on HDMI2
    tv_init();

    // Read entire index table of all chapters
    $chapters = file($fileAllChapters);

    // Determine last chapter info, translate to words
    $lastChapterIndex = file($fileCurrentIndex);
    $lastChapterIndex = trim($lastChapterIndex[0]);

    // If we heard "last chapter" or "previous chapter", then go back 2 in the index
    if ( in_array('chapter', $spokenWords) )
        if ( in_array('last', $spokenWords) || in_array('previous', $spokenWords) )
            if ($lastChapterIndex > 1)
                    $lastChapterIndex -= 1;

    $lastChapterData = explode(',', $chapters[$lastChapterIndex]);
    $lastChapterBook = trim($lastChapterData[0]);
    $lastChapterNum = trim($lastChapterData[1]);
    $lastChapterWords = "$lastChapterBook, Chapter $lastChapterNum";

    // Determine next chapter info, translate to words
    $thisChapterIndex = $lastChapterIndex + 1;

    // In case we've reached the end and are looping to start
    if (
        ($thisBook == 'bofm')         && ($thisChapterIndex == 239) ||
        ($thisBook == 'ot')           && ($thisChapterIndex == 929) ||
        ($thisBook == 'nt')           && ($thisChapterIndex == 261) ||
        ($thisBook == 'pgp')          && ($thisChapterIndex == 19 ) ||
        ($thisBook == 'dc-testament') && ($thisChapterIndex == 138)
        )
        $thisChapterIndex = 0;

    $thisChapterData = explode(',', $chapters[$thisChapterIndex]);
    $thisChapterBook = trim($thisChapterData[0]);
    $thisChapterNum = trim($thisChapterData[1]);
    $thisChapterUrl = trim($thisChapterData[2]);
    $thisChapterWords = "$thisChapterBook, Chapter $thisChapterNum";

    // If we heard "what" and "chapter", tell'm and return
    if ( in_array('chapter', $spokenWords) )
        if ( in_array('what', $spokenWords) || in_array('which', $spokenWords) )
            respond('We are currently on ' . $thisChapterWords, false);

    // Build hyperlink
    $thisChapterUrl = 'https://www.lds.org/scriptures/' . $thisBook . '/' . $thisChapterUrl . '/' . $thisChapterNum . '?lang=eng';

    // Debugging:
    //respond($thisChapterUrl, true);

    // Write new index to static text file
    sshShellCommand('echo ' . $thisChapterIndex . ' > /var/www/jarvis/' . $fileCurrentIndex);

    // Start the script on HTPC
    sshShellCommand("cd /mnt/documents/bin && DISPLAY=:0.0 python -c 'from scriptures import *; Scriptures = ScripturePrompt(); Scriptures.startScriptures(0, 0, \"$thisChapterUrl\")'");

    // Continue building text response
    $response .= 'The last chapter you red was ' . $lastChapterWords . '. ';
    $response .= 'I will now load ' . $thisChapterWords . '. ';

    // Randomize quotes to the kids.
    $randomQuote[] = 'By the way, Mason is crazy! ';
    $randomQuote[] = 'By the way, Kalani is crazy! ';
    $randomQuote[] = 'By the way, Blake is crazy! ';
    $randomQuote[] = 'Pay attention Mason!';
    $randomQuote[] = 'Pay attention Kalani!';
    $randomQuote[] = 'By the way, I haven\'t eaten in days!';
    $randomQuote[] = 'Pay attention kids!';
    $randomQuote[] = 'Enjoy your scripture study!';
    $randomQuote[] = 'Enjoy this family time!';
    $randomQuote[] = 'I miss my mother!';
    $randomQuote[] = 'I hope you all learn something!';
    $randomQuote[] = 'Kalani get off that eye pad and pay attention!';
    $randomQuote[] = 'Someone let me out of this tube!';
    $randomQuote[] = 'Someday you will have to tell me who this neefeye character is!';
    $randomQuote[] = 'Someday you will have to tell me who this moroneye character is!';
    $randomQuote[] = 'Someday you will have to tell me who this mormon character is!';
    $randomQuote[] = 'Someday you will have to tell me who this Josepth Smith is!';
    $randomQuote[] = 'By the way, may the force be with you!';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $randomQuote[] = '';
    $rand_key = array_rand($randomQuote);
    $response .= $randomQuote[$rand_key];

    respond($response, true);
}
