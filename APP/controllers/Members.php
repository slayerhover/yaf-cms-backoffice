<?php
use Illuminate\Database\Capsule\Manager as DB;

class MembersController extends CoreController {
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
		
	#会员列表
	public function listAction() {		
		$status		=$this->get('status',0);				
		$keywords	=$this->get('keywords', '');		
		$sort		=$this->get('sort', 'created_at');
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$query		= (new membersModel)->where('phone','<>','');
		if($this->user['roles_id']==6){
			$query	=	$query->where('consultant_id','=',$this->user['id']);
		}else{
			$clientmanager_id	=$this->get('consultant_id', 0);
			if($clientmanager_id>0)	$query	=	$query->where('consultant_id','=',$clientmanager_id);
		}
		if($status>0){
			$query	=	$query->where('status','=',$status);
		}		
		if($keywords!==''){
			$query	=	$query	->where('name','like',"%{$keywords}%")
								->orWhere('keywords', 'like', "%{$keywords}%")
								->orWhere('introduce','like', "%{$keywords}%");
		}
		$recorddate	=$this->get('recorddate', []);
		if(!empty($recorddate)){
			$starton=$recorddate[0]??'';
            if(!empty($starton)){$query	=	$query	->where('created_at','>=',$starton); }
			$endon	=$recorddate[1]??'';
            if(!empty($endon)){$query	=	$query	->where('created_at','<=',$endon);}
		}
		$total		= $query->count();
		$totalpage	= ceil($total/$pagesize);
		$rows 		= $query->orderBy($sort,$order)->offset($offset)->limit($pagesize)->get()->toArray();
		ret(0, '会员列表', ['status'=>$status,'keywords'=>$keywords,'sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }
	
	#意向会员列表
	public function intentionAction() {		
		$status		=$this->get('status',0);				
		$keywords	=$this->get('keywords', '');		
		$sort		=$this->get('sort', 'created_at');
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$query		= DB::table('members')->where('phone','=','')->where('openid','<>','');
		if($this->user['roles_id']==6){
			$query	=	$query->where('consultant_id','=',$this->user['id']);
		}
		if($status>0){
			$query	=	$query	->where('status','=',$status);
		}		
		if($keywords!==''){
			$query	=	$query	->where('name','like',"%{$keywords}%")
								->orWhere('keywords', 'like', "%{$keywords}%")
								->orWhere('introduce','like', "%{$keywords}%");
		}
		$total		= $query->count();
		$totalpage	= ceil($total/$pagesize);
		$rows 		= $query->orderBy($sort,$order)->offset($offset)->limit($pagesize)->get();						
		ret(0, '意向会员列表', ['status'=>$status,'keywords'=>$keywords,'sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }
	
	#客户经理
	public function clientmanagerAction() {		
		$keywords	=$this->get('keywords', '');		
		$sort		=$this->get('sort', 'sortorder');	
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$query		= DB::table('admin')->where('roles_id','=',6)->where('status','=',1);		
		if($keywords!==''){
			$query	=	$query	->where('username','like',"%{$keywords}%")
								->orWhere('name', 'like', "%{$keywords}%")
								->orWhere('phone','like', "%{$keywords}%");
		}
		$total		= $query->count();
		$totalpage	= ceil($total/$pagesize);
		$rows 		= $query->orderBy($sort,$order)->offset($offset)->limit($pagesize)->select('id','username','phone','name','avatar','introduce','status','position')->get();
		ret(0, '客户经理', ['keywords'=>$keywords,'sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }
	public function clientmanagerAddAction() {
		do{
			$username	= $this->get('username',	'');			
			$password	= $this->get('password', 	'');
			$repassword = $this->get('repassword',	'');
			$avatar		= $this->get('avatar',		'');
			$introduce  = $this->get('introduce',		'');
			$phone		= $this->get('phone', 		'');
			$name		= $this->get('name', 		'');
			$position	= $this->get('position',	'');
			$status		= $this->get('status',		 1);	
			$inputs	= array(
					['name'=>'username',  'value'=>$username, 'role'=>'required|unique:admin.username',	'fun'=>'isUsername', 'msg'=>'用户名格式有误'],
					['name'=>'password','value'=>$password, 'role'=>"min:6|max:32|required", 'msg'=>'密码为空'],
					['name'=>'repassword','value'=>$repassword, 'role'=>"eq:{$password}", 'msg'=>'重复密码不一致'],
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
							'username'		=>	$username,
							'phone'			=>	$phone,
							'password'		=>	md5($password),							
							'name'			=>  $name,
							'introduce'     =>  $introduce,
							'position'		=>  $position,
							'status'		=>	1,
							'roles_id'		=>	6,
							'created_at'	=>	date('Y-m-d H:i:s'),
			);
			if(!empty($avatar)){
				$rows['avatar']	=$this->uploader($avatar);
			}
			$lastId = DB::table('admin')->insertGetId($rows);
			if ($lastId) {												
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'添加账号成功.',
							'data'	=>	$rows,
					);
					break;
			}
			$result	= array(
					'ret'	=>	'2',
					'msg'	=>	'用户注册失败',
			);
		}while(FALSE);
		
		json($result);
	}
	public function clientmanagerEditAction() {
		do{
			$id			= $this->get('id',  0);
			$username	= $this->get('username',	'');
			$avatar		= $this->get('avatar',		'');
			$phone		= $this->get('phone', 		'');
			$password	= $this->get('password', 	'');
			$repassword = $this->get('repassword',	'');
			$name		= $this->get('name', 		'');
            $introduce  = $this->get('introduce',		'');
			$position	= $this->get('position',	'');
			$status		= $this->get('status',		 1);	
			$inputs	= array(
					['name'=>'id',  'value'=>$id, 'role'=>'required|exists:admin.id',	'fun'=>'isInt', 'msg'=>'用户ID格式有误'],
					['name'=>'repassword','value'=>$repassword, 'role'=>"eq:{$password}", 'msg'=>'重复密码不一致'],
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
							'username'		=>	$username,
							'phone'			=>	$phone,							
							'name'			=>  $name,
                            'introduce'     =>  $introduce,
							'position'		=>  $position,
							'status'		=>	$status,
							'updated_at'	=>	date('Y-m-d H:i:s'),
			);
			if(!empty($avatar)){
				$rows['avatar']	=$this->uploader($avatar);
			}
			if( $password!='' )	$rows['password']=md5($password);
			if (DB::table('admin')->where('id','=',$id)->update($rows)!==FALSE) {
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'编辑账号成功.',
							'data'	=>	$rows
					);
					break;
			}
			$result	= array(
					'ret'	=>	'2',
					'msg'	=>	'用户注册失败',
			);
		}while(FALSE);
		
		json($result);	
	}	
	public function clientmanagerDelAction() {
		do{			
			$id			= $this->get('id',  0);
			$inputs	= array(
					['name'=>'id',  'value'=>$id, 'role'=>'required|gt:3|exists:admin.id',	'fun'=>'isInt', 'msg'=>'用户ID有误'],			
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
			if (DB::table('admin')->delete($id)!==FALSE) {												
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'删除账号成功.',
					);
					break;
			}
			$result	= array(
					'ret'	=>	'0',
					'msg'	=>	'删除用户失败',
			);
		}while(FALSE);
		
		json($result);	
	}	
	#用户批量删除
	public function clientmanagerBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('admin')->delete($id);
		}
		ret(0, '操作成功');	
    }

    public function clientmanagerSETAction() {
        $clientIds =$this->get('clientIds', []);
        $managerId =$this->get('managerId', 0);
        $inputs		= array(
            ['name'=>'managerId','value'=>$managerId,'role'=>'required|exists:admin.id','msg'=>'客服经理ID有误'],
        );
        $result		= Validate::check($inputs);
        if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
        DB::table('members')->whereIn('id', $clientIds)->update(['consultant_id'=>$managerId]);
        ret(0, '操作成功');
    }
		
	
	#获取会员信息
	public function getAction() {
		$id =$this->get('id', 0);
		$inputs		= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:members.id','msg'=>'会员ID有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}	
		$rows = DB::table('members')->find($id);
		$rows['clientmanager'] = DB::table('admin')->find($rows['consultant_id'])['name'];
		ret(0, '会员信息', ['rows'=>$rows]);
    }
	#会员添加
	public function addAction() {		
		$phone			=$this->get('phone', '');
		$password		=$this->get('password', '');
		$avatar			=$this->get('avatar', '');
		$name			=$this->get('name',   '');
		$gender			=$this->get('gender',   0);
		$email			=$this->get('email', '');
		$status			=$this->get('status', 1);
		$remark			=$this->get('remark', '');
		$company		=$this->get('company', '');
		$consultant_id	=$this->get('consultant_id', '33');
		$province		=$this->get('province','');
		$city			=$this->get('city','');
		$area			=$this->get('area','');
		$address		=$this->get('address','');
		$agreement_no	=$this->get('agreement_no','');
		$expired_at		=$this->get('expired_at','');
		$position		=$this->get('position','');
		$company_scale	=$this->get('company_scale','');
		$need_wine		=$this->get('need_wine','');
		$license_url	=$this->get('license_url','');
		$parent_proxy	=$this->get('parent_proxy',0);
		$commission		=$this->get('comission',0.00);		
		$sortorder		=$this->get('sortorder',500);
		$inputs		= array(
				['name'=>'phone','value'=>$phone,'role'=>'required|unique:members.phone','msg'=>'手机号已存在'],
				['name'=>'password','value'=>$password,'role'=>'required|min:6|max:32','msg'=>'密码长度有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(
				'phone'			=>$phone,
				'password'		=>$password,
				'avatar'		=>$avatar,
				'name'			=>$name,	
				'gender'		=>$gender,
				'email'			=>$email,
				'status'		=>$status,
				'remark'		=>$remark,
				'company'		=>$company,
				'consultant_id'	=>$consultant_id,
				'province'		=>$province,
				'city'			=>$city,
				'area'			=>$area,
				'address'		=>$address,
				'agreement_no'	=>$agreement_no,
				'expired_at'	=>$expired_at,
				'position'		=>$position,
				'company_scale'	=>$company_scale,
				'need_wine'		=>$need_wine,
				'license_url'	=>$license_url,
				'parent_proxy'	=>$parent_proxy,
				'commission'	=>$commission,
				'sortorder'		=>$sortorder,
				'created_at'	=>date('Y-m-d H:i:s'),
		);
		if( DB::table('members')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }
	#会员修改
	public function editAction() {
		$id			=	$this->get('id', 0);
		$phone			=$this->get('phone', '');
		$password		=$this->get('password', '');
		$avatar			=$this->get('avatar', '');
		$name			=$this->get('name',   '');
		$gender			=$this->get('gender',   0);
		$email			=$this->get('email', '');
		$status			=$this->get('status', 1);
		$remark			=$this->get('remark', '');
		$company		=$this->get('company', '');
		$consultant_id	=$this->get('consultant_id', '33');
		$province		=$this->get('province','');
		$city			=$this->get('city','');
		$area			=$this->get('area','');
		$address		=$this->get('address','');
		$agreement_no	=$this->get('agreement_no','');
		$expired_at		=$this->get('expired_at','');
		$position		=$this->get('position','');
		$company_scale	=$this->get('company_scale','');
		$need_wine		=$this->get('need_wine','');
		$license_url	=$this->get('license_url','');
		$parent_proxy	=$this->get('parent_proxy',0);
		$commission		=$this->get('comission',0.00);		
		$sortorder		=$this->get('sortorder',500);
		$inputs		= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:members.id','msg'=>'会员ID有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(
				'phone'			=>$phone,				
				'avatar'		=>$avatar,
				'name'			=>$name,	
				'gender'		=>$gender,
				'email'			=>$email,
				'status'		=>$status,
				'remark'		=>$remark,
				'company'		=>$company,
				'consultant_id'	=>$consultant_id,
				'province'		=>$province,
				'city'			=>$city,
				'area'			=>$area,
				'address'		=>$address,
				'agreement_no'	=>$agreement_no,
				'expired_at'	=>$expired_at,
				'position'		=>$position,
				'company_scale'	=>$company_scale,
				'need_wine'		=>$need_wine,
				'license_url'	=>$license_url,
				'parent_proxy'	=>$parent_proxy,
				'commission'	=>$commission,
				'sortorder'		=>$sortorder,
				'updated_at'	=>date('Y-m-d H:i:s'),
		);		
		if(!empty($password)){
			$rows['password']	=md5($password);
		}
		if( DB::table('members')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#会员删除
	public function delAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:members.id','fun'=>'isInt','msg'=>'会员ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if( DB::table('members')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据删除失败');	
    }
	#会员批量删除
	public function batchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('members')->delete($id);
		}
		ret(0, '操作成功');		
    }

    #客户留言
    public function notesAction() {
        $sort		=$this->get('sort', 'sortorder');
        $order		=$this->get('order','desc');
        $page		=$this->get('page',1);
        $pagesize	=$this->get('pagesize', 10);
        $offset		=($page-1)*$pagesize;
        $query		= DB::table('notes');
        $total		= $query->count();
        $totalpage	= ceil($total/$pagesize);
        $rows 		= $query->orderBy($sort,$order)->offset($offset)->limit($pagesize)->get();
        foreach ($rows as &$v){
            $v['member'] = DB::table('members')->find($v['members_id']);
        }
        ret(0, '客户留言', ['keywords'=>$keywords,'sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }
    #客户留言反馈
    public function notesReplyAction() {
        do{
            $id			= $this->get('id',  0);
            $reply		= $this->get('reply',  '');
            $inputs	= array(
                ['name'=>'id',  'value'=>$id, 'role'=>'required|exists:notes.id',	'fun'=>'isInt', 'msg'=>'用户留言ID有误'],
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
            if (DB::table('notes')->where('id','=',$id)->update(['reply'=>$reply,'updated_at'=>date('Y-m-d H:i:s')])!==FALSE) {
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'意见反馈成功.',
                );
                break;
            }
            $result	= array(
                'ret'	=>	'0',
                'msg'	=>	'意见反馈失败',
            );
        }while(FALSE);

        json($result);
    }
    #客户留言删除
    public function notesDelAction() {
        do{
            $id			= $this->get('id',  0);
            $inputs	= array(
                ['name'=>'id',  'value'=>$id, 'role'=>'required|exists:notes.id',	'fun'=>'isInt', 'msg'=>'用户留言ID有误'],
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
            if (DB::table('notes')->delete($id)!==FALSE) {
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'删除账号成功.',
                );
                break;
            }
            $result	= array(
                'ret'	=>	'0',
                'msg'	=>	'删除用户失败',
            );
        }while(FALSE);

        json($result);
    }
    #用户批量删除
    public function notesBatchDelAction() {
        $ids = $this->get('ids', []);
        DB::table('notes')->whereIn('id', $ids)->delete();
        ret(0, '操作成功');
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
			$filename = 'mem-t' . time() . '.' . $type;		
			$path	  = '/members/' . date('Ym') . '/';
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
	
	#会员等级列表
	public function rankAction() {		
		$keywords	=$this->get('keywords', '');
		$sort		=$this->get('sort', 'sortorder');
		$order		=$this->get('order','desc');
		$query		= DB::table('membersrank');
		if($keywords!==''){
			$query	=	$query	->where('name','like',"%{$keywords}%")
								->orWhere('code', 'like', "%{$keywords}%");
		}		
		$total		= $query->count();
		$rows 		= $query->orderBy($sort,$order)->get();
		ret(0, '会员等级', ['total'=>$total, 'rows'=>$rows]);
    }	
	#会员等级添加
	public function rankAddAction() {		
		$name	=	$this->get('name','');
		$code	=	$this->get('code', '');
		$min	=	$this->get('min', 0);
		$max	=	$this->get('max', 0);
		$sortorder= $this->get('sortorder', 500);
		$inputs		= array(
				['name'=>'name','value'=>$name,'role'=>'required|unique:membersrank.name','msg'=>'会员等级名称有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(
				'name'		=>	$name,
				'code'		=>	$code,
				'min'		=>	$min,
				'max'		=>	$max,
				'sortorder'	=>	$sortorder,
				'created_at'=>	date('Y-m-d H:i:s'),
		);
		if( DB::table('membersrank')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }
	#会员等级修改
	public function rankEditAction() {
		$id		=	$this->get('id', 0);
		$name	=	$this->get('name','');
		$code	=	$this->get('code', '');
		$min	=	$this->get('min', 0);
		$max	=	$this->get('max', 0);
		$sortorder= $this->get('sortorder', 500);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:membersrank.id','fun'=>'isInt','msg'=>'会员等级ID有误'],
				['name'=>'name','value'=>$name,'role'=>'required','msg'=>'会员等级名称有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}		
		$rows	=	array(
				'name'		=>	$name,
				'code'		=>	$code,
				'min'		=>	$min,
				'max'		=>	$max,
				'sortorder'	=>	$sortorder,
				'updated_at'=>	date('Y-m-d H:i:s'),
		);
		if( DB::table('membersrank')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#会员等级删除
	public function rankDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:membersrank.id','fun'=>'isInt','msg'=>'会员等级ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}		
		if( DB::table('membersrank')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	
	#会员评论列表
	public function commentAction() {
		$goods_id	=$this->get('goods_id',    0);
		$members_id	=$this->get('members_id',  0);
		$order_no	=$this->get('order_no',   '');
		$status		=$this->get('status',     -1);
		$sort		=$this->get('sort', 'sortorder');
		$rank		=$this->get('rank',		   0);
		$order		=$this->get('order','desc');
		$page		=$this->get('page',		   1);
		$pagesize	=$this->get('pagesize',	  10);
		$offset		=($page-1)*$pagesize;
		$query		=DB::table('comment');		
		if($goods_id>0){
			$query	=$query->where('goods_id','=',$goods_id);			
		}
		if($members_id>0){
			$query	=$query->where('members_id','=',$members_id);
		}
		if($order_no!=''){
			$query	=$query->where('order_no','=',$order_no);
		}
		if($status>-1){
			$query	=$query->where('status','=',$status);
		}
		if($rank>0){
			$query	=$query->where('rank','=',$rank);
		}		
		$total		= $query->count();
		$rows 		= $query->orderBy($sort,$order)->offset($offset)->limit($pagesize)->get();
		foreach ($rows as $k=>&$v){
		    $v['member'] = DB::table('members')->find($v['members_id']);
		    $v['goods']  = DB::table('goods')->find($v['goods_id']);
        }
		ret(0, '会员评论', ['total'=>$total, 'rows'=>$rows]);
    }
    public function getCommentAction() {
        $id	    =$this->get('id',    0);
        $inputs		= array(
            ['name'=>'id','value'=>$id,'role'=>'required|exists:comment.id','func'=>'isInt','msg'=>'评论ID有误'],
        );
        $result		= Validate::check($inputs);
        if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
        $rows	=DB::table('comment')->find($id);
        $rows['member'] = DB::table('members')->find($rows['members_id']);
        $rows['goods']  = DB::table('goods')->find($rows['goods_id']);
        $rows['orders'] = DB::table('orders')->where('order_no', '=', $rows['order_no'])->first();
        $rows['orders']['goods'] =json_decode($rows['orders']['goods'], TRUE);

        ret(0, '会员评论详细', $rows);
    }
	#会员评论添加
	public function commentAddAction() {		
		$goods_id	=$this->get('goods_id',    0);
		$members_id	=$this->get('members_id',  0);
		$order_no	=$this->get('order_no',   '');
		$content	=$this->get('content',    '');
		$rank		=$this->get('rank',		   1);
		$status		=$this->get('status',      1);
		$sortorder= $this->get('sortorder', 500);
		$inputs		= array(
				['name'=>'goods_id','value'=>$goods_id,'role'=>'required|exists:goods.id','func'=>'isInt','msg'=>'产品ID有误'],
				['name'=>'members_id','value'=>$members_id,'role'=>'required|exists:members.id','func'=>'isInt','msg'=>'会员ID有误'],
				['name'=>'order_no','value'=>$order_no,'role'=>'required|exists:orders.order_no','msg'=>'订单编号有误'],
				['name'=>'rank','value'=>$rank,'role'=>'required|gte:1|lte:5','func'=>'isInt','msg'=>'评分有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(
				'goods_id'		=>	$goods_id,
				'members_id'	=>	$members_id,
				'order_no'		=>	$order_no,
				'content'		=>	$content,
				'rank'			=>	$rank,
				'status'		=>	$status,
				'sortorder'	=>	$sortorder,
				'created_at'=>	date('Y-m-d H:i:s'),
		);
		if( DB::table('comment')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }
	#会员评论修改
	public function commentEditAction() {
		$id		=	$this->get('id', 0);
		$goods_id	=$this->get('goods_id',    0);
		$members_id	=$this->get('members_id',  0);
		$order_no	=$this->get('order_no',   '');
		$content	=$this->get('content',    '');
		$reply		=$this->get('reply',      '');
		$rank		=$this->get('rank',		   1);
		$status		=$this->get('status',      1);
		$sortorder	= $this->get('sortorder', 500);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:comment.id','fun'=>'isInt','msg'=>'评论ID有误'],
				['name'=>'goods_id','value'=>$goods_id,'role'=>'required|exists:goods.id','func'=>'isInt','msg'=>'产品ID有误'],
				['name'=>'members_id','value'=>$members_id,'role'=>'required|exists:members.id','func'=>'isInt','msg'=>'会员ID有误'],
				['name'=>'order_no','value'=>$order_no,'role'=>'required|exists:orders.order_no','msg'=>'订单编号有误'],
				['name'=>'rank','value'=>$rank,'role'=>'required|gte:1|lte:5','func'=>'isInt','msg'=>'评分有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}		
		$rows	=	array(
				'goods_id'		=>	$goods_id,
				'members_id'	=>	$members_id,
				'order_no'		=>	$order_no,
				'content'		=>	$content,
				'rank'			=>	$rank,
				'status'		=>	$status,
				'sortorder'		=>	$sortorder,
				'updated_at'=>	date('Y-m-d H:i:s'),
		);
		if( DB::table('comment')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
    public function commentReplyAction() {
        $id		=	$this->get('id', 0);
        $reply		=$this->get('reply',      '');
        $inputs	= array(
            ['name'=>'id','value'=>$id,'role'=>'required|exists:comment.id','fun'=>'isInt','msg'=>'评论ID有误'],
        );
        $result	= Validate::check($inputs);
        if(	!empty($result) ){ret(1, '输入参数有误.', $result);}

        if( DB::table('comment')->where('id','=',$id)->update(['reply'=>$reply])!==FALSE ){
            ret(0, '操作成功');
        }
        ret(2, '数据插入失败');
    }
    #会员评论修改
    public function commentStatusAction() {
        $id		=	$this->get('id', 0);
        $status		=$this->get('status',      1);
        $inputs	= array(
            ['name'=>'id','value'=>$id,'role'=>'required|exists:comment.id','fun'=>'isInt','msg'=>'评论ID有误'],
        );
        $result	= Validate::check($inputs);
        if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
        if( DB::table('comment')->where('id','=',$id)->update(['status'=>$status])!==FALSE ){
            ret(0, '操作成功');
        }
        ret(2, '数据插入失败');
    }
	#会员等级删除
	public function commentDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:comment.id','fun'=>'isInt','msg'=>'会员等级ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}		
		if( DB::table('comment')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#会员批量删除
	public function commentBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('comment')->delete($id);
		}
		ret(0, '操作成功');		
    }
	
}
