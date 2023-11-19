<?php
/**
 * 商品一覧を返す
 *
 */

//-----------------------------------------------
// ライブラリ
//-----------------------------------------------
require_once('../lib/model.php');

//-----------------------------------------------
// DBから情報を取得
//-----------------------------------------------
$products = [ ];

try {
	// DBに接続
	$model = new BaseModel();

	// SQLを準備して実行
	$sth = $model->dbh->prepare('SELECT * FROM Products');		// 準備
	$sth->execute();		// 実行

	// DBから一括でデータを取得
	$products = $sth->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
	// エラー処理をここで行う
}

//-----------------------------------------------
// JSON形式で結果を返す
//-----------------------------------------------
header('Content-Type: application/json');
echo json_encode([
	'items' => $products
]);