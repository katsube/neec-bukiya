<?php

/**
 * レスポンスを送信する
 *
 * @param boolean $status  成功した場合はtrue、失敗した場合はfalse
 * @param mixed   $data    レスポンスデータ(エラー時はエラーメッセージ)
 * @return void
 */
function sendResponse($status, $data=null){
	// データを準備
	$params = ['status' => $status];
	if( $status ){
		$params['data'] = $data;
	}
	else{
		$params['error'] = $data;
	}

	// JSON形式で出力
	header('Content-Type: application/json');
	echo json_encode($params);
}