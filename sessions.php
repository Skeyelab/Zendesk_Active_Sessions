<?php

require 'vendor/autoload.php';

Dotenv::load(__DIR__);

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PlainTextHandler);
$whoops->register();



use Zendesk\API\Client as ZendeskAPI;

$subdomain = $_ENV['ZD_DOMAIN'];
$username  = $_ENV['ZD_USER'];
$token     = $_ENV['ZD_TOKEN']; // replace this with your token

$client = new ZendeskAPI($subdomain, $username);
$client->setAuth('token', $token); // set either token or password

$uri = "https://$subdomain.zendesk.com/api/v2/sessions.json";

$sessions = [];

do{
  $response = \Httpful\Request::get($uri)->authenticateWith($username."/token", $token)->send();

  foreach($response->body->sessions as $session){

    try {
    $user = $client->users()->find(["id"=>$session->user_id]);
    } catch (Exception $e) {
      echo 'Caught exception: ',  $e->getMessage(), "\n";
    }

    $session->user_role = $user->user->role;

    if ($session->user_role == "end-user"){

      $row = [$user->user->email,$session->user_role];

      $sessions[] = $row;
      echo "X";

    }
    else {
      echo "_";

    }
  }

  $uri = $response->body->next_page;
}while($response->body->next_page != null);

echo PHP_EOL;

$table = new \cli\Table();
$table->setRows($sessions);
$table->display();
