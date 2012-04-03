$(document).ready(function() {
  // Run the init method on document ready:
  topics.Init();
  topics.ShowTopTopics(7);
});

function vote(id, score) {
  $.tzPOST('vote', {id: id, score: score}, function(r) {
    // Do nothing at the moment
    console.log("Voting done..." + r.status);
  });
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
    $.tzGET('getTopTopics',
	    {range: range, count: count},
	    function(r) {
	      var topicList = $('#topTopics');
	      topicList.empty();
	      if (r.topics == undefined) {
		topicList.append("<span>Nothing to show.</span>");
	      } else {
		for (var i = 0; i < r.topics.length; i++) {
		  topicList.append(topics.Render(r.topics[i].id,
						 r.topics[i].text,
						 r.topics[i].score));
		}
	      }
	    });
  },

  ShowRecentTopics : function(count) {
    $.tzGET('getRecentTopics',
	    {count: count},
	    function(r) {
	      var topicList = $('#topTopics');
	      topicList.empty();
	      console.log("returned here!!!");
	      console.log(r);
	      if (r.topics == undefined) {
		topicList.append("<span>Nothing to show.</span>");
	      } else {
		console.log("heree!!!");
		for (var i = 0; i < r.topics.length; i++) {
		  topicList.append(topics.Render(r.topics[i].id,
						 r.topics[i].text,
						 r.topics[i].score));
		}
	      }
	    });
  },

  Render : function(id, text, score) {
    if (score == undefined) {
      score = "?";
    }
    arr = ["<div class=\"topic\">" + text + "\t[" + score + "]</div>",
	   "<div class=\"vote\"><span onclick=\"vote(" + id + ", 1)\">^</span>",
	   "&nbsp;&nbsp;",
	   "<span onclick=\"vote(" + id + ", -1);\">v</span></div><br/>"];
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
