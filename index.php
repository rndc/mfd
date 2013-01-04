<?php
include 'config.php';

if (isset($_POST['submit'])) {
	$user = mysql_real_escape_string(stripslashes($_POST['username']));
	$pass = mysql_real_escape_string(stripslashes(sha1($_POST['passwd'])));
	$_0 = mysql_query("SELECT username, password FROM users WHERE username='$user' and password='$pass'");
	if(mysql_num_rows($_0) == 1) {
		session_start();
		$_SESSION['user'] = $user;
		$_SESSION['passwd'] = sha1($pass);
		$login_ip = $_SERVER['REMOTE_ADDR'];
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		mysql_query("INSERT INTO user_log (login_ip, user_agent, tanggal_login) VALUES ('$login_ip','$user_agent',Now())");
		header("Location: main.php");
	}
}
?>
<head>
	<title>VulnAssasin</title>
	<link rel="stylesheet" type="text/css" href="style/style.css">
</head>
<body background="images/bg-tile.gif">
	<br/><br /><br /><br />
    <div id="table_login">
        <center>
            <img src="images/head-vuln.gif" alt="head-vuln" width="200" height="80">
            <table>
             <form method="post" name="login" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
                <tr>
                    <td>
                        username: <input type="text" name="username" />
                    </td>
                </tr>
                <tr>
                    <td>
                        password: <input type="password" name="passwd" />
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        <input type="submit" value="submit" name="submit" />
                    </td>
                </tr>
            </form>
        </table>
    </center>
</div>
</body>

