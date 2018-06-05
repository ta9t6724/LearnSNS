<?php 
  session_start();
  require('dbconnect.php');
  $sql = 'SELECT * FROM `users` WHERE `id`=?';
  $data = array($_SESSION['id']);
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);

  $signin_user = $stmt->fetch(PDO::FETCH_ASSOC);
  // DB接続
  require('dbconnect.php');

  // ユーザーの一覧を表示するため取得する
  $sql = 'SELECT * FROM `users`';
  $data = array();
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);
  // SQL文作成

  // SQL実行

  // 繰り返し文の中でフェッチ（配列に保存）
  $users = array();
  while (true) {
    $record = $stmt->fetch(PDO::FETCH_ASSOC); //  ここより上でfetchしてない？
    if ($record == false) {
        break;
    }
    $count_sql = 'SELECT COUNT(feed) AS `cnt` FROM `feeds` WHERE `user_id` = ?';
    $count_data = array($record['id']);
    $count_stmt = $dbh->prepare($count_sql);
    $count_stmt->execute($count_data);
    $feed_cnt = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $record["feed_cnt"] = $feed_cnt["cnt"];
    $users[] = $record;
  }


 ?>



<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Learn SNS</title>
  <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body style="margin-top: 60px; background: #E4E6EB;">
    <nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="timeline.php">Learn SNS</a>
      </div>
      <div class="collapse navbar-collapse" id="navbar-collapse1">
        <ul class="nav navbar-nav">
          <li><a href="timeline.php">タイムライン</a></li>
          <li class="active"><a href="user_index.php">ユーザー一覧</a></li>
        </ul>
        <form method="GET" action="" class="navbar-form navbar-left" role="search">
          <div class="form-group">
            <input type="text" name="search_word" class="form-control" placeholder="投稿を検索">
          </div>
          <button type="submit" class="btn btn-default">検索</button>
        </form>
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="user_profile_img/<?php echo $signin_user['img_name']; ?>" width="18" class="img-circle"><?php echo $signin_user['name']; ?><span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="#">マイページ</a></li>
              <li><a href="signout.php">サインアウト</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-xs-12">

        <?php foreach ($users as $user) { ?>
          <div class="thumbnail">
            <div class="row">
              <div class="col-xs-1">
                <a href="profile.php?user_id=<?php echo $user['id']; ?>"><img src="user_profile_img/<?php echo $user['img_name']; ?>" width="80"></a>
              </div>
              <div class="col-xs-11">
                <a href="profile.php?user_id=<?php echo $user['id']; ?>">名前 <?php echo $user['name']; ?></a><br>
                <a href="#" style="color: #7F7F7F;"><?php echo $user['created']; ?>からメンバー</a>
              </div>
            </div>
<!--             <?php 
                require('dbconnect.php');

                $count_sql = 'SELECT COUNT(feed) AS `cnt` FROM `feeds` WHERE `user_id` = ?';
                $count_data = array($user['id']);
                $count_stmt = $dbh->prepare($count_sql);
                $count_stmt->execute($count_data);
                $feed_cnt = $count_stmt->fetch(PDO::FETCH_ASSOC);
             ?> -->
            <div class="row feed_sub">
              <div class="col-xs-12">
                <span class="comment_count">つぶやき数 : <?php echo $user['feed_cnt']; ?></span>
              </div>
            </div>
          </div><!-- thumbnail -->
        <?php } ?>
      </div><!-- class="col-xs-12" -->
    </div><!-- class="row" -->
  </div><!-- class="cotainer" -->
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
</body>
</html>