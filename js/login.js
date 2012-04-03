var login = {
  validateLogin : function() {
    var username = $('#username').val();
    var password = $('#password').val();
    console.log("U:" + username + "," + password);
    $.tzPOST('login', {username: username, password: password},
	     function(r) {
	       console.log("## " + r.status);
	     });
    return false;
  }
};  // end class topics

// Custom GET & POST wrappers:
$.tzPOST = function(action, data, callback) {
  $.post('php/login.php?action=' + action, data, callback, 'json');
}

$.tzGET = function(action, data, callback) {
  $.get('php/login.php?action=' + action, data, callback, 'json');
}
