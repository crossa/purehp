<?php
class MongoSession extends FrontBase implements SessionHandlerInterface {

	private $client = null;
	private $collection = null; 
	private $database = null;
	private $id = null;
	private $cache = null;
	private $count = 0;
	//private $phpsid = null;
	public function __construct($cfg){
		$config = $this->config($cfg);
		$dsn = "mongodb://${config['host']}";
		$this->client = new MongoClient($dsn,array('connectTimeoutMS'=>3000));
		$this->database = $this->client->selectDB($config['database']);
		if($config['auth']){
			$rs = $this->database->authenticate($config['user'] && $config['password']);
			if(empty($rs['ok'])){
				die("数据库验证错误");
			}
		}
		if(!empty($config['table'])){
			$this->collection = $this->database->$config['table'];
			return;
		}
		$this->collection = $this->database->sessions;
	}

	public function start($sid=null){
		//print_r(new MongoId());
		session_set_save_handler($this);
		$sessid = $_COOKIE['secid'];
		if(empty($sid) || empty($sessid)){
			session_start();
			$sessid = session_id();
			setcookie("secid",$sessid,time()+3600,null,DOMAIN,false,false);
			$sid = new MongoId();
			$this->id = $sid->{'$id'};	
			$ep = array("_id"=>$sid,"phpsid"=>$sessid,"timestamp"=>new MongoDate(strtotime("now")));
			$this->collection->save($ep);
			return $this->id;
		}
		try{
			session_id($sessid);
			session_start();
			setcookie("secid",$sessid,time()+3600,null,DOMAIN,false,false);
			$this->collection->update(array("_id"=>new MongoId("$sid"),"phpsid"=>$sessid),
								  array('$set'=>array("timestamp"=>new MongoDate(strtotime("now")))),
								  array("upsert"=>true)
								  );
			$this->id = $sid;
		}catch(MongoCursorException $e){
			return $this->start();
		}
		
		return $this->id;

	}


	public function open($savePath=null, $sessionName=null){
        return true;
    }

    public function close(){
	     return true;
    }

	public function prepare($phpsid){
		if(empty($this->cache)){
			$result = $this->collection->findOne(
									array('_id'=>new MongoId($this->id),"phpsid"=>$phpsid),
									array("_id"=>-1,"data"=>1)
								   );
			$this->cache = $result['data'];
		}
	}


    public function read($id){
		$this->prepare($id);
		return $this->cache;
       //return (string)@file_get_contents("$this->savePath/sess_$id");
    }

    public function write($id, $data){
		$this->cache = $data;
		return true;
		/*
		$this->collection->update(array('_id'=>new MongoId($this->id)),
								  array('$set'=>array("data"=>"$data",'timestamp'=>new MongoDate(strtotime("now")))),
								  array('upsert'=>true)
								 );*/

        //return file_put_contents("$this->savePath/sess_$id", $data) === false ? false : true;
    }
	
	public function __destruct(){
		$str = $this->cache;
		$this->collection->update(array('_id'=>new MongoId($this->id)),
								  array('$set'=>array("data"=>"${str}",'timestamp'=>new MongoDate(strtotime("now"))))
								 );
	}



    public function destroy($id){
		$this->collection->remove(array('_id'=>new MongoId($this->id),"phpsid"=>"${id}"));
        return true;
    }

    public function gc($maxlifetime){
		/*
        foreach (glob("$this->savePath/sess_*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }*/
	    return true;
    }
}
?>