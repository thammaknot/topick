<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Signup</title>

<link rel="stylesheet" type="text/css" href="js/jScrollPane/jScrollPane.css" />
<link rel="stylesheet" type="text/css" href="css/page.css" />
<link rel="stylesheet" type="text/css" href="css/chat.css" />
<link rel="stylesheet" type="text/css" href="css/login.css" />

</head>

<body>

<div class="signup">
<form id="signup-form" action="signup" method="post"
      onsubmit="return login.validateSignup();">
  <div class="username-block">
    <label>Username</label>
    <input type="text" id="username"/>
  </div>
  <div class="password-block">
    <label>Password</label>
    <input type="password" id="password"/>
  </div>
  <div class="password-verify-block">
    <label>Verify Password</label>
    <input type="password" id="password-verify"/>
  </div>
  <div class="email">
    <label>Email</label>
    <input type="text" id="email"/>
  </div>
  <input type="submit" value="Signup"/>
</form>
</div>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js">
</script>
<script src="js/jScrollPane/jquery.mousewheel.js"></script>
<script src="js/jScrollPane/jScrollPane.min.js"></script>
<script src="js/login.js"></script>
</body>
</html>
