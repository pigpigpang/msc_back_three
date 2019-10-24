<?php
if ($_POST) {
    if (!isset($_POST["name"]) || empty(trim($_POST["name"])))
        exit("不正常的提交，未输入姓名");
    else if (!isset($_POST["qq"]) || empty(trim($_POST["qq"])))
        exit("不正常的提交，未输入QQ");
    else if (!isset($_POST["email"]) || empty(trim($_POST["email"])))
        exit("不正常的提交，未输入邮箱");
    else if (!isset($_POST["password"]) || empty(trim($_POST["password"])))
        exit("不正常的提交，未输入密码");

    $name = $_POST["name"];
    $qq = $_POST["qq"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    if (strlen($name) > 15)
        exit("姓名长度过长，疑似不正常的操作！");
    else if (strlen($password) > 16)
        exit("密码长度过长，疑似不正常的操作！");
    else if (strlen($email) > 100)
        exit("邮箱长度过长，疑似不正常的操作！");
    else if (!(filter_var($email, FILTER_VALIDATE_EMAIL)))
        exit("不是一个有效的邮箱");

    if (!is_numeric($qq) || strlen($qq) > 11)
        exit("QQ非纯数字，或者长度过长，疑似不正常的操作！");
    //通过注册来的用户，全部都是普通用户
    $isAdmin = 0;

    //插入数据库
    $mysql_server_URL = 'localhost';
    $mysql_username = 'root';
    $mysql_password = '';
    $mysql_datebase = 'msc_web_learning';
    $mysql_table = 'users_table';

    $sql_connect = mysqli_connect($mysql_server_URL, $mysql_username, $mysql_password, $mysql_datebase);
    if (mysqli_connect_errno($sql_connect))
        exit("连接数据库失败:" . mysqli_connect_error());

    $mysql_result = mysqli_query(
        $sql_connect,
        "SELECT * FROM $mysql_table WHERE email='$email'"
    );
    $row = $mysql_result->fetch_assoc();
    if (!empty($row))
        exit("邮箱已被注册，请直接登录");

    $sqlStr = "INSERT INTO $mysql_table (name, qq, email, password, isAdmin) 
            VALUES ('$name', '$qq', '$email', '$password', '$isAdmin')";
    if (mysqli_query($sql_connect, $sqlStr)) {
        echo "注册成功！";
    } else
        echo "注册失败！";
    mysqli_close($sql_connect);
} else
    echo "Hacker?";
