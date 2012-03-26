<?php

  /* The Chat class exploses public static methods, used by ajax.php */
class Chat {
  public static function login($name, $email) {
    if(!$name || !$email){
      throw new Exception('Fill in all the required fields.');
    }

    if(!filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL)){
      throw new Exception('Your email is invalid.');
    }

    // Preparing the gravatar hash:
    $gravatar = md5(strtolower(trim($email)));
    $user = new ChatUser(array(
			       'name'		=> $name,
			       'gravatar'	=> $gravatar
			       ));

    // The save method returns a MySQLi object
    if($user->save()->affected_rows != 1){
      throw new Exception('This nick is in use.');
    }

    $_SESSION['user']	= array(
				'name'		=> $name,
				'gravatar'	=> $gravatar
				);

    return array(
		 'status'	=> 1,
		 'name'		=> $name,
		 'gravatar'	=> Chat::gravatarFromHash($gravatar)
		 );
  }

  public static function checkLogged(){
    $response = array('logged' => false);

    if($_SESSION['user']['name']){
      $response['logged'] = true;
      $response['loggedAs'] = array(
				    'name'		=> $_SESSION['user']['name'],
				    'gravatar'	=> Chat::gravatarFromHash($_SESSION['user']['gravatar'])
				    );
    }

    return $response;
  }

  public static function logout(){
    DB::query("DELETE FROM webchat_users WHERE name = '".DB::esc($_SESSION['user']['name'])."'");

    $_SESSION = array();
    unset($_SESSION);

    return array('status' => 1);
  }

  public static function submitChat($chatText, $session_id, $user_id){
    if(!$chatText){
      throw new Exception('You haven\'t entered a chat message.');
    }

    $chat = new ChatLine(array('author'	=> $user_id,
			       'text' => $chatText,
			       'session_id' => $session_id));
    error_log('Chat object created ' . print_r($chat, true));

    // The save method returns a MySQLi object
    $insertID = $chat->save()->insert_id;

    error_log('done saving chat object!!!');
    return array('status'	=> 1,
		 'insertID'	=> $insertID);
  }

  public static function getUsers(){
    if($_SESSION['user']['name']){
      $user = new ChatUser(array('name' => $_SESSION['user']['name']));
      $user->update();
    }

    $result = DB::query('SELECT * FROM webchat_users ORDER BY name ASC LIMIT 18');

    $users = array();
    while($user = $result->fetch_object()){
      $user->gravatar = Chat::gravatarFromHash($user->gravatar,30);
      $users[] = $user;
    }

    return array(
		 'users' => $users,
		 'total' => DB::query('SELECT COUNT(*) as cnt FROM webchat_users')->fetch_object()->cnt
		 );
  }

  public static function getSessionStatus($session_id) {
    // First we must check if this session is still active.
    $session_query = "SELECT status from chat_sessions WHERE id = '" . $session_id . "'";
    $result = DB::query($session_query);
    if ($result->num_rows == 1) {
      $row = $result->fetch_object();
      return $row->status;
    }
    // If not found, consider the session terminated.
    return -1;
  }

  public static function getChats($last_id, $session_id) {
    $lastID = (int) $last_id;

    $query = "SELECT * FROM chat_lines WHERE session_id = '" . $session_id .
      "' AND id > " . $last_id . " ORDER BY id ASC";
    $result = DB::query($query);
    $chats = array();
    while($chat = $result->fetch_object()){
      // Returning the GMT (UTC) time of the chat creation:
      $chat->time = array('hours' => gmdate('H', strtotime($chat->ts)),
			  'minutes' => gmdate('i', strtotime($chat->ts)));
      $chats[] = $chat;
    }

    return array('chats' => $chats);
  }

  // Create a new chat session. The gist of this is to really pair up the user
  // with another available user.
  public static function newSession($user_id) {
    $kMaxChatCandidates = 10;
    $query = 'SELECT * FROM available_users ORDER BY RAND() LIMIT ' . $kMaxChatCandidates;
    $result = DB::query($query);
    $paired_user = null;
    while ($candidate = $result->fetch_object()) {
      $candidate_id = $candidate->id;
      if ($candidate_id == $user_id) {
	continue;
      }
      $delete_query = "DELETE FROM available_users WHERE id = '" . $candidate_id . "'";
      $deletion_result = DB::query($delete_query);
      if ($deletion_result == 1) {
	// We have found a pair!
	$paired_user = $candidate_id;
	break;
      }
    } // end while

    if (isset($paired_user)) {
      $session_id = Chat::CreateNewChatSession($user_id, $paired_user);
      return array('found' => true,
		   'session_id' => $session_id,
		   'paired_user_id' => $paired_user,
		   'msg' => 'found partner for ' . $user_id);
    } else {
      $insert_query = "INSERT INTO available_users (id) VALUES('" . $user_id . "')";
      $insertion_result = DB::query($insert_query);

      return array('found' => false,
		   'msg' => 'Could not find partner for ' . $user_id);
    }
  }

  private static function CreateNewChatSession($user_id1, $user_id2) {
    // Create new entry in session table
    $session_id = uniqid();
    $query_suffix = "','" . $user_id1 . "','" . $user_id2 . "')";
    $query = "INSERT INTO chat_sessions (id,user1,user2) VALUES('"
      . $session_id . $query_suffix;
    // Keep inserting until the session_id is unique.
    while (DB::query($query) != 1) {
      $session_id = uniqid();
      $query = "INSERT INTO chat_sessions (id,user1,user2) VALUES('"
	. $session_id . $query_suffix;
    }

    return $session_id;
  }

  // Queries DB to see if anyone has picked this user as a chat participant.
  public static function waitForNewSession($user_id) {
    $query = "SELECT id FROM chat_sessions WHERE user2 = '" .
      $user_id . "' AND status = 0";
    if ($result = DB::query($query)) {
      $num_rows = $result->num_rows;
      if ($num_rows == 1) {
	// Found! Now, we must
	// 1) Mark this session as no longer pending (pending (0) -> active (1).
	// 2) Return the session id back to the client.
	$row = $result->fetch_object();
	$session_id = $row->id;
	$query = "UPDATE chat_sessions SET status = 1 WHERE id = '" . $session_id . "'";
	$updated = DB::query($query);
	if ($updated != 1) {
	  error_log('Failed to update the status for session ' . $session_id);
	}
	$result->close();
	return array('session_id' => $session_id);
      } else if ($num_rows > 1) {
	error_log('Something is wrong here. User ' . $user_id . ' found active in ' .
		  $num_rows . ' sessions.');
	$result->close();
      }
    }
    return array();
  }

  public static function endSession($session_id, $user_id) {
    // There are 2 cases:
    // 1) If the user is already in a session:
    //   - update chat_sessions table to reflect current session status (ENDED) and end-time.
    // 2) If the user is in a waiting stage:
    //   - simply remove the user from the available users table
    error_log("End session called!!! sess: " . $session_id . " user: " . $user_id);
    if ($session_id != 'undefined') {
      $time = date("Y-m-d H:i:s");
      $query = "UPDATE chat_sessions SET status = -1, end_time = '" .
	$time . "' WHERE id = '" . $session_id . "' AND status != -1";
      // We need to add the 'status != -1' clause to the statement to make sure we don't
      // update the end_time (incorrectly) when the second participant ends the session.
      error_log(">>>> setting status to -1");
      DB::query($query);
    } else {
      error_log("@@@@ deleting from available users");
      $delete_query = "DELETE FROM available_users WHERE id = '" . $user_id . "'";
      DB::query($delete_query);
      $num_affected_rows = DB::getMySQLiObject()->affected_rows;
      if ($num_affected_rows != 1) {
	// Someone must have picked this user in the meantime.
	$time = date("Y-m-d H:i:s");
	$query = "UPDATE chat_sessions SET status = -1, end_time = '" .
	  $time . "' WHERE (user1 = '" . $user_id . "' OR user2 = '" .
	  $user_id . "') AND status != -1";
        DB::query($query);
      }
    }
    return array('status' => 'success');
  }

  public static function gravatarFromHash($hash, $size=23){
    return 'http://www.gravatar.com/avatar/'.$hash.'?size='.$size.'&amp;default='.
      urlencode('http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?size='.$size);
  }

  }


?>