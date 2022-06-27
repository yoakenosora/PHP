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
	
	//--------------  自作クラス使用準備1  -------------------------------------
	include('./class/class.php');
	$pageMove = new PageMove();	//ページ移動クラスインスタンス化
	
	//--------------  セッション関連  -----------------------------------------
	session_start();
	session_regenerate_id(true);
	
	if (isset($_POST['auto'])) {
		$_SESSION['auto'] = $_POST['auto'];
	}

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
		
	if (isset($_POST['password1'])){
		$password1 = $_POST['password1'];
	}else{
		$password1 = '';
	}
	
	if (isset($_POST['password2'])){
		$password2 = $_POST['password2'];
	}else{
		$password2 = '';
	}

	if (($username == '') or 
		($password1 == '') or 
		($password2 == '')){

		// リダイレクト
		$_SESSION['error_status'] = ErrorStatus::RegisterTypingError->value; //enum
		$pageMove->redirect_to_index();
		exit();
	}

	//--------------  確認用パスワード比較  -----------------------------------------
	if ($password1 != $password2) {

		$_SESSION['error_status'] = ErrorStatus::FailedPassword->value; //enum
		$pageMove->redirect_to_index();
		exit();
		
	}
	
	//--------------  DB使用準備 ----------------------------------------------------
	$pdo = new PDO(DNS, DB_USER, DB_PASS, $security->get_pdo_options());
	
	//--------------  自作クラス使用準備2 ----------------------------------------------
	$register = new Register($pdo, $username, $password1, $password2);	
    
	//--------------  新規登録 判定  --------------------------------------------------------------
	try {
		
		// ユーザー抽出
		$rows = $register->get_userInfo_in_record();
		
		if (count($rows) == 1){
			
			//ユーザー名が存在する
			//--------------  新規登録失敗 --------------------------------------------------------------
			
			// リダイレクト
			$_SESSION['error_status'] = ErrorStatus::ExistUsername->value; //enum
			$pageMove->redirect_to_index();
			exit();

		}else{
	
			//--------------  パスワードの正規表現  -----------------------------------------
			if (preg_match('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i', $password1)) {
				
				$username = $security->h($username);
				$password1 = $security->h($password1);

			} else {

				$_SESSION['error_status'] = ErrorStatus::BadPassword->value;
				$pageMove->redirect_to_index();
				exit();
				
			}

			//--------------- DB操作 ---------------------------------------------------------------------------
			
			//データベースへ接続、テーブルがない場合は作成
			try {
				
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				//テーブルがない場合は作成
				$pdo->exec("create table if not exists userdata(
					id int not null auto_increment primary key,
					username varchar(255),
					password varchar(255),
					created timestamp not null default current_timestamp
					)");
					
			} catch (Exception $e) {
				
				echo $e->getMessage() . PHP_EOL;
				
			}
		
			//--------------  新規登録成功 --------------------------------------------------------------
			$register->signUp();
			$_SESSION['error_status'] = ErrorStatus::Fine->value;
			$_SESSION['failed_count'] = 0;
	
		}

		//ログイン処理を行う。
		// セッションIDの振り直し
		session_regenerate_id(true); //session_idを新しく生成し、置き換える
		$_SESSION['username'] = $username;

		echo $_SESSION['csrf_token'];
		exit();

		echo '<form action = "register.php" method = "post" name = "postForm">';
		echo '	<input type="hidden" name="username" value="' . $security->h($username) . '>';
		echo '	<input type="hidden" name="csrf_token" value="' . $security->h($_SESSION['csrf_token']) . '>';
		echo '</form>';
		
	} catch (PDOException $e) {
		die($e->getMessage());
	}

?>