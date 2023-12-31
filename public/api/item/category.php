<?php
/**
 * 指定カテゴリーの商品一覧を返す
 *
 */

//-----------------------------------------------
// ライブラリ
//-----------------------------------------------
require_once('../lib/model.php');

//-----------------------------------------------
// パラメータを取得
//-----------------------------------------------
$cd = isset($_GET['cd'])? $_GET['cd']:null;

//-----------------------------------------------
// DBから情報を取得
//-----------------------------------------------
$products = [ ];

//-----------------------------------------------
// SQLを準備
//-----------------------------------------------
$sql = 'SELECT * FROM Products';
if ( $cd !== null ) {
	$sql .= ' WHERE category = :cd';
}

try {
	// DBに接続
	$model = new BaseModel();

	// SQLを準備して実行
	$sth = $model->dbh->prepare($sql);		// 準備
	if( $cd !== null ){
		$sth->bindParam(':cd', $cd, PDO::PARAM_STR);	// パラメーターを設定
	}
	$sth->execute();		// 実行

	// DBから一括でデータを取得
	$products = $sth->fetchAll(PDO::FETCH_ASSOC);
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
	'items' => $products
]);