<?php
use Illuminate\Database\Capsule\Manager as DB;
abstract class CommonController extends BCoreController {
	protected $table;
    protected $primaryKey;

	public function indexAction(){
		$this->_view->assign('uniqid',	 uniqid());
    }
	public function getAction($flag=FALSE){
		$page   =	$this->get('page', '');
		$sort	=	$this->get('sort',  'sortorder');
		$order	=	$this->get('order', 'desc');
		$keywords	= $this->get('keywords', '');
		$fields     = $this->get('fields', 'title');
		$query		= DB::table($this->table);
		if($keywords!==''){
		    $fields = explode(',', $fields);
			$query	=	$query->where(function($query)use($keywords, $fields){
                $query->where($fields[0],'like',"%{$keywords}%");
                $fieldsSize = sizeof($fields);
                if($fieldsSize>1){
                    for($i=1; $i<$fieldsSize; $i++){
                        $query->orWhere($fields[$i],'like',"%{$keywords}%");
                    }
                }
            });
		}
		$total		= $query->count();
		$query 		= $query->orderBy($sort,$order);
		if( !empty($page) ){
			$limit  =	$this->get('rows', 10);
			$offset	=	($page-1)*$limit;			
			$query	=	$query->offset($offset)
							 ->limit($limit);
		}
		$rows =	$query->get();
		if($flag){
			return ['total'=>$total, 'rows'=>$rows];
		}else{
			json(['total'=>$total, 'rows'=>$rows]);
		}
    }
	public function addAction(){		
		$this->_view->assign('uniqid',	 uniqid());
    }	
	public function increaseAction(){
		do{
			if( $this->method!='POST' ){
				$result	= array(
							'ret'   =>	1,
							'msg'	=>	'操作失败',		
						);
				break;
			}
			$rows = $this->formData;			
			$rows['created_at'] =	date('Y-m-d H:i:s');
			if( DB::table($this->table)->insert($rows) ){
				$result	= array(
							'ret'	=>	0,
							'msg'	=>	'操作成功',								
						);
			}else{
				$result	= array(
							'ret'	=>	3,
							'msg'	=>	'数据插入失败',	
						);
			}
		}while(FALSE);	
		
		json($result);
    }	
	public	function editAction($flag=FALSE){
		$id	= $this->get('id', 0);
     	$dataset  	= DB::table($this->table)->where($this->primaryKey,'=',$id)->first();
		$this->_view->assign('dataset', $dataset);
		$this->_view->assign('uniqid',	 uniqid());
		if($flag) return $dataset;
    }
    public function updateAction(){
		do{
			if( $this->method!='POST' ){
				$result	= array(
							'ret'	    =>	1,
							'msg'		=>	'操作失败',										
						);
				break;
			}            
			$rows = $this->formData;
			if(!isset($rows['id'])&&isset($rows['dataset'])&&is_array($rows['dataset'])){
				$rows = $rows['dataset'];
			}
			$inputs	= array(
                ['name'=>'id','value'=>$rows['id'],'role'=>"required|exists:{$this->table}.{$this->primaryKey}",'fun'=>'isInt','msg'=>'主键索引'],
            );
            $result	= Validate::check($inputs);
            if(	!empty($result) ){ret(2, $result);}
			$rows['updated_at'] =	date('Y-m-d H:i:s');
			if( DB::table($this->table)->where($this->primaryKey,'=',$rows['id'])->update($rows)!==FALSE ){
				$result	= array(
							'ret'		=>	0,
							'msg'		=>	'操作成功',	
						);
			}else{
				$result	= array(
							'ret'		=>	3,
							'msg'		=>	'更新失败',	
						);
			}
		}while(FALSE);
		
		json($result);
    }		
    public function deleteAction(){	
		do{
			if($this->method!='POST'){
				$result	= array(
							'ret'		=>	1,
							'msg'		=>	'操作失败',										
						);
				break;				
			}
            $id = $this->get('id', 0);
            $inputs	= array(
                ['name'=>'id','value'=>$id,'role'=>"required|exists:{$this->table}.{$this->primaryKey}",'fun'=>'isInt','msg'=>'主键索引'],
            );
            $result	= Validate::check($inputs);
            if(	!empty($result) ){ret(2, $result);}

			if(DB::table($this->table)->where($this->primaryKey,'=',$id)->delete()){
				$result		= array(
							'ret'		=>	0,
							'msg'		=>	'操作成功',
							);						
			}else{
				$result		= array(
							'ret'		=>	3,
							'msg'		=>	'删除失败',
							);
			}
		}while(FALSE);	
		
		json($result);    	
    }
}