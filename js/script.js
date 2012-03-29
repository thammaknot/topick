var user_id = null;
var global_session_id = 'undefined';

$(document).ready(function() {
  // Run the init method on document ready:
  chat.init();
  user_id = utils.guidGenerator();
  utils.registerKeyboardShortcuts();
});

window.onbeforeunload = function () {
  // When the user navigates away from the page (closing tab, reloading, etc),
  // we want to signal end-of-session msg to the server so that it can
  // clean things up appropriately. Here, we need to perform synchronous ajax
  // call to the server to end the session, otherwise the browser will
  // continue to unload the page and our ajax call will simply be unexecuted.
  $.ajax({ url: 'php/ajax.php?action=endSession',
	   data: {session_id: global_session_id, user_id: user_id},
	   type: "POST",
	   async: false
	 });
}

// Update new/end button to allow ending a session
function ToggleSessionButton() {
  button = $('#session');
  if (button.text() == 'New Chat') {
    button.html('End Chat');
  } else {
    button.html('New Chat');
  }
}

function UpdateToNewSession() {
  $('#endSession').html
}

function ResetChatTextBox() {
  $('#chatText').removeAttr('disabled');
  $('#chatText').val('');
}

function ResetChats() {
  chat.data.jspAPI.getContentPane().empty();
  // Readjust the scrollbar
  chat.data.jspAPI.reinitialise();
}

