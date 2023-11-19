<?php
class BaseModel{
	//-----------------------------------------------
	// プロパティ
	//-----------------------------------------------
	private $dsn = 'mysql:dbname=bukiyadb;host=localhost';
	private $id  = 'senpai';
	private $pw  = 'indocurry';
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
}