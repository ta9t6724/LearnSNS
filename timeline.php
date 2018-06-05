<?php
  // timeline.phpの処理を記載

  session_start();
  require('dbconnect.php');
  $sql = 'SELECT * FROM `users` WHERE `id`=?';
  $data = array($_SESSION['id']);
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);

  $signin_user = $stmt->fetch(PDO::FETCH_ASSOC);

      // 初期化
    $errors = array();

    // ユーザーが投稿ボタンを押したら発動
    if (!empty($_POST)) {

        // バリデーション
        $feed = $_POST['feed']; // 投稿データ

        // 投稿の空チェック
        if ($feed != '') {
            // 投稿処理
            $sql ='INSERT INTO `feeds` SET `feed` =?, `user_id` =?, `created` =NOW()';
            $data = array($feed,$signin_user['id']);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);

            header('Location: timeline.php');
            exit();

        } else {
            $errors['feed'] = 'blank';
        }

    }

    $page = ''; //ページ番号が入る変数
    $page_row_number = 5; //1ページあたりに表示するデータの数

    if (isset($_GET['page'])){
    	$page = $_GET['page'];
    }else{
    	// GET送信されてるページ数がない場合、1ページ目とみなす
    	$page = 1;
    }

    // max: カンマ区切りで羅列された数字の中から最大の数を返す
    // 第一引数に入っている値を見て、第二引数と比較。もし第二引数の方が大きければ第二引数の値を返す
    $page = max($page, 1);

    $count_sql = 'SELECT COUNT(feed) AS `cnt` FROM `feeds`';
    $count_data = array();
    $count_stmt = $dbh->prepare($count_sql);
    $count_stmt->execute($count_data);
    $feed_cnt = $count_stmt->fetch(PDO::FETCH_ASSOC);
    // ceil: 切り上げ
    $max_page = ceil($feed_cnt['cnt'] / $page_row_number);
    // $record["feed_cnt"] = $feed_cnt["cnt"];
    // if($record['feed_cnt'] % 5 == 0){
    // 	$max_page = $record['feed_cnt']/5;
    // }else{
    // 	$page_result = $record['feed_cnt']/5;
    // floor: 切り捨て
    // 	$max_page = floor($page_result) + 1;
    // }

    // 第一引数と第二引数を比較し、第二引数の方が小さければ、第二引数の値を返す
    $page = min($page, $max_page);

    // データを取得する開始番号を計算
    $start = ($page -1)*$page_row_number;

    //検索ボタンが押されたら、あいまい検索
    //検索ボタンが押された=GET送信されたsearch_wordというキーのデータが有る
    if (isset($_GET['search_word']) == true){
      //あいまい検索用SQL(LIKE演算子)

      $sql = 'SELECT `f`.*,`u`.`name`,`u`.`img_name` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id`=`u`.`id` WHERE `f`.`feed` LIKE "%'.$_GET['search_word'].'%" ORDER BY `f`.`created` DESC';
      // $sql = 'select `f`.*,`u`.`name`,`u`.`img_name` from feeds f left join users u on f.user_id = u.id where f.feed like "%'.$_GET['search_word'].'%" order by f. created desc';
    }else{
      // 通常（検索ボタンを押していない）は全件取得
      // LEFT JOINで全件取得
      $sql = "SELECT `f`.*,`u`.`name`,`u`.`img_name` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id`=`u`.`id` WHERE 1 ORDER BY `f`.`created` DESC LIMIT $start, $page_row_number";
    }

    $data = array();
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    // executeで取得したタイミングでは
    // Object型 → Array型に変換する
    // PDOでは、fetch()を使用する
    // var_dump($stmt);

    // 表示用の配列を初期化
    $feeds = array();
    // $arr = array();

    while (true) {
        $record = $stmt->fetch(PDO::FETCH_ASSOC); //  ここより上でfetchしてない？
        if ($record == false) {
            break;
        }
        // 同じWhile文の中に$stmtや$dataがすでにあるため、そのまま実行すると上書きされてしまう。
        // like数を取得するSQL文を作成

        $like_sql = 'SELECT COUNT(*) AS `like_cnt` FROM `likes` WHERE `feed_id` = ?';
        $like_data = array($record['id']);

        // SQL文を実行
    	$like_stmt = $dbh->prepare($like_sql);
    	$like_stmt->execute($like_data);
        // like数を取得
        $like = $like_stmt->fetch(PDO::FETCH_ASSOC); //  like数のfetch

        $record['like_cnt'] = $like['like_cnt'];

        // like済みか判断するSQLを作成
        $like_flag_sql = "SELECT COUNT(*) as `like_flag` FROM `likes` WHERE `user_id` = ? AND `feed_id` = ?";

        $like_flag_data = array($_SESSION["id"], $record['id']);

        // SQL実行
    	$like_flag_stmt = $dbh->prepare($like_flag_sql);
    	$like_flag_stmt->execute($like_flag_data);
        // likeしてる数を取得
        $like_flag = $like_flag_stmt->fetch(PDO::FETCH_ASSOC); //  like数のfetch

        if ($like_flag["like_flag"] > 0) {
        $record["like_flag"] = 1;
        }else{
        $record["like_flag"] = 0;
        }
        $comment_sql = "SELECT COUNT(*) AS `cnt` FROM `comments` WHERE `feed_id` = ?";
		// SQL実行
		$comment_data = array($record['id']);
		$comment_stmt = $dbh->prepare($comment_sql);
		$comment_stmt->execute($comment_data);
        $comment_cnt = $comment_stmt->fetch(PDO::FETCH_ASSOC); 
        $record["comment_cnt"] = $comment_cnt["cnt"];

        // いいね済みのみのリンクが押されたときは、配列にすでにいいね！してるものだけを代入する
        if (isset($_GET["feed_select"]) && ($_GET["feed_select"] == "likes") && ($record["like_flag"] == 1)) {
        	$feeds[] = $record;
        }
        // if (isset($_GET["feed_select"]) && ($_GET["feed_select"] == "news")) {
        // 	$feeds[] = $record;
        // }
        else{
        	$feeds[] = $record;
        }
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
  <div class="navbar navbar-default navbar-fixed-top">
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
          <li class="active"><a href="#">タイムライン</a></li>
          <li><a href="user_index.php">ユーザー一覧</a></li>
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
  </div>

  <div class="container">
    <div class="row">
      <div class="col-xs-3">
        <ul class="nav nav-pills nav-stacked">
        <?php if (isset($_GET["feed_select"]) && ($_GET["feed_select"] == "likes")) {
         ?>
          <li><a href="timeline.php?feed_select=news">新着順</a></li>
          <li class="active"><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
        <?php }else{ ?>
          <li class="active"><a href="timeline.php?feed_select=news">新着順</a></li>
          <li><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
        <?php } ?>

          <!-- <li><a href="timeline.php?feed_select=follows">フォロー</a></li> -->
        </ul>
      </div>
      <div class="col-xs-9">
        <div class="feed_form thumbnail">
          <form method="POST" action="">
            <div class="form-group">
              <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"></textarea><br>
                  <?php if(isset($errors['feed']) && $errors['feed'] == 'blank'){ ?>
                <p class="alert alert-danger">投稿データを入力してください</p>
              <?php } ?>
            </div>
            <input type="submit" value="投稿する" class="btn btn-primary">
       </form>
      </div>
       <!-- 繰り返し -->
        <?php foreach ($feeds as $feed) { ?>
          <div class="thumbnail">
            <div class="row">
              <div class="col-xs-1">
                <a href="profile.php?user_id=<?php echo $feed['user_id']; ?>"><img src="user_profile_img/<?php echo $feed['img_name']; ?>" width="40"></a>
              </div>
              <div class="col-xs-11">
                <a href="profile.php?user_id=<?php echo $feed['user_id']; ?>"><?php echo $feed["name"]; ?></a> <br>
                <a href="#" style="color: #7F7F7F;"><?php echo $feed['created'] ?></a>
              </div>
            </div>
            <div class="row feed_content">
              <div class="col-xs-12" >
                <span style="font-size: 24px;"><?php echo $feed['feed']; ?></span>
              </div>
            </div>
            <div class="row feed_sub">
              <div class="col-xs-12">
              	<?php if($feed['like_flag'] == 0){ ?>
              	<a href="like.php?feed_id=<?php echo $feed["id"]; ?>">
                    <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-thumbs-up" aria-hidden="true"></i>いいね！</button>
                </a>
                <?php }else{ ?>
                	<a href="unlike.php?feed_id=<?php echo $feed["id"]; ?>">
                    <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-thumbs-down" aria-hidden="true"></i>いいねを取り消す</button>
                </a>
                <?php } ?>

                <?php if($feed['like_cnt'] > 0){ ?>
                <span class="like_count">いいね数 : <?php echo $feed['like_cnt']; ?></span>
                <?php } ?>

                <a href="#collapseComment<?php echo $feed["id"]; ?>" data-toggle="collapse" aria-expanded="false"><span class="comment_count">コメント数 : <?php echo $feed['comment_cnt']; ?></span></a>
                  <?php if ($feed["user_id"] == $_SESSION["id"] ){ ?>
              
                  <a href="edit.php?feed_id=<?php echo $feed["id"]; ?>" class="btn btn-success btn-xs">編集</a>
                  <a onclick="return confirm('削除してよろしいですか？')" href="delete.php?feed_id=<?php echo $feed["id"] ?>" class="btn btn-danger btn-xs">削除</a>
                  <?php } ?>
              </div>

              <!-- コメントが押されたら表示される領域 -->
              	<?php include("comment_view.php"); ?>
            </div>
          </div>
        <?php } ?>
        <!-- 繰り返し終了 -->

        <div aria-label="Page navigation">
          <ul class="pager">
          	<?php if ($page == 1){ ?>
          	   <li class="previous disabled"><a href="#"><span aria-hidden="true">&larr;</span> Newer</a></li>
          	<?php }else{ ?>
            	<li class="previous"><a href="timeline.php?page=<?php echo $page - 1; ?>"><span aria-hidden="true">&larr;</span> Newer</a></li>
            <?php } ?>
            <?php if ($page == $max_page){ ?>
            	<li class="next disabled"><a href="#">Older <span aria-hidden="true">&rarr;</span></a></li>
            <?php }else{ ?>
            	<li class="next"><a href="timeline.php?page=<?php echo $page + 1; ?>">Older <span aria-hidden="true">&rarr;</span></a></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
</body>
</html>
