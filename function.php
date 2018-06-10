<?php 

	function get_signin_user($dbh,$user_id){
	  $sql = 'SELECT * FROM `users` WHERE `id`=?';
	  $data = array($user_id);
	  $stmt = $dbh->prepare($sql);
	  $stmt->execute($data);
	
	  $signin_user = $stmt->fetch(PDO::FETCH_ASSOC);
	
	  return $signin_user;

}

	function check_signin($user_id){
	  if (!isset($user_id)) {
	  	header("Location: signin.php");
	  	exit(); //このタイミングで処理を中断する
	  }
	}

 ?>