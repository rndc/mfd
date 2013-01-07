<?php
session_start();
if (! isset($_SESSION['user'])) {
    header("Location: main.php");
}

include 'config.php';
class Main {
    public $pid;

    function executeworker() {
        $this->getpid();

        switch(True):
            case (isset($_POST['startservice'])):
                if ($this->pid == '') {
                    exec("php worker.php scan 60 > /dev/null &");
                }
                break;

            case (isset($_POST['stopservice'])):
                if ($this->pid != '') {
                    exec("kill -9 $this->pid");
                    $this->clearpid();
                }
                break;

            case (isset($_POST['updatedb'])):
                if (! $this->pid) {
                    exec("php worker.php updatedb > /dev/null &");
                    sleep(5);
                    $this->clearpid();
                }
                break;

            default:
                break;
        endswitch;
    }

    function getpid() {
        $query = mysql_query("SELECT pid FROM worker_status");
        while($getpid = mysql_fetch_assoc($query)) {
            break;
        }
        $this->pid = $getpid['pid'];
    }

    function clearpid() {
        mysql_query("DELETE FROM worker_status");
    }

    function changepass() {
        $old = mysql_real_escape_string(stripslashes(sha1($_POST['oldpass'])));   // input encrypted oldpassword + sanitaze
        $new = mysql_real_escape_string(stripslashes(sha1($_POST['newpass'])));   // input encrypted newpassword + sanitaze
        $retype = mysql_real_escape_string(stripslashes(sha1($_POST['retype']))); // input encrypted retype password + sanitaze
        $sql = mysql_fetch_row(mysql_query("SELECT password FROM users WHERE username='admin'")); // old password dari database

        switch(True):
            case ($sql[0] != $old): // old password matching dengan yang ada di dalam database
                echo "<script>alert('password yang dimasukkan salah')</script>";
                break;

            case ($new != $retype): // new password matching dengan retype
                echo "<script>alert('password yang dimasukkan tidak sama')</script>";
                break;

            case ($sql[0] == $new): // password matching, jika password sama dengan yang lama
                echo "<script>alert('Password yang dimasukkan sama dengan yang lama')</script>";
                break;

            default:
                // update database ketika semua exception terpenuhi
                mysql_query("UPDATE users SET password='$new' WHERE username='admin'");
                echo "<script>alert('berhasil di update')</script>";
                break;
        endswitch;
    }

    function mailer() {
        $email = mysql_real_escape_string(stripslashes($_POST['email']));
        if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/" , $email)) {
            mysql_query("UPDATE users SET email='$email' WHERE username='admin'");
            echo "<script>alert('success')</script>";
        }
    }
}

$main = new Main();
$main->executeworker();
if (isset($_POST['gantipassword'])) {
    $main->changepass();
}
if (isset($_POST['append'])) {
    $main->mailer();
}
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: http://localhost/vuln/login.php");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>vuln</title>
  <link rel="stylesheet" type="text/css" href="style/style.css">
  <script type="text/javascript">
  function animate_tab(menuId) {
    var no_of_tabs = 3+1;

    for(i = 1; i < no_of_tabs; i++) {
      menu_id = "menu-0"+i.toString();
      tab_id  = "tab-0"+i.toString();

      if(menu_id == menuId) {
        document.getElementById(menu_id).className  = 'selected';
        document.getElementById(tab_id).style.display = 'block';
      } else {
        document.getElementById(menu_id).className  = 'unselected';
        document.getElementById(tab_id).style.display = 'none';
      }
    }
  }
  </script>
</head>
<body background="images/bg-tile.gif">
    <center><img src="images/head-vuln.gif" alt="head-vuln" width="200" height="80"></center>
    <div id="tab-menu">
      <div id="navigation">
        <ul>
          <li id="menu-01" class="selected"><a href="javascript:void(0);" onClick="animate_tab('menu-01');">Home</a></li>
          <li id="menu-03" class="unselected"><a href="javascript:void(0);" onClick="animate_tab('menu-03');">Settings</a></li>
      </ul>
      <div class="clear"></div>
  </div>

  <div id="tab-content">
    <div id="tab-01">
      <div id="scanning">
        <table id="background-button">
          <thead>
            <tr>
              <th>Scanning Directory</th>
          </tr>
      </thead>
      <tr>
        <td>
          Find a suspicious files in your web directory
          <form name="scan" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST" >
            <input class="bstyle" type="submit" name="startservice" value="start scanning"/>
        </form>
        <form name="input" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
            <input class="bstyle" type="submit" name="stopservice" value="stop scanning"/>
        </form>
    </td>
</tr>
</table>
</div>

<div id="detected-file">
    <table id="background-image">
      <thead>
        <tr>
          <th class="no_size">No.</th>
          <th class="file_size">Filename</th>
          <th class="date_size">MD5 sum      </th>
          <th class="md5_size">tanggal</th>
      </tr>
  </thead>
