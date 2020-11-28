<?php

$host = 'localhost';
$user = 'root';
$password = '12345';
$db_name = 'test';

$link = mysqli_connect($host, $user, $password, $db_name);
mysqli_query($link, "SET NAMES 'utf8'");
