<?php
class BaseModel{
	//-----------------------------------------------
	// プロパティ
	//-----------------------------------------------
	private $dsn = 'mysql:dbname=bukiyadb;host=localhost';
	private $id  = 'root';
	private $pw  = '';
	//private $dsn = 'mysql:dbname=G999G9999db;host=localhost';
	//private $id  = 'G999G9999';
	//private $pw  = 'aqwsedrftgyhuji';

	// DBハンドル
	public $dbh;

	/**
	 * コンストラクタ
	 *
	 * @param string $dsn  DBの接続情報
	 * @param string $id   DBのユーザー名
	 * @param string $pw   DBのパスワード
	 */
	function __construct($dsn=null, $id=null, $pw=null){
		// 引数があればプロパティに設定
		if($dsn !== null) $this->dsn = $dsn;
		if($id  !== null) $this->id  = $id;
		if($pw  !== null) $this->pw  = $pw;

		// DBに接続
		$this->dbh = new PDO($this->dsn, $this->id, $this->pw);
		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);		// エラーが発生したら例外を投げる
	}


	/**
	 * SELECT文を実行する
	 *
	 * @param string $sql
	 * @param array $bind
	 * @param int [$offset=0]  開始位置
	 * @param int [$limit=10]  取得件数
	 * @return array
	 * @example
	 *   $sql  = 'SELECT * FROM users WHERE user_id=:user_id';
	 *   $bind = [':user_id' => 1];
	 *
	 *   // 実行（2次元配列で返ってくる）
	 *   $result = $model->find($sql, $bind);
	 */
	function find($sql, $bind=[], $offset=0, $limit=10){
		// SQLを実行する準備
		$sql .= sprintf(' LIMIT %d, %d', $offset, $limit);
		$sth = $this->dbh->prepare($sql);

		// バインドする値があればバインドする
		foreach($bind as $key => $val){
			$datatype = $this->_getDataType($val);
			$sth->bindValue($key, $val, $datatype);
		}
		$sth->execute();		// SQLを実行する

		// 結果を取得する
		return( $sth->fetchAll(PDO::FETCH_ASSOC) );
	}

	/**
	 * 先頭1件だけ取得する
	 *
	 * @param string $sql
	 * @param array $bind
	 * @return array
	 * @example
	 *   $sql  = 'SELECT * FROM users WHERE user_id=:user_id';
	 *   $bind = [':user_id' => 1];
	 *
	 *   // 実行（常に1次元配列で返ってくる）
	 *   $result = $model->findOne($sql, $bind);
	 */
	function findOne($sql, $bind=[ ]){
		// SQLを実行する準備
		$sql .= ' LIMIT 1';
		$sth = $this->dbh->prepare($sql);

		// バインドする値があればバインドする
		foreach($bind as $key => $val){
			$datatype = $this->_getDataType($val);
			$sth->bindValue($key, $val, $datatype);
		}
		$sth->execute();		// SQLを実行する

		// 結果を返却する
		return( $sth->fetch(PDO::FETCH_ASSOC) );
	}

	/**
	 * SQLを実行する汎用メソッド
	 *
	 * SQL実行後にfetchなどはしないため、INSERTやUPDATEなどに使う
	 *
	 * @param string $sql
	 * @param array $bind
	 * @return mixed
	 */
	function execute($sql, $bind=null){
		// SQLを実行する準備
		$sth = $this->dbh->prepare($sql);

		// バインドする値があればバインドする
		if( $bind !== null ){
			foreach($bind as $key => $val){
				$datatype = $this->_getDataType($val);
				$sth->bindValue($key, $val, $datatype);
			}
		}
		$result = $sth->execute();		// SQLを実行する

		return( $result );
	}


	//-----------------------------------------------
	// プライベートメソッド
	//-----------------------------------------------
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