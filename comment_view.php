<?php 

 require("dbconnect.php");
 $comments = array();

 $sql = 'SELECT `c`.*,`u`.* FROM `users` AS `u` LEFT OUTER JOIN `comments` AS `c` ON `c`.`user_id`=`u`.`id` WHERE `c`.`feed_id` = ? ORDER BY `c`.`id` DESC';

  $data = array($feed["id"]);
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);
  while (true) {
    $record = $stmt->fetch(PDO::FETCH_ASSOC); //  ここより上でfetchしてない？
    if ($record == false) {
         break;
    }
   $comments[] = $record;
  }

 ?>

            <div class="collapse" id="collapseComment<?php echo $feed["id"]; ?>">
                <div class="col-xs-12" style="margin-top:10px;">
                 <?php foreach ($comments as $comment) { ?>
                  <img src="user_profile_img/<?php echo $comment['img_name']; ?>" width="40" class="img-circle">
                  <span style="border-radius: 100px!important; -webkit-appearance:none;background-color:#eff1f3;padding:10px;margin-top:10px;"><a href="#"><?php echo $comment["name"]; ?></a> <?php echo $comment["comment"]; ?></span>
                  <br>
<!--                   <br>
                  <img src="https://placehold.jp/40x40" width="40" class="img-circle">
                  <span style="border-radius: 100px!important; -webkit-appearance:none;background-color:#eff1f3;padding:10px;margin-top:10px;"><a href="#">おもえもん</a> のびたくん。。。？</span>
                  <br>
                  <img src="https://placehold.jp/40x40" width="40" class="img-circle">
                  <span style="border-radius: 100px!important; -webkit-appearance:none;background-color:#eff1f3;padding:10px;margin-top:10px;"><a href="#">おもえもん</a> のびたくん。。。？</span>
                  <br> -->
                 
                  <?php } ?>
                  <form method="post" class="form-inline" action="comment.php" role="comment">
                    <div class="form-group">
                      <img src="user_profile_img/<?php echo $signin_user['img_name']; ?>" width="40" class="img-circle">
                    </div>
                    <div class="form-group">
                        <input type="text" name="write_comment" class="form-control" style="width:400px;border-radius: 100px!important; -webkit-appearance:none;" placeholder="コメントを書く">
                    </div>
                    <input type="hidden" name="feed_id" value="<?php echo $feed["id"]; ?>">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Post</button>
                    </div>
                  </form>
                </div>
              </div>