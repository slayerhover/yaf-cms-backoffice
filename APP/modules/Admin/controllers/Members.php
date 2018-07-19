<?php
use Illuminate\Database\Capsule\Manager as DB;

class MembersController extends BCoreController{
			
	public function membersAction(){		
		$this->_view->assign('uniqid',	 uniqid());
        $this->_view->assign('consultant', DB::table('admin')->where('roles_id','=',6)->get());
    }
	public function membersGetAction() {
		$page   =	$this->getPost('page', 1);
		$limit  =	$this->getPost('rows', 10);
		$offset	=	($page-1)*$limit;			
		$sort	=	$this->getPost('sort',  'created_at');
		$order	=	$this->getPost('order', 'desc');
		$keywords=	$this->getPost('keywords', '');

		$query	= new membersModel;
		if($keywords!=''){
			$query	=	$query->where(function ($query) use($keywords) {
										$query->where('members.name','like',"%{$keywords}%")
											  ->orWhere('members.phone','like',"%{$keywords}%");
									});
		}
        if($this->auth->role==6){
            $query	=	$query->where('consultant_id','=',$this->auth->user_id);
        }else{
            $clientmanager_id	=$this->get('consultant_id', 0);
            if($clientmanager_id>0)	$query	=	$query->where('consultant_id','=',$clientmanager_id);
        }

        $start_on=$this->get('start_on', '');
        if(!empty($start_on)){$query=	$query->where('created_at','>=',$start_on);}
        $end_on	=$this->get('end_on', '');
        if(!empty($end_on)){$query	=	$query->where('created_at','<=',$end_on);}

		$total		= $query->count();
		$rows 		= $query->offset($offset)
							->limit($limit)
                            ->orderBy($sort, $order)
							->get()->toArray();
		json(['total'=>$total, 'rows'=>$rows]);		
    }	
	public function membersaddAction(){    
		return TRUE;
    }
	public function membersincreaseAction(){
		do{
			if( $this->method!='POST' ){
				$result	= array(
							'code'	=>	'300',
							'msg'	=>	'操作失败',										
						);
				break;
			}						
			$phone			= $this->getPost('phone', '');
			$password		= $this->getPost('password', '');
			$name			= $this->getPost('name', '');
			$openid			= $this->getPost('openid', '');
			$email			= $this->getPost('email', '');						
			$sortorder		= $this->getPost('sortorder', '');
			$status			= $this->getPost('status', 		0);		
			$file			= $this->getPost('images',[])[0];
			if( empty($phone) ){
				$result	= array(
							'code'	=>	'300',
							'msg'		=>	'手机号码不能为空',
						);
				break;
			}
			$rows	=	array(
							'phone'			=>	$phone,
							'password'		=>	md5($password),
							'name'			=>	$name,
							'openid'		=>	$openid,
							'email'			=>	$email,
							'sortorder'		=>	$sortorder,
							'status'		=>	$status,
							'avatar'		=>	$file,
							'created_at'	=>	date('Y-m-d H:i:s'),
			);
			if( DB::table('members')->insert($rows) ){
				$result	= array(
							'code'		=>	'200',
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
	public function memberseditAction(){    
		$id			= $this->get('id', 0);
		$dataset  	= DB::table('members')->find($id);
		$this->_view->assign('dataset', $dataset);
        $this->_view->assign('consultant', DB::table('admin')->where('roles_id','=',6)->get());
    }	
    public function membersupdateAction(){
		do{
			if( $this->method!='POST' ){
				$result	= array(
							'code'	=>	'300',
							'msg'	=>	'操作失败',										
						);
				break;
			}
			$id				= $this->getPost('id', '');
			$phone			= $this->getPost('phone', '');
			$password		= $this->getPost('password', '');
			$name			= $this->getPost('name', '');
			$gender         = $this->getPost('gender', 1);
			$birthday       = $this->getPost('birthday', '');
			$email			= $this->getPost('email', '');
            $consultant_id  = $this->getPost('consultant_id', 0);
			$sortorder		= $this->getPost('sortorder', '');
			$status			= $this->getPost('status', 		0);		
			$avatar			= $this->getPost('images', [])[0];
			if( empty($id) || empty($phone) ){
				$result	= array(
							'code'	=>	'300',
							'msg'		=>	'手机号码或ID不能为空',
						);
				break;
			}
			$rows	=	array(				
							'phone'			=>	$phone,							
							'name'			=>	$name,
							'gender'        =>  $gender,
							'birthday'      =>  $birthday,
							'email'			=>	$email,
							'consultant_id' =>  $consultant_id,
							'sortorder'		=>	$sortorder,
							'status'		=>	$status,
							'avatar'		=>	$avatar,
							'updated_at'	=>	date('Y-m-d H:i:s'),
			);			
			if(!empty($password)){	$rows['password'] =	md5($password); }
			if( DB::table('members')->where('id','=',$id)->update($rows)!==FALSE ){				
				$result	= array(
							'code'		=>	'200',
							'msg'		=>	'操作成功',	
						);
			}else{
				$result	= array(
							'code'	=>	'300',
							'msg'	=>	'数据更新失败',	
						);
			}			
		}while(FALSE);
    			
		die(json_encode($result));
    }
	public function membersRecycleAction(){	
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
			$status	=	DB::table('members')->find($id)['status'];
			$rows	=	array(
							'status'	=> ($status>0)?0:1, 
							'deleted_at'=> ($status>0)?date('Y-m-d H:i:s'):'0000-00-00 00:00:00', 
			);			
			if(DB::table('members')->where('id','=',$id)->update($rows)){
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
    public function membersdeleteAction(){	
		do{
			if($this->method!='POST'){
				$result	= array(
							'code'		=>	'300',
							'msg'		=>	'操作失败',										
						);
				break;				
			}
			$id	= $this->get('id', []);
			if( empty($id) ){
				$result	= array(
							'code'		=>	'300',
							'msg'		=>	'参数为空',
						);
				break;
			}
			if(is_array($id)){
                if (DB::table('members')->whereIn('id', $id)->delete()) {
                    $result = [
                        'code' => '200',
                        'msg'  => '操作成功',
                    ];
                    break;
                }
            }elseif(is_integer($id)) {
                    if (DB::table('members')->delete($id)) {
                        $result = [
                            'code' => '200',
                            'msg'  => '操作成功',
                        ];
                    }
                    break;
            }
            $result = [
                'code' => '300',
                'msg'  => '删除失败',

            ];
        } while(FALSE);
		
		die(json_encode($result));    	
    }

    public function setConsultantAction() {
	    do {
            $memberIds = $this->get('memberIds', []);
            $consultantId = $this->get('consultantId', 0);
            $inputs = [
                ['name' => 'consultantId', 'value' => $consultantId, 'role' => 'required|exists:admin.id|gt:0', 'msg' => '客服经理'],
            ];
            $result = Validate::check($inputs);
            if (!empty($result)) {
                $result = [
                    'code' => '300',
                    'msg'  => $result,
                ];
                break;
            }
            if(DB::table('members')->whereIn('id', $memberIds)->update(['consultant_id'=>$consultantId])!==FALSE){
                $result = [
                    'code' => '200',
                    'msg'  => '操作成功',
                ];
                break;
            }
            $result = [
                'code' => '300',
                'msg'  => '操作失败',
            ];
        }while(FALSE);

        die(json_encode($result));
    }

    #客户经理
    public function clientManagerAction(){
        $this->_view->assign('uniqid',	 uniqid());
    }
    public function clientManagerGetAction() {
        $page   =	$this->getPost('page', 1);
        $limit  =	$this->getPost('rows', 10);
        $offset	=	($page-1)*$limit;
        $sort	=	$this->getPost('sort',  'created_at');
        $order	=	$this->getPost('order', 'desc');
        $keywords=	$this->getPost('keywords', '');

        $page   =	$this->getPost('page',  1);
        $sort	=	$this->getPost('sort',  'sortorder');
        $order	=	$this->getPost('order', 'desc');
        $keywords	= $this->getPost('keywords', '');
        $query		= DB::table('admin')->where('roles_id', '=', 6);
        if($keywords!=''){
            $query	=	$query->where(function ($query) use($keywords) {
                $query->where('name','like',"%{$keywords}%")
                    ->orWhere('phone','like',"%{$keywords}%");
            });
        }
        $total		= $query->count();
        $rows 		= $query->offset($offset)
                            ->limit($limit)
                            ->orderBy($sort, $order)
                            ->get();
        json(['total'=>$total, 'rows'=>$rows]);
    }
    public function clientManagerAddAction(){
        $this->_view->assign('uniqid',	 uniqid());
    }
    public function clientManagerIncreaseAction(){
        do{
            if( $this->method!='POST' ){
                $result	= array(
                    'code'=>	'300',
                    'msg'	=>	'操作失败',
                );
                break;
            }
            $rows = $this->formData;			
			$rows['avatar']		=	$this->getPost('images', [])[0];
			unset($rows['images']);
            $rows['roles_id']   =   6;
            $rows['created_at'] =	date('Y-m-d H:i:s');
            if( DB::table('admin')->insert($rows) ){
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
    public	function clientManagerEditAction(){
        $id	= $this->get('id', 0);
        $dataset  	= DB::table('admin')->where('roles_id', '=', 6)->find($id);
        $this->_view->assign('dataset', $dataset);
        $this->_view->assign('uniqid',	 uniqid());
    }
    public function clientManagerUpdateAction(){
        do{
            if( $this->method!='POST' ){
                $result	= array(
                    'code'	=>	'300',
                    'msg'		=>	'操作失败',
                );
                break;
            }
            $rows = $this->formData;
			$rows['avatar']		=	$this->getPost('images', [])[0];
			unset($rows['images']);
            $rows['updated_at'] =	date('Y-m-d H:i:s');
            if( DB::table('admin')->where('id','=',$rows['id'])->where('roles_id', '=', 6)->update($rows)!==FALSE ){
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
	public function clientManagerRecycleAction(){	
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
			$status	=	DB::table('admin')->find($id)['status'];
			$rows	=	array(
							'status'	=> ($status>0)?0:1, 
							'deleted_at'=> ($status>0)?date('Y-m-d H:i:s'):'0000-00-00 00:00:00', 
			);			
			if(DB::table('members')->where('id','=',$id)->update($rows)){
				$result		= array(
							'code'		=>	'200',
							'msg'		=>	'操作成功',
							);						
			}else{
				$result		= array(
							'code'	=>	'300',
							'msg'		=>	'更新失败',
							);
			}
		}while(FALSE);	
		
		die(json_encode($result));    	
    }
    public function clientManagerDeleteAction(){
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
            if(DB::table('admin')->where('roles_id', '=', 6)->delete($id)){
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
