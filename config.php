<?php
/* Konfigurasi database */
$host = 'localhost';
$dbuser = 'root';
$dbpass = 'helloworld';
$dbname = 'vulnscan';

mysql_connect($host, $dbuser, $dbpass) or
	die('Tidak dapat terhubung dengan database : '. mysql_error());
mysql_select_db($dbname) or
	die('Database tidak ditemukan : '.mysql_error());

if (! mysql_query("DESCRIBE `data_file`")) {
                mysql_query("CREATE TABLE if not exists users (id INT PRIMARY KEY, username varchar(20) NOT NULL, password varchar(40) NOT NULL, email varchar(30))");  
                mysql_query("CREATE TABLE if not exists data_file (id INT PRIMARY KEY auto_increment, files varchar(1337), md5sum varchar(32), last_modified varchar(32), date_time datetime)");
                mysql_query("CREATE TABLE if not exists user_log (id INT PRIMARY KEY auto_increment, login_ip varchar(16), user_agent varchar(256), tanggal_login datetime)");
		mysql_query("CREATE TABLE if not exists vuln_files (id INT PRIMARY KEY auto_increment, vulnerability varchar(20), nama_file varchar(1337), risk varchar(30), date_time datetime)");
		mysql_query("CREATE TABLE if not exists detected_files (id INT PRIMARY KEY auto_increment, nama_file varchar(1337), md5sum varchar(32), date_time datetime)");
		mysql_query("CREATE TABLE if not exists update_log (id INT PRIMARY KEY auto_increment, nama_file varchar(1337), md5sum(32)))");
		mysql_query("CREATE TABLE if not exists worker_status (pid INT)");
		mysql_query("INSERT INTO users (username, password) VALUES ('admin', 'd033e22ae348aeb5660fc2140aec35850c4da997')");
}
?>
