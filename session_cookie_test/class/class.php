<?php

	//ページ移動用クラス
	class PageMove{
		
		//プロパティ
		
		
		//コンストラクタ
			//function __construct(){}
		
		//メソッド		
			//----------- 戻るボタン表示 ------------------------------------------------------------------------
			//クラス化すると3行⇒1行に減らせるため作成した。
			function pageBackButton(){
		
				print '<form>';
				print '    <input type = "button" onclick = "history.back()" value = "戻る">';
				print '</form>';

			}

			//----------- ログイン画面へのリダイレクト ------------------------------------------------------------------------
			function redirect_to_index() {
				
			  header("HTTP/1.1 301 Moved Permanently");
			  header("Location: index.php");
			  
			}
			
			//----------- ログインチェック画面へのリダイレクト ------------------------------------------------------------------------
			function redirect_to_login_check() {
				
			  header("HTTP/1.1 301 Moved Permanently");
			  header("Location: login_check.php");
			  
			}
			
			//----------- ログイン画面へのリダイレクト ------------------------------------------------------------------------
			function redirect_to_login() {
				
			  header("HTTP/1.1 301 Moved Permanently");
			  header("Location: login.php");
			  
			}
			
			//----------- 登録✓画面へのリダイレクト ------------------------------------------------------------------------
			function redirect_to_checkRegister() {
				
			  header("HTTP/1.1 301 Moved Permanently");
			  header("Location: register_check.php");
			  
			}			

			//----------- 登録画面へのリダイレクト ------------------------------------------------------------------------
			function redirect_to_register() {
				
			  header("HTTP/1.1 301 Moved Permanently");
			  header("Location: register.php");
			  
			}

			//----------- main画面へのリダイレクト ------------------------------------------------------------------------ 
			function redirect_to_main() {
				
			  header("HTTP/1.1 301 Moved Permanently");
			  header("Location: main.php");
			  
			}		

	}
	
	//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■			
	//認証クラス
	class Authentication {
		
		//-----------  プロパティ  --------------------------------------------------------------
		protected $_pdo;
		protected $_username;
		
		//-----------  コンストラクタ  ------------------------------------------------------------
		function __construct($pdo, $username){
			
			$this->_pdo = $pdo;
			$this->_username = $username;
	
		}
			
		//-----------  メソッド  ----------------------------------------------------------------
		//--------------  ユーザー名からユーザー情報を取得する  -----------------------------------------
		function get_userInfo_in_record() {
			$sql = "SELECT * FROM userdata WHERE username = ?;";

			$stmt = $this->_pdo->prepare($sql);
			$stmt->bindValue(1, $this->_username, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	
	//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
		//ログインクラス
		class Login extends Authentication {
			
			//-----------  プロパティ  ------------------------------------------------------------------


			//-----------  コンストラクタ  ----------------------------------------------------------------
			function __construct($pdo, $username){
					
				parent::__construct($pdo, $username);
			}
			
			//-----------  メソッド  ---------------------------------------------------------------------
			//-----------    自動ログイン処理  ------------------------------------------------------------------------
			
			function check_auto_login($cookie_token) {
				
				//プレースホルダで SQL 作成
				$sql = "SELECT * FROM auto_login WHERE token = ? AND registrated_time >= ?;";
				
				//2週間前の日付を取得
				$date = new DateTime("- 14 days");
				$stmt = $this->_pdo->prepare($sql);
				$stmt->bindValue(1, $cookie_token, PDO::PARAM_STR);
				$stmt->bindValue(2, $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
				$stmt->execute();
				$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
				return $rows;
			}
		}
		
		//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■			
		
		//登録クラス
		class Register extends Authentication{

			//-----------  プロパティ  -------------------------------------------------------------
			private $_password1;
			private $_password2;			
				
			//-----------  コンストラクタ  -------------------------------------------------------------
			function __construct($pdo, $username, $password1, $password2){
				
				parent::__construct($pdo, $username);				
				$this->_password1 = $password1;
				$this->_password2 = $password2;				
			}
			
			//-----------  メソッド  -------------------------------------------------------------
			//-----------    新規登録処理  ------------------------------------------------------------------------
			
			function signUp() {

				//※phpMyAdminで「username」をユニークキーに設定しておく。設定しないと既に登録されているユーザー名も登録されてしまう。
				$stmt = $this->_pdo->prepare("insert into userdata(username, password) value(?, ?)");
				$this->_password1 = password_hash($this->_password1, PASSWORD_DEFAULT);
				$stmt->execute([$this->_username, $this->_password1]);

			}
		}
?>