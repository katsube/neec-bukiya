<?php
require_once('../lib/model.php');

/**
 * セッション管理クラス
 *
 * PHPのセッション機能を使わず、独自にセッションを管理するクラス。
 * セッション内容はDBに保存する。
 *   ※学習目的のクラスです。実際にはPHPのセッション機能を使ってください。
 *
 * @example
 *   $session = new Session();
 *   $sess_id = $session->create();  				// セッションを新規に作成
 *
 *   // セッションIDを取得
 *   $sess_id = $session->getSessionId();
 *
 *   // セッション内に値を保存・取得
 *   $session->set('user_id', 12345);				// 保存
 *	 $user_id = $session->get('user_id');		// 取得
 *
 *   // セッション時間を延長
 *   $session->refresh();
 *
 *   // セッションを破棄
 *   $session->destroy();
 *
 *   // CookieにセッションIDを保存する場合 (コンテンツ出力前に実行)
 *   setcookie('sess_id', $session->getSessionId(), time()+60*60*24*30, '/');
 */
class Session{
	//-----------------------------------------------
	// 定数
	//-----------------------------------------------
	const SESSION_TABLE  = 'Sessions';		// セッション情報を保存するテーブル名
	const SESSION_PREFIX = 'SESS';				// セッションIDの先頭に付ける文字列(4文字以内)
	const SESSION_EXPIRE = 60 * 60 * 2;		// セッションの有効期限（秒） 2時間

	//-----------------------------------------------
	// プロパティ
	//-----------------------------------------------
	private $sess_id = null;		// セッションID
	private $model   = null;		// BaseModelのインスタンス

	/**
	 * コンストラクタ
	 *
	 * @param string [$sess_id=null] セッションID
	 */
	function __construct($sess_id=null){
		// DBに接続
		$this->model = new BaseModel();

		// セッションIDをプロパティにセット
		if( $sess_id !== null ){
			$this->setSessionId($sess_id);
		}
	}

	/**
	 * セッションIDを返却
	 *
	 * @return string セッションID
	 */
	function getSessionId(){
		return( $this->sess_id );
	}

	/**
	 * セッションIDを設定
	 *
	 * @param string $sess_id
	 * @return void
	 */
	function setSessionId($sess_id){
		$this->sess_id = $sess_id;
	}

	/**
	 * セッションを新規に作成
	 *
	 * @param integer [$user_id=null] ユーザーID
	 * @return string セッションID
	 * @access private
	 */
	function create($user_id=null){
		$count = 0;

		do{
			// 一定回数以上ループしたら例外を投げる ※無限ループ防止
			if( ++$count > 10 ){
				throw new Exception('Failed to create session id.');
			}

			// セッションID用の文字列を作成
			$sess_id = $this->_createSessionId();
		}
		while( $this->exists($sess_id) );		// DB内に既に同じIDが存在する場合は再作成

		// セッションIDを保存
		$this->_createRecord($user_id, $sess_id);
		$this->sess_id = $sess_id;

		return($sess_id);
	}

