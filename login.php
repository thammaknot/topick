<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Login</title>

<link rel="stylesheet" type="text/css" href="js/jScrollPane/jScrollPane.css" />
<link rel="stylesheet" type="text/css" href="css/page.css" />
<link rel="stylesheet" type="text/css" href="css/chat.css" />
<link rel="stylesheet" type="text/css" href="css/login.css" />

</head>

<body>

<div class="login">
<form id="login-form" action="login" method="post"
      onsubmit="return login.validateLogin();">
  <div class="username-block">
  <label>Username</label>
  <input type="text" id="username"/>
  </div>
  <div class="password-block">
  <label>Password</label>
  <input type="password" id="password"/>
  </div>
  <input type="submit" value="Login"/>
</form>
</div>
<div class="signup-link">
  <a href="/topick/signup.php">Sign-up</a>
</div>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js">
</script>
<script src="js/jScrollPane/jquery.mousewheel.js"></script>
<script src="js/jScrollPane/jScrollPane.min.js"></script>
<script src="js/auth.js"></script>
<script src="js/login.js"></script>
</body>
</html>
