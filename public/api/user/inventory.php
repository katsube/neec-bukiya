<?php
/**
 * 所有アイテムAPI
 *
 * セッションIDを受け取り、そのユーザーが所有している
 * アイテムの一覧を返却する
 *
 * @example
 *   Request:
 *     GET /api/user/inventory.php?session_id=xxxxx
 *
 *   Response:
 *     {
 *       "status": true,
 * 	     "items": [
 * 	       {
 * 	         "id": 1,
 * 	         "name": "アイテム1",
 * 	         "description": "アイテム1の説明",
 * 	         "image": "1.png",
 *           "date": "2023-12-01 00:00:00"
 * 	       }
 *       ]
 *     }
 */

//-----------------------------------------------
// ライブラリ
//-----------------------------------------------
require_once('../lib/util.php');
require_once('../lib/model.php');
require_once('../lib/session.php');

//-----------------------------------------------
// パラメータを取得
//-----------------------------------------------
$sess_id = isset($_GET['session_id'])? $_GET['session_id']:null;

// バリデーション
if( $sess_id === null ){
	sendResponse(false, 'セッションIDが指定されていません');
	exit;
}

//-----------------------------------------------
// セッション確認
//-----------------------------------------------
try{
	// セッションが存在しない場合はエラー
	$session = new Session($sess_id);

	if( $sess_id === null || $session->exists() === false ){
		sendResponse(false, 'セッションがありません');
		exit;
	}
}
catch(PDOException $e){
	sendResponse(false, $e->getMessage());
	exit;
}

//-----------------------------------------------
// 所有アイテムを取得
//-----------------------------------------------
// SQLを準備
$sql = <<<SQL
SELECT B.id, B.name, B.description, B.image, A.regist_date as date
FROM   Inventories A join Products B
          on A.product_id = B.id
WHERE A.user_id = :user_id
SQL;

try{
	$model = new BaseModel();

	//-----------------------------------------------
	// 情報を取得
	//-----------------------------------------------
	// セッションからユーザーIDを取得
	$user_id = $session->get('user_id');

	// 所有アイテムの一覧を取得
	$items = $model->find($sql, [':user_id' => $user_id]);
}
catch(PDOException $e){
	sendResponse(false, $e->getMessage());
	exit;
}

//-----------------------------------------------
// レスポンスを返す
//-----------------------------------------------
if( $items === false ){
	sendResponse(false, '所持アイテムの取得に失敗しました');
	exit;
}

sendResponse(true, $items);
