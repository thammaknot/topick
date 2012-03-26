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
require "classes/Chat.class.php";
require "classes/ChatBase.class.php";
require "classes/ChatLine.class.php";
require "classes/ChatUser.class.php";

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

  case 'login':
    $response = Chat::login($_POST['name'],$_POST['email']);
    break;

  case 'checkLogged':
    $response = Chat::checkLogged();
    break;

  case 'logout':
    $response = Chat::logout();
    break;

  case 'submitChat':
    $response = Chat::submitChat($_POST['chatText'],
				 $_POST['session_id'],
				 $_POST['user_id']);
    break;

  case 'getUsers':
    $response = Chat::getUsers();
    break;

  case 'getChats':
    // First, we must check session status
    $status = Chat::getSessionStatus($_GET['session_id']);
    if ($status == -1) {
      $response = array('status' => -1);
    } else {
      $response = Chat::getChats($_GET['lastID'], $_GET['session_id']);
    }
    break;

  case 'newSession':
    $response = Chat::newSession($_POST['user_id']);
    break;

  case 'waitForNewSession':
    $response = Chat::waitForNewSession($_GET['user_id']);
    break;

  case 'endSession':
    error_log("****End session!!!!!!" . print_r($_POST, true));
    $response = Chat::endSession($_POST['session_id'], $_POST['user_id']);
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