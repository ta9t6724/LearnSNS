<?php 
// session変数を使えるようにする
session_start();

// DBに接続
require('dbconnect.php');

// サインインしているユーザーの情報を取得
$sql = 'SELECT * FROM `users` WHERE `id`=?';
$data = array($_SESSION['id']);
$stmt = $dbh->prepare($sql);
$stmt->execute($data);
$signin_user = $stmt->fetch(PDO::FETCH_ASSOC);

// user_idを取得
$user_id = $_SESSION['user_id'];
// SQL文作成（INSERT文）
$sql = "INSERT INTO `followers` SET `user_id` = ?, `follower_id` = ?";
// SQL実行
$data = array($user_id, $signin_user['id']);
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

// echo $user_id . '<br>';
// echo $_SESSION['user_id']. '<br>';
// echo $_SESSION['id'];

// 一覧に戻る
header("Location: profile.php?user_id=". $_SESSION['user_id']);


 ?>