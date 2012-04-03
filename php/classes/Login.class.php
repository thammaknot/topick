<?php

class Login {
  public static function login($username, $password) {
    $query = "SELECT count(*) FROM users WHERE username = '" .
      $username . "' AND password = sha1('" . $password . "')";
    $result = DB::query($query);
    if ($result->num_row == 1) {
      return array('status' => '1');
    } else {
      return array('status' => '0');
    }
  }
}  // end class Topic


?>