</table>

<div style="overflow-y:auto; width:817px; height: 120px; background-color: #D89ED8;">
  <table style="table-layout:fixed" id="background-image">
    <tbody>
      <?php
      $tables = mysql_query("SELECT nama_file, md5sum, date_time FROM detected_files ORDER BY date_time DESC LIMIT 0,25");
      static $nomor = 1;
      while($row = mysql_fetch_array($tables)) {
        echo "<tr>";
        echo "<td class=\"no_size\">".$nomor."</td>";
        echo "<td class=\"file_size\">".$row['nama_file']."</td>";
                echo "<td class=\"date_size\">".$row['md5sum']."</td>"; //ganti jadi lastmodified
                echo "<td class=\"md5_size\">".$row['date_time']."</td>";
                echo "</tr>";
                $nomor++;
            }
            ?>
        </tbody>
    </table>
</div>

</div>
<br class="floating">
<br>

<div id="update-database">
    <table id="background-button">
      <thead>
        <tr>
          <th>Update Files</th>
      </tr>
  </thead>
  <tr>
    <td>
      update a whole files in your web directory
      <br><br><br>
      <form name="input" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
        <input class="bstyle" type="submit" name="updatedb" value="start scanning"/>
    </form>
</td>
</tr>
</table>
</div>

<div id="update-file">

    <table id="background-image">
      <thead>
        <tr>
          <th class="no_size">No.</th>
          <th class="file_size">MD5 sum</th>
          <th class="path_size">Updated Files</th>
      </tr>
  </thead>
</table>

<div style="overflow-y:auto; width:817px; height: 120px; background-color: #D89ED8;">
  <table style="table-layout:fixed" id="background-image">
    <tbody>
      <?php
      $nomor = 1;
      $tables = mysql_query("SELECT id, files, md5sum FROM data_file ORDER BY date_time DESC LIMIT 0,25");
      while ($row = mysql_fetch_array($tables)) {
        echo "<tr>";
        echo "<td class=\"no_size\">".$nomor."</td>";
        echo "<td class=\"file_size\">".$row['md5sum']."</td>";
        echo "<td class=\"md5_size\">".$row['files']."</td>";
        echo "</tr>";
        $nomor++;
    }
    ?>
</tbody>
</table>
</div>


</div>
<br class="floating">
<br>

<div id="last-login">
    <table id="background-image">
      <thead>
        <tr>
          <th class="no_size">No.</th>
          <th class="ip_size">IP Address</th>
          <th class="date_size">Date Login</th>
          <th class="path_size">User Agent</th>
      </tr>
  </thead>
</table>

<div style="overflow-y:auto; width:817px; height: 90px; background-color: #D89ED8;">
  <table style="table-layout:fixed" id="background-image">
    <tbody>
      <?php
      $nomor =1;
      $tables = mysql_query("SELECT login_ip, user_agent, tanggal_login FROM user_log");
      while ($row = mysql_fetch_array($tables)) {
        echo "<tr>";
        echo "<td class=\"no_size\">".$nomor."</td>";
        echo "<td class=\"ip_size\">".$row['login_ip']."</td>";
                echo "<td class=\"date_size\">".$row['tanggal_login']."</td>";
                echo "<td class=\"path_size\">".$row['user_agent']."</td>";
                echo "</tr>";
                $nomor++;
            }
            ?>
        </tbody>
    </table>
</div>

</div>
</div>
<div id="tab-03">
  <div id="passline" >
    <hr>
    <span><b>Set up your password for vulner scan</b></span>
    <form name="formgantipassword" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method=POST>
      <table>
        <tr>
          <td class="set1" rowspan="5"></td>
      </tr>
      <tr>
          <td class="set2">Old password : </td><td class="set2"><input type="password" name="oldpass" value="old-pasword"/></td>
      </tr>
      <td class="set2">New password : </td><td class="set2"><input type="password" name="newpass" value="new-pasword"/></td>
      <tr>
          <td class="set2">Retype Password : </td><td class="set2"><input type="password" name="retype" value="retype-pasword"/></td>
      </tr>
      <tr>
          <td></td><td><input class="bstyle" type="submit" name="gantipassword" value="Apply"/></td>
      </tr>
  </table>
</form>
<hr>
<b>Send report to admin by email</b>
<form name="formmail" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
  <table>
    <tr>
      <td class="set1" rowspan="3"></td>
  </tr>
  <tr>
      <td class="set2">Send Report : </td><td class="set2"><input type="text" name="email" value="email"/></td>
  </tr>
  <tr>
      <td></td><td><input class="bstyle" type="submit" name="append" value="Append"/></td>
  </tr>
</table>
</form>
<hr>
</div>
</div>
<form name="button_logoun" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
<table>
  <tr><td>
    <input type="submit" value="logout" name="logout">
  </tr></td>
</table>
</form>
</div>
</div>
</body>
</html>
