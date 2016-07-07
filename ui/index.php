<?php
// Iterate through modules and build array of GUI structure
$modFolder = '../modules';
if ($handle = opendir($modFolder)) {
    while (false !== ($file = readdir($handle))) {
        if ('.' === $file) continue;
        if ('..' === $file) continue;
        if (is_dir("$modFolder/$file")) continue;

        // Read each module.php into array
        $code = file("$modFolder/$file");

        // Look for #@gui and split : command
        foreach ($code as $line) {
            if (strstr($line, '#@gui')) {
                $module = ucwords(str_replace('.php', '', $file));
                $command = explode(':', $line);
                $command = $command[1];
                $gui[$module][] = $command;
            }
        }
    }
    closedir($handle);
}

// Iterate through utilities and build array for HTML nav
$modFolder = 'utilities';
if ($handle = opendir($modFolder)) {
    while (false !== ($file = readdir($handle))) {
        if ('.' === $file) continue;
        if ('..' === $file) continue;
        if (is_dir("$modFolder/$file")) continue;

        $utilities[$file] = ucwords(str_replace('.php', '', $file));
    }
    closedir($handle);
}

// Determine whether we're on a desktop or mobile device
require 'includes/Mobile_Detect.php';
$detect = new Mobile_Detect;
if( $detect->isMobile() )
    $mobile = true;
else
    $mobile = false;
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    <link rel="apple-touch-icon" sizes="57x57" href="ico/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="ico/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="ico/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="ico/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="ico/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="ico/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="ico/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="ico/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="ico/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="ico/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="ico/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="ico/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="ico/favicon-16x16.png">
    <link rel="manifest" href="ico/manifest.json">

    <title>Jarvis</title>
    <!-- Path to Framework7 Library CSS-->
    <link rel="stylesheet" href="css/framework7.min.css">
    <!-- Path to your custom app styles-->
    <link rel="stylesheet" href="css/my-app.css">
  </head>
  <body>
    <?php #echo '<pre>'; print_r($gui); echo '</pre>'; ?>
    <!-- Status bar overlay for fullscreen mode-->
    <div class="statusbar-overlay"></div>
    <!-- Right panel with cover effect-->
    <div class="panel panel-right panel-reveal">
      <div class="content-block">
        <p>Right panel content goes here</p>
      </div>
    </div>
    <!-- Views-->
    <div class="views">
      <!-- Put panels-overlay and left-panel with view inside of views-->
      <!-- Panels overlay-->
      <div class="panel-overlay"></div>
      <!-- Left panel with reveal effect-->
      <div class="panel panel-left panel-cover">
        <!-- Left view-->
        <div class="view view-left navbar-through">
          <div class="navbar">
            <div class="navbar-inner">
              <div class="left"></div>
              <div class="center sliding">Desktop Control</div>
              <div class="right"></div>
            </div>
          </div>
          <div class="pages">
            <div data-page="index-left" class="page">
              <div class="page-content">
                <div class="content-block-title">Jarvis Modules</div>
                <div class="list-block">
                  <ul>
                    <?php
                    foreach ($gui as $key=>$value) {
                        echo '
                    <li><a href="#'.$key.'" data-view=".view-main" class="item-link close-panel">
                        <div class="item-content">
                          <div class="item-inner">
                            <div class="item-title">'.$key.'</div>
                          </div>
                        </div></a></li>
                        ';
                    }
                    ?>
                  </ul>
                </div>
                <p><br /><br /></p>
                <div class="content-block-title">Utilities</div>
                <div class="list-block">
                  <ul>
                    <?php
                    foreach ($utilities as $key=>$value) {
                        echo '
                    <li><a href="utilities/'.$key.'" data-view=".view-main" class="item-link close-panel">
                        <div class="item-content">
                          <div class="item-inner">
                            <div class="item-title">'.$value.'</div>
                          </div>
                        </div></a></li>
                        ';
                    }
                    ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Right view, it is main view-->
      <div class="view view-main navbar-through">
        <div class="navbar">
          <div class="navbar-inner">
            <div class="center sliding">J.A.R.V.I.S. Home Automation</div>
            <div class="right"><a href="#" data-panel="left" class="link open-panel icon-only"><i class="icon icon-bars"></i></a></div>
          </div>

          <!-- Navbar inners for pages-->
          <?php
          foreach ($gui as $key=>$value) {
              echo '
          <div data-page="'.$key.'" class="navbar-inner cached">
            <div class="left sliding"><a href="#" class="back link"> <i class="icon icon-back"></i><span>Back</span></a></div>
            <div class="center sliding">'.$key.'</div>
          </div>
            ';
          }
          ?>

        </div>
        <!-- Pages-->
        <div class="pages">
          <!-- Page, data-page contains page name-->
          <div data-page="index-1" class="page">
            <!-- Scrollable page content-->
            <div class="page-content">

                 <?php
                    // If we're on Mobile
                    if ($mobile) {
                        echo '<div class="list-block">
                                <ul>';
                                    foreach ($gui as $key=>$value) {
                                        echo '
                                          <li><a href="#'.$key.'" class="item-link">
                                              <div class="item-content">
                                                <div class="item-inner">
                                                  <div class="item-title">'.$key.'</div>
                                                </div>
                                              </div></a></li>
                                        ';
                                    }
                        echo '</ul>
                           </div>';
                   } else {
                       // Desktop
                       echo '<img src="images/desktop.png" />';
                   }
                 ?>
            </div>
          </div>

         <?php
            foreach ($gui as $key=>$value) {
                echo '
                  <div data-page="'.$key.'" class="page cached">
                    <div class="page-content">
                      <div class="content-block">
                        <div class="list-block">
                          <ul>';

                foreach ($gui[$key] as $command) {
                    echo '
                          <li><a href="#" class="jarvis-command item-link">
                              <div class="item-content">
                                <div class="item-inner">
                                  <div class="item-title">'.$command.'</div>
                                </div>
                              </div></a></li>
                        ';
                }

                echo '
                        </ul>
                      </div>
                    </div>
                  </div>
                ';
            }
         ?>
        </div>
      </div>
    </div>

    <!-- Path to Framework7 Library JS-->
    <script type="text/javascript" src="js/framework7.min.js"></script>

    <!-- Plug ins -->
    <link rel="stylesheet" href="css/toast.css">
    <script type="text/javascript" src="js/toast.js"></script>

    <!-- Path to your app js-->
    <script type="text/javascript" src="js/my-app.js"></script>
  </body>
</html>
