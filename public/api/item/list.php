<?php
/**
 * 商品一覧を返す（モック）
 *
 */

header('Content-Type: application/json');
echo json_encode([
	"items" => [
		[
			'id'    => 1,
			'name'  => '木製ハンマー',
			'image' => '1.png',
			'description' => '叩かれるとシャレにならないハンマー',
			'price' => 100
		],
		[
			'id'    => 2,
			'name'  => '鉄の剣',
			'image' => '2.png',
			'description' => '名工にとて鍛えられたかのようなオーラを放つ剣',
			'price' => 200
		],
		[
			'id'    => 3,
			'name'  => '火縄銃',
			'image' => '3.png',
			'description' => '通称「種子島」、かつて歴史を変えた銃',
			'price' => 300
		],
		[
			'id'    => 4,
			'name'  => '鉄の籠手',
			'image' => '4.png',
			'description' => '殴られると痛気持ちいいと評判',
			'price' => 400
		],
		[
			'id'    => 5,
			'name'  => '魔導書',
			'image' => '5.png',
			'description' => '敵を毛むくじゃらにし身動きを封じる魔導書',
			'price' => 500
		]
	]
]);
