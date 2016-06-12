<?php
class QueryBuilder {
	protected $conn;
	protected $select;
	protected $update;
	protected $from;
	protected $cond;
	protected $cond_not = array ();
	protected $cond_and = array ();
	protected $cond_or = array ();
	protected $cond_in = array ();
	protected $cond_not_in = array ();
	protected $groupby;
	protected $joind = false;
	protected $orderby = array ();
	protected $limit;
	protected $param = array (); // 参数，实现绑定
	public function __construct($conn = null) {
		$this->conn = $conn;
	}
	public function condition($keys) {
		$this->cond = $keys;
		return $this;
	}
	public function where_not($keys) {
		$this->cond_not = array_merge ( $this->cond_not, $keys );
		return $this;
	}
	public function where_in($keys) {
		$this->cond_in = array_merge ( $this->cond_in, $keys );
		return $this;
	}
	public function where_not_in($keys) {
		$this->cond_not_in = array_merge ( $this->cond_not_in, $keys );
		return $this;
	}
	public function select($keys) {
		$this->select = $keys;
		return $this;
	}
	public function from($keys) {
		$this->from = $keys;
		return $this;
	}
	public function leftjoin($keys) {
		$this->from .= " LEFT JOIN  ${keys} ";
		return $this;
	}
	public function rightjoin($keys) {
		$this->from .= " RIGHT JOIN  ${keys} ";
		return $this;
	}
	public function innerjoin($keys) {
		$this->from .= " INNER JOIN  ${keys} ";
		return $this;
	}
	public function on($keys) {
		$str = implode ( "=", $keys );
		$this->from .= " ON $str ";
		return $this;
	}
	public function where($keys) {
		$this->cond_and = array_merge ( $this->cond_and, $keys );
		return $this;
	}
	public function where_or($keys) {
		$this->cond_or = array_merge ( $this->cond_or, $keys );
		return $this;
	}
	public function groupby($keys) {
		$this->groupby = $keys;
		return $this;
	}
	public function orderby($keys) {
		$this->orderby = array_merge ( $this->orderby, $keys );
		return $this;
	}
	public function limit($val1 = null, $val2 = null) {
		if (is_numeric ( $val1 ) && is_numeric ( $val2 )) {
			$this->limit = "LIMIT ${val1},${val2}";
		} else {
			$this->limit = "LIMIT ${val1}";
		}
		return $this;
	}
	public function sql() {
		$select = "";
		$where = "";
		$not = "";
		$and = "";
		$or = "";
		$in = "";
		$not_in = "";
		$cond = "";
		$filter = array ();
		$groupby = "";
		$orderby = "";
		if (is_array ( $this->select )) {
			$select .= " SELECT " . implode ( ",", $this->select );
		} else if (! empty ( $this->select )) {
			$select .= " SELECT " . $this->select;
		} else {
			$select .= " SELECT *";
		}
		
		/**
		 * 条件叠加*
		 */
		if (! empty ( $this->cond_and )) {
			if (is_array ( $this->cond_and )) {
				$arr_and = array ();
				foreach ( $this->cond_and as $key => $value ) {
					// $arr_and[] = " ${key}='${value}' ";
					$arr_and [] = "${key}=:${key}";
					$this->param [":$key"] = $value;
				}
				$and = implode ( " AND ", $arr_and );
			} else {
				$and = $this->cond_and;
			}
			
			if (! empty ( $and )) {
				$filter [] = $and;
			}
		}
		
		if (is_array ( $this->cond_or )) {
			if (is_array ( $this->cond_or )) {
				$arr_or = array ();
				foreach ( $this->cond_or as $key => $value ) {
					// $arr_or[] = " ${key}='${value}' ";
					$arr_or [] = "${key}=:${key}";
					$this->param [":$key"] = $value;
				}
				$or = implode ( " OR ", $arr_or );
			} else {
				$or = $this->cond_or;
			}
			
			if (! empty ( $or )) {
				$filter [] = $or;
			}
		}
		
		// in 条件
		if (! empty ( $this->cond_in )) {
			$arr_in = array ();
			foreach ( $this->cond_in as $key => $val ) {
				$arr_in [] = "${key} IN('" . implode ( "','", $val ) . "')";
			}
			
			if (! empty ( $arr_in )) {
				$in = implode ( " AND ", $arr_in );
			}
			if (! empty ( $arr_in )) {
				$filter [] = $in;
			}
		}
		
		// not in 条件
		if (! empty ( $this->cond_not_in )) {
			$arr_not_in = array ();
			foreach ( $this->cond_not_in as $key => $val ) {
				$arr_not_in [] = "$key NOT IN('" . implode ( "','", $val ) . "')";
			}
			if (! empty ( $arr_not_in )) {
				$not_in = implode ( " AND ", $arr_not_in );
			}
			if (! empty ( $arr_in )) {
				$filter [] = $not_in;
			}
		}
		
		if (! empty ( $this->cond_not ) && is_array ( $this->cond_not )) {
			if (is_array ( $this->cond_and )) {
				$arr_not = array ();
				foreach ( $this->cond_not as $key => $value ) {
					// $arr_not[] = " ${key}!='${value}' ";
					$arr_not [] = "${key}!=:${key}";
					$this->param [":${key}"] = $value;
				}
				$not = implode ( " AND ", $arr_not );
			} else {
				$not = $this->cond_not;
			}
			
			if (! empty ( $not )) {
				$filter [] = $not;
			}
		}
		
		if (! empty ( $this->cond )) {
			$filter [] = $this->cond;
		}
		
		if (! empty ( $filter )) {
			$where .= implode ( " AND ", $filter );
		}
		
		/*
		 * if(!empty($and) && !empty($or)){
		 * $where.=$and." AND ".$or;
		 * }else{
		 * $where.=(!empty($and))?$and:"";
		 * $where.=(!empty($or))?$or:"";
		 * }
		 */
		
		/*
		 * groupby
		 */
		if (! empty ( $this->groupby )) {
			$groupby .= $this->groupby;
		}
		
		/*
		 * orderby
		 */
		if (! empty ( $this->orderby ) && is_array ( $this->orderby )) {
			$arr = array ();
			foreach ( $this->orderby as $key => $value ) {
				$arr [] = "${key} ${value}";
			}
			if (! empty ( $arr )) {
				$orderby = implode ( ",", $arr );
			}
		}
		// combine sql
		if (! empty ( $select )) {
			$sql = "";
			$sql .= $select;
			if (! empty ( $this->from )) {
				$sql .= " FROM " . $this->from;
			}
			
			if (! empty ( $where )) {
				$sql .= " WHERE " . $where;
			}
			
			if (! empty ( $groupby )) {
				$sql .= " GROUP BY " . $groupby;
			}
			
			if (! empty ( $orderby )) {
				$sql .= " ORDER BY " . $orderby;
			}
			if (! empty ( $this->limit )) {
				$sql .= " " . $this->limit;
			}
			return array (
					"sql" => $sql,
					"param" => $this->param 
			);
		}
		return false;
	}
	/**
	 * 执行查询
	 * @return boolean|unknown
	 */
	public function query() {
		if (empty ( $this->conn ))
			return false;
		$sql = $this->sql ();
		if (empty ( $sql ))
			return false;
		$statm = $this->conn->prepare ( $sql );
		$statm->setFetchMode ( PDO::FETCH_NAMED );
		if (! empty ( $this->param )) {
			foreach ( $this->param as $key => $value ) {
				$statm->bindParam ( $key, $value );
			}
		}
		$rs = $statm->execute ();
		$info = $this->info = $statm->errorInfo ();
		if ("00000" == $info [0]) {
			return array ();
		}
		$data = $statm->fetchAll ();
		return $data;
	}
}
