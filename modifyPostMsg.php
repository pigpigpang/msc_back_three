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
$mysql_postMsg_table = 'postMsg_table';

$sql_connect = mysqli_connect($mysql_server_URL, $mysql_username, $mysql_password, $mysql_datebase);
if (mysqli_connect_errno($sql_connect))
    exit("连接数据库失败:" . mysqli_connect_error());

//查找此session是否合法
$mysql_result = mysqli_query(
    $sql_connect,
    "SELECT * FROM $mysql_users_table WHERE email='$email'"
);
$row = $mysql_result->fetch_assoc();
if (empty($row)) {
    exit("无效的登录信息，请重新登录");
};
//检测是否进行了更改/删除帖子操作
if (isset($_POST['changedMsgNum']) && !empty($_POST['changedMsgNum'])) {
    $changedNum = $_POST['changedMsgNum'];
    //判断普通用户是否尝试非法修改他人帖子
    if ($row['isAdmin'] == 0) {
        $mysql_aResult = mysqli_query(
            $sql_connect,
            "SELECT * FROM $mysql_postMsg_table WHERE usersEmail='$email' AND num='$changedNum'"
        );
        $a_row = $mysql_aResult->fetch_assoc();
        if (empty($a_row))
            exit("所指向的帖子无法修改");
    }
    //进行修改帖子操作
    if (isset($_POST['changedPostMsg']) && !empty(trim($_POST['changedPostMsg']))) {
        $changedPostMsg = $_POST['changedPostMsg'];
        if (mysqli_query(
            $sql_connect,
            "UPDATE $mysql_postMsg_table SET postMsg='$changedPostMsg' WHERE num='$changedNum'"
        ))
            echo "帖子修改成功！";
        else
            echo "帖子修改失败！";
        //删除帖子
    } else {
        if (mysqli_query(
            $sql_connect,
            "DELETE FROM $mysql_postMsg_table WHERE num='$changedNum'"
        ))
            echo "帖子删除成功！";
        else
            echo "帖子删除失败!";
    }
} else {
    //发帖
    if (isset($_POST["sentPostMsg"]) && !empty(trim($_POST["sentPostMsg"]))) {
        $sentPostMsg = $_POST["sentPostMsg"];
        if (strlen($sentPostMsg) > 255)
            exit("帖子长度过长，疑似不正常的操作！");
        //插入帖子
        $dateTimeClass = new DateTime('Asia/Shanghai');
        $dateTime = $dateTimeClass->format('Y-m-d H:i:s');
        $sqlStr = "INSERT INTO $mysql_postMsg_table (postMsg, usersEmail, dateTime) VALUES ('$sentPostMsg', '$email', '$dateTime')";

        if (mysqli_query($sql_connect, $sqlStr)) {
            echo "发帖成功！";
        } else
            echo "发帖失败！";
    }
}
echo "<br>";

//针对不同用户设置不同查询条件
if ($row['isAdmin'] == 0)
    $sqlStr = "SELECT * FROM $mysql_postMsg_table WHERE usersEmail='$email' ORDER BY dateTime DESC";
else
    $sqlStr = "SELECT * FROM $mysql_postMsg_table ORDER BY dateTime DESC";
//输出帖子信息
if ($result = mysqli_query($sql_connect, $sqlStr)) {
    while ($rowMsg = $result->fetch_assoc()) {
        echo $rowMsg['num'] . ":  " . " / UserEmail: " . $rowMsg['usersEmail'] . " / DateTime: " . $rowMsg['dateTime'] . " / Msg: " . $rowMsg['postMsg'] . "<br>";
    }
} else
    echo "查看帖子失败！";


mysqli_close($sql_connect);
?>

<br>
<form method="POST" action="#" name="changePostMsg">
    <label>1.帖子序号<label>
            <input type="number" name="changedMsgNum" />
            <label>输入修改后的帖子内容<label>
                    <input type="type" name="changedPostMsg" placeholder="留空则删除帖子" />
                    <button type="submit">修改</button>
</form>
<br><br>
<form method="POST" action="#" name="sendPostMsg">
    <label>2.发帖<label>
            <label>输入帖子内容<label>
                    <input type="type" name="sentPostMsg" placeholder="无法发送空帖子" />
                    <button type="submit">发送</button>
</form>