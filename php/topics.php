<?php

  /* Database Configuration. Add your details below */
$dbOptions = array(
		   'db_host' => 'localhost',
		   'db_user' => 'root',
		   'db_pass' => '{ko-hv,^]',
		   'db_name' => 'chat'
		   );

/* Database Config End */

error_reporting(E_ALL ^ E_NOTICE);

require "classes/DB.class.php";
require "classes/Topic.class.php";

session_name('webchat');
session_start();

if(get_magic_quotes_gpc()){
  // If magic quotes is enabled, strip the extra slashes
  array_walk_recursive($_GET, create_function('&$v,$k','$v = stripslashes($v);'));
  array_walk_recursive($_POST, create_function('&$v,$k','$v = stripslashes($v);'));
}

try{

  // Connecting to the database
  DB::init($dbOptions);

  $response = array();

  // Handling the supported actions:

  switch($_GET['action']){

  case 'addTopic':
    $response = Topic::addTopic($_POST['topic']);
    break;
  case 'getTopTopics':
    $response = Topic::getTopTopics($_GET['range'], $_GET['count']);
    break;
  case 'getRecentTopics':
    $response = Topic::getRecentTopics($_GET['count']);
    break;
  case 'vote':
    $response = Topic::vote($_POST['id'], $_POST['score']);
    break;
  default:
    throw new Exception('Wrong action');
  }

  echo json_encode($response);
}
catch(Exception $e){
  die(json_encode(array('error' => $e->getMessage())));
}

?>