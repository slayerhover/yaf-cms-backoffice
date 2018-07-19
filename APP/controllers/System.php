<?php
use Illuminate\Database\Capsule\Manager as DB;

class SystemController extends CoreController{
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
		
	public function shippingAction() {
		do{	
			$keywords	=$this->get('keywords', '');
			$rows		=DB::table('shipping');
			if(!empty($keywords)){
				$rows	=$rows->where('name','like',"%{$keywords}%");
			};			
			$rows	= $rows->get();
			$result	= array(
						'ret'=>'0','msg'=>'配送方式','data'=>$rows,
			);						
		}while(FALSE);
		
		json($result);
	}
	public function shippingAddAction() {
		do{
			$name	= $this->get('name',	'');
			$description= $this->get('description', '');
			$price		= $this->get('price', 		'');
			$status		= $this->get('status',		 1);	
			$sortorder	= $this->get('sortorder',	500);
			$inputs	= array(
					['name'=>'name',  'value'=>$name, 'role'=>'required|unique:shipping.name',	'fun'=>'isname', 'msg'=>'配送方式名称有误'],					
			);
            $result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			$rows	=	array(
							'name'		=>	$name,
							'description'	=>	$description,
							'price'			=>	$price,							
							'status'		=>	1,
							'sortorder'		=>	$sortorder,
							'created_at'	=>	date('Y-m-d H:i:s'),
			);
			$lastId = DB::table('shipping')->insertGetId($rows);
			if ($lastId) {												
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'操作成功.',
					);
					break;
			}
			$result	= array(
					'ret'	=>	'2',
					'msg'	=>	'添加配送方式失败',
			);
		}while(FALSE);
		
