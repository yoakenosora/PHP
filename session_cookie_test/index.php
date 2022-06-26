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
	session_start();

	//--------------  セキュリティ準備 -----------------------------------------
	include('./class/security_class.php');
	$security = new Security();
	$token = new Token();	
	//クリックジャッキング対策
	$security->guardClickJacking('deny');
	
	//-------------  自作クラス使用準備 -----------------------------------------
	include('./class/class.php');
	$pageMove = new PageMove();

	//--------------  文字コード指定  -----------------------------------
	header("Content-type: text/html; charset=utf-8");
	
	//--------------  セッション関連  -----------------------------------------
	session_regenerate_id(true);
	$_SESSION['csrf_token'] = $token->get_token();
		
	///////////////// デバッグ用 ///////////////////////////////////////////////////////////////////
	if (isset($_SESSION['error_status'])) {?>
		<div id=ly_position_debuglValue1>error_status: <?php echo $_SESSION['error_status'];?><br></div>
	<?php }

	if (isset($_SESSION['failed_count'])) {?>
		<div id=ly_position_debuglValue2>failed_count: <?php echo $_SESSION['failed_count'];?></div>
	<?php }  ////////////////////////////////////////////////////////////////////////////////////

	//-------------  自動ログイン用トークンがセットされていたらlogin_check.phpへリダイレクト  ---------------------------
	if (isset($_COOKIE['cookie_token'])): ?>
		<form action = "autologin_check.php" method = "post" name = "postForm">
			<input type="hidden" name="csrf_token" value="<?php echo $security->h($_SESSION['csrf_token']); ?>">
		</form>
			
		<?php exit();
	endif;
	
	//--------------  アカウントロックチェック --------------------------------------------------------------
	if (isset($_SESSION['locked_time'])) {
		$lock_time_diff = strtotime('now') - strtotime($_SESSION['locked_time']);
		
		// アカウントロック中
		if ($lock_time_diff < LOGIN_LOCK_PERIOD) {
			// リダイレクト
			$_SESSION['error_status'] = ErrorStatus::AccountLocked->value; //enum
			
		} else {
			// アカウントロック期間終了だったらロック解除
			$_SESSION['error_status'] = ErrorStatus::Fine->value; //enum
			$_SESSION['failed_count'] = 0;
			$_SESSION['locked_time'] = NULL;
			$_SESSION['locked_status'] = 'unlocked';
		}
	}

	//--------------  最初にこのページを開いたときの初期化処理 --------------------------------------------------------------
 	if (!isset($_SESSION['failed_count'])) {
		if (isset($_SESSION['error_status'])) {
			if (((($_SESSION['error_status'] != ErrorStatus::RegisterTypingError->value) && 
				   $_SESSION['error_status'] != ErrorStatus::ExistUsername->value) && 
				   $_SESSION['error_status'] != ErrorStatus::FailedPassword->value) && 
				   $_SESSION['error_status'] != ErrorStatus::BadPassword->value){
				
					$_SESSION['error_status'] = ErrorStatus::Fine->value; //enum
					$_SESSION['locked_status'] = 'unlocked';
			}
		}else{
			$_SESSION['error_status'] = ErrorStatus::Fine->value; //enum
			$_SESSION['locked_status'] = 'unlocked';
		}
	}

	//--------------  エラーメッセージの設定 --------------------------------------------------------------

	if (isset($_SESSION['error_status'])) {

		if ($_SESSION['error_status'] == ErrorStatus::LoginTypingError->value) { //enum
			$message_for_login = 'メールアドレスまたはパスワードが間違っています。';
		}elseif ($_SESSION['error_status'] == ErrorStatus::BadRequest->value) {
			$message_for_login = '不正なリクエストです。再度ページを読み込んでください。';
		}elseif ($_SESSION['error_status'] == ErrorStatus::AccountLocked->value) {
			$message_for_login = 'アカウントがロックされました。<br>時間を空けてから再度お試しください。';
		}else {
			$message_for_login = '';
		}

		if ($_SESSION['error_status'] == ErrorStatus::RegisterTypingError->value) {		
			$message_for_register = 'ユーザー名、パスワード、確認用パスワードを正しく入力してください。';
		}elseif ($_SESSION['error_status'] == ErrorStatus::ExistUsername->value) {	
			$message_for_register = '既に使用されているユーザー名です。他のユーザー名を入力してください。';
		}elseif ($_SESSION['error_status'] == ErrorStatus::FailedPassword->value) {	
			$message_for_register = '※パスワードが一致しません。入力し直してください。';
		}elseif ($_SESSION['error_status'] == ErrorStatus::BadPassword->value) {	
			$message_for_register = '※パスワードは半角英数字をそれぞれ1文字以上使用してください。<br>
										また、合計8文字以上になるように設定してください。';
										
		}else {
			$message_for_register = '';
		}
		
	}else{
		$message_for_login = '';
		$message_for_register = '';
	}
