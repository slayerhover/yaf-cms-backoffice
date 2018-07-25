<?php
use Illuminate\Database\Capsule\Manager as DB;

class AuthsController extends CoreController {
	private $user;
	/**
	 * 初始化验证 *
	 **/
	public function init(){
        Yaf_Dispatcher::getInstance()->disableView();
		parent::init();        
		$token = $this->get('token', '');
		$this->user = Cache::get('auth_'.$token);
	}
	
	/**
	 *接口名称	用户中心首页
	 *接口地址	http://api.com/user/index/
	 *接口说明	显示欢迎页图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   图片地址组
	 **/
	public function indexAction(){
		do{
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'用户中心',
							'data'	=>	$this->user,
			);
		}while(FALSE);
		
		json($result);
	}
	
	public function usersAction() {
		do{	
			$keywords	=$this->get('keywords', '');
			$rows		=DB::table('admin');
			if(!empty($keywords)){
				$rows	=$rows->where('username','like',"%{$keywords}%")
								->orWhere('phone','like',"%{$keywords}%");
			};
			$rows	= $rows->get();
			foreach($rows as $k=>$v) unset($rows[$k]['password']);			
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'管理员账号.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}

	public function usersAddAction() {
		do{
			$username	= $this->get('username',	'');
			$phone		= $this->get('phone', 		'');
			$password	= $this->get('password', 	'');
			$repassword = $this->get('repassword',	'');
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
							'status'		=>	1,
							'created_at'	=>	date('Y-m-d H:i:s'),
			);
			$lastId = DB::table('admin')->insertGetId($rows);
			if ($lastId) {												
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'添加账号成功.',
							'data'	=>	array(
											'id'		=>	$lastId,
											'username'		=>	$username,
											'password'		=>	$password,
											'status'		=>	1,											
										)
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

	public function usersEditAction() {
		do{
			$id			= $this->get('id',  0);
			$username	= $this->get('username',	'');
			$phone		= $this->get('phone', 		'');
			$password	= $this->get('password', 	'');
			$repassword = $this->get('repassword',	'');
			$status		= $this->get('status',		 1);	
			$inputs	= array(
					['name'=>'id',  'value'=>$id, 'role'=>'required|exists:admin.id',	'fun'=>'isInt', 'msg'=>'用户ID格式有误'],
					['name'=>'username',  'value'=>$username, 'role'=>'required|unique:admin.username',	'fun'=>'isUsername', 'msg'=>'用户名格式有误'],
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
							'status'		=>	$status,
							'updated_at'	=>	date('Y-m-d H:i:s'),
			);
			
			if( $password!='' )	$rows['password']=md5($password);
			if (DB::table('admin')->where('id','=',$id)->update($rows)!==FALSE) {
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'编辑账号成功.',
							'data'	=>	array(
											'id'		=>	$id,
											'username'		=>	$username,
											'phone'			=>	$phone,											
											'status'		=>	$status,
										)
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
	
	public function usersDelAction() {
		do{			
			$id			= $this->get('id',  0);
			$inputs	= array(
					['name'=>'id',  'value'=>$id, 'role'=>'required|exists:admin.id',	'fun'=>'isInt', 'msg'=>'用户ID有误'],			
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
	public function usersBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('admin')->delete($id);
		}
		ret(0, '操作成功');	
    }
	
	#权限管理
	public function authsAction() {
		do{	
			$keywords	=$this->get('keywords', '');
			$level		=$this->get('level',	 2);
			$rows		=DB::table('auths');
			if(!empty($keywords)){
				$rows	=$rows->where('authname','like',"%{$keywords}%");
			}			
			if($level<2){
				$rows	=$rows->where('up','=',0)->get();
			}else{
				$rows	=$rows->get();
				foreach($rows	as	$k=>$v){
					$rows[$k]['children']	=	DB::table('auths')->where('up','=',$v['id'])
															  ->orderBy($sort,$order)
															  ->get();
				}
			}			
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'权限列表.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}
	
	public function getctlsAction(){		
		/***获取所有控制器BOF***/
		$controllerdir	=	Yaf_Registry::get('config')['application']['directory'].'/controllers';
		$filenames		=	scandir($controllerdir);
		$controllers	=	array();
		foreach($filenames	as	$v){			
			if(!is_dir($v)){
				$controllers[]	=	substr($v, 0, -4);				
			}
		}
		/***获取所有控制器EOF***/
		
		ret(0, '控制器列表', $controllers);
    }
	public function getactsAction(){
		do{
			$controller = ucfirst($this->get('controller', ''));
			if( empty($controller) ){
				$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'控制器名不能为空',
						);
				break;
			}			
			require_once(Yaf_Registry::get('config')['application']['directory'].'/controllers/'.$controller.'.'.Yaf_Registry::get('config')['application']['ext']);			
			$rows = [];
			foreach(get_class_methods($controller.'Controller') as $k=>$v){
				if(strstr($v, 'Action')){ $rows[] = substr($v, 0, -6); }  
			}
			$result	= array(
					'ret'	=>	'0',
					'msg'	=>	'控制器方法列表',
					'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}

	public function authsAddAction() {
		do{
			$up			=	$this->getPost('up', 			 0);
			$authname	=	$this->getPost('authname', 		'');
			$controller	=	$this->getPost('controller',    '');
			$action		=	$this->getPost('action',    	[]);
			$sortorder	=	$this->get('sortorder',			500);
			if( empty($authname) ){
				$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'权限名不能为空',
						);
				break;
			}			
			$rows	=	array(					
					'authname'		=>	$authname,
					'up'			=>	$up,
					'controller'	=>	$controller,
					'action'		=>	implode(',', $action),
					'sortorder'		=>	$sortorder,
					'created_at'	=>	date("Y-m-d H:i:s"),
			);
			if( DB::table('auths')->insert($rows)){
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'操作成功',	
					);
			}else{
					$result	= array(
							'ret'	=>	'2',
							'msg'	=>	'添加权限组失败,请多试几下',
					);
			}
		}while(FALSE);
		
		json($result);
	}

	public function authsEditAction() {
		do{
			$id			=	$this->get('id', 		'');
			$up			=	$this->getPost('up', 			 0);
			$authname	=	$this->getPost('authname', 		'');
			$controller	=	$this->getPost('controller',    '');
			$action		=	$this->getPost('action',    	[]);
			$sortorder	=	$this->get('sortorder',			500);
			if( empty($id)||empty($authname) ){
				$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'ID或权限名不能为空',
						);
				break;
			}			
			$rows	=	array(					
					'authname'		=>	$authname,
					'up'			=>	$up,
					'controller'	=>	$controller,
					'action'		=>	implode(',', $action),
					'sortorder'		=>	$sortorder,
					'updated_at'	=>	date("Y-m-d H:i:s"),
			);
			if( DB::table('auths')->where('id','=',$id)->update($rows)){
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'操作成功',	
					);
			}else{
					$result	= array(
							'ret'	=>	'2',
							'msg'	=>	'更新权限组失败,请多试几下',
					);
			}			
		}while(FALSE);
		
		json($result);	
	}
	
	public function authsDelAction() {
		do{			
			$id			= $this->get('id',  0);
			$inputs	= array(
					['name'=>'id',  'value'=>$id, 'role'=>'required|exists:auths.id',	'fun'=>'isInt', 'msg'=>'权限ID有误'],			
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
			if (DB::table('auths')->delete($id)!==FALSE) {												
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'删除权限成功.',
					);
					break;
			}
			$result	= array(
					'ret'	=>	'0',
					'msg'	=>	'删除权限失败',
			);
		}while(FALSE);
		
		json($result);	
	}
	
	#用户批量删除
	public function authsBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('auths')->delete($id);
		}
		ret(0, '操作成功');	
    }
	
	#角色列表
	public function rolesAction() {
		$page   =	$this->getPost('page', 1);
		$limit  =	$this->getPost('pagesize', 10);
		$offset	=	($page-1)*$limit;			
		$sort	=	$this->getPost('sort',  'id');
		$order	=	$this->getPost('order', 'asc');
		$keywords	= trim($this->getPost('keywords', ''));		
		$query		= DB::table('roles');
		if($keywords!==''){
			$query	=	$query	->where('rolename','like',"%{$keywords}%");
		}		
		$total		= $query->count();
		$rows 		= $query->orderBy($sort,$order)
							->offset($offset)
							->limit($limit)							
							->get();
		
		ret(0, '角色列表', ['total'=>$total, 'rows'=>$rows]);
    }
	public function rolesAddAction(){
		do{
			$rolename	=	$this->get('rolename', 	 '');
			$auths		=	$this->get('auths', 	 []);
			$sortorder	=	$this->get('sortorder',  500);
			if( empty($rolename)||empty($auths) ){
				$result	= array(
							'ret'	=>	'3',
							'msg'		=>	'角色名或权限列表不能为空',
				);
				break;
			}
			$auth_names = [];
			foreach($auths as $k=>$v){
					$auth = DB::table('auths')->find($v);
					if($auth['up']>0){
						$auth_names[] = DB::table('auths')->find($auth['up'])['authname'];
					}
					$auth_names[] = $auth['authname'];
			}
			$auth_names = array_unique($auth_names);
			$rows		= array(
					'rolename'		=>	$rolename,
					'auth_ids'		=>	implode(',', $auths),
					'auth_names'	=>	implode(',', $auth_names),
					'sortorder'		=>	$sortorder,
					'created_at'	=>	date("Y-m-d H:i:s"),
			);
			if( DB::table('roles')->insert($rows)){
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'操作成功',	
					);
			}else{
					$result	= array(
							'ret'	=>	'3',
							'msg'	=>	'添加角色失败,请多试几下',
					);
			}			
		}while(FALSE);
		
		json($result);
    }
	public function rolesGetAction(){
		$id	= $this->get('id' , NULL);
		if($id==NULL) return FALSE;
     	$dataset= DB::table('roles')->find(intval($id));
		$auths	= explode(',', $dataset['auth_ids']);
		
		/***获取所有权限BOF***/
		$query		= DB::table('auths')->where('up','=',0);
		$rows 		= $query->orderBy('sortorder','desc')->get();
		foreach($rows	as	$k=>$v){				
				$rows[$k]['children']	=	DB::table('auths')->where('up','=',$v['id'])
															  ->orderBy('sortorder','desc')
															  ->get();
				foreach($rows[$k]['children']	as	$k1=>&$v1){
					if(in_array($v1['id'], $auths)){
						$v1['flag'] = 1;
					}else{
						$v1['flag'] = 0;
					}
				}
		}
		/***获取所有控制器EOF***/
		ret(0, '当前角色权限列表', ['auths'=>$rows, 'roles'=>$dataset]);
    }
	
    public function rolesEditAction(){
		do{
			$id			=	$this->get('id', 		 '');
			$rolename	=	$this->get('rolename', 	 '');
			$auths		=	$this->get('auths', 	 []);
			$sortorder	=	$this->get('sortorder',  500);
			if( empty($rolename)||empty($auths) ){
				$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'ID,角色名或权限列表不能为空',
				);
				break;
			}
			$auth_names = [];
			foreach($auths as $k=>$v){
					$auth = DB::table('auths')->find($v);
					if($auth['up']>0){
						$auth_names[] = DB::table('auths')->find($auth['up'])['authname'];
					}
					$auth_names[] = $auth['authname'];
			}
			$auth_names = array_unique($auth_names);
			$rows		= array(
					'rolename'		=>	$rolename,
					'auth_ids'		=>	implode(',', $auths),
					'auth_names'	=>	implode(',', $auth_names),
					'sortorder'		=>	$sortorder,
					'created_at'	=>	date("Y-m-d H:i:s"),
			);
			if( DB::table('roles')->where('id','=',$id)->update($rows)){
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'操作成功',	
					);
			}else{
					$result	= array(
							'ret'	=>	'3',
							'msg'	=>	'添加角色失败,请多试几下',
					);
			}			
		}while(FALSE);
		
		json($result);
    }
	public function rolesDelAction(){	
		do{
			$id	= $this->get('id', '');
			if( empty($id) ){
				$result	= array(
							'ret'	=>	'1',
							'msg'		=>	'参数为空',
						);
				break;
			}
			$rows	=	DB::table('roles')->find($id);
			if($rows['rolename']=='系统管理员'||$rows['rolename']=='EVERYONE'){
				$result	= array(
							'ret'	=>	'2',
							'msg'	=>	'系统权限组不能删除.',
						);
				break;
			}			
			if(DB::table('roles')->delete($id)){
				$result		= array(
							'ret'	=>	'0',
							'msg'		=>	'操作成功',
							);						
			}else{
				$result		= array(
							'ret'	=>	'3',
							'msg'		=>	'删除失败',
							);
			}
		}while(FALSE);	
		
		json($result);
    }
	
	
}
