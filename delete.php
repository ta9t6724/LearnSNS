<?php
// GET送信
// urlの最後 ?id=○○　の○○の取り出し方
session_start();
$feed_id = $_GET['feed_id'];
require('dbconnect.php');

// ２．SQL文を実行する オーダー
// $sqlの''の中身に入れる

// SQLインジェクション対策 ? を入れる
$sql = 'DELETE FROM `feeds` WHERE `feeds`.`id` = ?';

// ?に入れたい変数名を入れる
$data = array($feed_id);
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

// ３．データベースを切断する 電話切る
$dbh = null;

// 一覧であるview.phpに転送
header("Location: timeline.php");

?>