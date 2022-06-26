<?php
	include('enum.php');

	//--------------  セキュリティ準備 -----------------------------------------
	include('./class/security_class.php');
	$security = new Security();
	
	//--------------  クリックジャッキング対策  -----------------------------------
	$security->guardClickJacking('deny');
	
	//--------------  文字コード指定  ---------------------------------------
	header("Content-type: text/html; charset=utf-8");
	
	//--------------  自作クラス使用準備  -------------------------------------
	include('./class/class.php');
	$pageMove = new PageMove();
	$token = new Token();
	
	//--------------  セッション関連  -----------------------------------------
	session_start();
	session_regenerate_id(true);
	
	//強制ブラウズはリダイレクト
	if (!isset($_COOKIE['cookie_token'])) {
		
		if (!isset($_SESSION['username'])){
				
				$_SESSION['error_status'] = ErrorStatus::BadRequest->value;
				$pageMove->redirect_to_index();
				exit();
				
		}
	
		if (!isset($_SESSION['csrf_token'])){

			$_SESSION['error_status'] = ErrorStatus::BadRequest->value; //enum
			$pageMove->redirect_to_index();
			exit();
		}

		if (!isset($_POST['csrf_token'])){

			$_SESSION['error_status'] = ErrorStatus::BadRequest->value; //enum
			$pageMove->redirect_to_index();
			exit();
		} else {

			$csrf_token = $_POST['csrf_token'];
		}

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
	}

	// CSRFのトークンを取得する
	//$_SESSION['csrf_token'] = $token->get_token();
	
	// CSRFのチェックを行わない理由

?>
	
<!DOCTYPE html>
<html lang = "ja">
	<head>
	    <meta charset="utf-8">
		<title>Login</title>
		<link rel="stylesheet" href="CSS/style.css">
	</head>
	<body id=pagename_main class=ly_body>
		<div class=ly_contents>
			<form action="logout.php" method="post">
				<input type="hidden" name="csrf_token" value="<?php echo $security->h($_SESSION['csrf_token']); ?>">
				<div id=ly_position_logoutButton_upperRight_1>
					<input type="submit" class=logoutButton_design name="logout" value="ログアウト">
				</div>
			</form>
			<h1 id=position_text1 class=fontsize_xlarge>好きなカテゴリを選んでください。</h1>
			<form action="logout.php" method="post">
				<input type="button" value="true">
				<input type="button" value="true">				
				<input type="button" value="true">				
				<input type="button" value="true">				
				<input type="button" value="true">				
			</form>
		</div>
	</body>
</html>
