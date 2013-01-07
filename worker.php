<?php
include 'config.php';

class extMain extends Main {

    // cek file matching
    function routine_check() {
        $this->search('/\.php$/');                          // cari file php
        $this->check();                                     // ambil data dari database
        $default = 0;                                       // counter

        foreach ($this->files as $file) {                   // extract files hasil pencarian
            $md5sum = md5_file($file);
            $stat = stat($file);
            $lastmod = $stat['mtime'];
            $dbcheck = mysql_fetch_row(mysql_query("SELECT md5sum, last_modified FROM data_file WHERE files='$file'"));

            switch(True):
                case (! in_array($file, $this->stack)):     // pembanding berdasarkan nama
                    echo "file : ".$file."\n";              // untuk debugging
                    $default++;
                    mysql_query("INSERT INTO detected_files (nama_file, md5sum, date_time) VALUES ('$file','$md5sum', Now())");
                    unlink($file);
                    break;

                case (! ($md5sum == $dbcheck[0])):          // pembanding berdasarkan md5sum
                    echo "md5sum : ".$md5sum."\n";          // untuk debugging
                    $default++;
                    mysql_query("INSERT INTO detected_files (nama_file, md5sum, date_time) VALUES ('$file','$md5sum', Now())");
                    break;

                case (! ($lastmod == $dbcheck[1])):
                    echo "lastmodified : ".$lastmod."\n";   // untuk debugging
                    $default++;
                    mysql_query("INSERT INTO detected_files (nama_file, md5sum, last_modified, date_time) VALUES ('$file', $md5sum', '$lastmod', Now())");
                    break;

                default:
                    break;
            endswitch;
        }

        if($default != 0) {
            $this->sendmail();
        }
    }

    // cek gambar, apakah gambar yang ada adalah gambar asli atau palsu
    function image_check() {
        $this->search('/(\.png$)|(\.jpg$)|(\.gif$)|(\.bmp$)|(\.ico$)/');    // cari file gambar

        foreach ($this->files as $file) {                                   // extract files hasil pencarian
            $image = getimagesize($file);
            $image_type = $image[2];                                        // array 2 dari getimagesize adalah tipe gambar
            if(!in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_ICO))) {
                // pembanding gambar asli dengan yang palsu
                mysql_query("INSERT INTO detected_files (nama_file, date_time) VALUES ('$file', Now())");
                unlink($file); // jika palsu maka file dihapus
            }
        }
    }

    // update informasi file ke dalam database
    function updatedb() {
        $this->search('/\.php$/');          // cari semua file php
        $this->check();

        foreach ($this->files as $file) {   // extract hasil pencarian menjadi satuan
            $md5sum = md5_file($file);
            $stat = stat($file);            // fungsi stat()
            $lastmod = $stat['mtime'];      // stat['mtime'] adalah fungsi yang menampilkan informasi last time modified file
            $dbcheck = mysql_fetch_row(mysql_query("SELECT md5sum, last_modified FROM data_file WHERE files='$file'"));
            // fetch md5 dan last time modified dari database

            switch(True):
                case (!in_array($file, $this->stack)): // membandingkan nama file dari database dengan dari hasil check langsung
                    mysql_query("INSERT INTO data_file (files, md5sum, last_modified, date_time) VALUES ('$file', '$md5sum', '$lastmod', Now())"); // masukkan hasil pencarian ke dalam database
                    break;

                case (!($md5sum == $dbcheck[0])): // membandingkan md5sum dari database dengan dari hasil check langsung
                    mysql_query("UPDATE data_file SET md5sum='$md5sum', last_modified='$lastmod', date_time=Now() WHERE files='$file'"); // update database untuk md5sum
                    break;

                case (!($lastmod == $dbcheck[1])): // membandingkan last time modified dari database dengan yang dari check langsung
                    mysql_query("UPDATE data_file SET last_modified='$lastmod', date_time=Now() Where files='$file'"); // update database untuk last time modified
                    echo "lastmod\n".$lastmod;
                    break;

                default:
                    break;
            endswitch;
        }
    }
}

class Main {
    public $files, $stack, $email;

    function search($pattern) { // class untuk mencari file di dalam direktori secara recursive
        $dirs = new RecursiveDirectoryIterator('../../');
        $files = new RegexIterator(new RecursiveIteratorIterator($dirs), $pattern, RegexIterator::MATCH); // matching pencarian berdasarkan pattern regex
        $this->files = $files;
        return;
    }

    function check() {
        $counter = 0;
        $stack = array();
        $tables = mysql_query("SELECT files FROM data_file"); // ambil informasi tentang database file yang tersimpan
        while($row = mysql_fetch_row($tables)) { // extract informasi
            $stack[$counter] = $row[0]; // array untuk informasi files
            $counter++;
        }
        $this->stack = $stack;
        return;
    }

    function status() {
        $pid = posix_getpid();
        mysql_query("INSERT INTO worker_status (pid) VALUES ('$pid')"); // update pid ke database
    }

    function getmail() {
        $tables = mysql_query("SELECT email FROM users"); // ambil informasi email user
        while($row = mysql_fetch_row($tables)) {
            break;
        }
        $this->email = $row[0];
    }

    function sendmail() {
        $this->getmail();
        if(! $this->email) {
            return;
        }
        $subject = "Detection Report";
        $message = "Telah ditemukan aktifitas mencurigakan pada website anda, untuk informasi lebih lanjut silahkan cek pada VulnAssassin admin panel";
        $headers = "From: vulnassassin@rndc.or.id\r\n";
        $headers .= "Content-type: text/html\r\n";
        mail($this->email, $subject, $message, $headers); //kirim email
        echo "email sent"; // untuk debugging
    }
}

$scan = new extMain();
if(isset($argv[1])) {
    switch($argv[1]):
        case ('scan'):
            $scan->status();
            while(1) {
                $scan->routine_check();
                $scan->image_check();
                sleep($argv[2]);
            }
        case ('updatedb'):
            $scan->updatedb();
            $scan->status();
            exit;
        case ('vulnscan'):
            $scan->vulnscan();
            $scan->getpid();
            exit;
        default:
            exit;
    endswitch;
}
?>
