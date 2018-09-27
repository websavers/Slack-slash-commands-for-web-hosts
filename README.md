# Slack slack commands for web hosts

REQUIREMENTS

* A custom slash command on a Slack team
* A web server running PHP7.2 with cURL enabled (probably works with earlier versions too)
* Your Slack Signing Secret. Create a Slack app and find it under your app's "Settings > Basic Information" page
* For /whois to work you must have the whois utility installed on your server

USAGE

0. Upload files to your web server
1. Either edit your existing Slack app or create a new one [here](https://api.slack.com/apps).
2. Obtain the Signing Secret under the app's "Settings > Basic Information" 
   page and save it to config.ini.php file
3. Under "Features > Slash Commands, create a command for each you wish to use.
   e.g.: /isitup /getip (or /host) /whois /ping /dig
4. Enter the URL to the location of the script on your server (same one for each command)
5. Provide a short description and usage hint
6. If using an existing Slack app, reload your app when prompted
