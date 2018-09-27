<?php
$_DEBUG = 0;
$_SETTINGS = parse_ini_file( 'config.ini.php' );

# Grab some of the values from the slash command, create vars for post back to Slack
$command = $_POST['command'];
$text = $_POST['text'];

# Validate signed secret to ensure the request comes from our Slack app
# https://api.slack.com/docs/verifying-requests-from-slack
$basestring = implode(':', array(
  'v0',
  $_SERVER['HTTP_X_SLACK_REQUEST_TIMESTAMP'],
  file_get_contents("php://input"),
));
$computed_sig = 'v0=' . hash_hmac( 'sha256', $basestring, $_SETTINGS['SIGNING_SECRET'] );

if ($_DEBUG){
  error_log('HTTP_X_SLACK_REQUEST_TIMESTAMP: ' . $_SERVER['HTTP_X_SLACK_REQUEST_TIMESTAMP'], 0);
  error_log('Computed Signature: ' . $computed_sig, 0);
  error_log('Actual Signature: ' . $_SERVER['HTTP_X_SLACK_SIGNATURE'], 0);
}

if ( ! hash_equals($_SERVER['HTTP_X_SLACK_SIGNATURE'], $computed_sig) ) die("Slack Signature Invalid.");

switch( $command ) {
  
  case '/isitup':
      # Get JSON version. If it's not a valid domain, isitup.org will respond with a `3`.
      $isitupurl = "https://isitup.org/".$text.".json";

      # Set up cURL 
      $ch = curl_init($isitupurl);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, "CustomSlackCommands/1.0 (" . $_SETTINGS['USER_AGENT_URL'] . ")");
      # Do Action
      $response = curl_exec($ch);
      curl_close($ch);

      # Decode the JSON array sent back by isitup.org
      $iiu_response = json_decode($response,true);
      
      # Build our response 
      if ($response === FALSE){
        $reply = ":interrobang: Sorry, isitup may be down.";
      } 
      else {
        if($iiu_response["status_code"] == 1){ //up
          $reply = ":thumbsup: <http://{$iiu_response['domain']}|{$iiu_response['domain']}> is *up*";
        } else if($iiu_response["status_code"] == 2){ //down
          $reply = ":disappointed: <http://{$iiu_response['domain']}|{$iiu_response['domain']}> is *not up*";
        } else if($iiu_response["status_code"] == 3){ //invalid
          $reply = ":interrobang: $text does not appear to be a valid domain. \n";
        }
      }
  
      break;
      
  case '/getip':
  case '/host':
      $reply = gethostbyname($text);
      break;
      
  case '/whois':
      $reply = get_from_term("whois $text");
      break;
      
  case '/ping':
      $reply = get_from_term("ping -c1 $text");
      break;
      
  case '/dig':
      $reply = get_from_term("dig $text");
      break;
      
  default:
      $reply = "The command '$command' is invalid.";
      
}

# Send the reply back to the user. 
echo $reply;

function get_from_term($cmd){
  $output = array();
  exec($cmd, $output);
  return "```" . implode("\n", $output) . "```";
}
