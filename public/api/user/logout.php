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
$sess_id = isset($_POST['session_id'])? $_POST['session_id']:null;

//-----------------------------------------------
// DBからレコードを削除してログアウト
//-----------------------------------------------
$status = false;	// 実行結果
$error  = null;		// エラーメッセージ

try{
	$session = new Session();
	$session->destroy($sess_id);
	$status = true;
}
catch(Exception $e){
	$error = $e->getMessage();
	$status = false;
}

//-----------------------------------------------
// JSON形式で結果を返す
//-----------------------------------------------
// 返却用のデータを作成
$data = ['status' => $status];
if( $status === false ){
	$data['error'] = $error;
}

// JSON形式で返す
header('Content-Type: application/json');
echo json_encode($data);

