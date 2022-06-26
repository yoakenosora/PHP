<?php

	//データベース名：test_login

	//↓本番環境ではコメントアウトしておく
	//ini_set('display_errors', 1);

	define('DNS', 'mysql:host=localhost;dbname=test_login');
	define('DB_USER', 'root');
	define('DB_PASS', 'test1234');
	define('LOGIN_FAILED_LIMIT', 5); // 上限回数 (ロックがかかる回数)
	define('LOGIN_LOCK_PERIOD', 8); // 秒
	