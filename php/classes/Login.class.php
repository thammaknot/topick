<?php

class Login {
  public static function loginUser($username, $password) {
    $query = "SELECT username FROM users WHERE username = '" .
      $username . "' AND password = sha1('" . $password . "')";
    $result = DB::query($query);
    if ($result->num_rows == 1) {
      //Login Successful
      //Regenerate session ID to prevent session fixation attacks
      session_regenerate_id();
      $user = $result->fetch_object();
      $_SESSION['SESS_MEMBER_ID'] = $user->username;

      //Write session to disc
      session_write_close();
      return array('status' => 1);
    } else {
      return array('status' => 0, 'msg' => 'Incorrect username or password');
    }
  }

  public static function signup($username, $password, $email) {
    $query = "INSERT INTO users (username, password, email) VALUES('" .
      $username . "',sha1('" . $password . "'),'" . $email . "')";
    error_log("Executing: " . $query);
    $result = DB::query($query);
    if ($result != 1) {
      $query = "SELECT email FROM users WHERE email = '" . $email . "'";
      $result = DB::query($query);
      error_log("Result is >>> " . print_r($result, true));
      if ($result->num_rows > 0) {
	$msg = "Email address " . $email . " has already been used.";
      } else {
	$msg = "Username " . $username . " is already taken.";
      }
      return array('status' => 0, 'msg' => $msg);
    } else {
      return array('status' => 1);
    }
  }

  public static function checkUsername($username) {
    $query = "SELECT count(*) FROM users WHERE username = '" . $username . "'";
    $result = DB::query($query);
    if ($result->num_row == 1) {
      // Already exists!
      return array('status' => 0);
    } else {
      return array('status' => 1);
    }
  }

  public static function auth() {
    //Start session
    session_start();

    //Check whether the session variable SESS_MEMBER_ID is present or not
    if(!isset($_SESSION['SESS_MEMBER_ID']) || (trim($_SESSION['SESS_MEMBER_ID']) == '')) {
      return array('status' => 0);
    }
    return array('status' => 1, username => $_SESSION['SESS_MEMBER_ID']);
  }
}  // end class Topic


?>