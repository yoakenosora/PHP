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
											
	//--------------  クリックジャッキング対策  -----------------------------------
	$security->guardClickJacking('deny');
	
	//--------------  文字コード指定  -----------------------------------
	header("Content-type: text/html; charset=utf-8");
	
	//--------------  自作クラス使用準備1  -------------------------------------
	include('./class/class.php');
	$pageMove = new PageMove();	
	
	//--------------  セッション関連  -----------------------------------------
	session_start();
	session_regenerate_id(true);

	//強制ブラウズはリダイレクト
	if (!isset($_SESSION['csrf_token'])){

		$_SESSION["error_status"] = ErrorStatus::BadRequest->value;
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
		$_SESSION["error_status"] = ErrorStatus::BadRequest->value;
		$pageMove->redirect_to_index();
		exit();
	}
	
	//-------------- $_POST 変数格納  -----------------------------------------
	if (isset($_POST['username'])){	
		$username = $_POST['username'];	
	}else{
		$username = '';
	}
?>

<!DOCTYPE html>
<html lang = "ja">
	<head>
	    <meta charset="utf-8">
		<title>Login</title>
		<link rel="stylesheet" href="CSS/style.css">
	</head>
	<body id=ly_body>
		<div class=ly_contents>
			<div id=position_hello>
				<form action="logout.php" method="post">
					<input type="hidden" name="csrf_token" value="<?php echo $security->h($_SESSION['csrf_token']); ?>">
					<div id=ly_position_logoutButton_upperRight>
						<input type="submit" class=logoutButton_design name="logout" value="ログアウト">
					</div>
				</form>				
				
				<p>
					<h1 class=fontsize_large>登録完了</h2>
				</p>
				<a href = './main.php'>メインぺージへ</a>
			</div>
		</div>
	</body>
</html>