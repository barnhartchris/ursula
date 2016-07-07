// Initialize your app
var myApp = new Framework7();

// Export selectors engine
var $$ = Dom7;

// Add views
var leftView = myApp.addView('.view-left', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: true
});
var mainView = myApp.addView('.view-main', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: true,
    domCache:true
});

// Function to execute / send command to Jarvis
function postCommand(obj) {

    // Get command from DOM element and remove line breaks
    var command = $$(obj).text();
    command = command.replace(/(\r\n|\n|\r)/gm,""); // Strip newlines

    // If we need user input for free-form variable
    if (command.indexOf('_custom input_') > -1) {

        var input = prompt(command, "");
        if (input != null) {
            command = command.replace('_custom input_',input);
        } else {
            return;
        }
    }

    // Remove all non-alphanumeric from JSON command (Alexa only does alpha-numeric
    command = command.replace(/[^a-zA-Z0-9 :]/g, '');

    // Send command to jarvis
    // *** NB: Rooted AdBlocker on my Cell Phone sometimes interfers with AJAX requests!!!
    $$.post('../main.php', '{"request":{"type":"IntentRequest","intent":{"name":"DoCommand","slots":{"command":{"name":"command","value":"'+command+'"}}}}}', function (data) {
      var jsonData = JSON.parse(data);
      var toast = myApp.toast(jsonData.response.outputSpeech.text, '', {})
      toast.show(true);
    });

}

// Apply Jarvis Interactions to html class
$$('.jarvis-command').on('click', function (e) {

    postCommand(this);

});

