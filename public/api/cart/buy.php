<?php
/*
 * 商品購入API
 *
 */

//-----------------------------------------------
// ライブラリ
//-----------------------------------------------
require_once('../lib/util.php');
require_once('../lib/model.php');
require_once('../lib/session.php');

//-----------------------------------------------
// パラメーターを取得
//-----------------------------------------------
$sess_id = isset($_GET['session_id'])? $_GET['session_id']:null;
$product_id = isset($_GET['product_id'])? $_GET['product_id']:null;

// バリデーション
if( $sess_id === null || $product_id === null ){
	sendResponse(false, 'セッションIDか商品IDが指定されていません');
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
// 購入処理
//-----------------------------------------------
try{
	$model = new BaseModel();

	// トランザクション開始
	$model->dbh->beginTransaction();

	//-----------------------------------------------
	// 情報を取得
	//-----------------------------------------------
	// セッションからユーザーIDを取得
	$user_id = $session->get('user_id');

	// ユーザー情報を取得
	$user = $model->findOne(
							'SELECT amount FROM Users WHERE id=:user_id',
							[':user_id' => $user_id]
						);

	// 商品情報を取得
	$product = $model->findOne(
							'SELECT price FROM Products WHERE id=:product_id',
							[':product_id' => $product_id]
						);

	//-----------------------------------------------
	// バリデーション
	//-----------------------------------------------
	// ユーザーが（なぜか）存在しない場合はエラー
	if( $user === false ){
		sendResponse(false, 'ユーザーが存在しません');
		exit;
	}
	// 商品が存在しない場合はエラー
	if( $product === false ){
		sendResponse(false, '商品が存在しません');
		exit;
	}
	// 所持金よりも商品価格が高い場合はエラー
	if( $user['amount'] < $product['price'] ){
		sendResponse(false, '所持金が足りません');
		exit;
	}
	// すでに所持している商品の場合はエラー
	$inventory = $model->findOne(
								'SELECT count(*) FROM Inventories WHERE user_id=:user_id AND product_id=:product_id',
								[
									':user_id'    => $user_id,
									':product_id' => $product_id
								]
							);

	if( $inventory['count(*)'] > 0 ){
		sendResponse(false, 'すでに購入済みの商品です');
		exit;
	}

	//-----------------------------------------------
	// 購入処理
	//-----------------------------------------------
	// ユーザーの所持金を減らす
	$model->execute(
					'UPDATE Users SET amount=amount-:price WHERE id=:user_id',
					[
						':price'   => $product['price'],
						':user_id' => $user_id
					]
	);
	// ユーザーのインベントリーに追加
	$model->execute(
					'INSERT INTO Inventories(user_id, product_id) VALUES(:user_id, :product_id)',
					[
						':user_id'    => $user_id,
						':product_id' => $product_id
					]
	);

	// コミット
	$model->dbh->commit();
}
catch(PDOException $e){
	$model->dbh->rollBack();
	sendResponse(false, $e->getMessage());
	exit;
}

	//-----------------------------------------------
	// レスポンス
	//-----------------------------------------------
	sendResponse(true, '購入しました');