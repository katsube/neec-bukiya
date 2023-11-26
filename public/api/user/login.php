<?php
/**
 * ログインAPI
 *
 */

//-----------------------------------------------
// ライブラリ
//-----------------------------------------------
require_once('../lib/model.php');
require_once('../lib/session.php');

//-----------------------------------------------
// パラメータを取得
//-----------------------------------------------
$loginid  = isset($_POST['loginid'])?  $_POST['loginid']:null;
$password = isset($_POST['password'])? $_POST['password']:null;

//-----------------------------------------------
// SQLを準備
//-----------------------------------------------
$sql = <<<SQL
	SELECT id
	FROM   Users
	WHERE  loginid=:loginid
	AND    password=:password
	AND    status=0
SQL;

//-----------------------------------------------
// DBからデータを取得
//-----------------------------------------------
$status = false;	// 実行結果
$error  = null;		// エラーメッセージ

try{
	// DBに接続
	$model = new BaseModel();

	// SQLを準備して実行
	$sth = $model->dbh->prepare($sql);		// 準備
	$sth->bindParam(':loginid',  $loginid,  PDO::PARAM_STR);		// ログインIDを設定
	$sth->bindParam(':password', $password, PDO::PARAM_STR);		// パスワードを設定
	$sth->execute();		// 実行

	// 結果を取得（0件の場合はfalseが返る）
	$result = $sth->fetch(PDO::FETCH_ASSOC);		// 常に1件しか返らないのでfetchでOK

	// ログイン成功
	if( $result !== false ){
		$status = true;

		// セッションを作成
		$session = new Session();
		$sess_id = $session->create();
		$session->set('user_id', $result['id']);
	}
	// ログイン失敗
	else{
		$error = 'Invalid loginid or password.';
	}
}
catch (PDOException $e) {
	$status = false;
	$error  = $e->getMessage();
}

//-----------------------------------------------
// JSON形式で結果を返す
//-----------------------------------------------
// 返却用のデータを作成
$data = ['status' => $status];

// 成功時はセッションIDを返す
if( ! $error ){
	$data['session_id'] = $sess_id;
}
// 失敗時はエラーメッセージを返す
else{
	$data['error'] = $error;
}

// JSON形式で返す
header('Content-Type: application/json');
echo json_encode($data);
