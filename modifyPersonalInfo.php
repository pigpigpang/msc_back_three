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

if(isset($_POST['changedUser_Email']) && !empty(trim($_POST['changedUser_Email'])))
{
    $changedUser_Email = $_POST['changedUser_Email'];
    //设置用户所能操作的用户空间
    if($row['isAdmin'] == 0)
        $sql_msgs = "SELECT * FROM $mysql_users_table WHERE email='$email' AND email='$changedUser_Email'";
    else
        $sql_msgs = "SELECT * FROM $mysql_users_table WHERE email='$changedUser_Email'";
    $mysql_aResult = mysqli_query($sql_connect, $sql_msgs);
    $a_row = $mysql_aResult->fetch_assoc();
    if (empty($a_row))
        exit("所指向的用户无法修改");

    if(isset($_POST['changedQQ']) && !empty(trim($_POST['changedQQ'])))
    {
        $changedQQ = $_POST['changedQQ'];
        if (!is_numeric($changedQQ) || strlen($changedQQ) > 11)
        exit("QQ非纯数字，或者长度过长，疑似不正常的操作！");
        if(!mysqli_query($sql_connect, "UPDATE $mysql_users_table SET qq='$changedQQ' WHERE email='$changedUser_Email'"))
            exit("在修改用户QQ时发生错误");
        else
            echo "QQ修改成功";
    }
    if(isset($_POST['changedName']) && !empty(trim($_POST['changedName'])))
    {
        $changedName = $_POST['changedName'];
        if (strlen($changedName) > 15)
        exit("姓名长度过长，疑似不正常的操作！");
        if(!mysqli_query($sql_connect, "UPDATE $mysql_users_table SET name='$changedName' WHERE email='$changedUser_Email'"))
            exit("在修改用户name时发生错误");
            else
                echo "name修改成功";
    }
    if(isset($_POST['changedEmail']) && !empty(trim($_POST['changedEmail'])))
    {
        $changedEmail = $_POST['changedEmail'];
        if (strlen($changedEmail) > 100)
            exit("邮箱长度过长，疑似不正常的操作！");
        else if (!(filter_var($changedEmail, FILTER_VALIDATE_EMAIL)))
            exit("不是一个有效的邮箱");
        $mysql_result = mysqli_query(
                $sql_connect,
                "SELECT * FROM $mysql_users_table WHERE email='$changedEmail'"
            );
            $row = $mysql_result->fetch_assoc();
            if (!empty($row))
                exit("此邮箱已被注册，更改邮箱失败");

        if(!mysqli_query($sql_connect, "UPDATE $mysql_users_table SET email='$changedEmail' WHERE email='$changedUser_Email'"))
            exit("在修改用户email时发生错误");
            else
                echo "email修改成功";
    }
    //两次密码只要有一个输入框中输入了密码
    if((isset($_POST['changedPassword']) && !empty(trim($_POST['changedPassword'])))
        || (isset($_POST['changedPassword_repeat']) && !empty(trim($_POST['changedPassword_repeat']))))
    {
        if($_POST['changedPassword'] !== $_POST['changedPassword_repeat'])
            exit("两次密码输入不相同，请重新输入");
        $changedPassword = $_POST['changedPassword'];  
        if (strlen($changedPassword) > 16)
        exit("密码长度过长，疑似不正常的操作！");
        if(!mysqli_query($sql_connect, "UPDATE $mysql_users_table SET password='$changedPassword' WHERE email='$changedUser_Email'"))
            exit("在修改用户password时发生错误");
        else
                echo "password修改成功";
    }

}
//两次删除邮箱只要有一个输入框中输入了邮箱
else if((isset($_POST['deletedUser_Email']) && !empty(trim($_POST['deletedUser_Email'])))
    || (isset($_POST['deletedUser_Email_repeat']) && !empty(trim($_POST['deletedUser_Email_repeat']))))
{
    if($_POST['deletedUser_Email'] !== $_POST['deletedUser_Email_repeat'])
        exit("两次邮箱输入不相同，请重新输入");
    $deletedUser_Email = $_POST['deletedUser_Email'];  
    if (strlen($deletedUser_Email) > 100)
        exit("邮箱长度过长，疑似不正常的操作！");
    else if (!(filter_var($deletedUser_Email, FILTER_VALIDATE_EMAIL)))
        exit("不是一个有效的邮箱");
    
    if($row['isAdmin'] == 0)
        $sql_str = "DELETE FROM $mysql_users_table WHERE email='$email' AND email='$deletedUser_Email'";
    else
        $sql_str = "DELETE FROM $mysql_users_table WHERE email='$deletedUser_Email'";

    if(!mysqli_query($sql_connect, $sql_str))
        exit("在删除用户时发生错误，可能原因是无权限删除某个用户");
    else
        echo "用户删除成功";
}

echo "<br><br>";

//针对不同用户设置不同查询条件
if ($row['isAdmin'] == 0)
    $sqlStr = "SELECT * FROM $mysql_users_table WHERE email='$email'";
else
    $sqlStr = "SELECT * FROM $mysql_users_table";
//输出用户信息
if ($result = mysqli_query($sql_connect, $sqlStr)) {
    while ($rowMsg = $result->fetch_assoc())
    {
        if($email == $rowMsg['email'])
            echo "==> ";
        echo  "Email: " . $rowMsg['email'] . " / name: " . $rowMsg['name'] . " / QQ: " . $rowMsg['qq'] . "<br>";
    }
} else
    echo "查看用户信息失败！";


mysqli_close($sql_connect);
?>

<br>
<form method="POST" action="#" name="changeUserInfo">
    <label>1.请输入用户email<label>
    <input type="email" name="changedUser_Email" /><br>   
    <label>请输入修改后的用户资料内容（留空则保持不变）<label><br>
    <input type="text" name="changedQQ" placeholder="请输入修改后的QQ" /><br>
    <input type="text" name="changedName" placeholder="请输入修改后的name" /><br>
    <input type="email" name="changedEmail" placeholder="请输入修改后的email" /><br>
    <input type="password" name="changedPassword" placeholder="请输入修改后的密码" /><br>
    <input type="password" name="changedPassword_repeat" placeholder="请再次输入修改后的密码" /><br>
    <button type="submit">修改</button><br>
</form>
<br>

<form method="POST" action="#" name="deleteUser">
    <label>2.删除用户<label><br>
    <input type="email" name="deletedUser_Email" placeholder="输入被删除用户的邮箱" /><br>
    <input type="email" name="deletedUser_Email_repeat" placeholder="再次输入被删除用户的邮箱" /><br>                             
    <button type="submit">WARNING!!!!  删除无法撤销!!!!  按下此按钮进行删除!!!</button><br>
</form>