	/**
	 * セッションIDが存在するかチェック
	 *
	 * @param string [$sess_id=null] セッションID
	 * @return boolean
	 */
	function exists($sess_id=null){
		// セッションIDが指定されていない場合は、プロパティから取得
		if( $sess_id === null ){
			$sess_id = $this->sess_id;
		}

		// SQLを準備
		$table = self::SESSION_TABLE;		// 定数は文字列内で展開されないので変数に代入
		$sql = <<<"SQL"
			SELECT count(*) as `COUNT`
			FROM   {$table}
			WHERE  id=:sess_id
			AND    expired>=:now
		SQL;

		try{
			$sth = $this->model->dbh->prepare($sql);								// 準備
			$sth->bindValue(':sess_id', $sess_id, PDO::PARAM_STR);	// セッションIDを設定
			$sth->bindValue(':now',     time(),   PDO::PARAM_INT);	// 有効期限を設定
			$sth->execute();														// 実行
			$result = $sth->fetch(PDO::FETCH_ASSOC);		// 結果を取得

			// 1件以上あればtrue、0件ならfalseを返す
			return($result['COUNT'] > 0);
		}
		catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * セッションに値を保存
	 *
	 * @param string $key    Sessionsテーブルのカラム名
	 * @param mixed  $value  保存する値
	 * @return boolean
	 */
	function set($key, $value){
		// セッションIDの有効性をチェック
		if( ! $this->exists() ){
			return(false);
		}

		// $keyに指定できる値を制限
		$grant_keys = ['user_id', 'expired'];		// この配列内のキーのみ許可
		if( ! in_array($key, $grant_keys) ){
			throw new Exception('Invalid key.');
		}

		// SQLを準備
		$table = self::SESSION_TABLE;		// 定数は文字列内で展開されないので変数に代入
		$sql = <<<"SQL"
			UPDATE {$table}
			SET    {$key}=:data
			WHERE  id=:sess_id
		SQL;

		try{
			$sth = $this->model->dbh->prepare($sql);		// 準備
			$sth->bindValue(':sess_id', $this->sess_id, PDO::PARAM_STR);			// セッションIDを設定
			$sth->bindValue(':data',    $value,         $this->_getDataType($value));	// 保存するデータを設定
			$sth->execute();		// 実行

			return(true);
		}
		catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * セッションから値を取得
	 *
	 * @param string|array [$keys=null] Sessionsテーブルのカラム名。Nullの場合は全てのカラムを返す。
	 * @return mixed
	 */
	function get($keys=null){
		// セッションIDの有効性をチェック
		if( ! $this->exists() ){
			return(false);
		}

		// この配列内のキーのみ許可する
		$grant_keys = ['id', 'user_id', 'expired'];

		// $keysが文字列の場合は配列に変換
		if( is_string($keys) ){
			$keys = [$keys];
		}
		// $keysが指定されていない場合は全ての値を返す
		else if( $keys === null ){
			$keys = $grant_keys;
		}

		// $keysが配列でない場合、または許可されていないキーが含まれている場合はエラー
		if( ! is_array($keys) || array_diff($keys, $grant_keys) ){
			throw new Exception('Invalid keys.');
		}

		// SQLを準備
		$table = self::SESSION_TABLE;		// 定数は文字列内で展開されないので変数に代入
		$columns = implode(',', $keys);	// ['id', 'user_id'] → 'id,user_id'
		$sql = <<<"SQL"
			SELECT {$columns}
			FROM   {$table}
			WHERE  id=:sess_id
		SQL;

		try{
			$sth = $this->model->dbh->prepare($sql);		// 準備
			$sth->bindValue(':sess_id', $this->sess_id, PDO::PARAM_STR);	// セッションIDを設定
			$sth->execute();		// 実行
			$result = $sth->fetch(PDO::FETCH_ASSOC);		// 結果を取得

			// キーが1つだけ指定されている場合は、その値を返す
			if( count($keys) === 1 ){
				return($result[$keys[0]]);
			}
			return($result);
		}
		catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * セッション時間を延長
	 *
	 * @param integer [$time=null] 延長する時間（秒）。Nullの場合はSESSION_EXPIREを使用。
	 * @return boolean
	 */
	function refresh($time=null){
		// セッションIDの有効性をチェック
		if( ! $this->exists() ){
			return(false);
		}

		// 有効期限を計算
		if( $time === null || ! is_numeric($time) ){
			$time = self::SESSION_EXPIRE;
		}
		$expired = time() + $time;

		// SQLを準備
		$table = self::SESSION_TABLE;		// 定数は文字列内で展開されないので変数に代入
		$sql = <<<"SQL"
			UPDATE {$table}
			SET    expired=:expired
			WHERE  id=:sess_id
		SQL;

		try{
			$sth = $this->model->dbh->prepare($sql);		// 準備
			$sth->bindValue(':sess_id', $this->sess_id, PDO::PARAM_STR);	// セッションIDを設定
			$sth->bindValue(':expired', $expired, PDO::PARAM_INT);	// 有効期限を設定
			$sth->execute();		// 実行

			return(true);
		}
		catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * セッションを破棄
	 *
	 * DBの該当するレコードを物理削除します。
	 *
	 * @return boolean
	 */
	function destroy(){
		// セッションIDの有効性をチェック
		if( ! $this->exists() ){
			return(false);
		}

		// SQLを準備
		$table = self::SESSION_TABLE;		// 定数は文字列内で展開されないので変数に代入
		$sql = <<<"SQL"
			DELETE FROM {$table}
			WHERE  id=:sess_id
		SQL;

		try{
			$sth = $this->model->dbh->prepare($sql);		// 準備
			$sth->bindValue(':sess_id', $this->sess_id, PDO::PARAM_STR);	// セッションIDを設定
			$sth->execute();		// 実行

			// セッションIDをクリア
			$this->sess_id = null;

			return(true);
		}
		catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
	}


	//-----------------------------------------------------------------
	// private methods
	//-----------------------------------------------------------------
	/**
	 * セッションIDを作成
	 *
	 * 乱数と現在時刻、サーバのIPアドレスを組み合わせて作成する。
	 * 戻り値はSHA1でハッシュ化した文字列(40byte)＋接頭語(4byte)。
	 *
	 * @param string $prefix セッションIDの先頭に付ける文字列
	 * @return string セッションID
	 * @access private
	 * @see microtime関数 https://www.php.net/manual/ja/function.microtime
	 * @see sha1関数 https://www.php.net/manual/ja/function.sha1
	 */
	private function _createSessionId($prefix=null){
		// 必要な値を準備
		$rand = rand(0, 1000000);					// 乱数を作成（例 12345）
		$now  = microtime(true);					// 現在時刻をマイクロ秒で取得（例 1700663241.2977）
		$ip   = isset($_SERVER['SERVER_ADDR'])? $_SERVER['SERVER_ADDR']:'127.0.0.1';	// サーバのIPアドレスを取得（例 192.168.1.1）

		// ハッシュ化
		$seed = implode(':', [$rand, $now, $ip]);	// 例 12345:1700663241.2977:192.168.1.1
		$hash = sha1($seed);											// 例 385a0c2e9410425f71e0bd5cde24216a7b0f9d56%

		// セッションIDを作成
		if( $prefix === null ){
			$prefix = self::SESSION_PREFIX;
		}
		$sess_id = $prefix . $hash;		// 例 SESS385a0c2e9410425f71e0bd5cde24216a7b0f9d56%

		return($sess_id);
	}


	/**
	 * セッション用レコードを新規作成
	 *
	 * @param integer $user_id ユーザーID
	 * @param string $sess_id セッションID
	 * @param integer [$expired=null] 有効期限（秒）。Nullの場合はSESSION_EXPIREを使用。
	 * @return boolean
	 * @access private
	 */
	function _createRecord($user_id, $sess_id, $expired=null){
		// SQLを準備
		$table = self::SESSION_TABLE;		// 定数は文字列内で展開されないので変数に代入
		$sql = <<<"SQL"
			INSERT INTO {$table}(id, user_id, expired)
			VALUES(:sess_id, :user_id, :expired)
		SQL;

		// 有効期限を計算
		if( $expired === null || ! is_numeric($expired) ){
			$expired = time() + self::SESSION_EXPIRE;
		}

		try{
			$sth = $this->model->dbh->prepare($sql);								// 準備
			$sth->bindValue(':sess_id', $sess_id, PDO::PARAM_STR);	// セッションIDを設定
			$sth->bindValue(':user_id', $user_id, PDO::PARAM_INT);	// ユーザーIDを設定
			$sth->bindValue(':expired', $expired, PDO::PARAM_INT);	// 有効期限を設定
			$sth->execute();		// 実行

			return(true);
		}
		catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
	}


	/**
	 * 指定した値のデータ型を調べる
	 *
	 * @param mixed $value
	 * @return integer
	 * @access private
	 * @see gettype関数 https://www.php.net/manual/ja/function.gettype
	 * @see PDOの定義済み定数 https://www.php.net/manual/ja/pdo.constants.php
	 */
	private function _getDataType($value){
		$datatype = null;

		// gettypeでデータ型を調べ、PDO::PARAM_XXXに変換
		switch( gettype($value) ){
			case 'integer':
				$datatype = PDO::PARAM_INT;
				break;
			case 'boolean':
				$datatype = PDO::PARAM_BOOL;
				break;
			case 'NULL':
				$datatype = PDO::PARAM_NULL;
				break;
			case 'string':
			default:
				$datatype = PDO::PARAM_STR;
				break;
		}

		return($datatype);
	}
}