var chat = {
  // data holds variables for use in the class:

  data : {
    lastID : 0,
    noActivity	: 0
  },

  initChatPanel : function() {
    // Converting the #chatLineHolder div into a jScrollPane,
    // and saving the plugin's API in chat.data:
    chat.data.jspAPI = $('#chatLineHolder').jScrollPane({
      verticalDragMinHeight: 12,
      verticalDragMaxHeight: 12
    }).data('jsp');
  },

  // Init binds event listeners and sets up timers:
  init : function() {
    chat.initChatPanel();
    // We use the working variable to prevent
    // multiple form submissions:
    var working = false;

    // Submitting a new chat entry:
    $('#submitForm').submit(function() {
      if (global_session_id == 'undefined') {
	chat.displayError('Still looking for chat partner. Please wait...');
	return false;
      }

      var text = $('#chatText').val();

      if (text.length == 0) {
	return false;
      }

      if (chat.working) {
	return false;
      }
      chat.working = true;

      // Assigning a temporary ID to the chat:
      var tempID = 't' + Math.round(Math.random() * 1000000),
	params = {
	  id : tempID,
	  author : user_id,
	  text : text.replace(/</g, '&lt;').replace(/>/g, '&gt;')
	};

      // Using our addChatLine method to add the chat
      // to the screen immediately, without waiting for
      // the AJAX request to complete:
      chat.addChatLine($.extend({}, params));

      // Using our tzPOST wrapper method to send the chat
      // via a POST AJAX request:
      $.tzPOST('submitChat',
	       {chatText: text, session_id: global_session_id, user_id: user_id},
	       function(r) {
		 chat.working = false;
		 $('#chatText').val('');
		 $('div.chat-' + tempID).remove();
		 params['id'] = r.insertID;
		 chat.addChatLine($.extend({}, params));
	       });

      return false;
    });

    document.getElementById('session').onclick = function() {
      if (chat.working) {
	return false;
      }
      chat.working = true;

      button = $('#session');
      if (button.text() == 'New Chat') {
	chat.newSession(user_id);
      } else {
	chat.endSession(global_session_id);
      }
      ToggleSessionButton();
    };

    document.getElementById('topic').onclick = function() {
      if (chat.working) {
	return false;
      }
      chat.working = true;
      chat.getNewTopic();
    };
  },

  getChatsForCurrentSession : function() {
    if (global_session_id == 'undefined') {
      return;
    }
    $.tzGET('getChats',
	    {session_id: global_session_id, lastID: chat.data.lastID},
	    function(r) {
	      // Check if session has ended
	      if (r.status == -1) {
		// Ended!
		ToggleSessionButton();
		utils.endSessionCleanUp();
		return;
	      }

	      for (var i = 0; i < r.chats.length; i++) {
		chat.addChatLine(r.chats[i]);
	      }

	      if (r.chats.length) {
		chat.data.noActivity = 0;
		chat.data.lastID = r.chats[i - 1].id;
	      } else {
		// If no chats were received, increment
		// the noActivity counter.
		chat.data.noActivity++;
	      }

	      if (!chat.data.lastID) {
		chat.data.jspAPI.getContentPane().html('<p class="noChats">Don\'t be shy. Say something!</p>');
	      }

	      // Setting a timeout for the next request,
	      // depending on the chat activity:
	      var nextRequest = 1000;

	      // 2 seconds
	      if (chat.data.noActivity > 3) {
		nextRequest = 2000;
	      }

	      if (chat.data.noActivity > 10) {
		nextRequest = 5000;
	      }

	      // 15 seconds
	      if (chat.data.noActivity > 20) {
		nextRequest = 15000;
	      }

	      var curried = function () { chat.getChatsForCurrentSession(); };
	      setTimeout(curried, nextRequest);
	    });
  },

  waitForNewSession : function(user_id) {
    $.tzGET('waitForNewSession', {user_id: user_id}, function(r) {
      if (typeof r.session_id != 'undefined') {
	// Found!
	chat.data.msg = 'In session ' + r.session_id;
	$('#chatTopBar').html(chat.render('debug', chat.data));
	global_session_id = r.session_id;
	chat.getChatsForCurrentSession();
      } else {
	newSessionWaitIntervalMs = 1000;
	var curried = function () { chat.waitForNewSession(user_id); }
	setTimeout(curried, newSessionWaitIntervalMs);
      }
    });
  },

  newSession : function(user_id) {
    ResetChatTextBox();
    ResetChats();
    chat.data.msg = 'New session for ' + user_id;
    $('#chatTopBar').html(chat.render('debug', chat.data));
    chat.data.user_id = user_id;
    $.tzPOST('newSession', {user_id: user_id}, function(r) {
      console.log("$$$$ Server responded to newSession!!!");
      chat.working = false;
      if (typeof r.session_id != 'undefined') {
	// Successfully created a session.
	global_session_id = r.session_id;
	chat.getChatsForCurrentSession();
	chat.data.msg = r.msg + ' session id = ' + r.session_id;
      } else {
	// In this case, we failed to find a participant. We must
	// open a request to the server and wait until someone picks
	// this user as a pair and create a new session out of it.
	chat.data.msg = r.msg + ' no session id';
	chat.waitForNewSession(user_id);
      }
      $('#chatTopBar').html(chat.render('debug', chat.data));
    });
  },

  // Ends the current chat session.
  endSession : function() {
    utils.endSessionCleanUp();
    $.tzPOST('endSession', {session_id: global_session_id, user_id: user_id}, function(r) {
      console.log("$$$$$ Server responded to EndSession...!!!");
      chat.working = false;
      chat.data.msg = r.status;
      $('#chatTopBar').html(chat.render('debug', chat.data));
    });
  },

  addEndOfSessionMessage : function() {
    var params = {
      id: 9999999
    };

    if(!chat.data.lastID){
      // If this is the first chat, remove the
      // paragraph saying there aren't any:
      $('#chatLineHolder p').remove();
    }

    var markup = chat.render('endOfSession', params);
    chat.data.jspAPI.getContentPane().append(markup);
    // As we added new content, we need to
    // reinitialise the jScrollPane plugin:
    chat.data.jspAPI.reinitialise();
    chat.data.jspAPI.scrollToBottom(true);
  },

  getNewTopic : function() {
    $.tzGET('newTopic',
	    {session_id: global_session_id, user_id: user_id},
	    function(r) {
	      chat.working = false;
	      var params = {
		id : r.insertID,
		author : user_id,
		text : r.text
	      };
	      console.log("Response: ");
	      console.log(r);
	      chat.addChatLine(params);
	    });
  },

  // The render method generates the HTML markup
  // that is needed by the other methods:
  render : function(template, params) {
    var arr = [];
    switch (template) {

    case 'debug':
      arr = [
	'<span class="name">',params.msg,'</span>'];
      break;

    case 'loginTopBar':
      arr = [
	'<span class="name">',params.name,'</span>'];
      break;

    case 'chatLine':
      name = 'You';
      if (params.author != user_id) {
	name = 'Other';
      }
      arr = [
	'<div class="chat chat-', params.id, ' rounded">', '<span class="author">', name,
	': </span><span class="text">', params.text, '</span><span class="time">', params.time, '</span></div>'];
      break;

    case 'topicLine':
      arr = ['<div class="topic topic-', params.id, ' rounded">', '<span class="author">', name,
      ': </span><span class="text">', params.text, '</span><span class="time">', params.time, '</span></div>'];
      break;

    case 'user':
      arr = [
	'<div class="user" title="',params.name,'"><img src="',
	params.gravatar,'" width="30" height="30" onload="this.style.visibility=\'visible\'" /></div>'
      ];
      break;

    case 'endOfSession':
      arr = [
	'<div class="chat chat-', params.id, ' rounded">', '<span class="text">This chat session has ended.</span></div>'];
      break;
    }

    // A single array join is faster than
    // multiple concatenations
    return arr.join('');
  },

  // The addChatLine method ads a chat entry to the page
  addChatLine : function(params){

    // All times are displayed in the user's timezone
    var d = new Date();
    if(params.time) {
      // PHP returns the time in UTC (GMT). We use it to feed the date
      // object and later output it in the user's timezone. JavaScript
      // internally converts it for us.
      d.setUTCHours(params.time.hours,params.time.minutes);
    }

    params.time = (d.getHours() < 10 ? '0' : '' ) + d.getHours()+':'+
      (d.getMinutes() < 10 ? '0':'') + d.getMinutes();

    var markup = chat.render('chatLine', params),
      exists = $('#chatLineHolder .chat-' + params.id);

    if(exists.length){
      exists.remove();
    }

    if(!chat.data.lastID){
      // If this is the first chat, remove the
      // paragraph saying there aren't any:
      $('#chatLineHolder p').remove();
    }

    // If this isn't a temporary chat:
    if(params.id.toString().charAt(0) != 't'){
      var previous = $('#chatLineHolder .chat-'+(+params.id - 1));
      if(previous.length){
	previous.after(markup);
      } else {
	chat.data.jspAPI.getContentPane().append(markup);
      }
    } else {
      chat.data.jspAPI.getContentPane().append(markup);
    }

    // As we added new content, we need to
    // reinitialise the jScrollPane plugin:
    chat.data.jspAPI.reinitialise();
    chat.data.jspAPI.scrollToBottom(true);
  },

  // This method displays an error message on the top of the page:
  displayError : function(msg) {
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
};

var utils = {
  // Generates a random-looking ID
  guidGenerator : function() {
    var S4 = function() {
      return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
    };
    return (S4()+S4()+"-"+S4()+"-"+S4()+S4()+S4());
  },

  registerKeyboardShortcuts : function() {
    $(document).keyup(function(e) {
      if (e.which == 27) {
	// Esc
	chat.newSession(user_id);
      }
    });
  },

  endSessionCleanUp : function() {
    // Reset global session id so that every function knows the session has ended.
    global_session_id = 'undefined';
    chat.data.lastID = 0;
    // Display end-of-session messages
    chat.addEndOfSessionMessage();
    $('#chatText').attr('disabled', 'disabled');
  }
};

// Custom GET & POST wrappers:

$.tzPOST = function(action, data, callback) {
  $.post('php/ajax.php?action=' + action, data, callback, 'json');
}

$.tzGET = function(action, data, callback) {
  $.get('php/ajax.php?action=' + action, data, callback, 'json');
}

// A custom jQuery method for placeholder text:

$.fn.defaultText = function(value) {
  var element = this.eq(0);
  element.data('defaultText',value);

  element.focus(function(){
    if(element.val() == value){
      element.val('').removeClass('defaultText');
    }
  }).blur(function(){
    if(element.val() == '' || element.val() == value){
      element.addClass('defaultText').val(value);
    }
  });

  return element.blur();
}