<?php
require "../../functions.php";
?>
<div class="navbar">
  <div class="navbar-inner">
    <div class="left"><a href="#" class="back link"> <i class="icon icon-back"></i><span>Back</span></a></div>
    <div class="center sliding">Log Viewer</div>
    <div class="right"><a href="#" data-panel="left" class="link open-panel icon-only"><i class="icon icon-bars"></i></a></div>
  </div>
</div>
<div class="pages">
  <div data-page="services" class="page">
    <div class="page-content">


      <div class="content-block-title"> / var / log / apache2 / error.log</div>
      <div class="content-block">
      <?php
        $error = sshShellCommand('tail -100 /var/log/apache2/error.log', false);
        $error = array_reverse($error);
        echo "<pre style='font-size: 12px; height: 200px; overflow: scroll; border: solid 1px; padding: 5px'>";
        foreach($error as $line) echo $line."<br />";
        echo "</pre>";
      ?>
      </div>


      <div class="content-block-title"> / tmp / echo.log / user commands</div>
      <div class="content-block">
      <?php
        $echo = sshShellCommand('cat /tmp/echo.log | grep -E \'(\\[value\\]|----------)\'', false);
        echo "<pre style='font-size: 12px; height: 200px; overflow: scroll; border: solid 1px; padding: 5px'>";
        foreach($echo as $key=>$line) {

            if (strstr($line, 'value')) {

                $date = $echo[$key-1];
                $date = str_replace('-', '', $date);

                $statement = str_replace('                                    [value] => ', '', $line);

                $statements[] = "$date: $statement<br />";

            }
        }
        $statements = array_reverse($statements);
        foreach($statements as $statement) echo $statement;
        echo "</pre>";
      ?>
      </div>


      <div class="content-block-title"> / tmp / echo.log / raw</div>
      <div class="content-block">
      <?php
        $echo = sshShellCommand('tail -100 /tmp/echo.log', false);
        echo "<pre style='font-size: 12px; height: 400px; overflow: scroll; border: solid 1px; padding: 5px'>";
        foreach($echo as $line) echo $line."<br />";
        echo "</pre>";
      ?>
      </div>


      <div class="content-block-title"> / tmp / network_diagnostic.log</div>
      <div class="content-block">
      <?php
        $echo = sshShellCommand('touch /tmp/network_diagnostic.log; cat /tmp/network_diagnostic.log', false);
        echo "<pre style='font-size: 12px; height: 400px; overflow: scroll; border: solid 1px; padding: 5px'>";
        foreach($echo as $line) echo $line."<br />";
        echo "</pre>";
      ?>
      </div>


    </div>
  </div>
</div>
