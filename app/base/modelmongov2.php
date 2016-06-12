<?php
class ModelMongoV2 {
	protected $config;
	protected $conn;
	public function __construct($config="database/default"){
		$this->config = $this->loadConfig($config);
		$this->init();
	}
	
	public function init(){
		global $_connections;
		$config = $this->config;
		$uri = "mongodb://".$config['host']."/".$config['database'];
		if($this->config['auth']){
			$uri = "mongodb://".$config['user'].":".$config['password']."@".$config['host']."/".$config['database'];
		}
		$key = md5ToInt(md5($uri));
		if(!empty($_connections["$key"])){
			$this->conn = $_connections["$key"];
			return;
		}
		$this->conn = new MongoDB\Driver\Manager($uri);
		$_connections["$key"] = $this->conn;
	}
	
	public function find($fields=array(),$cond=array(),$sort=array(),$skip=0,$limit=null){
		$options = array();
		if(!empty($fields))
			$options['projection']=$fields;
		if(!empty($sort))
			$options['sort']=$sort;
		if(!empty($sort))
				$options['skip']=intval($skip);
		if(!empty($limit))
					$options['$limit']=intval($limit);
		$query = new MongoDB\Driver\Query($cond,$options);
		$cursor = $this->conn->executeQuery($this->config['database'].".".$this->table,$query);
		return iterator_to_array($cursor);
	}
	
	/**
	 * 保存数据
	 * @param unknown $data
	 */
	public function save($data){
		if(empty($data))
			return;
		$bulk = new MongoDB\Driver\BulkWrite($data);
		$bulk->insert($data);
		return $this->conn->executeBulkWrite($this->config['database'].".".$this->table,$data);
	}
	
	
	/**
	 * 更新数据
	 * @param unknown $data
	 */
	public function update($cond,$data,$options=array()){
		if(empty($data))
			return;
		$bulk = new MongoDB\Driver\BulkWrite($data);
		$bulk->update($cond,$data,$options);
		return $this->conn->executeBulkWrite($this->config['database'].".".$this->table,$data);
	}
	
	/**
	 * 移除数据
	 * @param unknown $cond
	 * @param number $Limit
	 */
	public function remove($cond,$Limit=0){
		if(empty($data))
			return false;
		$bulk = new MongoDB\Driver\BulkWrite();
		$bulk->delete($cond,$limit);
		return $this->conn->executeBulkWrite($this->config['database'].".".$this->table,$data);
	}
	
	/**
	 * 
	 * @param unknown $config
	 * @return NULL|unknown
	 */
	public function count(){
		
	}
	

	
	protected function loadConfig($config) {
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
	
	public function table($table=null){
		if(empty($table))
			return $this;
		$this->table = $table;
		return $this;
	}
	
	
}
?>