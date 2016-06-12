<?php
require_once (dirname ( __FILE__ ) . "/querybuilder.php");
class ModelMultiMySQL {
	public $conn;
	public $table;
	public $info;
	public $loader;
	public $sys;
	public $sql;
	public $__config = null;
	protected $__connected = false;
	
	/**
	 * 将MD5转换为INT值
	 * @param unknown $hash
	 */
	public function md5ToInt($hash) {
		$n = 0;
		for($i = 0; $i < 4; $i ++) {
			$str = substr ( $hash, $i * 2, 2 );
			$x = intval ( $str, 16 );
			$n = $n << 8;
			$n |= $x;
		}
		return abs ( $n );
	}
	
	
	
	public function __construct($config = null) {
		if (empty ( $config )) {
			$this->__config = $this->config ( "database/default" );
			return;
		}
		if (is_array ( $config )) {
			$this->__config = $config;
			return;
		}
		if (is_string ( $config )) {
			$this->__config = $this->config ( $config );
		}
	}
	public function __config($config = null) {
		if (! empty ( $config )) {
			$this->__config = $config;
		}
	}
	public function begin() {
		$this->conn->beginTransaction ();
	}
	public function rollback() {
		$this->conn->rollBack ();
	}
	public function end() {
		$this->conn->commit ();
	}
	public function init($config = null) {
		global $_connections;
		if (empty ( $config ) && empty ( $this->__config ))
			return;
		$config = empty ( $this->__config ) ? $config : $this->__config;
		$config = is_string ( $config ) ? $this->config ( $config ) : $config;
		
		$dsn = "mysql:host=" . $config ['host'] . ";dbname=" . $config ['database'];
		if (! empty ( $_connections [$dsn] )) {
			$this->conn = $_connections [$dsn];
		} else {
			try {
				$this->conn = new PDO ( $dsn, $config ['user'], $config ['password'] );
				$_connections [$dsn] = $this->conn;
			} catch ( PDOException $e ) {
				echo $e->getMessage ();
				die ();
			}
		}
		$this->conn->exec ( "SET NAMES 'UTF8'" );
		$this->__config = $config;
	}
	public function find($arr_key, $where = null, $arr_param = null, $order = null, $group = null, $limit = null) {
		if (empty ( $arr_key ))
			return false;
		$str_key = is_array ( $arr_key ) ? implode ( ",", $arr_key ) : $arr_key;
		$sql = " SELECT ${str_key} FROM " . $this->table . " ";
		if (! empty ( $where )) {
			$sql = $sql . " WHERE " . $where;
		}
		if (! empty ( $group )) {
			$sql .= " GROUP BY ${group} ";
		}
		
		if (! empty ( $order )) {
			$sql .= " ORDER BY ${order} ";
		}
		
		if (! empty ( $limit )) {
			$sql .= " LIMIT ${limit} ";
		}
		$statm = $this->conn->prepare ( $sql );
		$statm->setFetchMode ( PDO::FETCH_NAMED);
		if (is_array ( $arr_param ) && ! empty ( $arr_param )) {
			foreach ( $arr_param as $key => $value ) {
				$$key = $value;
				$statm->bindParam ( $key, $$key );
			}
		}
		$rs = $statm->execute ();
		$this->info = $statm->errorInfo ();
		$this->sql = $sql;
		if ($rs) {
			$arr_rs = $statm->fetchAll ();
			return $arr_rs;
		}
		return false;
	}
	public function count($where = null, $arr_param = null, $group = null) {
		$sql = " SELECT COUNT(*) AS total FROM " . $this->table . " ";
		if (! empty ( $where ))
			$sql .= " WHERE " . $where;
		if (! empty ( $group ))
			$sql .= " GROUP BY ${group} ";
		$statm = $this->conn->prepare ( $sql );
		$this->sql = $sql;
		if (is_array ( $arr_param ) && ! empty ( $arr_param )) {
			foreach ( $arr_param as $key => $value ) {
				$$key = $value;
				$statm->bindParam ( $key, $$key );
			}
		}
		
		$statm->setFetchMode ( PDO::FETCH_NAMED );
		$rs = $statm->execute ();
		if ($rs) {
			$arr_rs = $statm->fetchAll ();
			return $arr_rs [0] ['total'];
		}
		return 0;
	}
	public function save($arr_key) {
		if (empty ( $arr_key ))
			return false;
		if (! empty ( $arr_key ["id"] )) {
			$upd_key = $arr_key;
			unset ( $upd_key ["id"] );
			/*
			foreach ( $upd_key as $key => $value ) {
				$arr_cond [] = "`${key}`=:${key}";
				$arr_value [":${key}"] = $value;
			}*/
			$arr_value [":id"] = $arr_key ['id'];
			$rs = $this->update ( $upd_key, "id=:id", array(":id"=>$arr_key ["id"] ));
			return $rs;
		}
		
		foreach ( $arr_key as $key => $value ) {
			$arr_cond [] = "`${key}`=:${key}";
			$arr_value [":${key}"] = $value;
		}
		$str_set = implode ( ",", $arr_cond );
		$sql = " INSERT INTO " . $this->table . " SET ${str_set} ";
		$this->sql = $sql;
		$statm = $this->conn->prepare ( $sql );
		foreach ( $arr_value as $key => $value ) {
			$$key = $value;
			$statm->bindParam ( $key, $$key );
		}
		$rs = $statm->execute ();
		$this->info = $statm->errorInfo ();
		if ($rs) {
			return $this->conn->lastInsertId ();
		}
		return false;
	}
	public function inc($inc, $where = null, $param = null) {
		if (empty ( $inc ))
			return false;
		$sql = " UPDATE " . $this->table . " SET ";
		foreach ( $inc as $key => $value ) {
			if ($value > 0) {
				$arr_cond [] = "`${key}`= `${key}`+${value}";
			} else {
				$value = abs ( $value );
				$arr_cond [] = "`${key}`= `${key}`-${value}";
			}
		}
		
		$sql .= implode ( ",", $arr_cond );
		
		if (! empty ( $where )) {
			$sql .= " WHERE " . $where;
		}

		/* 绑定where */
		$statm = $this->conn->prepare($sql);
		if (is_array ( $param ) && ! empty ( $param )) {
			foreach ( $param as $key => $value ) {
				$$key = $value;
				$statm->bindParam ( $key, $$key );
			}
		}
		
		$rs = $statm->execute ();
		$info = $this->info = $statm->errorInfo ();
		if("00000"==$info[0])
			return true;
		return false;
		/*
		if ($rs) {
			$total = $statm->rowCount ();
			if (empty ( $total )) {
				return true;
			}
			return $total;
		}
		return false;*/
	}
	public function update($arr_key, $where = null, $param = null) {
		if (empty ( $arr_key ))
			return false;
		
		foreach ( $arr_key as $key => $value ) {
			$arr_cond [] = "`${key}`=:${key}";
			$arr_value [":${key}"] = $value;
		}
		$str_set = implode ( ",", $arr_cond );
		$sql = " UPDATE " . $this->table . " SET ${str_set} ";
		if (! empty ( $where )) {
			$sql .= " WHERE " . $where;
		}
		$statm = $this->conn->prepare ( $sql );
		/* 绑定更新值参数 */
		foreach ( $arr_value as $key => $value ) {
			$$key = $value;
			$statm->bindParam ( $key, $$key );
		}
		/* 绑定where */
		if (is_array ( $param ) && ! empty ( $param )) {
			foreach ( $param as $key => $value ) {
				$$key = $value;
				$statm->bindParam ( $key, $$key );
			}
		}
		$rs = $statm->execute ();
		$info = $this->info = $statm->errorInfo ();
		$this->sql = $sql;
		if("00000"==$info[0])
			return true;
		return false;
		
		/*
		if ($rs) {
			$total = $statm->rowCount ();
			if (empty ( $total )) {
				return true;
			}
			return $total;
		}
		return false;*/
	}
	public function __destruct() {
		$this->conn = null;
	}
	public function findOne($arr_key, $where = null, $arr_param = null, $order = null, $group = null) {
		if (empty ( $arr_key ))
			return false;
		if (is_array ( $arr_key )) {
			$str_key = implode ( ",", $arr_key );
		} else {
			$str_key = $arr_key;
		}
		$sql = " SELECT ${str_key} FROM " . $this->table . " ";
		if (! empty ( $where )) {
			$sql = $sql . " WHERE " . $where;
		}
		
		if (! empty ( $group ))
			$sql = $sql . " GROUP BY ${group} ";
		
		if (! empty ( $order ))
			$sql = $sql . " ORDER BY ${order} ";
		
		$sql .= " LIMIT 1 ";
		$statm = $this->conn->prepare ( $sql );
		$statm->setFetchMode ( PDO::FETCH_NAMED );
		if (is_array ( $arr_param ) && ! empty ( $arr_param )) {
			foreach ( $arr_param as $key => $value ) {
				$$key = $value;
				$statm->bindParam ( $key, $$key );
			}
		}
		
		$rs = $statm->execute ();
		
		$this->info = $statm->errorInfo ();
		if ($rs) {
			$arr_rs = $statm->fetchAll ();
			return $arr_rs [0];
		}
		return false;
	}
	public function exec($sql) {
		if (empty ( $sql ))
			return false;
		$rs = $this->conn->exec ( $sql );
		return ($rs === false) ? false : true;
	}

