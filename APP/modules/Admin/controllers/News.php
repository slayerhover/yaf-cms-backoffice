<?php
use Illuminate\Database\Capsule\Manager as DB;

class NewsController extends BCoreController{
		
	public function classAction(){		    	
		$this->_view->assign('uniqid',	 uniqid());
    }	
	public function classGetAction() {
		$page   =	$this->getPost('page', 1);
		$limit  =	$this->getPost('rows', 10);
		$offset	=	($page-1)*$limit;				
		$sort	=	$this->getPost('sort', 'sortorder');
		$order	=	$this->getPost('order', 'desc');
		$keywords	= $this->getPost('keywords', '');				
		$query		= DB::table('newsclass');
		if($keywords!==''){
			$query	=	$query->where('title','like',"%{$keywords}%");
		}else{
			$query	=	$query->where('up','=','0');
		}
		$total		= $query->count();
		$rows 		= $query->orderBy($sort,$order)
							->get();			
		if(!empty($rows)&&is_array($rows)){
		foreach($rows	as	$k=>$v){
				$rows[$k]['recordcount']=	DB::table('news')->where('newsclass_id','=',$v['id'])->count();	
				$rows[$k]['children']	=	DB::table('newsclass')->where('up','=',$v['id'])->orderBy($sort,$order)->get();
				if(!empty($rows[$k]['children'])&&is_array($rows[$k]['children'])){
				foreach($rows[$k]['children']	as	$k1=>$v1){
					$rows[$k]['children'][$k1]['recordcount']=	DB::table('news')->where('newsclass_id','=',$v1['id'])->count();
				}}
		}}				
		json(['total'=>$total, 'rows'=>$rows]);		
    }
	public function classaddAction(){
		$dataset  	= DB::table('newsclass')->where('up','=',0)->get();
		$this->_view->assign('dataset', $dataset);
    }	
	public function classincreaseAction(){
		do{
			if( $this->method!='POST' ){
				$result	= array(
							'code'=>	'300',
							'msg'	=>	'操作失败',										
						);
				break;
			}
			$title		= $this->getPost('title', '');
			$up			= $this->getPost('up', 	0);
			$sortorder	= $this->getPost('sortorder', 0);			
			if( empty($title) ){
				$result	= array(
							'code'	=>	'300',
							'msg'		=>	'菜单名称不能为空',
						);
				break;
			}
			$rows	= array(
								'title'		=>	$title,
								'up'		=>	$up,
								'sortorder'	=>	$sortorder,
								'created_at'=>	date('Y-m-d H:i:s'),
					);
			if( DB::table('newsclass')->insert($rows) ){
				$result	= array(
							'code'	=>	'200',
							'msg'		=>	'操作成功',								
						);
			}else{
				$result	= array(
							'code'=>	'300',
							'msg'	=>	'数据插入失败',	
						);
			}
		}while(FALSE);
		
		die(json_encode($result));
    }	
	public	function classeditAction(){    
		$dataset  	= DB::table('newsclass')->where('up','=',0)->get();
		
		$id			= intval($this->get('id', NULL));
		if($id==NULL){	return false;	}		
     	$mymenu  	= DB::table('newsclass')->find($id);

		$this->_view->assign('dataset', $dataset);
		$this->_view->assign('mymenu',  $mymenu);
    }	
    public function classupdateAction(){
		do{
			if( $this->method!='POST' ){
				$result	= array(
							'code'	=>	'300',
							'msg'		=>	'操作失败',										
						);
				break;
			}
			$id			= $this->getPost('id', '');
			$title		= $this->getPost('title', '');
			$up			= $this->getPost('up', 	0);
			$sortorder	= $this->getPost('sortorder', 0);			
			if( empty($id)||empty($title) ){
				$result	= array(
							'code'		=>	'300',
							'msg'		=>	'菜单id与标题不能为空',
						);
				break;
			}
			if( $id==$up ){
				$result	= array(
							'code'		=>	'300',
							'msg'		=>	'上级菜单循环设置.',
						);
				break;
			}
			$rows	=	array(	
							'title'		=>	$title,
							'up'		=>	$up,
							'sortorder'	=>	$sortorder,
							'updated_at'=>	date('Y-m-d H:i:s'),
						);
			if( DB::table('newsclass')->where('id','=',$id)->update($rows)!==FALSE ){
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
    			
		die(json_encode($result));
    }		
    public function classdeleteAction(){	
		do{
			if($this->method!='POST'){
				$result	= array(
							'code'=>	'300',
							'msg'	=>	'操作失败',										
						);
				break;				
			}
			$id	= $this->get('id', '');
			if( empty($id) ){
				$result	= array(
							'code'	=>	'300',
							'msg'		=>	'参数为空',
						);
				break;
			}
			if(DB::table('newsclass')->delete($id)){
				$result		= array(
							'code'	=>	'200',
							'msg'		=>	'操作成功',
							'navTabId'		=>	'newsclass',
							);						
			}else{
				$result		= array(
							'code'	=>	'300',
							'msg'		=>	'删除失败',
							);
			}
		}while(FALSE);	
		
		die(json_encode($result));    	
    }
		
	public function newsAction(){
		$this->_view->assign('uniqid',	 uniqid());
		
		$rows	= DB::table('newsclass')->where('up','=',0)->orderBy('sortorder','desc')->get();
		if(!empty($rows)&&is_array($rows)){
		foreach($rows	as	$k=>$v){
				$rows[$k]['recordcount']=	DB::table('news')->where('newsclass_id','=',$v['id'])->count();	
				$rows[$k]['children']	=	DB::table('newsclass')->where('up','=',$v['id'])->orderBy('sortorder','desc')->get();
				if(!empty($rows[$k]['children'])&&is_array($rows[$k]['children'])){
				foreach($rows[$k]['children']	as	$k1=>$v1){
					$rows[$k]['children'][$k1]['recordcount']=	DB::table('news')->where('newsclass_id','=',$v1['id'])->count();
				}}
		}}		
		$this->_view->assign('newsclass',	 $rows);
    }
	public function newsGetAction() {
		$page   =	$this->getPost('page', 1);
		$limit  =	$this->getPost('rows', 10);
		$offset	=	($page-1)*$limit;			
		$sort	=	$this->getPost('sort',  'sortorder');
		$order	=	$this->getPost('order', 'desc');
		$keywords		= $this->getPost('keywords', '');
		$newsclass_id	= $this->getPost('newsclass_id', 0);

		$query	= DB::table('news');
		if($newsclass_id>0){
			$query	=	$query->where('news.newsclass_id','=',$newsclass_id);
		}
		if($keywords!==''){
			$query	=	$query->where(function ($query) use($keywords) {
										$query->where('news.title','like',"%{$keywords}%")
											  ->orWhere('news.keywords','like',"%{$keywords}%");
									});						
		}
		$total		= $query->count();
		$rows 		= $query->join('newsclass','news.newsclass_id','=','newsclass.id')
							->orderBy($sort,$order)
							->offset($offset)
							->limit($limit)
							->select('newsclass.title as classname','news.*')
							->get();			
						
		json(['total'=>$total, 'rows'=>$rows]);		
    }
	public function newsaddAction(){		
		$newsclass	= DB::table('newsclass')->where('up','=',0)->orderBy('sortorder','DESC')->get();
		if(!empty($newsclass)&&is_array($newsclass)){
		foreach($newsclass as $k=>$v){
			$newsclass[$k]['children']	=	DB::table('newsclass')->where('up','=',$v['id'])->orderBy('sortorder','DESC')->get();
		}}
		$this->_view->assign('newsclass', 	$newsclass);
    }	
	public function newsincreaseAction(){
		do{
			if( $this->method!='POST' ){
				$result	= array(
							'code'	=>	'300',
							'msg'	=>	'操作失败',										
						);
				break;
			}
			$title			= $this->getPost('title', '');
			$newsclass_id	= $this->getPost('newsclass_id', '');
			$keywords		= $this->getPost('keywords', '');
			$author			= $this->getPost('author', '');
			$copyfrom		= $this->getPost('copyfrom', '');
			$copyfromurl	= $this->getPost('copyfromurl', '');
			$sortorder		= $this->getPost('sortorder', '');
			$status			= $this->getPost('status', 		0);			
			$recommend		= $this->getPost('recommend',	0);			
			$content		= $this->getPost('content', '');			
			if( empty($title) || empty($content) ){
				$result	= array(
							'code'	=>	'300',
							'msg'		=>	'标题和内容不能为空',
						);
				break;
			}
			$rows	=	array(				
							'newsclass_id'	=>	$newsclass_id,
							'title'			=>	$title,
							'keywords'		=>	$keywords,
							'author'		=>	$author,
							'copyfrom'		=>	$copyfrom,
							'copyfromurl'	=>	$copyfromurl,
							'sortorder'		=>	$sortorder,
							'status'		=>	$status,
							'recommend'		=>	$recommend,							
							'created_at'	=>	date('Y-m-d H:i:s'),
						);	
			$files	= $this->getFiles('upfile', NULL);				
			if( $files!=NULL && $files['size']>0 ){
				if( $image = $this->_uploadPictureToCDN('upfile') ){
					$rows['logo']	=	$image;
				}else{
					$result	= array(
						'code'		=>	'300',
						'msg'		=>	'图片上传失败.',
					);
					break;
				}
			}elseif( preg_match('#<img.*?src\=[\"\']([^\"\']*)[\"\']#is', stripslashes($content), $imagesurl) ){
				$rows['logo']	=	$imagesurl[1];				
			}			
			if( $id=DB::table('news')->insertGetId($rows) ){
				DB::table('newscontent')->insert(['id'=>$id,'content'=>$content]);
				$result	= array(
							'code'		=>	'200',
							'msg'		=>	'操作成功',	
						);
			}else{
				$result	= array(
							'code'	=>	'300',
							'msg'	=>	'数据插入失败',	
						);
			}
		}while(FALSE);
		
		die(json_encode($result));
    }	
	public function newseditAction(){    
		$id			= $this->get('id', 0);
		$dataset  	= (new newsModel)->with('content')->find(intval($id))->toArray();
		$this->_view->assign('dataset', $dataset);
				
		$newsclass	= DB::table('newsclass')->where('up','=',0)->orderBy('sortorder','DESC')->get();		
		if(!empty($newsclass)&&is_array($newsclass)){
		foreach($newsclass as $k=>$v){
			$newsclass[$k]['children']	=	DB::table('newsclass')->where('up','=',$v['id'])->orderBy('sortorder','DESC')->get();
		}}
		$this->_view->assign('newsclass', 	$newsclass);
    }	
    public function newsupdateAction(){
		do{
			if( $this->method!='POST' ){
				$result	= array(
							'code'	=>	'300',
							'msg'	=>	'操作失败',										
						);
				break;
			}
			$id				= $this->getPost('id', '');
			$title			= $this->getPost('title', '');
			$newsclass_id	= $this->getPost('newsclass_id', '');
			$keywords		= $this->getPost('keywords', '');
			$author			= $this->getPost('author', '');
			$copyfrom		= $this->getPost('copyfrom', '');
			$copyfromurl	= $this->getPost('copyfromurl', '');
			$sortorder		= $this->getPost('sortorder', '');
			$status			= $this->getPost('status', 		0);			
			$recommend		= $this->getPost('recommend',	0);			
			$content		= $this->getPost('content', '');			
			if( empty($title) || empty($content) ){
				$result	= array(
							'code'	=>	'300',
							'msg'		=>	'标题和内容不能为空',
						);
				break;
			}
			$rows	=	array(
							'newsclass_id'	=>	$newsclass_id,
							'title'			=>	$title,
							'keywords'		=>	$keywords,
							'author'		=>	$author,
							'copyfrom'		=>	$copyfrom,
							'copyfromurl'	=>	$copyfromurl,
							'sortorder'		=>	$sortorder,
							'status'		=>	$status,
							'recommend'		=>	$recommend,							
							'updated_at'	=>	date('Y-m-d H:i:s'),
						);	
			$files	= $this->getFiles('upfile', NULL);				
			if( $files!=NULL && $files['size']>0 ){
				if( $image = $this->_uploadPictureToCDN('upfile') ){
					$rows['logo']	=	$image;
				}else{
					$result	= array(
						'code'		=>	'300',
						'msg'		=>	'图片上传失败.',
					);
					break;
				}
			}elseif( empty($rows['logo']) &&  preg_match('#<img.*?src\=[\"\']([^\"\']*)[\"\']#is', stripslashes($content), $imagesurl) ){
				$rows['logo']	=	$imagesurl[1];				
			}
			if( DB::table('news')->where('id','=',$id)->update($rows)!==FALSE ){
				DB::table('newscontent')->where('id','=',$id)->update(['content'=>$content]);
				$result	= array(
							'code'		=>	'200',
							'msg'		=>	'操作成功',	
						);
			}else{
				$result	= array(
							'code'	=>	'300',
							'msg'	=>	'数据插入失败',	
						);
			}			
		}while(FALSE);
    			
		die(json_encode($result));
    }
	public function newsRecycleAction(){	
		do{
			if($this->method!='POST'){
				$result	= array(
							'code'=>	'300',
							'msg'	=>	'操作失败',										
						);
				break;				
			}
			$id	= $this->get('id', '');
			if( empty($id) ){
				$result	= array(
							'code'	=>	'300',
							'msg'		=>	'参数为空',
						);
				break;
			}
			
			$status	=	DB::table('news')->find($id)['status'];
			$rows	=	array(
							'status'	=> ($status>0)?0:1, 
							'deleted_at'=> ($status>0)?date('Y-m-d H:i:s'):'0000-00-00 00:00:00', 
			);			
			if(DB::table('news')->where('id','=',$id)->update($rows)){
				$result		= array(
							'code'		=>	'200',
							'msg'		=>	'操作成功',
							);						
			}else{
				$result		= array(
							'code'	=>	'300',
							'msg'		=>	'删除失败',
							);
			}
		}while(FALSE);	
		
		die(json_encode($result));    	
    }
    public function newsdeleteAction(){	
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
			if(DB::table('newscontent')->delete($id) && DB::table('news')->delete($id)){
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
		
		die(json_encode($result));    	
    }
	
	public function _uploadPictureToCDN($upfile) {
        $files	= $this->getFiles($upfile);
		if( $files!=NULL && $files['size']>0 ){
			$uploader  = new FileUploader();
			$files     = $uploader->getFile($upfile);
            if(!$files){
				return FALSE;
			}
            if($files->getSize()==0){
				return FALSE;
            }
			$config	= Yaf_Registry::get('config');
            if (!$files->checkExts($config['application']['upfileExts'])){				
            	return FALSE;
            }
			if (!$files->checkSize($config['application']['upfileSize'])){
            	return FALSE;
            }
			$cdnFileName = 'Images-t' . time().rand(100,999) . '.' . $files->getExt();
			if( $image = $this->uploadToCDN($files->getTmpName(), $cdnFileName) ){
				return $image;
			}else{
				return FALSE;
			}
		}
		
		return FALSE;
    }
	
	/**
     * deal imgage upload
     */
    private function _uploadPicture($upfile) {
        do {
            $uploader  = new FileUploader();
            $files     = $uploader->getFile($upfile);
            if(!$files) break; 
            if($files->getSize()==0){
				//throw new Exception('file size is zero.');
				break;
            }
			$config	= Yaf_Registry::get('config');
            if (!$files->checkExts($config['application']['upfileExts'])){				
            	//throw new Exception('文件类型出错, 只允许'.$config['application']['upfileExts']);
                break;
            }
			if (!$files->checkSize($config['application']['upfileSize'])){
            	//throw new Exception('文件大小出错, 不允许超过.'.$config['application']['upfileSize'].'字节');
                break;
            }
			
			$filename = 'home-t' . time() . '.' . $files->getExt();
			$descdir  = $config['application']['uploadpath'] . '/Home/';
			if( !is_dir($descdir) ){ mkdir($descdir); }
			$realpath = $descdir . $filename;			
			if($files->move($realpath)){				
				return str_replace('./', '/', $realpath);
			}
        }while(false);
        
        return false;
    }

	/***PHP上传文件到七牛cdn***/
	public function uploadToCDN($filePath, $cdnfileName){					
			// 需要填写你的 Access Key 和 Secret Key
			$accessKey = $this->config['application']['cdn']['accessKey'];
			$secretKey = $this->config['application']['cdn']['secretKey'];

			// 构建鉴权对象
			$auth = new \Qiniu\Auth($accessKey, $secretKey);
			// 要上传的空间
			$bucket = $this->config['application']['cdn']['bucket'];
			
			// 生成上传 Token
			$token = $auth->uploadToken($bucket);

			// 上传到七牛后保存的文件名
			$key = $cdnfileName;

			// 初始化 UploadManager 对象并进行文件的上传
			$uploadMgr = new \Qiniu\Storage\UploadManager;

			// 调用 UploadManager 的 putFile 方法进行文件的上传
			list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
			if ($err !== null) {
				return false;
			} else {
				return $this->config['application']['cdn']['url'] . $ret['key'];
			}
	}

	
}
