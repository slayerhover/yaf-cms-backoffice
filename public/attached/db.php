<?php
class DB{
    public            $pdo;
    protected         $res;
    protected         $config;
    function __construct($config){
        $this->Config = $config;
        $this->connect();
    }         
    public function connect(){
        $this->pdo = new PDO($this->Config['dsn'], $this->Config['name'], $this->Config['password']);
        $this->pdo->query('set names utf8;');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    function __destruct(){
        $this->pdo = null;
    }
    public function close(){
        $this->pdo = null;
    }
    public function ping(){		
		try{
			$this->pdo->getAttribute(PDO::ATTR_SERVER_INFO);
		} catch (PDOException $e) {
			if(stripos($e->getMessage(), 'MySQL server has gone away')!==FALSE){
				$this->connect();
			}
		}
	}
    public function query($sql){
        $res = $this->pdo->query($sql);
        if($res){
            $this->res = $res;
        }
    }
    public function exec($sql){
        $res = $this->pdo->exec($sql);
        if($res){
            $this->res = $res;
        }
    }
    public function getAll($sql){
        $this->query($sql);
        return $this->res->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getRow($sql){
        $this->query($sql);
        return $this->res->fetch(PDO::FETCH_ASSOC);
    }
    public function getValue($sql){
        $this->query($sql);
        return $this->res->fetchColumn(PDO::FETCH_ASSOC);
    }
    public function getOne($sql){
        $this->query($sql);
        return $this->res->fetchColumn(PDO::FETCH_ASSOC);
    }
    public function insertId(){
        return $this->pdo->lastInsertId();
    }
    public function add($table, $dataset, $debug=0){
        if( empty($table) ){
            throw new Exception("表名不能为空.");
        }
        if( !is_array($dataset) || count($dataset)<=0) {
            throw new Exception('没有要插入的数据');
        }
        $value = '';
	foreach($dataset as $key=>$val)
            $value .= "`{$key}`='{$val}',";
        $value = substr( $value,0,-1);
        if($debug === 0){
            $this->query("insert into `{$table}` set {$value}");
            if(!$this->res){
                return FALSE;
            }else{
                return $this->insertId();
            }
        }else{
            echo "insert into `{$table}` set {$value}";
            if($debug === 2){ exit; }
        }
    }
     
    public function update($table, $dataset, $conditions="", $debug=0){
        if( empty($table) ){
            throw new Exception("表名不能为空.");
        }
        if( !is_array($dataset) || count($dataset)<=0) {
            throw new Exception('没有要更新的数据');
            return false;
        }
        if( empty($conditions) ){
            throw new Exception("删除条件为空哦.");
        }
        $conditions = " where " . $conditions;
        $value    = '';
        while( list($key,$val) = each($dataset))
            $value .= "`$key`='$val',";
        $value  = substr( $value,0,-1);
        
        //数据库操作
        if($debug === 0){
        $this->exec("update `{$table}` set {$value} {$conditions}");
            return $this->res;
        }else{
            echo "update `{$table}` set {$value} {$conditions}";
            if($debug === 2){ exit; }
        }
    }
     
    public function delete($table, $conditions="", $debug=0){
        if( empty($table) ){
            throw new Exception("表名不能为空.");
        }
        if( empty($conditions) ){
            throw new Exception("删除条件为空哦.");
        }
        $conditions = " where " . $conditions;        
        //数据库操作
        if($debug === 0){
            $this->exec("delete from {$table} {$conditions}");
            return $this->res;
        }else{
            echo "delete from {$table} {$conditions}";
            if($debug === 2){ exit; }
        }
    }
}
