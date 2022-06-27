<?php
	class Security{
	
		//プロパティ
		
		
		//コンストラクタ
			//function __construct(){}
		
		//メソッド
			//----------- 文字列無害化(クロスサイトスクリプティング対策) -----------------------------------------------------
			function h($str){
				
				$str = htmlspecialchars($str, ENT_QUOTES, 'utf-8');
				return $str;
			
			}
			
			//----------- クリックジャッキング対策 ------------------------------------------------------------------------
			function guardClickJacking($option){
				
				switch ($option){
				
					case 'deny':
				
						/* フレーム内のページ表示を全ドメインで禁止したい場合 */
						return header('X-Frame-Options: DENY');
						break;
					
					case 'SAMEORIGIN':
											
						/* フレーム内のページ表示を同一ドメイン内のみ許可したい場合 */
						return header('X-Frame-Options: SAMEORIGIN');
						break;
										
					case 'ALLOW-FROM':
										
						/* フレーム内のページ表示を指定されたドメインに限り許可したい場合 */						
						return header('X-Frame-Options: ALLOW-FROM http://example.jp');
						break;
						
					default:
						
				}
			}
			
			//----------- PDO の接続オプション取得 ------------------------------------------------------------------------
			//MySQL との接続で、
			//      ①エラー発生時は例外をスローするようにする、
			//      ②MySQLのSQLの複文を禁止する
			//      ③静的プレースホルダを使用する
			function get_pdo_options() {
				
				return array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
							 PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
							 PDO::ATTR_EMULATE_PREPARES => false);
						
			}
	}
	
	//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
	
	//トークンクラス
	class Token{
		
		//-----------  プロパティ  -----------------------------------------------------------------------------
		
		private $token_length = 16;    //16*2=32byte 32桁あるので安全

		//-----------  コンストラクタ  --------------------------------------------------------------------------
			//function __construct(){
							
			//}
		
		//-----------  メソッド  ------------------------------------------------------------------------------
		//-----------　　　 トークン作成 ------------------------------------------------------------------------
		function get_token() {
			
			$bytes = openssl_random_pseudo_bytes($this->token_length);
			return bin2hex($bytes);
		
		}
		
		//----------- 　　トークンの登録 ------------------------------------------------------------------------
		function register_token($pdo, $username, $token) {

			//プレースホルダで SQL 作成
			$sql = "INSERT INTO auto_login (username, token, registrated_time) VALUES (?,?,?);";
			// 現在日時を取得
			$date = date('Y-m-d H:i:s');
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(1, $username, PDO::PARAM_STR);
			$stmt->bindValue(2, $token, PDO::PARAM_STR);
			$stmt->bindValue(3, $date, PDO::PARAM_STR);
			$stmt->execute();
			
		}
		
		//----------- 　　トークンの削除 ------------------------------------------------------------------------
		function delete_old_token($pdo, $token) {
			
			//プレースホルダで SQL 作成
			$sql = "DELETE FROM auto_login WHERE token = ?";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(1, $token, PDO::PARAM_STR);
			$stmt->execute();
			
		}
	}
	
?>