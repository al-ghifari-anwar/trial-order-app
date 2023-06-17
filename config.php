<?php

$db_host = 'localhost';
$db_user = 'admin_orderapp';
$db_pass = 'Srv.TopMortar1ndonesia!';
$db_name = 'admin_orderapp';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if ($conn) {
    // echo 'Connected';
} else {
    // echo 'Not Connected';
}
