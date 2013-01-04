<?php
session_start();
if (! isset($_SESSION['user'])) {
	header("Location: http://localhost/labs/login.php");
}
/* koneksi database */
include 'config.php';

function fork() {

}

function VulnCheck() {
	$pattern = array(
		"RCE" => array(
			'\\system\(\$\_GET\[*\)/', '\\system\(\$\_REQUEST\[*\)/'),
		"LFI" => array(
			'include\(\$_GET\[\'*\)'),
		"SQLi" => array(
			'sqlibro'),
		"Backdoor" => array(
			'gzinflate', 'base64')
		);
	$suspect = preg_grep($pattern, $array);
}

function Logger() {
	/* need to fix : ip + user agent + internet service provider */
	$remoteip = mysql_real_escape_string(stripslashes($_SERVER['REMOTE_ADDR']));
	$agent = mysql_real_escape_string(stripslashes($_SERVER['HTTP_USER_AGENT']));
	if (! mysql_query("INSERT INTO user_log (login_ip, user_agent) VALUES ('$remoteip','$agent')")) {
		echo "gorilla";
	}
}
function gantipw() {
	/* fix me for password matching with the database */
	if (isset($_POST['update'])) {
		$old = mysql_real_escape_string(stripslashes($_POST['oldpass']));
		$new = mysql_real_escape_string(stripslashes(sha1($_POST['newpass'])));
		$retype = mysql_real_escape_string(stripslashes(sha1($_POST['retype'])));
		$_0 = mysql_fetch_assoc(mysql_query("SELECT password FROM users WHERE username='admin'"));
		switch (True):
			case ($old == $_0['password']):
				echo 'password lama yang dimasukkan salah';
				break;
			case ($new != $retype):
				echo 'password yang dimasukkan tidak sama';
				break;
			case ($_0['password'] == $new):
				echo 'password yang dimasukkan sama dengan password lama';
				break;
			default:
				mysql_query("UPDATE users SET password='$new' WHERE username='admin'");
				echo "updated";
				return;
		endswitch;
	}
}

function executeworker() {
if(isset($_POST['startservice'])) {
		exec("php worker.php > /dev/null &");
	}
}

function imagecheck() {

}

function LogOut() {
	session_destroy();
}
gantipw();
executeworker();
?>
<html>
<body>
<center>
<form method=POST name=gantipassword action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
Old pass: <input type=text name=oldpass /><br />
new pass: <input type=text name=newpass /><br />
Retype  : <input type=text name=retype /><br />
<input type=submit name=update value=update />
<input type=submit name=startservice value=startservice />
</form>
</center>
</body>
</html>
