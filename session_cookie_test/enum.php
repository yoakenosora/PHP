<?php
	enum ErrorStatus:string
	{
		case Fine = 'Fine';
		//ログイン関連
		case LoginTypingError = 'LoginTypingError';
		case BadRequest = 'BadRequest';
		case AccountLocked = 'AccountLocked';
		//アカウント新規登録関連
		case RegisterTypingError = 'RegisterTypingError';
		case ExistUsername = 'ExistUsername';
		case FailedPassword = 'FailedPassword';
		case BadPassword = 'BadPassword';
	}
?>