		json($result);
	}
	public function shippingEditAction() {
		do{
			$id			= $this->get('id',  0);
			$name		= $this->get('name',	'');
			$description= $this->get('description', '');
			$price		= $this->get('price', 		'');
			$status		= $this->get('status',		 1);	
			$sortorder	= $this->get('sortorder',	500);
			$inputs	= array(
				['name'=>'id',  'value'=>$id, 'role'=>'required|exists:shipping.id',	'fun'=>'isInt', 'msg'=>'配送方式ID有误'],					
			);
            $result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			$rows	=	array(						
							'name'		=>	$name,
							'description'	=>	$description,
							'price'			=>	$price,							
							'status'		=>	1,
							'sortorder'		=>	$sortorder,
							'updated_at'	=>	date('Y-m-d H:i:s'),
			);
			if (DB::table('shipping')->where('id','=',$id)->update($rows)!==FALSE) {
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'编辑配送方式成功.',
					);
					break;
			}
			$result	= array(
					'ret'	=>	'2',
					'msg'	=>	'操作失败',
			);
		}while(FALSE);
		
		json($result);	
	}	
	public function shippingDelAction() {
		do{			
			$id			= $this->get('id',  0);
			$inputs	= array(
					['name'=>'id',  'value'=>$id, 'role'=>'required|exists:shipping.id',	'fun'=>'isInt', 'msg'=>'配送方式ID有误'],			
			);			
            $result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}			
			if (DB::table('shipping')->delete($id)!==FALSE) {												
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'删除配送方式成功.',
					);
					break;
			}
			$result	= array(
					'ret'	=>	'0',
					'msg'	=>	'删除配送方式失败',
			);
		}while(FALSE);
		
		json($result);	
	}	
	public function shippingBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('shipping')->delete($id);
		}
		ret(0, '操作成功');	
    }
	
	public function paymentAction() {
		do{	
			$keywords	=$this->get('keywords', '');
			$rows		=DB::table('payment');
			if(!empty($keywords)){
				$rows	=$rows->where('name','like',"%{$keywords}%");
			};			
			$rows	= $rows->get();
			$result	= array(
						'ret'=>'0','msg'=>'支付方式','data'=>$rows,
			);						
		}while(FALSE);
		
		json($result);
	}
	public function paymentAddAction() {
		do{
			$name	= $this->get('name',	'');
			$param		= $this->get('param', '');			
			$status		= $this->get('status',		 1);	
			$sortorder	= $this->get('sortorder',	500);
			$inputs	= array(
					['name'=>'name',  'value'=>$name, 'role'=>'required|unique:payment.name',	'fun'=>'isUsername', 'msg'=>'支付方式名称有误'],					
			);
            $result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			$rows	=	array(
							'name'		=>	$name,
							'param'		=>	$param,
							'status'		=>	1,
							'sortorder'		=>	$sortorder,
							'created_at'	=>	date('Y-m-d H:i:s'),
			);
			$lastId = DB::table('payment')->insertGetId($rows);
			if ($lastId) {												
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'操作成功.',
					);
					break;
			}
			$result	= array(
					'ret'	=>	'2',
					'msg'	=>	'添加支付方式失败',
			);
		}while(FALSE);
		
		json($result);
	}
	public function paymentEditAction() {
		do{
			$id			= $this->get('id',  0);
			$name		= $this->get('name',	'');
			$param		= $this->get('param', '');
			$status		= $this->get('status',		 1);	
			$sortorder	= $this->get('sortorder',	500);
			$inputs	= array(
				['name'=>'id',  'value'=>$id, 'role'=>'required|exists:payment.id',	'fun'=>'isInt', 'msg'=>'支付方式ID有误'],					
			);
            $result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			$rows	=	array(						
							'name'		=>	$name,
							'param'		=>	$param,
							'status'		=>	1,
							'sortorder'		=>	$sortorder,
							'updated_at'	=>	date('Y-m-d H:i:s'),
			);
			if (DB::table('payment')->where('id','=',$id)->update($rows)!==FALSE) {
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'编辑支付方式成功.',
					);
					break;
			}
			$result	= array(
					'ret'	=>	'2',
					'msg'	=>	'操作失败',
			);
		}while(FALSE);
		
		json($result);	
	}	
	public function paymentDelAction() {
		do{			
			$id			= $this->get('id',  0);
			$inputs	= array(
					['name'=>'id',  'value'=>$id, 'role'=>'required|exists:payment.id',	'fun'=>'isInt', 'msg'=>'支付方式ID有误'],			
			);			
            $result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}			
			if (DB::table('payment')->delete($id)!==FALSE) {												
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'删除支付方式成功.',
					);
					break;
			}
			$result	= array(
					'ret'	=>	'0',
					'msg'	=>	'删除支付方式失败',
			);
		}while(FALSE);
		
		json($result);	
	}	
	public function paymentBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('payment')->delete($id);
		}
		ret(0, '操作成功');	
    }
	
	public function resetPwdAction(){
		do{
			$id			=	$this->getPost('id', 0);
			$oldPassword=	$this->getPost('oldpassword', '');
			$newPassword=	$this->getPost('newpassword', '');
			$rePassword=	$this->getPost('repassword', '');
		
			if( empty($oldPassword)||empty($rePassword) ){
				$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'原密码或新密码不能为空',
						);	
				break;				
			}
			if( $rePassword!=$newPassword ){
				$result	= array(
							'ret'	=>	'2',
							'msg'	=>	'重复密码不一致.',
						);	
				break;				
			}
			if($id==0){				
				$id	=$this->user['id'];
			}
			
				/***检查旧密码是否正确***/
				if( DB::table('admin')->find($id)['password']!==md5($oldPassword) ){
					$result	= array(
								'ret'	=>	'3',
								'msg'	=>	'原密码输入有误.',
							);	
					break;
				}
				if( DB::table('admin')->where('id','=',$id)->update(['password'	=>	md5($newPassword)])!==FALSE ){
						$result	= array(
								'ret'	=>	'0',
								'msg'	=>	'操作成功',
						);
				}else{
						$result	= array(
								'ret'	=>	'4',
								'msg'	=>	'更新失败,请多试几下',	
						);
				}
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 * type 图片类型
	 * 0: 启动图片
	 * 1: 首页滚动图片
	 * 2: 广告图片
	 **/
	public function imagesAction(){		
		$type	=$this->get('type', 1);
		$sort   =$this->get('sort', 'sortorder');
        $order  =$this->get('order', 'desc');
		$rows	=	DB::table('images')->where('type','=',   $type)
									 ->orderBy($sort, $order)
									 ->get();
		$title	='';							 
		switch($type){
			case 1:	$title = '首页动图';	break;
			case 2:	$title = '广告图片';	break;
			case 3:	$title = '专题图片';
			    foreach($rows as &$v){
			            $v['links'] = '#/zhuanti/' . $v['id'];
                }
			    break;
		}
		ret(0, $title, $rows);
	}
	
	public function imagesGetAction(){		
		$id	=$this->get('id', 0);
		$rows	=	DB::table('images')->find($id);		
		switch($rows['type']){
			case 1:	$title = '首页动图';	break;
			case 2:	$title = '广告图片';	break;
			case 3:	$title = '专题图片';
                $rows['links'] = '#/zhuanti/' . $rows['id'];
                break;
		}
		ret(0, $title, $rows);
	}
		
	/**
	 *接口名称	上传图片	 
	 *参数 @param
	 * @logo 		图片文件
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function imagesAddAction(){
		$type	=$this->get('type', 1);
		$image	=$this->get('image', '');
		$title	=$this->get('title', '');
		$content=$this->get('content', '');
		$status	=$this->get('status', 0);
		$sortorder=$this->get('sortorder', 500);
		$links	=$this->get('links', '');
		
		$rows = array(
			'type'		=>$type,
			'sortorder'	=>$sortorder,
			'links'		=>$links,
			'title'		=>$title,
			'content'	=>$content,
			'status'	=>$status,
			'created_at'=>date('Y-m-d H:i:s'),
		);
		if(!empty($image)){
			$rows['image']	=$this->uploader($image);
		}
		DB::table('images')->insert($rows);				
		ret(0, '上传成功', $image);
	}
	
	/**
	 *接口名称	上传图片	 
	 *参数 @param
	 * @logo 		图片文件
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function imagesEditAction(){
		$id=$this->get('id', 0);
		$type	=$this->get('type', 1);
		$image	=$this->get('image', '');
		$title	=$this->get('title', '');
		$content=$this->get('content',  '');
		$status	=$this->get('status', 0);
		$sortorder=$this->get('sortorder', 500);
		$links	=$this->get('links', '');

		$rows = array(
			'type'		=>$type,
			'sortorder'	=>$sortorder,
			'links'		=>$links,
			'title'		=>$title,
			'content'	=>$this->convertContent($content),
			'status'	=>$status,
			'created_at'=>date('Y-m-d H:i:s'),
		);
		if(!empty($image)){
			$rows['image']	=$this->uploader($image);
		}
		DB::table('images')->where('id','=',$id)->update($rows);				
		ret(0, '更新成功', DB::table('images')->find($id));
	}
	private function convertContent($content)
    {
        $result = [];
        if (preg_match_all('/(data:\s*image\/\w+;base64[^\"]*)/is', $content, $result)) {

            foreach ($result[0] as $k=>$v) {
                $fileUrl = $this->convertImg($v);
                $content = str_replace($v, $fileUrl, $content);
            }
        }
        return $content;
    }
    private function convertImg($files){
        $base64result = [];
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $files, $base64result)){
            $type	  = $base64result[2];
            if($type=='jpeg') $type='jpg';
            $config	  = Yaf_Registry::get('config');
            $filename = 'im-t' . time() . '.' . $type;
            $path	  = '/imgs/' . date('Ym') . '/';
            $descdir  = $config['application']['uploadpath'] . $path;
            if(!is_dir($descdir)){ mkdir($descdir, 0777, TRUE); }
            $realpath = $descdir . $filename;
            if(file_put_contents($realpath, base64_decode(str_replace(' ', '+', str_replace($base64result[1], '', $files))))){
                $cdnfileName = 'Img-t' . time().rand(1000,9999) . '.' . $type;
                if( $image = $this->uploadToCDN($realpath, $cdnfileName) ){
                    return $image;
                }
            }
        }
        return FALSE;
    }
	
	public function imagesDelAction(){		
		$id	=$this->get('id', 0);
		if(DB::table('images')->delete($id)===FALSE){
			ret(1, '删除失败');
		}		
		ret(0, '删除成功');
	}
}
