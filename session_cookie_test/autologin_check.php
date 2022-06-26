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
	
	//--------------  自作クラス使用準備  -------------------------------------
	include('./class/class.php');
	$pageMove = new PageMove();	//ページ移動クラスインスタンス化
	
	//--------------  セッション関連  -----------------------------------------
	session_start();
	session_regenerate_id(true);

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
	
	if (isset($_POST['csrf_token'])){	
		$csrf_token = $_POST['csrf_token'];	
	}else{
		$csrf_token = '';
	}	
	
	if (isset($_POST['auto'])){	
		$auto = $_POST['auto'];	
	}else{
		$auto = '';
	}

	//CSRF チェック
	if (!isset($cookie_token)) {
		if ($csrf_token != $_SESSION['csrf_token']) {

			$_SESSION = array();
			session_destroy();
			session_start();
		
			// リダイレクト
			$_SESSION['error_status'] = ErrorStatus::BadRequest->value;
			$pageMove->redirect_to_index();
			exit();
		}
	}

	//ログイン判定フラグ
	$auto_result = false;

	//--------------  ログイン判定  --------------------------------------------------------------
	try {

		$pdo = new PDO(DNS, DB_USER, DB_PASS, $security->get_pdo_options());
		$login = new Login($pdo, $username, $password);
		
		//```````  自動ログイン `````````````````````````````````````````````````````````````
		$token = new Token();
	
		if (!empty($_COOKIE['cookie_token']) ) {

			$row = $login->check_auto_login($_COOKIE['cookie_token']);
	
			if (count($row) == 1) {

				//自動ログイン成功
				$_SESSION['username'] = $row[0]['username'];
				$auto_result = true;
				$username = $_SESSION['username']; // 後続の処理のため格納
				
			} else {
	
				//自動ログイン失敗
				//古くなったトークンを削除
				$token->delete_old_token($pdo, $_COOKIE['cookie_token']);				
				//Cookie のトークンを削除
				setcookie("cookie_token", '', -1, "/", null, TRUE, TRUE); // secure, httponly
			}
		}

		//トークン生成処理
		if (($auto == true) || $auto_result) {
	   
			//トークンの作成
			$new_cookie_token = $token->get_token();
			//トークンの登録
			$token->register_token($pdo, $username, $new_cookie_token);
			//$_COOKIE['cookie_token']が上書きされる前に変数に退避
			
			if ($auto_result) {
				
				//古いトークンの削除
				$token->delete_old_token($pdo, $_COOKIE['cookie_token']);
				
			}

			//自動ログインのトークンを２週間の有効期限でCookieにセット
			//setcookie("cookie_token", $new_cookie_token, time()+60*60*24*14, "/", null, TRUE, TRUE); // secure, httponly
			setcookie("cookie_token", $new_cookie_token, time()+60*60*24*14, "/", null, TRUE, TRUE); // secure, httponly 14日間自動ログイン有効

			// 値を送る
			echo '<form action = "login.php" method = "post" name = "postForm">';
			echo 	'<input type="hidden" name="username" value="' . $security->h($username) . '">';
			echo 	'<input type="hidden" name="password" value="' . $security->h($password) . '">';
			echo 	'<input type="hidden" name="csrf_token" value="' . $security->h($_SESSION['csrf_token']) . '">';
			echo '</form>';
			
			exit();
			
		} else {
	
			// 値を送る
			echo '<form action = "login.php" method = "post" name = "postForm">';
			echo 	'<input type="hidden" name="username" value="' . $security->h($username) . '">';
			echo 	'<input type="hidden" name="password" value="' . $security->h($password) . '">';
			echo 	'<input type="hidden" name="csrf_token" value="' . $security->h($_SESSION['csrf_token']) . '">';			
			echo '</form>';

			exit();
			
		}
		
	} catch (PDOException $e) {
		die($e->getMessage());
	}

?>