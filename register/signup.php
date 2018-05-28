<?php
  session_start();

  if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'rewrite') {
        $_POST['input_name'] = $_SESSION['register']['name'];
        $_POST['input_email'] = $_SESSION['register']['email'];
        $_POST['input_password'] = $_SESSION['register']['password'];

        $errors['rewrite'] = true;
    }
  $name   = '';
  $email  = '';
  $errors = array();

  if (!empty($_POST)) {
    $name = $_POST['input_name'];
    $email = $_POST['input_email'];
    $password = $_POST['input_password'];

    $count_password = strlen($password);
    $count_name     = mb_strlen($name);
  // ユーザー名の空チェック
        if ($name == '') {
            $errors['name'] = 'blank';
        }elseif ($count_name < 3 || 8 < $count_name) {
          $errors['name'] = 'length';
        }else { 
          // 1.DB接続
          require('../dbconnect.php');
          // 2.SQL接続
          $sql = 'SELECT COUNT(*) as `cnt_name` FROM `users` WHERE `name` = ?';
          $data = array($name);
          $stmt = $dbh->prepare($sql);
          $stmt->execute($data);

          // 3.DB切断
          $dbh = null;

          // 4.取り出し処理
          $rec = $stmt->fetch(PDO::FETCH_ASSOC);

          if ($rec['cnt_name'] > 0) {
              $errors['name'] = 'duplicate';
          }
        }
        if ($email == '') {
            $errors['email'] = 'blank';
        } 
        // メールアドレスの重複チェック
        else { 
          // 1.DB接続
          require('../dbconnect.php');
          // 2.SQL接続
          $sql = 'SELECT COUNT(*) as `cnt` FROM `users` WHERE `email` = ?';
          $data = array($email);
          $stmt = $dbh->prepare($sql);
          $stmt->execute($data);

          // 3.DB切断
          $dbh = null;

          // 4.取り出し処理
          $rec = $stmt->fetch(PDO::FETCH_ASSOC);

          if ($rec['cnt'] > 0) {
              $errors['email'] = 'duplicate';
          }
        }
        if ($password == '') {
            $errors['password'] = 'blank';
        }elseif ($count_password < 4 || 16 < $count_password) {
          $errors['password'] = 'length';
        }

        // 画像名を取得
        $file_name = ''; // ①
        if (!isset($_REQUEST['action'])) { // ②
        $file_name = $_FILES['input_img_name']['name'];
        }
        if (!empty($file_name)) {
            // 拡張子チェックの処理
            $file_type = substr($file_name, -4); // 画像名の後ろから3文字を取得
            $file_type = strtolower($file_type); // 大文字が含まれていた場合すべて小文字化
            if ($file_type != '.jpg' && $file_type != '.png' && $file_type != '.gif' && $file_type != 'jpeg') {
                $errors['img_name'] = 'type';
            }
        } else {
            $errors['img_name'] = 'blank';
        }
        // エラーがなかった時の処理
        if (empty($errors)) {

            date_default_timezone_set('Asia/Manila');
            $date_str = date('YmdHis'); // YmdHisを指定することで取得フォーマットを指定
            $submit_file_name = $date_str . $file_name;

            move_uploaded_file($_FILES['input_img_name']['tmp_name'], '../user_profile_img/' . $submit_file_name);

            $_SESSION['register']['name'] = $_POST['input_name'];
            $_SESSION['register']['email'] = $_POST['input_email'];
            $_SESSION['register']['password'] = $_POST['input_password'];
            // 上記3つは$_SESSION['register'] = $_POST;という書き方で1文にまとめることもできます
            $_SESSION['register']['img_name'] = $submit_file_name;

            header('Location: check.php');
            exit();
        }

  }


?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Learn SNS</title>
  <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="../assets/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/style.css"> <!-- 追加 -->
</head>
<body style="margin-top: 60px">
  <div class="container">
    <div class="row">
      <!-- ここにコンテンツ -->
      <div class="col-xs-8 col-xs-offset-2 thumbnail">
        <h2 class="text-center content_header">アカウント作成</h2>
        <form method="POST" action="signup.php" enctype="multipart/form-data">
          <div class="form-group">
            <label for="name">ユーザー名</label>
            <!-- inputタグのvalue属性の値に$nameを出力 -->
            <input type="text" name="input_name" class="form-control" id="name" placeholder="山田 太郎" value="<?php echo htmlspecialchars($name); ?>">
            <?php if(isset($errors['name']) && $errors['name'] == 'blank') { ?>
              <p class="text-danger">ユーザー名を入力してください</p>
            <?php } elseif(isset($errors['name']) && $errors['name'] == 'length') { ?>
              <p class="text-danger">名前は3 ~ 8文字で入力してください</p>
             <?php }elseif(isset($errors['name']) && $errors['name'] == 'duplicate') { ?>
              <p class="text-danger">すでに登録されているニックネームです。</p>
            <?php } ?>
          </div>
          <div class="form-group">
            <label for="email">メールアドレス</label>
            <!-- inputタグのvalue属性の値に$nameを出力 -->
            <input type="email" name="input_email" class="form-control" id="email" placeholder="example@gmail.com" value="<?php echo htmlspecialchars($email); ?>">
            <?php if(isset($errors['email']) && $errors['email'] == 'blank') { ?>
              <p class="text-danger">メールアドレスを入力してください</p>
            <!-- 重複エラーメッセージ -->
            <?php }elseif(isset($errors['email']) && $errors['email'] == 'duplicate') { ?>
              <p class="text-danger">すでに登録されているアドレスです。</p>
            <?php } ?>
          </div>
          <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" name="input_password" class="form-control" id="password" placeholder="4 ~ 16文字のパスワード">
            <?php if(isset($errors['password']) && $errors['password'] == 'blank') { ?>
              <p class="text-danger">パスワードを入力してください</p>
            <?php } ?>
            <?php if(isset($errors['password']) && $errors['password'] == 'length') { ?>
              <p class="text-danger">パスワードは4 ~ 16文字で入力してください</p>
            <?php } ?>
            <!-- もし$errorsが空じゃなければエラーメッセージを出力する -->
            <?php if(!empty($errors)) { ?>
              <p class="text-danger">パスワードを再度入力して下さい</p>
            <?php } ?>
          </div>
          <div class="form-group">
            <label for="img_name">プロフィール画像</label>
            <input type="file" name="input_img_name" id="img_name" accept="image/*">
            <?php if(isset($errors['img_name']) && $errors['img_name'] == 'blank') { ?>
              <p class="text-danger">画像を選択してください</p>
            <?php } ?>
            <?php if(isset($errors['img_name']) && $errors['img_name'] == 'type') { ?>
              <p class="text-danger">拡張子が「jpg」「png」「gif」の画像を選択してください</p>
            <?php } ?>
          </div>
          <input type="submit" class="btn btn-default" value="確認">
          <a href="../signin.php" style="float: right; padding-top: 6px;" class="text-success">サインイン</a>
        </form>
      </div>
      <!-- ここまで -->

    </div>
  </div>
  <script src="../assets/js/jquery-3.1.1.js"></script>
  <script src="../assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="../assets/js/bootstrap.js"></script>
</body>
</html>
