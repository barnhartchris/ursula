<?php
// Setup
require "functions.php";
putenv("PATH=" .$_SERVER["PATH"]. ':/mnt/documents/bin'); // for shell scripts to execute
$applicationIdValidation    = 'amzn1.echo-sdk-ams.app.#CHANGE ME#';
$userIdValidation           = 'amzn1.account.#CHANGE ME#';
$echoServiceDomain          = 'echo-api.amazon.com';

// Capture Amazon's POST JSON request:
$jsonRequest    = file_get_contents('php://input');
$data           = json_decode($jsonRequest, true);

//
// Logging
//
$log        = '/tmp/echo.log';
$request    = "\n\n\n--------------------------------------------------------------------------------------------------";
$request   .= "\n-------------------------------" . date(DATE_RFC2822) . "------------------------------------\n";

// Apache headers
$headers = apache_request_headers();

foreach ($headers as $header => $value) {
   $request .= "$header: $value \n";
}

// HTTP POST Data
$request .= "HTTP Raw Data: ";
$request .= $jsonRequest;

// PHP Array from JSON
$request .= "\n\nPHP Array from JSON: ";
$request .= print_r($data, true);

// Write raw request to log file
file_put_contents($log, $request, FILE_APPEND);

//
// END Logging
//

//
// Parse out key variables
//
$sessionId          = @$data['session']['sessionId'];
$applicationId      = @$data['session']['application']['applicationId'];
$userId             = @$data['session']['user']['userId'];
$requestTimestamp   = @$data['request']['timestamp'];
$command            = @$data['request']['intent']['slots']['command']['value'];
$command            = strtolower($command); // For the GUI
$requestType        = $data['request']['type'];

@list($script,$args) = explode(" ", $command);

//
// Validations based on API documentation at:
// https://developer.amazon.com/appsandservices/solutions/alexa/alexa-skills-kit/docs/developing-an-alexa-skill-as-a-web-service#Checking%20the%20Signature%20of%20the%20Request
//

// Amazon Echo comes as an HTTP POST, if this is a GET, die with no info, confuse the haxxors
if ($_SERVER['REQUEST_METHOD'] == 'GET') fail('HTTP GET when POST was expected');

// Only validate if it's not a LAN request
if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {

    // Die if applicationId isn't valid
    if ($applicationId != $applicationIdValidation) fail('Invalid Application id: ' . $applicationId);

    // Die if this request isn't coming from Matt Farley's Amazon Account
    if ($userId != $userIdValidation) fail('Invalid User id: ' . $userId);

    // Determine if we need to download a new Signature Certificate Chain from Amazon
    $md5pem = md5($_SERVER['HTTP_SIGNATURECERTCHAINURL']);
    $md5pem = $md5pem . '.pem';

    // If we haven't received a certificate with this URL before, store it as a cached copy
    if (!file_exists($md5pem)) {
        file_put_contents($md5pem, file_get_contents($_SERVER['HTTP_SIGNATURECERTCHAINURL']));
    }

    // Validate proper format of Amazon provided certificate chain url
    validateKeychainUri($_SERVER['HTTP_SIGNATURECERTCHAINURL']);

    // Validate certificate chain and signature
    $pem = file_get_contents($md5pem);
    $ssl_check = openssl_verify($jsonRequest, base64_decode($_SERVER['HTTP_SIGNATURE']), $pem);
    if ($ssl_check != 1)
        fail(openssl_error_string());

    // Parse certificate for validations below
    $parsedCertificate = openssl_x509_parse($pem);
    if (!$parsedCertificate)
        fail('x509 parsing failed');

    // Check that the domain echo-api.amazon.com is present in the Subject Alternative Names (SANs) section of the signing certificate
    if(strpos($parsedCertificate['extensions']['subjectAltName'], $echoServiceDomain) === false)
        fail('subjectAltName Check Failed');

    // Check that the signing certificate has not expired (examine both the Not Before and Not After dates)
    $validFrom = $parsedCertificate['validFrom_time_t'];
    $validTo   = $parsedCertificate['validTo_time_t'];
    $time      = time();
    if (!($validFrom <= $time && $time <= $validTo))
        fail('certificate expiration check failed');

    // Check the timestamp of the request and ensure it was within the past minute
    if (time() - strtotime($requestTimestamp) > 60)
        fail('timestamp validation failure.. Current time: ' . time() . ' vs. Timestamp: ' . $requestTimestamp);

    // Network Notify what Jarvis heard (what Amazon interpreted)
    if (strlen($command) > 0)
        sshShellCommand('notify_network /mnt/documents/Icons/jarvis.png "" "Jarvis heard the following:" "' . $command . '" ""');
}

//
// Debugging:
//
//respond($command . '. Goodbye.');

// Parse command, determine which function to call
$spokenWords = explode(' ', $command);
$firstThreeWords = array_slice($spokenWords,0,3);
$firstFiveWords = array_slice($spokenWords,0,5);

// If this is a launch request, just prompt for action
if ($requestType == 'LaunchRequest')
    respond("Jarvis started");

// Iterate through modules and execute matched commands
$modFolder = 'modules';
if ($handle = opendir($modFolder)) {
    while (false !== ($file = readdir($handle))) {
        if ('.' === $file) continue;
        if ('..' === $file) continue;
        if (is_dir("$modFolder/$file")) continue;

        require("$modFolder/$file");
    }
    closedir($handle);
}

// None of the modules identified the proper command
respond("Jarvis says please repeat");
