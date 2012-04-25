$(document).ready(function() {
  // Run the init method on document ready:
  topics.Init();
  // Wait 200 ms for authentication to finish
  setTimeout(function () {
    topics.ShowTopTopics(7, 10);
  }, 200);
});

function sleep(ms) {
  var start = new Date().getTime();
  while (new Date().getTime() < start + ms);
}

function vote(id, score) {
  if (username == undefined) {
    // Reroute to login flow.
    LoadPopup("You need to login to vote.",
	      "Login",
	      function() {
		window.location = "/topick/login.php";
	      });
  } else {
    // Actual voting
    $.tzPOST('vote', {id: id, score: score, username: username}, function(r) {
      // Do nothing at the moment
      console.log("Voting done..." + r.status);
    });
  }
}

var topics = {
  // Init binds event listeners
  Init : function() {
    // We use the working variable to prevent
    // multiple form submissions:
    var working = false;
    $('#topicForm').submit(function() {
      var topic = $('#topicText').val();
      // Sanitize
      topic = topic.replace(/</g, '&lt;').replace(/>/g, '&gt;')
      $.tzPOST('addTopic', {topic: topic}, function(r) {
	$('#topicText').val('');
      });
      return false;
    });

    $('#allTime').click(function() {
      topics.ShowTopTopics(-1, 10);
    });

    $('#30days').click(function() {
      topics.ShowTopTopics(30, 10);
    });

    $('#7days').click(function() {
      topics.ShowTopTopics(7, 10);
    });

    $('#1day').click(function() {
      topics.ShowTopTopics(1, 10);
    });

    $('#recent').click(function() {
      topics.ShowRecentTopics(10);
    });
  },  // end init

  ShowTopTopics : function(range, count) {
    console.log("getting top topics for " + username);
    $.tzGET('getTopTopics',
	    {range: range, count: count, username: username},
	    function(r) {
	      var topicList = $('#topTopics');
	      topicList.empty();
	      console.log(r);
	      if (r.topics == undefined) {
		topicList.append("<span>Nothing to show.</span>");
	      } else {
		for (var i = 0; i < r.topics.length; i++) {
		  topicList.append(topics.Render(r.topics[i].id,
						 r.topics[i].text,
						 r.topics[i].score,
						 r.topics[i].vote));
		}
	      }
	    });
  },

  ShowRecentTopics : function(count) {
    $.tzGET('getRecentTopics',
	    {count: count, username: username},
	    function(r) {
	      var topicList = $('#topTopics');
	      topicList.empty();
	      console.log(r);
	      if (r.topics == undefined) {
		topicList.append("<span>Nothing to show.</span>");
	      } else {
		for (var i = 0; i < r.topics.length; i++) {
		  topicList.append(topics.Render(r.topics[i].id,
						 r.topics[i].text,
						 r.topics[i].score,
						 r.topics[i].vote));
		}
	      }
	    });
  },

  Render : function(id, text, score, vote) {
    if (score == undefined) {
      score = "?";
    }
    arr = ["<div class=\"topic\">" + text + "\t[" + score + "]</div>",
	   "<div class=\"vote\"><span onclick=\"vote(" + id + ", 1)\">^</span>",
	   "&nbsp;&nbsp;",
	   "<span onclick=\"vote(" + id + ", -1);\">v</span></div>",
	   "<div class=\"vote\">{" + vote + "}</div><br/>"];
    return arr.join('');
  }
};  // end class topics

// Custom GET & POST wrappers:
$.tzPOST = function(action, data, callback) {
  $.post('php/topics.php?action=' + action, data, callback, 'json');
}

$.tzGET = function(action, data, callback) {
  $.get('php/topics.php?action=' + action, data, callback, 'json');
}
