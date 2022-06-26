<!-- このページを最後まで読み込んだあと、自動的に次のページへ飛ぶ。
     PHPではそのような処理を実装できない。
     hiddenの値も一緒に送る。
	 document. (formNameを入れる) .submit　-->
<script type="text/javascript">
	window.onload = function(){
		document.postForm.submit();
	}
</script>

<?php

	include('config.php');
	include('enum.php');

	//--------------  セキュリティ準備 -----------------------------------------
	include('./class/security_class.php');
	$security = new Security();
	
	//--------------  自作クラス使用準備1 -------------------------------------
	include('./class/class.php');
	$pageMove = new PageMove();
	
	//--------------  セッション関連 -----------------------------------------
	session_start();
	session_regenerate_id(true);

	//強制ブラウズはリダイレクト
	if (!isset($_SESSION['csrf_token'])){

		$_SESSION['error_status'] = ErrorStatus::BadRequest->value; //enum
		$pageMove->redirect_to_index();
		exit();
	}

	if (!isset($_POST['csrf_token'])){

		// リダイレクト
		$_SESSION['error_status'] = ErrorStatus::BadRequest->value; //enum
		$pageMove->redirect_to_index();
		exit();
	}
	
	$csrf_token = $_POST['csrf_token'];

	//CSRF チェック
	if ($csrf_token != $_SESSION['csrf_token']) {

		$_SESSION = array();
		session_destroy();
		session_start();

		// リダイレクト
		$_SESSION['error_status'] = ErrorStatus::BadRequest->value; //enum
		$pageMove->redirect_to_index();
		exit();
	}

	//パラメーター取得
	if (isset($_POST['username'])){	
		$username = $_POST['username'];	
	}else{
		$username = '';
	}
		
	if (isset($_POST['password'])){	
		$password = $_POST['password'];	
	}else{
		$password = '';
	}
	
	if (isset($_POST['auto'])){
		$auto = 'true';	
		$_SESSION['auto'] = 'checked';
	}else{
		$auto = '';
		$_SESSION['auto'] = Null;
	}

	//--------------  DB使用準備 ----------------------------------------------------
	$pdo = new PDO(DNS, DB_USER, DB_PASS, $security->get_pdo_options());
	
	//--------------  自作クラス使用準備2 ----------------------------------------------
	$login = new Login($pdo, $username);
  
	//--------------  ログイン判定 --------------------------------------------------------------
	try {
		
		// ユーザー抽出
		$rows = $login->get_userInfo_in_record();
		
		if ((count($rows) == 0)
			       or 
			(!password_verify($password, $rows[0]['password']))) {
				
			//--------------  ログイン認証失敗 --------------------------------------------------------------
			// アカウント名かパスワードを間違えていたら失敗回数をカウントする。
				
			$_SESSION['failed_count'] += 1;
		
			if ($_SESSION['failed_count'] >= LOGIN_FAILED_LIMIT) {
				// アカウントロック
				$_SESSION['locked_time'] = date('Y-m-d H:i:s');
				$_SESSION['locked_status'] = 'locked';
				
				// リダイレクト
				$_SESSION['error_status'] = ErrorStatus::AccountLocked->value; //enum
				$pageMove->redirect_to_index();
				exit();
			}
						
			// リダイレクト
			$_SESSION['error_status'] = ErrorStatus::LoginTypingError->value; //enum
			$pageMove->redirect_to_index();
			exit();
		}else{

			//--------------  ログイン成功 --------------------------------------------------------------
			$_SESSION['error_status'] = ErrorStatus::Fine->value;
			// アカウントロック解除
			$_SESSION['failed_count'] = 0;
			$_SESSION['locked_time'] = NULL;
			$_SESSION['locked_status'] = 'unlocked';

		}	
		
		// セッションIDの振り直し
		session_regenerate_id(true);

		// 値を送る

		echo '<form action = "autologin_check.php" method = "post" name = "postForm">';
		echo '	<input type="hidden" name="username" value="' . $security->h($username) . '">';
		echo '	<input type="hidden" name="password" value="' . $security->h($password) . '">';
		echo '	<input type="hidden" name="auto" value="' . $security->h($auto) . '">';
		echo '	<input type="hidden" name="csrf_token" value="' . $security->h($_SESSION['csrf_token']) . '">';
		echo '</form>';

		// リダイレクト
		$_SESSION['username'] = $rows[0]['username'];
		//$pageMove->redirect_to_main();
		//exit();

	} catch (PDOException $e) {
		die($e->getMessage());
	}

?>