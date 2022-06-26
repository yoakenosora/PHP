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
	
	//--------------  自作クラス使用準備  -------------------------------------
	include('./class/class.php');
	$pageMove = new PageMove();	
	$token = new Token();
	
	//--------------  セッション関連  -----------------------------------------
	session_start();
	session_regenerate_id(true);

	//強制ブラウズはリダイレクト
	if (!isset($_SESSION['username'])) {
		if (!isset($_COOKIE['cookie_token'])){

			$_SESSION['error_status'] = ErrorStatus::BadRequest->value;
			$pageMove->redirect_to_index();
			exit();
		}
	}

	if (isset($_COOKIE['cookie_token'])) {

		header('Location: main.php');
		
	}
	
	//--------------- DB操作  --------------------------------------------
	//POSTされたusernameを検索する。
	try {
		
			$pdo = new PDO(DNS, DB_USER, DB_PASS, $security->get_pdo_options());
			$stmt = $pdo->prepare('select * from userdata where username = ?');
			
			//POSTの無害化
			$username = $security->h($_POST['username']);
			$stmt->execute([$username]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
	} catch (\Exception $e) {
		
			echo $e->getMessage() . PHP_EOL;
			
	}

	//usernameがDB内に存在しているか確認
	if (!isset($row['username'])) {

		echo 'ユーザー名又はパスワードが間違っています。';
		
		$pageMove->pageBackButton();
		return false;
		
	}
				
	//パスワード確認後sessionにユーザー名を渡す
	if (password_verify($_POST['password'], $row['password'])) {
					
		session_regenerate_id(true); //session_idを新しく生成し、置き換える
		$_SESSION['username'] = $row['username'];

		// 値を送る
		echo '<form action = "main.php" method = "post" name = "postForm">';
		echo 	'<input type="hidden" name="username" value="' . $security->h($username) . '">';
		echo 	'<input type="hidden" name="csrf_token" value="' . $security->h($_SESSION['csrf_token']) . '">';			
		echo '</form>';
		

		//eader("Location: main.php");
		
	} else {
	
		$pageMove->pageBackButton();
		return false;
		
	}