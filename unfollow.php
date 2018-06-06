<?php 
// session変数を使えるようにする
session_start();
// DBに接続
require('dbconnect.php');

// user_idを取得
$user_id = $_SESSION['user_id'];
// SQL文作成（DELETE文）
$sql = "DELETE FROM `followers` WHERE `user_id` = ? AND `follower_id` = ?";
// SQL実行
$data = array($user_id, $_SESSION['id']);
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

// echo $user_id . '<br>';
// echo $signin_user['id'];


// echo $user_id . '<br>';
// echo $_SESSION['user_id']. '<br>';
// echo $_SESSION['id'];

// 一覧に戻る
header("Location: profile.php?user_id=". $_SESSION['user_id']);

 ?>