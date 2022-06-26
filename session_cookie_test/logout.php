<?php

	include('config.php');
	include('enum.php');
	
	//--------------  セキュリティ準備 -----------------------------------------
	include('./class/security_class.php');
	$security = new Security();	
	
	//--------------  クリックジャッキング対策  -----------------------------------
	$security->guardClickJacking('deny');
	
	//--------------  自作クラス使用準備  -------------------------------------
	include('./class/class.php');
	$pageMove = new PageMove();	
	
	//--------------  セッション関連  -----------------------------------------
	session_start();
	session_regenerate_id(true);
	
	//強制ブラウズはリダイレクト
	if (empty($_SESSION['username']) && empty($_COOKIE['cookie_token'])){
			
		$_SESSION['error_status'] = ErrorStatus::BadRequest->value;
		$pageMove->redirect_to_index();
		exit();
			
	}
	//--------------  CSRF対策  -------------------------------------------
	//パラメーター取得
	if (isset($_POST['csrf_token'])){	
		$csrf_token = $_POST['csrf_token'];	
	}else{
		$csrf_token = '';
	}

	//csrfトークンが空ならindex.phpへ
	if (!isset($_SESSION['csrf_token'])){

		$_SESSION['error_status'] = ErrorStatus::BadRequest->value; //enum
		$pageMove->redirect_to_index();
		exit();

	}
	
	//CSRF チェック
	if ($csrf_token != $_SESSION['csrf_token']) {
		//リダイレクト
		$_SESSION['error_status'] = ErrorStatus::BadRequest->value;
		$pageMove->redirect_to_index();
		exit();
	}
	
	//--------------  自動ログイン解除  -------------------------------------
	if (isset($_COOKIE['cookie_token'])) {
		
		$cookie_token = $_COOKIE['cookie_token'];
		try {
			
			// DBとの接続
			$pdo = new PDO(DNS, DB_USER, DB_PASS, $security->get_pdo_options());
			//プレースホルダで SQL 作成
			$sql = "DELETE  FROM auto_login WHERE token = ?";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(1, $cookie_token, PDO::PARAM_STR);
			$stmt->execute();
			
			//Cookie のトークンを削除
			setCookie("cookie_token", '', -1, "/", null, TRUE, TRUE); // secure, httponly
			
		} catch (PDOException $e) {
			
			die($e->getMessage());
			
		}
	}
	
	//--------------  メイン処理  -------------------------------------------
	if (isset($_SESSION['username'])) {
		$output = 'ログアウトしました';
	} else {
		$output = 'セッションがタイムアウトしました';
	}
	
	//セッション変数のクリア
	$_SESSION = array();
	
	//セッションクッキーも削除
	if (ini_get("session.use_cookies")) {
		
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
	
	//セッションクリア
	@session_destroy();	
	
?>

<!DOCTYPE html>
<html lang = "ja">
	<head>
	    <meta charset="utf-8">
		<title>Login</title>
		<link rel="stylesheet" href="CSS/style.css">
	</head>
	<body id=pagename_logout class=ly_body>
		<div class=ly_contents>
			<div id=position_logout>
				<h1 class=fontsize_medium><?php echo $output; ?></h1>
				<a href = './index.php'>ログイン画面へ</a>
			</div>
		</div>
	</body>
</html>