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
require "classes/Login.class.php";

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
    $response = Login::loginUser($_POST['username'], $_POST['password']);
    break;
  case 'logout':
    $response = Login::logoutUser($_POST['username']);
    break;
  case 'signup':
    $response = Login::signup($_POST['username'], $_POST['password'], $_POST['email']);
    break;
  case 'checkUsername':
    $response = Login::checkUsername($_GET['username']);
    break;
  case 'auth':
    // Check if the user of the current session already logs in.
    $response = Login::auth();
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