?>

<!DOCTYPE html>
<html lang = "ja">
	<head>
	    <meta charset="utf-8">
		<title>Login</title>
		<link rel="stylesheet" href="CSS/style.css">
	</head>
	<body id=pagename_index class=ly_body>
		<div class=ly_contents>
			<div class=position_alert_top>
				<p class=alert-text>
					<?php echo $message_for_login; ?>
				</p>
			</div>
			
			<?php //if (isset($_SESSION['username'])): ?>
			<?php if($_SESSION['locked_status'] == 'unlocked'): ?>
			<!-- ログイン済みの場合 -->
				<?php if(isset($_SESSION['username'])): ?>
					<div id=position_hello>
						<h1 class=fontsize_large>ようこそ <?php echo $security->h($_SESSION['username']); ?>さん</h1>
						<p><a href = './main.php'>メインページへ</a></p>
						
						<form action = "logout.php" method = "post">
							<input type = "hidden" name = "csrf_token" value="<?php echo $security->h($_SESSION['csrf_token']); ?>">
							<div id=ly_position_logoutButton_upperRight_2>
								<input type = "submit" value = "ログアウト">
							</div>
						</form>
					</div>
					<?php exit(); ?>
				<?php endif; ?>

			<!-- ログイン前 -->
			
				<div id=position_loginForm1>
					<h1 class=fontsize_xlarge>ユーザー名とパスワードを入力してください。</h1>
					<form action = "login_check.php" method = "post">
						<input type="hidden" name="csrf_token" value="<?php echo $security->h($_SESSION['csrf_token']); ?>">
						<p><label for = "text" id=label_username>ユーザー名</label></p>
							<p><input type = "text" name = "username"></p>
							<p><label for = "password">パスワード</label></p>
							<p><input type = "password" name = "password"></p>
							<p class=checkbox_margin>自動ログイン:<input type="checkbox" name="auto" value="true" <?php echo isset($_SESSION['auto']) ? 'checked' : Null; ?>></p>
						<p class=button_margin><input type = "submit" value = "ログイン" class=loginButton_design>
					</form>
				</div>
				<div id=position_signUpForm1>
					<p class=alert-text>
						<?php echo $message_for_register; ?>
					</p>
				</div>
				<div id=position_signUpForm2>
					<h1 class=fontsize_large>初めての方はこちら</h1>
					<form action = "register_check.php" method = "post">
						<input type="hidden" name="csrf_token" value="<?php echo $security->h($_SESSION['csrf_token']); ?>">
						<p><label for = "text">ユーザー名</label></p>
						<p><input type = "text" name = "username"></p>
						<p><label for = "password1">パスワード</label></p>
						<p><input type = "password" name = "password1"></p>
						<p><label for = "password2">確認用パスワード</label></p>
						<p><input type = "password" name = "password2"></p>
						<p class=button_margin><button type = "submit" class=signupButton_design>新規登録</button></p>
						<p>※パスワードは半角英数字をそれぞれ1文字以上使用してください。<br>
							また、合計8文字以上になるように設定してください。</P>
					</form>
				</div>
			<?php else : ?>				
				<div id=position_loginForm2>
					<form action="index.php" method="post">
						<input type="button" value="このページを再読み込みします" onclick="window.location.reload();" >
					</form>
				</div>
			<?php endif; ?>
		</div>
	</body>
</html>