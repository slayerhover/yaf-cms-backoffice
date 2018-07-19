<?php
use Illuminate\Database\Capsule\Manager as DB;

class NewsController extends CoreController{
	private $user;
	/**
	 * 初始化验证 *
	 **/
	public function init(){
        Yaf_Dispatcher::getInstance()->disableView();
		parent::init();        
		$token = $this->get('token', '');
		$this->user = Cache::getInstance()->get('auth_'.$token);
	}
	public function classAction() {
		$page   =	$this->get('page', 1);
		$limit  =	$this->get('rows', 10);
		$offset	=	($page-1)*$limit;				
		$sort	=	$this->get('sort', 'sortorder');
		$order	=	$this->get('order', 'desc');
		$keywords	= $this->get('keywords', '');				
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
		ret(0, '文章列表',['total'=>$total, 'rows'=>$rows]);
    }
	public function classAddAction(){
		do{			
			$title		= $this->get('title', '');
			$up			= $this->get('up', 	0);
			$sortorder	= $this->get('sortorder', 0);			
			if( empty($title) ){
				$result	= array(
							'ret'	=>	3,
							'msg'	=>	'文章分类名称不能为空',
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
							'ret'	=>	0,
							'msg'		=>	'操作成功',								
						);
			}else{
				$result	= array(
							'ret'	=>	4,
							'msg'	=>	'数据插入失败',	
						);
			}
		}while(FALSE);
		
		json($result);
    }	
    public function classEditAction(){
		do{		
			$id			= $this->get('id', '');
			$title		= $this->get('title', '');
			$up			= $this->get('up', 	0);
			$sortorder	= $this->get('sortorder', 0);			
			if( empty($id)||empty($title) ){
				$result	= array(
							'ret'		=>	'3',
							'msg'		=>	'文章类别id与标题不能为空',
						);
				break;
			}
			if( $id==$up ){
				$result	= array(
							'ret'		=>	'4',
							'msg'		=>	'上级循环设置.',
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
							'ret'		=>	'0',
							'msg'		=>	'操作成功',	
						);
			}else{
				$result	= array(
							'ret'		=>	'6',
							'msg'		=>	'更新失败',	
						);
			}
		}while(FALSE);
    			
		json($result);
    }		
    public function classDelAction(){	
		do{
			$id	= $this->get('id', '');
			if( empty($id) ){
				$result	= array(
							'ret'	=>	'3',
							'msg'		=>	'参数为空',
						);
				break;
			}
			if(DB::table('newsclass')->delete($id)){
				$result		= array(
							'ret'		=>	'0',
							'msg'		=>	'操作成功',							
				);
			}else{
				$result		= array(
							'ret'	=>	'4',
							'msg'		=>	'删除失败',
				);
			}
		}while(FALSE);	
		
		json($result);    	
    }
	
