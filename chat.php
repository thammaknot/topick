<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Chat Away!</title>

<link rel="stylesheet" type="text/css" href="js/jScrollPane/jScrollPane.css" />
<link rel="stylesheet" type="text/css" href="css/page.css" />
<link rel="stylesheet" type="text/css" href="css/chat.css" />
<link rel="stylesheet" type="text/css" href="css/topics.css" />

</head>

<body>

<?PHP include("nav.php"); ?>
<br/>
<div id="chatContainer">
    <div id="chatTopBar" class="rounded"></div>
    <div id="chatLineHolder"></div>
    <div id="controlPanel" class="rounded">
      <input id="topic" value="New Topic" type="submit" class="blueButton">
    </div>
    <div id="chatBottomBar" class="rounded">
    <div class="tip"></div>
    <!--span class="inputPanelSpan"-->
      <form id="submitForm" method="post" action="">
        <input id="chatText" name="chatText" class="rounded" maxlength="255" />
        <input type="submit" class="blueButton" value="Submit" />
      </form>
    <!--/span-->
    <span class="inputPanelSpan">
      <label class="blueButton labelButton" id="session">New Chat</label>
    </span>
    </div>
</div>
<?PHP include("popup.php"); ?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js">
</script>
<script src="js/jScrollPane/jquery.mousewheel.js"></script>
<script src="js/jScrollPane/jScrollPane.min.js"></script>
<script src="js/auth.js"></script>
<script src="js/popup.js"></script>
<script src="js/script.js"></script>
</body>
</html>
