<?php
/**
 * 商品一覧を返す
 *
 */

//-----------------------------------------------
// DBの接続情報
//-----------------------------------------------
$dsn = 'mysql:dbname=bukiyadb;host=localhost';
$id  = 'senpai';			// DBのユーザー名
$pw  = 'indocurry';		// DBのパスワード

//-----------------------------------------------
// DBから情報を取得
//-----------------------------------------------
$products = [ ];

try {
	// DBに接続
	$dbh = new PDO($dsn, $id, $pw);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// SQLを準備して実行
	$sth = $dbh->prepare('SELECT * FROM Products');		// 準備
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