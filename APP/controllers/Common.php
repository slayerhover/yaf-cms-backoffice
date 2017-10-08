<?php
use Illuminate\Database\Capsule\Manager as DB;

abstract class CommonController extends CoreController {
	
	public function indexAction(){
		$this->_view->assign('uniqid',	 uniqid());
    }

	public function getAction() {			
		$page   =	$this->getPost('page', '');
		$sort	=	$this->getPost('sort',  'sortorder');
		$order	=	$this->getPost('order', 'desc');
		$keywords	= $this->getPost('keywords', '');
		$query		= DB::table($this->controllerName);
		if($keywords!==''){
			$query	=	$query	->where('title','like',"%{$keywords}%");
		}
		$total		= $query->count();
		$query 		= $query->orderBy($sort,$order);
		if( !empty($page) ){
			$limit  =	$this->getPost('rows', 10);
			$offset	=	($page-1)*$limit;			
			$query	=	$query->offset($offset)
							 ->limit($limit);
		}
		$rows =	$query->get();
		return ['total'=>$total, 'rows'=>$rows];
    }
	public function addAction(){
		$dataset  	= DB::table($this->controllerName)->where('up','=',0)->get();
		$this->_view->assign('dataset', $dataset);
    }	
	public function increaseAction(){
		do{
			if( $this->method!='POST' ){
				$result	= array(
							'code'=>	'300',
							'msg'	=>	'操作失败',		
						);
				break;
			}
			$rows = $this->getPost();			
			$rows['created_at'] =	date('Y-m-d H:i:s');
			if( DB::table($this->controllerName)->insert($rows) ){
				$result	= array(
							'code'	=>	'200',
							'msg'	=>	'操作成功',								
						);
			}else{
				$result	= array(
							'code'	=>	'300',
							'msg'	=>	'数据插入失败',	
						);
			}
		}while(FALSE);	
		
		json($result);
    }	
	public	function editAction(){
		$id	= $this->getQuery('id', 0);
     	$dataset  	= DB::table($this->controllerName)->find($id);
		$this->_view->assign('dataset', $dataset);
    }
    public function updateAction(){
		do{
			if( $this->method!='POST' ){
				$result	= array(
							'code'	=>	'300',
							'msg'		=>	'操作失败',										
						);
				break;
			}				
			$rows = $this->getPost();
			$rows['updated_at'] =	date('Y-m-d H:i:s');
			if( DB::table($this->controllerName)->where('id','=',$rows['id'])->update($rows)!==FALSE ){
				$result	= array(
							'code'		=>	'200',
							'msg'		=>	'操作成功',	
						);
			}else{
				$result	= array(
							'code'		=>	'300',
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
							'code'		=>	'300',
							'msg'		=>	'操作失败',										
						);
				break;				
			}
			$id	= $this->get('id', '');
			if( empty($id) ){
				$result	= array(
							'code'		=>	'300',
							'msg'		=>	'参数为空',
						);
				break;
			}
			if(DB::table($this->controllerName)->delete($id)){
				$result		= array(
							'code'		=>	'200',
							'msg'		=>	'操作成功',
							);						
			}else{
				$result		= array(
							'code'		=>	'300',
							'msg'		=>	'删除失败',
							);
			}
		}while(FALSE);	
		
		json($result);    	
    }


}