	public function query($sql) {
		if (empty ( $sql ))
			return false;
		$statm = $this->conn->prepare ( $sql );
		$statm->setFetchMode ( PDO::FETCH_NAMED );
		$statm->execute ();
		$rs = $statm->fetchAll ();
		$this->info = $statm->errorInfo ();
		return $rs;
	}
	public function delete($where = null, $arr_param = null) {
		$sql = " DELETE FROM " . $this->table . " ";
		if (! empty ( $where )) {
			$sql = $sql . " WHERE " . $where;
		}
		$statm = $this->conn->prepare ( $sql );
		$statm->setFetchMode ( PDO::FETCH_NAMED );
		if (is_array ( $arr_param ) && ! empty ( $arr_param )) {
			foreach ( $arr_param as $key => $value ) {
				$$key = $value;
				$statm->bindParam ( $key, $$key );
			}
		}
		
		$rs = $statm->execute ();
		$this->info = $statm->errorInfo ();
		if("00000"==$this->info[0])
			return true;
		return false;
	}
	public function loaddconfig($config) {
		$file = DCONFIG . "/$config.php";
		if (file_exists ( $file )) {
			require_once ($file);
			return $$config;
		}
		return false;
	}
	public function loadkey($key, $where = null, $param = null) {
		if (empty ( $key ))
			return false;
		$rs = $this->findOne ( "$key", $where, $param );
		return $rs [$key];
	}
	public function loadsingle($id) {
		$rs = $this->findOne ( "*", "id=:id", array (
				":id" => $id 
		) );
		return $rs;
	}
	public function getfields($table = null) {
		$table = empty ( $table ) ? $this->table : $table;
		$sql = "SHOW COLUMNS FROM " . $table;
		$rs = $this->query ( $sql );
		return $rs;
	}
	public function builder() {
		$builder = new QueryBuilder ( $this->conn );
		return $builder;
	}
	protected function config($config) {
		$file = CONFIG . "/$config.php";
		$tmp = explode ( "/", $config );
		$tmp = array_diff ( $tmp, array (
				null 
		) );
		if (count ( $tmp ) == 0) {
			return null;
		}
		if (file_exists ( $file )) {
			require ($file);
			return $config;
		}
		require (CONFIG . "/" . $tmp [0] . "/default.php");
		return $config;
	}
	public function insert($arr_key) {
		if (empty ( $arr_key ))
			return false;
		
		foreach ( $arr_key as $key => $value ) {
			$arr_cond [] = "`${key}`=:${key}";
			$arr_value [":${key}"] = $value;
		}
		$str_set = implode ( ",", $arr_cond );
		$sql = " INSERT INTO " . $this->table . " SET ${str_set} ";
		$statm = $this->conn->prepare ( $sql );
		foreach ( $arr_value as $key => $value ) {
			$$key = $value;
			$statm->bindParam ( $key, $$key );
		}
		$rs = $statm->execute ();
		if ($rs) {
			return $this->conn->lastInsertId ();
		}
		return false;
	}
	
	public function table($table){
		$obj = $this;
		$obj->table=$table;
		return $obj;
	}
	
	public function select($fields){
		$q = $this->builder();
		$q->select($fields);
		return $q;
	}
	
}
?>