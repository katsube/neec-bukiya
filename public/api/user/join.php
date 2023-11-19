<?php
/**
 * ユーザー登録
 *
 */

//-----------------------------------------------
// ライブラリ
//-----------------------------------------------
require_once('../lib/model.php');

//-----------------------------------------------
// パラメータを取得
//-----------------------------------------------
$nickname = isset($_POST['nickname'])? $_POST['nickname']:null;
$loginid  = isset($_POST['loginid'])?  $_POST['loginid']:null;
$password = isset($_POST['password'])? $_POST['password']:null;

//-----------------------------------------------
// SQLを準備
//-----------------------------------------------
$sql = 'INSERT INTO Users (nickname, loginid, password) VALUES (:nickname, :loginid, :password)';

try {
	// DBに接続
	$model = new BaseModel();

	// SQLを準備して実行
	$sth = $model->dbh->prepare($sql);		// 準備
	$sth->bindParam(':nickname', $nickname, PDO::PARAM_STR);		// ニックネームを設定
	$sth->bindParam(':loginid',  $loginid,  PDO::PARAM_STR);		// ログインIDを設定
	$sth->bindParam(':password', $password, PDO::PARAM_STR);		// パスワードを設定
	$sth->execute();		// 実行
}
catch (PDOException $e) {
	// エラー処理をここで行う
	echo $e->getMessage();
}

//-----------------------------------------------
// JSON形式で結果を返す
//-----------------------------------------------
header('Content-Type: application/json');
echo json_encode([
	'status' => true
]);