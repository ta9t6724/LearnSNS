<?php
session_start();

	// echo "<pre>";
	// var_dump($_POST);
	// echo "</pre>";

$login_user_id = $_SESSION["id"];
$comment = $_POST["write_comment"];
$feed_id = $_POST["feed_id"];

require("dbconnect.php");
// コメントをINSERTするSQL文作成
$sql = "INSERT INTO `comments` SET `user_id` = ?, `comment` = ?, `feed_id` = ?";
// SQL実行
$data = array($_SESSION["id"], $comment, $feed_id);
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

// timeline.php（一覧）に戻る
header("Location: timeline.php");

 ?>