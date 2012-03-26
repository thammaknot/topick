<?php

  /* Chat line is used for the chat entries */

class ChatLine extends ChatBase {
  protected $text = '', $author = '', $session_id = '';

  public function save() {
    $query = "INSERT INTO chat_lines (author, session_id, text) VALUES ('" .
      DB::esc($this->author) . "','" . DB::esc($this->session_id) . "','" .
      DB::esc($this->text) . "')";
    error_log("Executing query to save: " . $query);
    $result = DB::query($query);
    error_log("result  is " . print_r($result, true));
    // Returns the MySQLi object of the DB class
    return DB::getMySQLiObject();
  }
}

?>