<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Suggest a Topic!</title>

<link rel="stylesheet" type="text/css" href="js/jScrollPane/jScrollPane.css" />
<link rel="stylesheet" type="text/css" href="css/page.css" />
<link rel="stylesheet" type="text/css" href="css/chat.css" />
<link rel="stylesheet" type="text/css" href="css/topics.css" />

</head>

<body>

   <?PHP include("nav.php"); ?>
<br/>
<div class="mainPanel">
   <div class="topicList">
   <div class="navBar">
   <ul>
   <li>Popular Topics</li>
   <li><a href="#" id="allTime">All time</a></li>
   <li><a href="#" id="30days">30 Days</a></li>
   <li><a href="#" id="7days">7 Days</a></li>
   <li><a href="#" id="1day">1 Day</a></li>
   <li><a href="#" id="recent">Recent</a></li>
   </ul>
   </div>
   <div id="topTopics" class="topTopics" />
   </div>
   <form id="topicForm" method="post" action="">
   <input id="topicText" name="topicText" class="topicInput" maxlength="200" />
   <input type="submit" class="blueButton" value="Submit" />
   </form>
   </div>

   <?PHP include("popup.php"); ?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js">
   </script>
<script src="js/jScrollPane/jquery.mousewheel.js"></script>
<script src="js/jScrollPane/jScrollPane.min.js"></script>
<script src="js/auth.js"></script>
<script src="js/popup.js"></script>
<script src="js/topics.js"></script>
</body>
</html>
