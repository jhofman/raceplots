<?php

$DATA_DIR = '/var/www/raceplots/data';

$userpass = file('/home/jhofman/.raceplots');
$DB_USER = trim($userpass[0]);
$DB_PASS = trim($userpass[1]);
$DB_NAME = 'raceplots';

$db = mysql_connect('localhost', $DB_USER, $DB_PASS);
mysql_select_db($DB_NAME);

unset($DB_USER);
unset($DB_PASS);

?>