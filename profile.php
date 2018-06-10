<?php 

 require("dbconnect.php");

 session_start();
 // require('dbconnect.php');
 // $sql = 'SELECT * FROM `users` WHERE `id`=?';
 // $data = array($_SESSION['id']);
 // $stmt = $dbh->prepare($sql);
 // $stmt->execute($data);
 // $signin_user = $stmt->fetch(PDO::FETCH_ASSOC);

require('function.php');

check_signin($_SESSION['id']);

$signin_user = get_signin_user($dbh, $_SESSION['id']);


 $user_id = $_GET["user_id"];
 $_SESSION['user_id'] = $user_id;

 $users = array();

 $sql = 'SELECT `f`.*,`u`.* FROM `users` AS `u` LEFT OUTER JOIN `followers` AS `f` ON `f`.`user_id`=`u`.`id` WHERE `u`.`id` = ? ORDER BY `f`.`id` DESC';

  $data = array($_SESSION['user_id']);
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);
  while (true) {
    $record = $stmt->fetch(PDO::FETCH_ASSOC); //  ここより上でfetchしてない？
    if ($record == false) {
         break;
    }
      // like済みか判断するSQLを作成
      $follow_flag_sql = "SELECT COUNT(*) AS `follow_flag` FROM `followers` WHERE `follower_id` = ? AND `user_id` = ?";

      $follow_flag_data = array($signin_user['id'], $_SESSION['user_id']);

      // SQL実行
      $follow_flag_stmt = $dbh->prepare($follow_flag_sql);
      $follow_flag_stmt->execute($follow_flag_data);
      // followしてる数を取得
      $follow_flag = $follow_flag_stmt->fetch(PDO::FETCH_ASSOC); //  follow数のfetch

      if ($follow_flag["follow_flag"] > 0) {
      $record["follow_flag"] = 1;
      }else{
      $record["follow_flag"] = 0;
      }
   $users[] = $record;
  }

  $followers = array();
  $follower_sql = 'SELECT `f`.*,`u`.* FROM `users` AS `u` LEFT OUTER JOIN `followers` AS `f` ON `f`.`user_id`=`u`.`id` WHERE `f`.`follower_id` = ? ORDER BY `f`.`id` DESC';

  $follower_data = array($_SESSION['user_id']);
  $follower_stmt = $dbh->prepare($follower_sql);
  $follower_stmt->execute($follower_data);
  while (true) {
    $record = $follower_stmt->fetch(PDO::FETCH_ASSOC); //  ここより上でfetchしてない？
    if ($record == false) {
         break;
    }
   $followers[] = $record;
  }

  $follows = array();
  $follow_sql = 'SELECT `f`.*,`u`.* FROM `users` AS `u` LEFT OUTER JOIN `followers` AS `f` ON `f`.`follower_id`=`u`.`id` WHERE `f`.`user_id` = ? ORDER BY `f`.`id` DESC';

  $follow_data = array($_SESSION['user_id']);
  $follow_stmt = $dbh->prepare($follow_sql);
  $follow_stmt->execute($follow_data);
  while (true) {
    $record = $follow_stmt->fetch(PDO::FETCH_ASSOC); //  ここより上でfetchしてない？
    if ($record == false) {
         break;
    }
   $follows[] = $record;
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
<!-- <?php 
echo "<pre>"; 
var_dump($followers);
echo "</pre>"; 
?> -->
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
          <li><a href="register/signup.php">サインアップ</a></li>
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
              <li><a href="profile.php?user_id=<?php echo $signin_user['id']; ?>">マイページ</a></li>
              <li><a href="signout.php">サインアウト</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-xs-3 text-center">
        <img src="user_profile_img/<?php echo $users[0]['img_name']; ?>" class="img-thumbnail" />
        <h2><?php echo $users[0]['name']; ?></h2>
          <?php if ($_SESSION['user_id'] != $signin_user['id']){ ?>
            <?php if($users[0]['follow_flag'] == 0){ ?>
              <a href="follow.php?user_id=<?php echo $users[0]['id']; ?>" class="btn btn-default btn-block">フォローする</a>
            <?php }else{ ?>
              <a href="unfollow.php?user_id=<?php echo $users[0]['id']; ?>" class="btn btn-default btn-block">フォロー解除する</a>
            <?php } ?>
          <?php } ?>
      </div>

      <div class="col-xs-9">
        <ul class="nav nav-tabs">
          <li class="active">
            <a href="#tab1" data-toggle="tab">Followers</a>
          </li>
          <li>
            <a href="#tab2" data-toggle="tab">Following</a>
          </li>
        </ul>
        <!--タブの中身-->
        <div class="tab-content">
          <div id="tab1" class="tab-pane fade in active">
           <?php foreach ($follows as $follow) { ?>
            <div class="thumbnail">
              <div class="row">
                <div class="col-xs-2">
                  <a href="profile.php?user_id=<?php echo $follow['id']; ?>"><img src="user_profile_img/<?php echo $follow['img_name']; ?>" width="80"></a>
                </div>
                <div class="col-xs-10">
                  <a href="profile.php?user_id=<?php echo $follow['id']; ?>"><?php echo $follow['name']; ?></a><br>
                  <a href="#" style="color: #7F7F7F;"><?php echo $follow['created']; ?>からメンバー</a>
                </div>
              </div>
            </div>
          <?php } ?>
          <!-- thumbnail -->
          </div>
          <div id="tab2" class="tab-pane fade">
           <?php foreach ($followers as $follower) { ?>
            <div class="thumbnail">
              <div class="row">
                <div class="col-xs-2">
                  <a href="profile.php?user_id=<?php echo $follower['id']; ?>"><img src="user_profile_img/<?php echo $follower['img_name']; ?>" width="80"></a>
                </div>
                <div class="col-xs-10">
                  <a href="profile.php?user_id=<?php echo $follower['id']; ?>">名前 <?php echo $follower['name']; ?></a><br>
                  <a href="#" style="color: #7F7F7F;"><?php echo $follower['created']; ?>からメンバー</a>
                </div>
              </div>
            </div>
           <?php } ?>
          </div>
        </div>

      </div><!-- class="col-xs-12" -->

    </div><!-- class="row" -->
  </div><!-- class="cotainer" -->
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
</body>
</html>