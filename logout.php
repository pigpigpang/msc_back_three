<?php

if (isset($_COOKIE['SessionID'])) {
    session_id($_COOKIE['SessionID']);
    session_start();
    $email = $_SESSION['user'];
} else
    exit("请先登录");

//插入数据库
$mysql_server_URL = 'localhost';
$mysql_username = 'root';
$mysql_password = '';
$mysql_datebase = 'msc_web_learning';
$mysql_users_table = 'users_table';
$mysql_postMsg_table = 'postMSG_table';

$sql_connect = mysqli_connect($mysql_server_URL, $mysql_username, $mysql_password, $mysql_datebase);
if (mysqli_connect_errno($sql_connect))
    exit("连接数据库失败:" . mysqli_connect_error());

$mysql_result = mysqli_query(
    $sql_connect,
    "SELECT * FROM $mysql_users_table WHERE email='$email'"
);
$row = $mysql_result->fetch_assoc();
if (empty($row))
    exit("无效的登录信息，请重新登录");
else {
    $sessionID = session_id();
    setcookie("SessionID", $sessionID, time() - 1);
    header('Location: login.html');
}
mysqli_close($sql_connect);
