<?php
class ModelMultiMongo extends MongoCollection {
	public $database;
	protected $table;
	protected $loader;
	protected $client;
	protected $__autoload = true;
	protected $__config = null;
	protected $__connected = false;
	public function __config($config) {
		$this->__config = $config;
	}
	protected function __connect($config) {
		if ($this->__connected) {
			if ($config ['host'] == $this->__config ['host'] && $config ['database'] == $this->__config ['database']) {
				return false;
			}
		}
		global $_connections, $_authenticated;
		$dsn = "mongodb://${config['host']}";
		if (! empty ( $_connections [$dsn] )) {
			$this->client = $_connections [$dsn];
		} else {
			$this->client = new MongoClient ( $dsn, array (
					'connectTimeoutMS' => 99999999 
			) );
			$_connections [$dsn] = $this->client;
		}
		
		$dbname = $config ['database'];
		$this->database = $this->client->selectDB ( $dbname );
		if ($config ['auth'] && empty($_authenticated[$dsn])) {
			$rs = $this->database->authenticate ( $config ['user'], $config ['password'] );
			if (empty ( $rs ['ok'] )) {
				exit (json_encode(array("success"=>false,"msg"=>"数据库验证错误" )));
			}
			$_authenticated[$dsn]=true;
		}
		parent::__construct ( $this->database, $this->table );
		if (null != $this->client) {
			$this->__connected = true;
		}
	}
	public function init($config = null) {
		global $_connections;
		if(empty($config)){
			$config = $this->config("database/default");
			$this->__config = $config;
		}else if(!is_array($config)){
			$config = $this->config($config);
			$this->__config = $config;
		}
		//$config = empty ( $config ) ? $this->__config : $config;
		if (! empty ( $config )) {
			$this->__connect ( $config );
		}
	}
	public function close() {
		if ($this->client != null) {
			$this->client->close ();
		}
	}
	public function __destruct() {
		if ($this->client != null) {
			$this->client->close ();
		}
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
	
	/*
	public function save($document, $options = array()) {
		if (empty ( $document ['_id'] )) {
			$document ['creation_date'] = new \MongoDate ( strtotime ( "now" ) );
		}
		$document ['updated_date'] = new \MongoDate ( strtotime ( "now" ) );
		return parent::save ( $document, $option );
	}*/
	/*
	public function insert($document, $options = array()) {
		$document ['creation_date'] = new \MongoDate ( strtotime ( "now" ) );
		return parent::insert ( $document, $options );
	}*/
	public function update($cond, $data, $options=array()) {
		$updated_date = new \MongoDate ( strtotime ( "now" ) );
		$data ['$set'] ['updated_date'] = $updated_date;
		return parent::update ( $cond, $data, $options );
	}
	public function findAndModify($query, $update = null, $fields = null, $options = null) {
		$updated_date = new \MongoDate ( strtotime ( "now" ) );
		if (! empty ( $update )) {
			//if (! empty ( $update ['$set'] )) {
				$update ['$set'] ['updated_date'] = $updated_date;
			//} else {
			//	$update ['updated_date'] = $updated_date;
			//}
		}
		return parent::findAndModify ( $query, $update, $fields, $options );
	}
	public function upgradeField($id,$data){
		if(empty($data) || !is_array($data) || empty($id)){
			return false;
		}
		$cond['_id'] = is_string($id)?new \MongoId($id):$id;
		//$cond = array('_id'=>new \MongoId($id));
		return $this->update($cond,array('$set'=>$data));
	}
	
	public function findDataByIds($ids,$fields=null,$p=null,$limit=null){
		if(empty($ids)){
			return false;
		}
		$cond = array();
		if(is_array($ids)){
			$cond["_id"]['$in'] = $ids;
		}else if(is_string($ids)){
			$cond["_id"]=new \MongoId($ids);
		}else{
			$cond["_id"]=$ids;
		}
		$cursor = $this->find($cond);
		if(!empty($fields)){
			$cursor = $cursor->fields($fields);
		}
		
		if(!empty($p) && !empty($limit)){
			$cursor = $cursor->skip(($p-1)*$limit);
		}
		
		if(!empty($limit)){
			$cursor = $cursor->limit($limit);
		}
		return iterator_to_array($cursor);
	}
	
	public function findDataByCond($cond,$fields=null,$p=null,$limit=null){
		if(empty($ids)){
			return false;
		}
		$cursor = $this->find($cond);
		if(!empty($fields)){
			$cursor = $cursor->fields($fields);
		}
	
		if(!empty($p) && !empty($limit)){
			$cursor = $cursor->skip(($p-1)*$limit);
		}
	
		if(!empty($limit)){
			$cursor = $cursor->limit($limit);
		}
		return iterator_to_array($cursor);
	}
	
	public function findSingleDataById($id,$fields=null){
		if(empty($id))
			return;
		$cond = array();
		$cond['_id'] = is_string($id)?new \MongoId($id):$id;
		$data = empty($fields)?$this->findOne($cond):$this->findOne($cond,$fields);
		return $data;
	}
	
	
	
}
?>
