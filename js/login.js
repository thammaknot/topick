var login = {
  validateLogin : function() {
    var username = $('#username').val();
    var password = $('#password').val();
    console.log("U:" + username + "," + password);
    $.tzPOST('login', {username: username, password: password},
	     function(r) {
	       if (r.status == 1) {
		 window.location = "/topick/ajax-chat.html";
	       } else {
		 displayError("Login failed. " + r.msg);
	       }
	     });
    return false;
  },

  validateSignup : function() {
    var username = $('#username').val();
    var password = $('#password').val();
    var password2 = $('#password-verify').val();
    var email = $('#email').val();
    if (!login.validateUsername(username)) {
      return false;
    } else if (!login.validatePassword(password, password2)) {
      return false;
    } else if (!login.validateEmail(email)) {
      return false;
    }

    $.tzPOST('signup', {username: username, password: password, email: email},
	     function(r) {
	       if (r.status == 1) {
		 window.location = "/topick/ajax-chat.html";
	       } else {
		 displayError("Signup failed. " + r.msg);
	       }
	     });
    return false;
  },

  validateUsername : function(username) {
    if (username.length < 4 || username.length > 20) {
      displayError("Username must be between 4 and 20 characters.");
      return false;
    }
    var re = /^[a-z](?=[\w.]{3,19}$)\w*\.?\w*$/i;
    if (!re.test(username)) {
      displayError("Username contains invalid character(s).");
      return false;
    }
    return true;
  },

  validatePassword : function(pw1, pw2) {
    if (pw1.length < 6 || pw1.length > 13) {
      displayError("Password must be between 6 to 13 characters.");
      return false;
    }
    if (pw1 != pw2) {
      displayError("Password verification failed.");
      return false;
    }
    return true;
  },

  validateEmail : function(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if(!re.test(email)) {
      displayError("Please enter a valid email address.");
      return false;
    }
    return true;
  }
};  // end class login

displayError = function(msg) {
  var elem = $('<div>',{
    id	: 'chatErrorMessage',
    html	: msg
  });

  elem.click(function(){
    $(this).fadeOut(function(){
      $(this).remove();
    });
  });

  setTimeout(function(){
    elem.click();
  }, 2000);

  elem.hide().appendTo('body').slideDown();
}

// Custom GET & POST wrappers:
$.tzPOST = function(action, data, callback) {
  $.post('php/login.php?action=' + action, data, callback, 'json');
}

$.tzGET = function(action, data, callback) {
  $.get('php/login.php?action=' + action, data, callback, 'json');
}