	public function newsAction() {
		$page   =	$this->get('page', 1);
		$limit  =	$this->get('rows', 10);
		$offset	=	($page-1)*$limit;			
		$sort	=	$this->get('sort',  'sortorder');
		$order	=	$this->get('order', 'desc');
		$keywords		= $this->get('keywords', '');
		$newsclass_id	= $this->get('newsclass_id', 0);

		$query	= DB::table('news')->join('newsclass','news.newsclass_id','=','newsclass.id');
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
		$rows 		= $query->orderBy($sort,$order)
							->offset($offset)
							->limit($limit)
							->select('news.id','news.title','newsclass.title as classname','news.author','news.sortorder','news.keywords',DB::raw('if(status=1,"激活","失效") as status'),'news.created_at','news.updated_at','news.deleted_at')
							->get();			
						
		ret(0, '文章列表', ['total'=>$total, 'rows'=>$rows]);		
    }
	#获取文章信息
	public function newsGetAction($id=0) {
	    $id = $this->get('id', 0);
		$inputs		= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:news.id','msg'=>'文章ID有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}	
		$rows = DB::table('news')->join('newscontent','news.id','=','newscontent.id')->select('news.*','newscontent.content')->where('news.id','=',$id)->first();
		ret(0, '文章信息', $rows);
    }
	public function newsAddAction(){
		do{
			$title			= $this->get('title', '');
			$newsclass_id	= $this->get('newsclass_id', '');
			$keywords		= $this->get('keywords', '');
			$author			= $this->get('author', '');
			$copyfrom		= $this->get('copyfrom', '');
			$copyfromurl	= $this->get('copyfromurl', '');
			$sortorder		= $this->get('sortorder', '');
			$status			= $this->get('status', 		0);			
			$recommend		= $this->get('recommend',	0);			
			$content		= $this->get('content', '');
			$logo			= $this->get('logo', '');			
			if( empty($title) || empty($content) ){
				$result	= array(
							'ret'	=>	'3',
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
					'logo'			=>	$logo,
					'sortorder'		=>	time(),
					'status'		=>	$status,
					'recommend'		=>	$recommend,							
					'created_at'	=>	date('Y-m-d H:i:s'),
			);						
			if( $id=DB::table('news')->insertGetId($rows) ){
				DB::table('newscontent')->insert(['id'=>$id,'content'=>$content]);
				$result	= array(
							'ret'		=>	'0',
							'msg'		=>	'操作成功',	
						);
			}else{
				$result	= array(
							'ret'	=>	'4',
							'msg'	=>	'数据插入失败',	
						);
			}
		}while(FALSE);
		
		json($result);
    }
    public function newsEditAction(){
		do{
			$id				= $this->get('id', '');
			$title			= $this->get('title', '');
			$newsclass_id	= $this->get('newsclass_id', '');
			$keywords		= $this->get('keywords', '');
			$author			= $this->get('author', '');
			$copyfrom		= $this->get('copyfrom', '');
			$copyfromurl	= $this->get('copyfromurl', '');
			$sortorder		= $this->get('sortorder', '');
			$status			= $this->get('status', 		0);			
			$recommend		= $this->get('recommend',	0);			
			$content		= $this->get('content', '');
			$logo			= $this->get('logo', '');
			if( empty($title) || empty($content) ){
				$result	= array(
							'ret'	=>	'3',
							'msg'	=>	'标题和内容不能为空',
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
							'logo'			=>	$logo,
							'recommend'		=>	$recommend,							
							'updated_at'	=>	date('Y-m-d H:i:s'),
			);				
			if( DB::table('news')->where('id','=',$id)->update($rows)!==FALSE ){
				DB::table('newscontent')->where('id','=',$id)->update(['content'=>$content]);
				$result	= array(
							'ret'		=>	'0',
							'msg'		=>	'操作成功',	
						);
			}else{
				$result	= array(
							'ret'	=>	'4',
							'msg'	=>	'数据插入失败',	
						);
			}			
		}while(FALSE);
    			
		json($result);
    }
	public function newsTrashAction(){	
		do{			
			$id	= $this->get('id', '');
			if( empty($id) ){
				$result	= array(
							'ret'	=>	'3',
							'msg'	=>	'参数为空',
						);
				break;
			}			
			if(DB::table('news')->where('id','=',$id)->update(['deleted_at'=>$is_deleted>0 ? 0 : date('Y-m-d H:i:s')])){
				$result		= array(
							'ret'	=>	'0',
							'msg'	=>	'操作成功',
							);						
			}else{
				$result		= array(
							'ret'	=>	'4',
							'msg'	=>	'操作失败',
							);
			}
		}while(FALSE);	
		
		json($result);    	
    }
	public function newsUntrashAction(){	
		do{			
			$id	= $this->get('id', '');
			if( empty($id) ){
				$result	= array(
							'ret'	=>	'3',
							'msg'	=>	'参数为空',
						);
				break;
			}			
			if(DB::table('news')->where('id','=',$id)->update(['deleted_at'=>0])){
				$result		= array(
							'ret'	=>	'0',
							'msg'	=>	'操作成功',
							);						
			}else{
				$result		= array(
							'ret'	=>	'4',
							'msg'	=>	'操作失败',
							);
			}
		}while(FALSE);			
		json($result);    	
    }
    public function newsDelAction(){	
		do{
			$id	= $this->get('id', '');
			if( empty($id) ){
				$result	= array(
							'ret'		=>	'300',
							'msg'		=>	'参数为空',
						);
				break;
			}
			if(DB::table('newscontent')->delete($id) && DB::table('news')->delete($id)){
				$result		= array(
							'ret'		=>	'0',
							'msg'		=>	'操作成功',
							);						
			}else{
				$result		= array(
							'ret'		=>	'3',
							'msg'		=>	'删除失败',
							);
			}
		}while(FALSE);	
		
		json($result);    	
    }
	public function newsBatchDelAction(){	
		do{
			$ids	= $this->get('ids', []);
			if( empty($id) ){
				$result	= array(
							'ret'		=>	'3',
							'msg'		=>	'参数为空',
						);
				break;
			}			
			if(DB::table('newscontent')->whereIn('id',$ids)->delete() && DB::table('news')->whereIn('id',$ids)->delete()){
				$result		= array(
							'ret'		=>	'0',
							'msg'		=>	'操作成功',
							);						
			}else{
				$result		= array(
							'ret'		=>	'4',
							'msg'		=>	'删除失败',
							);
			}
		}while(FALSE);	
		
		json($result);    	
    }
	

	
	/**
	 *接口名称	上传产品图片	 
	 *参数 @param
	 * @logo 		图片文件
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function uploadImageAction(){
		$files	= $this->get('image', '');
		if(empty($files)){
			ret(3, '图片内容为空');
		}
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $files, $base64result)){
			$type	  = $base64result[2];
			if($type=='jpeg') $type='jpg';
			$config	  = Yaf_Registry::get('config');
			$filename = 'news-t' . time() . '.' . $type;		
			$path	  = '/news/' . date('Ym') . '/';
			$descdir  = $config['application']['uploadpath'] . $path;
			if(!is_dir($descdir)){ mkdir($descdir, 0777, TRUE); }
			$realpath = $descdir . $filename;				
			$webpath  = $config['application']['uploadwebpath'] . $path . $filename;
			if(!file_put_contents($realpath, base64_decode(str_replace(' ', '+', str_replace($base64result[1], '', $files))))){				
				ret(4, '储存图片出错.');
			}
			$cdnfilename = 'Img-t' . time().rand(1000,9999) . '.' . $type;
			if( $image = $this->uploadToCDN($realpath, $cdnfileName) ){
				ret(0, '上传图片成功', $image);
			}else{
				ret(1, '上传图片失败');
			}
		}else{
			ret(2, '上传图片格式有误');
		}
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
