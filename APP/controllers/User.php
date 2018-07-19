<?php
use Illuminate\Database\Capsule\Manager as DB;

class UserController extends CoreController {
	private $user;
	/**
	 * 初始化验证 *
	 **/
	public function init(){
        Yaf_Dispatcher::getInstance()->disableView();
		parent::init();
		$token = $this->get('token', '');
		$this->user = Cache::getInstance()->get('auth_'.$token);
		if(empty($this->user)){
			ret(1, '请先登陆');
		}
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
	public function homeAction(){
		do{
			$result	=	array(
							'ret'	=>	'0',
							'msg'	=>	'用户中心',
							'data'	=>	(new membersModel)->getUser($this->user['id']),
			);
		}while(FALSE);
		
		json($result);
	}

    /**
     * 新留言
     */
    public function notesAddAction(){
        do{
            $contents    =  trim($this->get('contents', ''));
			$images	     =  trim($this->get('images', ''));
			$phone 		 =  trim($this->get('phone', ''));
            $inputs	= array(
                ['name'=>'contents', 'value'=>$contents,	'role'=>'required', 'msg'=>'内容不能为空'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }
            $rows	=	array(
                'members_id'	=>	$this->user['id'],
                'contents'		=>	$contents,
				'images'		=>	$images,
				'phone'			=>	$phone,
                'status'        =>  0,
                'created_at'	=>	date('Y-m-d H:i:s'),
            );
            if( DB::table('notes')->insert($rows)!==FALSE ){
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'提交留言成功.',
                    'data'	=>	$rows,
                );
            }else{
                $result	= array(
                    'ret'	=>	'2',
                    'msg'	=>	'提交新收货地址失败，请重试.',
                );
            }
        }while(FALSE);

        json($result);
    }
    /**
     * 我的留言
     *
     */
    public function myNotesAction(){
        do{
            $page		=$this->get('page',1);
            $pagesize	=$this->get('pagesize', 10);
            $offset		=($page-1)*$pagesize;
            $rows	    = DB::table('notes')->where('members_id','=',$this->user['id']);
            $total		= $rows->count();
            $totalpage	= ceil($total/$pagesize);
            $rows =$rows->orderBy('created_at', 'desc')->offset($offset)->limit($pagesize)->get();
            foreach ($rows as &$v){
                $v['images'] = empty($v['images']) ? [] : explode(',', $v['images']);
            }
            $result	= array(
                'ret'	=>	'0',
                'msg'	=>	'我的留言.',
                'data'	=>	array(
                    'page'      => $page,
                    'pagesize'  => $pagesize,
                    'total'     => $total,
                    'totalpage' => $totalpage,
                    'rows'      => $rows,
                ),
            );
        }while(FALSE);

        json($result);
    }

    /**
     * 我的浏览记录
     *
     */
    public function myRecordsAction(){
        do{
            $page		=$this->get('page',1);
            $pagesize	=$this->get('pagesize', 10);
            $offset		=($page-1)*$pagesize;
            $rows	    = DB::table('records')->where('members_id','=',$this->user['id']);
            $total		= $rows->count();
            $totalpage	= ceil($total/$pagesize);
            $rows =$rows->orderBy('created_at', 'desc')->offset($offset)->limit($pagesize)->get();
            foreach ($rows as $k=>&$v){
                $v['goods'] = DB::table('goods')->find($v['goods_id']);
            }
            $result	= array(
                'ret'	=>	'0',
                'msg'	=>	'我的浏览记录.',
                'data'	=>	array(
                    'page'      => $page,
                    'pagesize'  => $pagesize,
                    'total'     => $total,
                    'totalpage' => $totalpage,
                    'rows'      => $rows,
                ),
            );
        }while(FALSE);

        json($result);
    }

    /**
     * 清空浏览记录
     *
     */
    public function myRecordsClearAction(){
        do{
            $rows	    = DB::table('records')->where('members_id','=',$this->user['id'])->delete();
            $result	= array(
                'ret'	=>	'0',
                'msg'	=>	'清空浏览记录成功.',
            );
        }while(FALSE);

        json($result);
    }


    /**
	 *接口名称	邀请二维码
	 *接口地址	http://api.com/public/qrcode/
	 *接口说明	显示二维码图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 **/
	public function myQrcodeAction(){
		do{
			$inviteUrl=urlencode("http://putuan.zy52.cn/index/intention/inviter_id/" . $this->user['id']);
			$result	= array(
					'ret'	=>	'0',
					'msg'	=>	'邀请会员二维码',
					'data'	=>	array(
						'url'	=>	'http://putuan.zy52.cn/index/qrcode/data/' . $inviteUrl,
					),
			);
		}while(FALSE);
		
		json($result);
	}
			
	/**
	 * 关联个推账号CID
	 **/
	public function setGetuiCidAction(){
		do{	
			$cid       = $this->get('cid', '');
			if(empty($cid)){
				$result	=	array(
							'ret'	=>	'1',
							'msg'	=>	'CID参数不能为空',
						);
				break;
			}			
			if (DB::table('members')->where('id','=',$this->user['id'])->update(['getui_cid'=>$cid])===FALSE) {
				$result	= array(
					'ret'	=>	'2',
					'msg'	=>	'关联用户个推CID失败.',
				);				
			}else{
				$result	= array(
					'ret'	=>	'0',
					'msg'	=>	'关联用户个推CID成功.',
					'data'	=>	array(
									'cid'	=>	$cid,
					),
				);
			}
		}while(FALSE);

		json($result);
	}
	
	public function sendTestAction(){
		$result = Getui::send('a81e7a6aa9848642d1fba96768259d7f', '收到新的询价单', '有新的询价单等待报价,请注意查收');
		dump($result);
	}
		
	/**
	 *接口名称	完善信息
	 *接口地址	http://api.com/user/consummate/
	 *接口说明	新注册用户完善个人信息
	 *参数 @param
	 * @realname 	姓名
	 * @card_id 	身份证号
	 * @email		邮箱
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function consummateAction(){
		do{			
			$name       = $this->get('name',    '');
			$gender     = $this->get('gender', '0');
            $email	    = $this->get('email',   '');
			$birthday  	= $this->get('birthday','');
			$company	= $this->get('company', '');
			$company_scale= $this->get('company_scale', '');
			$position	= $this->get('position', '');
			$need_wine	= $this->get('need_wine', '');
			$license_url= $this->get('license_url', '');
			$province	= $this->get('province', '');
			$city		= $this->get('city', '');
			$area		= $this->get('area', '');
			$address	= $this->get('address', '');			
            $inputs	= array(
                ['name'=>'name', 	'value'=>$name,	 'fun'=>'isChinese', 'msg'=>'姓名格式有误'],     
				['name'=>'email',	'value'=>$email, 'fun'=>'isEmail','msg'=>'邮箱格式有误'],
			);
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }			
			$rows =	array(				
						'name'		=>	$name,
						'gender'	=>	$gender,
						'email'		=>	$email,
						'birthday'	=>	$birthday,
						'company'	=>	$company,
						'company_scale'=>$company_scale,
						'position'	=>	$position,
						'need_wine'	=>	$need_wine,
						'province'	=>	$province,
						'city'		=>	$city,
						'area'		=>	$area,
						'address'	=>	$address,
			);
			if(!empty($license_url)&&$image=$this->uploader($license_url)){
				$rows['license_url'] = $image;
			}
			if (DB::table('members')->where('id','=', $this->user['id'])->update($rows)===FALSE) {
						$result	= array(
								'ret'	=>	'2',
								'msg'	=>	'更新用户信息失败.',
						);
			}else{						
						$result	= array(
								'ret'	=>	'0',
								'msg'	=>	'用户信息更新成功.',
								'data'	=>	(new membersModel)->getUser($this->user['id']),
						);
			}
		}while(FALSE);

		json($result);
	}
			
	public function uploadlogoAction(){
		do{
			$files	= $this->get('logo', '');
			if(empty($files)){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'图片内容为空',
						);
					break;
			}
			if ($image=$this->uploader($files)){				
				if (DB::table('members')->where('id','=',$this->user['id'])->update(['avatar'=>$image])!==FALSE) {
					$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'用户头像更新成功.',
						'data'	=>	array(
										'avatar'	=>	$image,
									),
					);
				}else{
					$result	= array(
							'ret'	=>	'5',
							'msg'	=>	'图片更新到数据库出错.',
					);
				}					
			}else{
				$result	= array(
								'ret'	=>	'4',
								'msg'	=>	'上传图片解析有误.',
				);
			}					
		}while(FALSE);

		json($result);
	}
		
	/**
	  * 收货地址列表
	  *
	  */
	public function shippingaddrAction(){
		do{
			$rows = DB::table('shippingaddr')->where('members_id','=',$this->user['id'])->get();			
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'收货地址列表.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}	
	/**
	  * 新收货地址
	  */
	public function shippingaddrAddAction(){
		do{	
			$name  		=  trim($this->get('name', ''));
			$phone    	=  trim($this->get('phone', ''));
			$province  	=  trim($this->get('province', ''));
			$city    	=  trim($this->get('city', ''));
			$area    	=  trim($this->get('area', ''));
			$address    =  trim($this->get('address', ''));
			$flag		=  intval($this->get('flag',  0));			
			$inputs	= array(
                ['name'=>'name', 	'value'=>$name, 'role'=>'required',	 'fun'=>'isName','msg'=>'姓名'],
                ['name'=>'phone', 	'value'=>$phone, 'role'=>'required', 'fun'=>'isPhone',   'msg'=>'手机号码'],
                ['name'=>'address', 	'value'=>$address, 'role'=>'required', 'msg'=>'收件地址'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }
			$rows	=	array(
				'members_id'	=>	$this->user['id'],
				'name'			=>	$name,
				'phone'			=>	$phone,
				'province'		=>	$province,
				'city'			=>	$city,
				'area'			=>	$area,
				'address'		=>	$address,
				'created_at'	=>	date('Y-m-d H:i:s'),
			);
            if( DB::table('shippingaddr')->where('members_id','=',$this->user['id'])->count()==0 ){
                $rows['flag'] = 1;
            }
			if($addressId=DB::table('shippingaddr')->insertGetId($rows)){			
				$rows['id'] = $addressId;
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'提交新收货地址成功.',
						'data'	=>	$rows,
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'提交新收货地址失败，请重试.',
						'data'	=>	$rows,
				);
			}
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 修改收货地址
	  *
	  */
	public function shippingaddrEditAction(){
		do{
			$id  		=  trim($this->get('shippingaddr_id',   0));			
			$name  		=  trim($this->get('name', ''));
			$phone    	=  trim($this->get('phone', ''));
			$province  	=  trim($this->get('province', ''));
			$city    	=  trim($this->get('city', ''));
			$area    	=  trim($this->get('area', ''));
			$address    =  trim($this->get('address', ''));
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:shippingaddr.id|gt:0', 'msg'=>'收件地址ID'],
                ['name'=>'name', 	'value'=>$name,	 'fun'=>'isName','msg'=>'姓名'],
                ['name'=>'phone', 	'value'=>$phone, 'fun'=>'isPhone',   'msg'=>'手机号码'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }
			$rows	=	array(
				'name'			=>	$name,
				'phone'			=>	$phone,
				'province'		=>	$province,
				'city'			=>	$city,
				'area'			=>	$area,
				'address'		=>	$address,
				'updated_at'	=>	date('Y-m-d H:i:s'),
			);			
			if(DB::table('shippingaddr')->where('id','=',$id)->update($rows)){
				$rows['id'] = $id;
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'更新收货地址成功.',
						'data'	=>	$rows,
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'更新收货地址失败，请重试.',
						'data'	=>	$rows,
				);
			}
		}while(FALSE);		
		json($result);
	}
	/**
	  * 设置默认收货地址
	  *
	  */
	public function setDefaultShippingAddrAction(){
		do{			
			$id  		=  trim($this->get('shippingaddr_id',   0));			
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:shippingaddr.id|gt:0', 'msg'=>'ID格式有误'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }	
			DB::table('shippingaddr')->where('id','<>',$id)->where('members_id','=',$this->user['id'])->update(['flag'=>0]);			
			if(DB::table('shippingaddr')->where('id','=',$id)->update(['flag'=>1])!==FALSE){
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'设置默认收货地址成功.',
						'data'	=>	DB::table('shippingaddr')->find($id),
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'设置默认收货地址失败，请重试.',
				);
			}
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 获取收货地址列表
	  *
	  */
	public function getDefaultShippingAddrAction(){
		do{
			$rows = DB::table('shippingaddr')->where('members_id','=',$this->user['id'])->where('flag','=',1)->first();			
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'获取默认收货地址.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 删除收货地址
	  *
	  */
	public function shippingaddrDelAction(){
		do{	
			$id  		=  trim($this->get('shippingaddr_id',   0));			
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:shippingaddr.id|gt:0', 'msg'=>'ID格式有误'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }			
			if(DB::table('shippingaddr')->where('members_id','=',$this->user['id'])->where('id','=',$id)->delete()){
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'删除收货地址成功.',
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'更新收货地址失败，请重试.',
				);
			}
		}while(FALSE);
		
		json($result);
	}
	
	/**
	  * 为订单选择收货地址
	  *
	  */
	public function setOrdersShippingAddrAction(){
		do{
			$order_no  	 	=  trim($this->get('order_no', ''));
			$shippingaddr_id=  intval($this->get('shippingaddr_id', 0));
			$inputs	= array(
				['name'=>'order_no','value'=>$order_no,'role'=>'exists:orders.order_no', 'msg'=>'订单编号'],
				['name'=>'shippingaddr_id','value'=>$shippingaddr_id,'fun'=>'isInt','role'=>'exists:shippingaddr.id|gt:0', 'msg'=>'收件地址ID'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }			
			if( DB::table('orders')->where('order_no','=',$order_no)->update(['shippingaddr_id'=>$shippingaddr_id])!==FALSE ){
				$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'设置收货地址成功.',
							'data'	=>	[
											'order_no'			=>$order_no,
											'shippingaddr_id'	=>$shippingaddr_id,
							],
				);
			}else{
				$result	= array(
							'ret'	=>	'2',
							'msg'	=>	'设置收货地址失败.',
				);
			}
		}while(FALSE);
		
		json($result);
	}
	
	/**
	  * 银行卡列表
	  *
	  */
	public function bankcardAction(){
		do{
			$rows = DB::table('bankcard')->where('members_id','=',$this->user['id'])->get();			
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'银行卡列表.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}	
	/**
	  * 新银行卡
	  */
	public function bankcardAddAction(){
		do{	
			$bank		=  trim($this->get('bank', ''));
			$name  		=  trim($this->get('name', ''));
			$card    	=  trim($this->get('card', ''));			
			$flag		=  intval($this->get('flag',  0));			
			$inputs	= array(
                ['name'=>'name', 	'value'=>$name,	 'fun'=>'isName','msg'=>'姓名'],
				['name'=>'bank', 	'value'=>$bank,	 'role'=>'required','msg'=>'银行名'],
				['name'=>'card', 	'value'=>$card,	 'role'=>'required','msg'=>'卡号'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }
			$rows	=	array(
				'members_id'	=>	$this->user['id'],
				'bank'			=>	$bank,
				'name'			=>	$name,
				'card'			=>	$card,				
				'flag'			=>	$flag,
				'created_at'	=>	date('Y-m-d H:i:s'),
			);
			if($flag==1){
				DB::table('bankcard')->where('members_id','=',$this->user['id'])->update(['flag'=>0]);
			}
			if($bankcardId=DB::table('bankcard')->insertGetId($rows)){			
				$rows['id'] = $bankcardId;
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'提交新银行卡成功.',
						'data'	=>	$rows,
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'提交新银行卡失败，请重试.',
				);
			}
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 修改银行卡
	  *
	  */
	public function bankcardEditAction(){
		do{
			$id  		=  trim($this->get('bankcard_id',   0));			
			$bank		=  trim($this->get('bank', ''));
			$name  		=  trim($this->get('name', ''));
			$card    	=  trim($this->get('card', ''));
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:bankcard.id|gt:0', 'msg'=>'ID'],
                ['name'=>'name', 	'value'=>$name,	 'fun'=>'isName','msg'=>'姓名'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }
			$rows	=	array(
				'bank'			=>	$bank,
				'name'			=>	$name,
				'card'			=>	$card,				
				'updated_at'	=>	date('Y-m-d H:i:s'),
			);			
			if(DB::table('bankcard')->where('id','=',$id)->update($rows)){
				$rows['id'] = $id;
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'更新银行卡成功.',
						'data'	=>	$rows,
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'更新银行卡失败，请重试.',
				);
			}
		}while(FALSE);		
		
		json($result);
	}
	/**
	  * 设置默认银行卡
	  *
	  */
	public function setDefaultbankcardAction(){
		do{			
			$id  		=  trim($this->get('bankcard_id',   0));			
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:bankcard.id|gt:0', 'msg'=>'ID'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }	
			DB::table('bankcard')->where('id','<>',$id)->where('members_id','=',$this->user['id'])->update(['flag'=>0]);			
			if(DB::table('bankcard')->where('id','=',$id)->update(['flag'=>1])!==FALSE){
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'更新默认银行卡成功.',
						'data'	=>	DB::table('bankcard')->find($id),
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'更新银行卡失败，请重试.',
				);
			}
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 获取默认银行卡
	  *
	  */
	public function getDefaultbankcardAction(){
		do{
			$rows = DB::table('bankcard')->where('members_id','=',$this->user['id'])->where('flag','=',1)->first();			
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'获取默认银行卡.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 删除银行卡
	  *
	  */
	public function bankcardDelAction(){
		do{	
			$id  		=  trim($this->get('bankcard_id',   0));			
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:bankcard.id|gt:0', 'msg'=>'银行卡'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }			
			if(DB::table('bankcard')->where('members_id','=',$this->user['id'])->where('id','=',$id)->delete()){
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'删除银行卡成功.',
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'更新银行卡失败，请重试.',
				);
			}
		}while(FALSE);
		
		json($result);
	}
	
	/**
	  * 发票信息列表
	  *
	  */
	public function membersinvoiceAction(){
		do{
			$type = $this->get('type', 0);
			$rows = DB::table('membersinvoice')->where('members_id','=',$this->user['id']);
			if($type>0){
				$rows =$rows->where('type', '=', $type);
			}			
			$rows = $rows->get();
			foreach($rows as &$v){
				$v['typename'] = $v['type']==1?'普通增值税发票':'专用增值税发票';
			}
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'发票信息列表.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 新发票信息
	  */
	public function membersinvoiceAddAction(){
		do{	
			$type = $this->get('type', 1);
			$title  	=  trim($this->get('title', ''));
			$content	=  trim($this->get('content', ''));
			$credit_code=  trim($this->get('credit_code', ''));
			$reg_addr 	=  trim($this->get('reg_addr', ''));
			$reg_tel 	=  trim($this->get('reg_tel', ''));
			$bank	    =  trim($this->get('bank', ''));
			$bank_account= trim($this->get('bank_account', ''));
			$flag		=  intval($this->get('flag',  0));			
			$inputs	= array(
                ['name'=>'title', 	'value'=>$title, 'role'=>'required','msg'=>'开标单位'],
				['name'=>'credit_code', 'value'=>$credit_code, 'role'=>'required','msg'=>'纳税人识别码'],
            );
			if($type==2){
				array_push($inputs, ['name'=>'reg_addr', 	'value'=>$reg_addr, 'role'=>'required','msg'=>'注册地址']);
				array_push($inputs, ['name'=>'reg_tel', 	'value'=>$reg_tel,	'role'=>'required','msg'=>'注册电话']);
				array_push($inputs, ['name'=>'bank', 		'value'=>$bank, 	'role'=>'required','msg'=>'开户银行']);
				array_push($inputs, ['name'=>'bank_account','value'=>$bank_account, 'role'=>'required','msg'=>'银行卡号']);
			}
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }
			$rows	=	array(
				'members_id'	=>	$this->user['id'],
				'type'			=>	$type,
				'title'			=>	$title,
				'content'		=>	$content,
				'credit_code'	=>	$credit_code,
				'reg_addr'		=>	$reg_addr,
				'reg_tel'		=>	$reg_tel,
				'bank'			=>	$bank,
				'bank_account'	=>	$bank_account,
				'flag'			=>	$flag,
				'created_at'	=>	date('Y-m-d H:i:s'),
			);
			
			if($addressId=DB::table('membersinvoice')->insertGetId($rows)){			
				$rows['id'] = $addressId;
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'提交新发票信息成功.',
						'data'	=>	$rows,
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'提交新发票信息失败，请重试.',
						'data'	=>	$rows,
				);
			}
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 修改发票信息
	  *
	  */
	public function membersinvoiceEditAction(){
		do{
			$id  	= $this->get('id',   0);
			$type 	= $this->get('type', 1);
			$title  	=  trim($this->get('title', ''));
			$content	=  trim($this->get('content', ''));
			$credit_code=  trim($this->get('credit_code', ''));
			$reg_addr 	=  trim($this->get('reg_addr', ''));
			$reg_tel 	=  trim($this->get('reg_tel', ''));
			$bank	    =  trim($this->get('bank', ''));
			$bank_account= trim($this->get('bank_account', ''));
			$flag		=  intval($this->get('flag',  0));			
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:membersinvoice.id|gt:0', 'msg'=>'ID'],
                ['name'=>'title', 	'value'=>$title, 'role'=>'required','msg'=>'公司名称'],
				['name'=>'credit_code', 'value'=>$credit_code, 'role'=>'required','msg'=>'纳税人识别码'],
            );
			if($type==2){
				array_push($inputs, ['name'=>'reg_addr', 	'value'=>$reg_addr, 'role'=>'required','msg'=>'注册地址']);
				array_push($inputs, ['name'=>'reg_tel', 	'value'=>$reg_tel,	'role'=>'required','msg'=>'注册电话']);
				array_push($inputs, ['name'=>'bank', 		'value'=>$bank, 	'role'=>'required','msg'=>'开户银行']);
				array_push($inputs, ['name'=>'bank_account','value'=>$bank_account, 'role'=>'required','msg'=>'银行卡号']);
			}
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }
			$rows	=	array(
				'type'			=>	$type,
				'title'			=>	$title,
				'content'		=>	$content,
				'credit_code'	=>	$credit_code,
				'reg_addr'		=>	$reg_addr,
				'reg_tel'		=>	$reg_tel,
				'bank'			=>	$bank,
				'bank_account'	=>	$bank_account,
				'flag'			=>	$flag,
				'updated_at'	=>	date('Y-m-d H:i:s'),
			);
			if(DB::table('membersinvoice')->where('id','=',$id)->where('members_id','=',$this->user['id'])->update($rows)){
				$rows['id'] = $id;
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'更新发票信息成功.',
						'data'	=>	$rows,
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'更新发票信息失败，请重试.',
						'data'	=>	$rows,
				);
			}
		}while(FALSE);		
		json($result);
	}
	/**
	  * 设置默认发票信息
	  *
	  */
	public function setDefaultMembersInvoiceAction(){
		do{			
			$id  		=  trim($this->get('id',   0));			
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:membersinvoice.id|gt:0', 'msg'=>'ID'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }	
			DB::table('membersinvoice')->where('id','<>',$id)->where('members_id','=',$this->user['id'])->update(['flag'=>0]);			
			if(DB::table('membersinvoice')->where('id','=',$id)->update(['flag'=>1])!==FALSE){
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'设置默认发票信息成功.',
						'data'	=>	DB::table('membersinvoice')->find($id),
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'更新发票信息失败，请重试.',
				);
			}
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 获取发票信息列表
	  *
	  */
	public function getDefaultMembersInvoiceAction(){
		do{
			$rows = DB::table('membersinvoice')->where('members_id','=',$this->user['id'])->where('flag','=',1)->first();			
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'获取默认发票信息.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 删除发票信息
	  *
	  */
	public function membersinvoiceDelAction(){
		do{	
			$id  		=  trim($this->get('id',   0));			
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:membersinvoice.id|gt:0', 'msg'=>'ID'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }			
			if(DB::table('membersinvoice')->where('members_id','=',$this->user['id'])->where('id','=',$id)->delete()){
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'删除发票信息成功.',
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'删除发票信息失败，请重试.',
				);
			}
		}while(FALSE);
		
		json($result);
	}
	
	/**
	  * 商品收藏列表
	  *
	  */
	public function favoriteAction(){
		do{
            $page        =  intval($this->get('page', 1));
            $pagesize    	=  intval($this->get('pagesize', 10));
            $startpagenum	=  ($page-1) * $pagesize;

			$rows = DB::table('favorite')->where('members_id','=',$this->user['id'])->orderby('created_at','desc')->offset($startpagenum)->limit($pagesize)->get();
			foreach($rows as &$v){
				$v['goods'] = DB::table('goods')->select('name','title','logo','price','currentprice','score')->find($v['goods_id']);
			}
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'商品收藏列表.',
						'data'	=>	array(
						     'page'     =>  $page,
						     'pagesize' =>  $pagesize,
						     'rows'     =>  $rows,
                        ),
			);
		}while(FALSE);
		
		json($result);
	}	
	/**
	  * 新商品收藏
	  */
	public function favoriteAddAction(){
		do{	
			$goods_id	=  $this->get('goods_id', 0);
			$inputs	= array(
                ['name'=>'goods_id','value'=>$goods_id,'role'=>'exists:goods.id','msg'=>'商品ID'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }
			if(DB::table('favorite')->where('members_id',$this->user['id'])->where('goods_id',$goods_id)->count()>0){
				$result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'已加入收藏.',
                );
                break;
			}
			$rows	=	array(
				'members_id'	=>	$this->user['id'],
				'goods_id'		=>	$goods_id,
				'created_at'	=>	date('Y-m-d H:i:s'),
			);			
			if(DB::table('favorite')->insert($rows)){
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'收藏成功.',
						'data'	=>	$rows,
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'提交新商品收藏失败，请重试.',
						'data'	=>	$rows,
				);
			}
		}while(FALSE);		
		json($result);
	}
	/**
	  * 删除商品收藏
	  *
	  */
	public function favoriteDelAction(){
		do{
			$goods_id	=  $this->get('goods_id', 0);
			$inputs	= array(
				['name'=>'goods_id', 'value'=>$goods_id, 'fun'=>'isInteger', 'role'=>'exists:goods.id|gt:0', 'msg'=>'商品'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }			
			if(DB::table('favorite')->where('members_id','=',$this->user['id'])->where('goods_id','=',$goods_id)->count()>0){
                if(DB::table('favorite')->where('members_id','=',$this->user['id'])->where('goods_id','=',$goods_id)->delete()!==FALSE) {
                    $result = [
                        'ret' => '0',
                        'msg' => '取消宝贝收藏成功.',
                    ];
                    break;
                }else{
                    $result	= array(
                        'ret'	=>	'2',
                        'msg'	=>	'取消宝贝收藏失败，请重试.',
                    );
                    break;
                }
            }
            $result = [
                'ret' => '0',
                'msg' => '取消宝贝收藏成功.',
            ];
		}while(FALSE);		
		json($result);
	}
		
	#钱包
	public function walletAction() {
		do{
			$result	=	array(
							'ret'	=>	'1',
							'msg'	=>	'我的钱包',
							'data'	=>	[
											"account"	=> $this->user['account'],
											"frozen"	=> $this->user['frozen'],
											"usemoney"	=> $this->user['usemoney'],
										]
			);
		}while(FALSE);
		
		json($result);		
	}
	
	/**
	 *接口名称	添加到购物车
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function addCartAction(){
		$goods_id 	= $this->get('goods_id' , 0);
		$goods_num	= $this->get('goods_num', 1);
		$inputs	= array(
				['name'=>'goods_id','value'=>$goods_id,'role'=>'required|exists:goods.id','fun'=>'isInt','msg'=>'产品ID'],
				['name'=>'goods_num','value'=>$goods_num,'role'=>'required|gt:0','fun'=>'isInt','msg'=>'产品数量'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, $result);}
				
		if( DB::table('cart')->where('members_id','=',$this->user['id'])->where('goods_id','=',$goods_id)->count()>0 ){
			DB::table('cart')->where('members_id','=',$this->user['id'])->where('goods_id','=',$goods_id)->increment('goods_num');
		}else{
			$rows = array(
				'members_id'	=>	$this->user['id'],
				'goods_id'		=>	$goods_id,				
				'goods_num'		=>	$goods_num,
                'created_at'    =>  date('Y-m-d H:i:s'),
			);
			DB::table('cart')->insert($rows);
		}	
		ret(0, '添加到购物车成功', DB::table('cart')->where('members_id','=',$this->user['id'])->get());
	}
	public function removeCartAction(){
		$goods_id 	= $this->get('goods_id' , '');
		$goods_id   = json_decode($goods_id, TRUE);
		Log::out('cart', 'I', json_encode($goods_id));
		if(	empty($goods_id) ){ret(1, '商品ID为空.');}
        DB::enableQueryLog();
		if( DB::table('cart')->where('members_id','=',$this->user['id'])->whereIn('goods_id',$goods_id)->delete()===FALSE ){
			ret(2, '移除购物车失败，请重试');
		}
        $this->sqllog();
		ret(0, '从购物车移除商品成功');			
	}
	public function clearCartAction(){	
		if( DB::table('cart')->where('members_id','=',$this->user['id'])->delete()===FALSE ){
			ret(1, '清空购物车失败，请重试');
		}
		ret(0, '清空购物车成功');
	}
	
	/**
	 *接口名称	我的购物车
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function myCartAction(){
		$rows = DB::table('cart')->where('members_id','=',$this->user['id'])->orderby('created_at','DESC')->get();
		$total= 0.00;
		if(!empty($rows)){
		foreach($rows as $k=>&$v){
		    $v['flag']  = false;
			$v['goods'] = (new goodsModel)->cartGoodsDetail($v['goods_id']);
			$v['subtotal'] = number_format(round($v['goods']['currentprice'] * $v['goods_num'], 2), 2, ".", "");
			$total += $v['subtotal'];
		}}
		ret(0, '购物车', ['goods'=>$rows, 'num'=>sizeof($rows), 'total'=>number_format($total, 2, ".", "")]);
	}
		
	public function myOrdersAction() {
		do{
			$page        =  intval($this->get('page', 1));
			$pagesize    	=  intval($this->get('pagesize', 10));
			$startpagenum	=  ($page-1) * $pagesize;
			$status			=  $this->get('status', 0);
			
			$rows	= ordersModel::where('orders.members_id','=',$this->user['id'])->where('shipping_type','<>',3);
			if($status>0){
			    if($status==600){
                    $rows = $rows->where(function($query){
                        $query->where('status', '=', 600)->orWhere('status','=',700);
                    });
                }else {
                    $rows = $rows->where('status', '=', $status);
                }
			}
			$total  =$rows->count();
			$rows	=$rows->orderBy('orders.paid_at', 'DESC')->orderBy('orders.id', 'DESC')
										 ->offset($startpagenum)
										 ->limit($pagesize)
										 ->get()
										 ->toArray();			
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'我的订单.',
						'data'	=>	array(
									'page'		=> $page,
									'pagesize'	=> $pagesize,
									'status'    => $status,
									'total'     => $total,
									'totalpage' => ceil($total/$pagesize),
									'rows'		=> $rows,
						),
			);
		}while(FALSE);
		
		json($result);
	}

    public function ordersNumAction() {
        do{
            $rows	= ordersModel::where('orders.members_id','=',$this->user['id'])->where('shipping_type','<>',3);
            $num1   = $rows->where('status', '=', 100)->count();
            $rows	= ordersModel::where('orders.members_id','=',$this->user['id'])->where('shipping_type','<>',3);
            $num2   = $rows->where('status', '=', 200)->count();
            $rows	= ordersModel::where('orders.members_id','=',$this->user['id'])->where('shipping_type','<>',3);
            $num3   = $rows->where('status', '=', 400)->count();
            $rows	= ordersModel::where('orders.members_id','=',$this->user['id'])->where('shipping_type','<>',3);
            $num4   = $rows->where('status', '=', 500)->count();
            $rows	= ordersModel::where('orders.members_id','=',$this->user['id'])->where('shipping_type','<>',3);
            $rows   = $rows->where(function($query){
                $query->where('status', '=', 600)->orWhere('status','=',700);
            });
            $num5   = $rows->count();

            $result	= array(
                'ret'	=>	'0',
                'msg'	=>	'我的订单数量.',
                'data'  =>  array(
                    'num1'  =>  $num1,
                    'num2'  =>  $num2,
                    'num3'  =>  $num3,
                    'num4'  =>  $num4,
                    'num5'  =>  $num5,
                    'num6'  =>  $num6,
                ),
            );
        }while(FALSE);

        json($result);
    }
	
	public function myInvoiceAction() {
		do{
			$page       =  intval($this->get('page', 1));
			$pagesize  	=  intval($this->get('pagesize', 10));
			$offset		=  ($page-1) * $pagesize;
			$status		=  $this->get('status', 2);
			
			$rows	= (new invoiceModel)->getInvoice([
							'members_id'	=>	$this->user['id'],							
							'offset'		=>	$offset,
							'pagesize'		=>	$pagesize,
							'status'		=>	$status,
					]);
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'我的发票.',
						'data'	=>	array(
									'page'		=> $page,
									'pagesize'	=> $pagesize,
									'rows'		=> $rows,
						),
			);
		}while(FALSE);
		
		json($result);
	}
		
	public function ordersDetailAction() {
		do{
			$order_no	= trim($this->get('order_no', ''));
            $inputs	= array(
                ['name'=>'order_no',  'value'=>$order_no,	 'role'=>'required|exists:orders.order_no', 'msg'=>'订单号.'],
            );
            $result		= Validate::check($inputs);
            if(!empty($result)){
                ret(1, $result);
            }
			$rows		= ordersModel::where('order_no','=',$order_no)->first()->toArray();
			if(empty($rows)||!is_array($rows)){
				$result	= array(
						'ret'	=>	'1',
						'msg'	=>	'没找着订单.',
				);
				break;
			}			
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'我的订单.',
						'data'	=>	$rows
			);
		}while(FALSE);
		
		json($result);
	}
		
	public function commentAction(){
		$order_no	= $this->get('order_no', '');
		$goods_id 	= $this->get('goods_id' , 0);
		$content	= $this->get('content',  '');
		$rank		= $this->get('rank', 	  5);
		$photos		= $this->get('photos',	 '');
		$inputs	= array(
				['name'=>'order_no','value'=>$order_no,'role'=>'required|exists:orders.order_no','msg'=>'订单编号'],
				['name'=>'goods_id','value'=>$goods_id,'role'=>'required|exists:goods.id','fun'=>'isInt','msg'=>'产品ID'],
				['name'=>'rank','value'=>$rank,'role'=>'in:1,2,3,4,5','fun'=>'isInt','msg'=>'产品评分值'],
				
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, $result);}
		$rows = array(
			'goods_id'	=>	$goods_id,
			'order_no'	=>	$order_no,
			'content'	=>	$content,
			'photos'	=>	$photos,
			'rank'		=>	$rank,
			'ip'		=>	getIp(),
			'status'	=>	0,
			'members_id'=>	$this->user['id'],
			'created_at'=>	date('Y-m-d H:i:s'),
		);
		if( DB::table('comment')->insert($rows)!==FALSE ){
		    DB::table('orders')->where('order_no','=',$order_no)->update(['status'=>800, 'updated_at'=>date('Y-m-d H:i:s')]);
			ret(0, '发表评价成功', $rows);	
		}
		ret(2, '发表评价失败，请重试');
	}

	public function uploadPhotoAction(){
		do{
			$files	= $this->get('photo', '');
			if(empty($files)){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'图片内容为空',
						);
					break;
			}
			if ($image=$this->uploader($files)){
				$result	= array(
					'ret'	=>	'0',
					'msg'	=>	'上传图片成功.',
					'data'	=>	array(
									'photo'	=>	$image,
								),
				);									
			}else{
				$result	= array(
								'ret'	=>	'4',
								'msg'	=>	'上传图片解析有误.',
				);
			}					
		}while(FALSE);

		json($result);
	}
		
	/**
	  * 交易流水
	  *
	  * 返回所有询价单	  
	  */
	public function scoreLogAction(){
		do{	
			$rows	= DB::table('scorelog')->where('members_id','=',$this->user['id'])
											->orderBy('created_at', 'desc')
											->get();
			if(empty($rows)||!is_array($rows)){
				$result	= array(
						'ret'	=>	'1',
						'msg'	=>	'无积分明细.',
				);
				break;
			}
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'积分明细.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	  * 领取优惠券
	  *
	  */
	public function drawCouponAction(){
		do{	
			$coupon_id = $this->get('coupon_id', 0);			
			$inputs	= array(
					['name'=>'coupon_id','value'=>$coupon_id,'role'=>'required|exists:coupon.id','msg'=>'优惠券ID'],
			);
			$result	= Validate::check($inputs);
			if(	!empty($result) ){ret(1, $result);}
			
			if( DB::table('couponlist')->where('members_id', '=', $this->user['id'])->where('coupon_id', '=', $coupon_id)->count()>0 ){
				$result	= array(
							'ret'	=>	'2',
							'msg'	=>	'此优惠券已领取过.',
				);
				break;
			}
			$rows	= array(
				'members_id'	=>$this->user['id'],
				'coupon_id'		=>$coupon_id,
				'flag'			=>0,
				'sendtime'		=>date('Y-m-d H:i:s'),
			);
			if(DB::table('couponlist')->insert($rows)){			
				$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'领取优惠券成功.',
							'data'	=>	$rows,
				);
			}else{
				$result	= array(
							'ret'	=>	'3',
							'msg'	=>	'优惠券领取失败.',
				);
			}
		}while(FALSE);
		
		json($result);
	}


	
	/**
	  * 我的优惠券
	  *
	  */
	public function myCouponAction(){
		do{
            $page		=$this->get('page',1);
            $pagesize	=$this->get('pagesize', 10);
            $offset		=($page-1)*$pagesize;
		    $status = $this->get('status', 1);
			$rows	= DB::table('couponlist')->join('coupon','couponlist.coupon_id','=','coupon.id')->where('members_id','=',$this->user['id']);
            $rows1	= clone $rows;
            $rows2	= clone $rows;
            $rows3	= clone $rows;
			switch ($status){
                case 1:
                    $rows =$rows->where('flag','=',0)->where('end_on','>',date('Y-m-d H:i:s'));
                    break;
                case 2:
                    $rows =$rows->where('flag','=',1);
                    break;
                case 3:
                    $rows =$rows->where('flag','=',0)->where('end_on','<',date('Y-m-d H:i:s'));
                    break;
            }
            $total		= $rows->count();
            $totalpage	= ceil($total/$pagesize);
            $rows =$rows->orderBy('sendtime', 'desc')->offset($offset)->limit($pagesize)->select('coupon.*','couponlist.*','couponlist.id as couponlist_id')->get();

            $num1   = $rows1->where('flag','=',0)->where('end_on','>',date('Y-m-d H:i:s'))->count();
            $num2   = $rows2->where('flag','=',1)->count();
            $num3   = $rows3->where('flag','=',0)->where('end_on','<',date('Y-m-d H:i:s'))->count();
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'我的优惠券.',
						'data'	=>	array(
                            'page'      => $page,
                            'pagesize'  => $pagesize,
                            'num1'      => $num1,
                            'num2'      => $num2,
                            'num3'      => $num3,
                            'total'     => $total,
                            'totalpage' => $totalpage,
                            'rows'      => $rows,
                        ),
			);
		}while(FALSE);
		
		json($result);
	}
		
	/**
	 *接口名称	我的消息
	 *接口地址	http://api.com/user/message/
	 *接口说明	列出我的消息
	 *参数 @param
	 * @status   	整数  0:未读  1：已读
	 * @pagenum		页码 
	 * @pagesize	每页数量
	 * @token		登陆标记
	 *返回 @return
	 * @list		消息列表
	 *
	 **/
	public function messageAction(){		
		$page    =  intval($this->get('page', 1));
        $pagesize   =  intval($this->get('pagesize', 10));
		$offset		=  ($page-1)*$pagesize;
		$status		=  intval($this->get('status',  2));
		
		$query	=DB::table('message')->where('members_id','=',$this->user['id']);
		if($status<2){
			$query	=$query->where('status','=',$status);
		}
		$total	=$query->count();
		$rows	=$query->offset($offset)->limit($pagesize)->get();
		
		$result		= array(
						'ret'	=>	'0',
						'msg'	=>	'数据读取成功',
						'data'	=>	array(										
										'total'		=>	$total,
										'page'		=>	$page,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($total/$pagesize),
										'rows'		=>	$rows,
									),
					);
		json($result);
	}
	
	public function readMessageAction(){		
		$id         =  intval($this->get('id',  0));			
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:message.id','fun'=>'isInt','msg'=>'消息ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, $result);}
		$rows	=DB::table('message')->where('members_id','=',$this->user['id'])->where('id','=',$id)->first();
		DB::table('message')->where('members_id','=',$this->user['id'])->where('id','=',$id)->update(['status'=>1]);
		
		$result		= array(
						'ret'	=>	'0',
						'msg'	=>	'数据读取成功',
						'data'	=>	$rows,
					);
		json($result);
	}
	
	/**
	 *接口名称	未读消息数
	 *接口地址	http://api.com/user/newmessagenum/
	 *接口说明	列出我的消息
	 *参数 @param
	 * @token		登陆标记
	 *返回 @return
	 * @num			未读消息条数
	 *
	 **/
	public function newMessageNumAction(){		
		$result		= array(
						'ret'	=>	'0',
						'msg'	=>	'未读消息条数',
						'data'	=>	array(										
										'num'	=>DB::table('message')->where('members_id','=',$this->user['id'])->where('status','=',0)->count(),
						),
					);
		json($result);
	}
		
	/**
	 *接口名称	消息删除
	 *接口地址	http://api.com/user/messagedelete/
	 *接口说明	删除我的消息
	 *参数 @param
	 * @id   		消息ID
	 * @token		登陆标记
	 *返回 @return
	 * @list		消息列表
	 *
	 **/
	public function messageDelAction(){				
			$id         =  intval($this->get('id',  0));
			$all        =  intval($this->get('all', 0));			
			if($all==0){
				$inputs	= array(
						['name'=>'id','value'=>$id,'role'=>'required|exists:message.id','fun'=>'isInt','msg'=>'消息ID有误'],
				);
				$result	= Validate::check($inputs);
				if(	!empty($result) ){ret(1, $result);}
				DB::table('message')->where('members_id','=',$this->user['id'])->where('id','=',$id)->delete();
			}else{
				DB::table('message')->where('members_id','=',$this->user['id'])->delete();	
			}
			$result		= array(
						'ret'	=>	'0',
						'msg'	=>	'消息删除成功',
			);
			json($result);
	}
	
	#提交订单
	public function submitOrderAction(){
		$goods	=$this->get('goods', '');
		$goods	=json_decode($goods, TRUE);		
		if(!is_array($goods)||empty($goods)){
			ret(1, '提交商品参数有误.');
		}
		$shipping_type	=$this->get('shipping_type', 0);
        $shipping_name	=$this->get('shipping_name', '');
        $shipping_phone	=$this->get('shipping_phone', '');
		$shippingaddr_id=$this->get('shippingaddr_id', 0);
		$remark		    =$this->get('remark',	'');
		$comefrom	    =$this->get('comefrom',	'Web');
        $kuizeng_remark	=$this->get('kuizeng_remark',	'');
		$inputs	= array(				
				['name'=>'shipping_type','value'=>$shipping_type,'role'=>'required|in:0,1,2,3','fun'=>'isInt','msg'=>'送货方式有误'],
		);
		if($shipping_type==0){
			array_push($inputs, ['name'=>'shippingaddr_id','value'=>$shippingaddr_id,'role'=>'required|exists:shippingaddr.id','fun'=>'isInt','msg'=>'收件地址有误']);
		}
		$is_invoice	=$this->get('is_invoice', 0);
		$invoice_id	=$this->get('invoice_id', 0);		
		if($is_invoice>0){
			array_push($inputs, ['name'=>'invoice_id','value'=>$invoice_id,'role'=>'required|exists:membersinvoice.id','fun'=>'isInt','msg'=>'发票信息ID有误']);
		}
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, $result);}
			
		$couponValue = 0.00;
        $coupon_id	=$this->get('coupon_id', 0);
        Log::out('coupon', 'I', json_encode($coupon_id));
		if(!empty($coupon_id)){
			//查询优惠券
            DB::enableQueryLog();
            $myCoupon = DB::table('couponlist')->join('coupon','coupon.id','=','couponlist.coupon_id')
                                                ->where('couponlist.members_id','=',$this->user['id'])
                                                ->where('couponlist.id','=',$coupon_id)
                                                ->where('flag', '=', 0)
                                                ->first();
            $this->sqllog();
			$couponValue=$myCoupon['value'];
		}
		Log::out('coupon', 'I', json_encode($myCoupon));
		$goodsList=[];
		$total	  =0.00;
		foreach($goods as $oneGoods){
			$myGoods =DB::table('goods')->find($oneGoods['goods_id']);
			if($pro=DB::table('promotion')->join('activity', 'activity.id','=', 'promotion.activity_id')
                                          ->where('goods_id','=',$oneGoods['goods_id'])
                                          ->where('activity.start_on','<',date('Y-m-d H:i:s'))
                                          ->where('activity.end_on','>',date('Y-m-d H:i:s'))
                                          ->first()){
				$price = $pro['promotionprice'];
			}else{
				$price = $myGoods['currentprice'];
			}
			array_push($goodsList, [
					'goods_id'	=>$oneGoods['goods_id'],
					'price'		=>$price,
					'number'	=>$oneGoods['goods_num'],
					'minquantity'=>$myGoods['minquantity'],
					'goodsname'	=>$myGoods['name'],
					'logo'		=>$myGoods['logo'],
					'score'		=>$myGoods['score'],
			]);
			$total+=$price * $oneGoods['goods_num'] * $myGoods['minquantity'];
		}
		if($total>=$myCoupon['min_amount']){
            $amount =$total - $couponValue;
            DB::table('couponlist')->where('couponlist.id','=',$coupon_id)->update(['flag'=>1, 'usetime'=>date('Y-m-d H:i:s')]);
        }else{
		    $amount =$total;
        }
		if($amount<=0.00){ret(2, '订单总价计算有误.');}
		$fee	=0.00;
		$tax	=0.00;				
		$order_no=date('YmdHis') . rand(100000, 999999);
		$rows	=array(
				'order_no'		=>$order_no,
				'members_id'	=>$this->user['id'],
				'goods'			=>json_encode($goodsList, JSON_UNESCAPED_UNICODE),				
				'fee'			=>$fee,
				'tax'			=>$tax,
				'amount'		=>$amount,
				'status'		=>100,
				'shipping_type'	=>$shipping_type,
				'coupon_id'     =>$coupon_id,
				'remark'		=>$remark,
                'kuizeng_remark'=>$kuizeng_remark,
				'sortorder'		=>500,
				'comefrom'      =>$comefrom,
				'created_at'	=>date('Y-m-d H:i:s'),
		);
		if($shipping_type==0){
			$shippingaddr = DB::table('shippingaddr')->find($shippingaddr_id);
			$rows['shipping_name']	=$shippingaddr['name'];
			$rows['shipping_phone']	=$shippingaddr['phone'];
			$rows['shipping_province']	=$shippingaddr['province'];
			$rows['shipping_city']		=$shippingaddr['city'];
			$rows['shipping_area']		=$shippingaddr['area'];
			$rows['shipping_address']	=$shippingaddr['address'];
		}else{
			$station_id = $this->get('station_id', 0);
			$rows['station_id']	=	$station_id;
            $rows['shipping_name']	=$shipping_name;
            $rows['shipping_phone']	=$shipping_phone;
		}
		if(DB::table('orders')->insert($rows)!==FALSE){
			if($is_invoice>0){
				$invoice = DB::table('membersinvoice')->find($invoice_id);
				#创建发票记录
				$invoice_rows=array(
					'order_no'		=>$order_no,
					'members_id'	=>$this->user['id'],
					'fee'			=>$amount,
					'type'			=>$invoice['type'],
					'content'		=>$invoice['content'],
					'title'			=>$invoice['title'],
					'credit_code'	=>$invoice['credit_code'],
					'reg_addr'		=>$invoice['reg_addr'],
					'reg_tel'		=>$invoice['reg_tel'],
					'bank'			=>$invoice['bank'],
					'bank_account'	=>$invoice['bank_account'],
					'created_at'	=>date('Y-m-d H:i:s'),
				);						
				DB::table('invoice')->insert($invoice_rows);
			}
			ret(0, '创建订单成功.', $rows);
		}else{
			ret(2, '订单创建失败.');
		}		
	}

    #提交充值订单
    public function submitRechargeOrderAction(){
        $phone		=$this->get('phone',	'');
        $amount		=$this->get('amount',	'');
        $inputs	= array(
            ['name'=>'phone','value'=>$phone,'fun'=>'isPhone','msg'=>'手机号码'],
            ['name'=>'phone1','value'=>$phone,'role'=>'exists:members.phone','msg'=>'会员手机号码'],
            ['name'=>'amount','value'=>$amount,'role'=>'required|gt:0.00','msg'=>'充值金额'],
        );
        $result	= Validate::check($inputs);
        if(	!empty($result) ){ret(1, $result);}
        $fee	=0.00;
        $tax	=0.00;
        $remark =empty($phone)?'充值订单':'给好友充值';
        $order_no=date('YmdHis') . rand(100000, 999999);
        $rows	=array(
            'order_no'		=>$order_no,
            'members_id'	=>$this->user['id'],
            'goods'			=>'',
            'fee'			=>$fee,
            'tax'			=>$tax,
            'amount'		=>$amount,
            'status'		=>100,
            'shipping_type'	=>3,
            'shipping_phone'=>$phone,
            'remark'		=>$remark,
            'sortorder'		=>500,
            'created_at'	=>date('Y-m-d H:i:s'),
        );
        if(DB::table('orders')->insert($rows)!==FALSE){
            ret(0, '创建充值单成功.', $rows);
        }else{
            ret(2, '充值单创建失败.');
        }
    }

    /**
     * 退款
     *
     */
    public function refundAction(){
        do{
            $order_no = $this->get('order_no', '');
            $reason   = $this->get('reason', '');
            $myOrder  = DB::table('orders')->where('members_id', '=', $this->user['id'])->where('order_no', '=', $order_no)->first();
            if( empty($myOrder) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	'订单编号有误.',
                );
                break;
            }
            if( $myOrder['status']!=200 ){
                $result	= array(
                    'ret'	=>	'2',
                    'msg'	=>	'订单状态有误,无法退款',
                );
                break;
            }
            try{
                DB::beginTransaction();
                $rows	= array(
                    'members_id'	=>$this->user['id'],
                    'order_no'		=>$order_no,
                    'type'			=>3,
                    'fee'			=>$myOrder['amount'],
                    'status'		=>0,
                    'remark'		=>'申请退款',
                    'created_at'	=>date('Y-m-d H:i:s'),
                );
                if(DB::table('orderslog')->insert($rows)!==FALSE){
                    DB::table('orders')->where('members_id', '=', $this->user['id'])->where('order_no', '=', $order_no)->update(['status'=>600, 'reason'=>$reason, 'updated_at'=>date('Y-m-d H:i:s')]);
                    $result	= array(
                        'ret'	=>	'0',
                        'msg'	=>	'申请退款成功,请等待审核.',
                        'data'	=>	$rows,
                    );
                }
                #给用户发发订单退款消息
                $members = DB::table('members')->find($rows['members_id']);
                if(!empty($members['phone'])) {
                    $this->sendSmsPacket('', 10, $members['phone']);
                }
                #给客户经理发订单退款消息
                $consultant_id = $members['consultant_id'];
                $clientManagerPhone = DB::table('admin')->find($consultant_id)['phone'];
                if(!empty($clientManagerPhone)) {
                    $this->sendSmsPacket('', 7, $clientManagerPhone);
                }
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $result	= array(
                    'ret'	=>	'3',
                    'msg'	=>	'申请退款失败.',
                );
            }
        }while(FALSE);

        json($result);
    }

    private function sendSmsPacket($rand, $type, $phone){
        Yaf_Loader::import(APP_PATH . '/library/Alidayu/TopSdk.php');
        $product= '葡团';
        switch ($type){
            case 1:
                #注册
                $templateCode   ='SMS_13735560';
                break;
            case 2:
                #手机验证码登陆
                $templateCode   ='SMS_13735562';
                break;
            case 3:
                #修改登陆密码
                $templateCode   ='SMS_13735558';
                break;
            case 4:
                #修改支付密码
                $templateCode   ='SMS_13735557';
                break;
            case 5:
                #好友注册成功
                $templateCode   ='SMS_135525176';
                break;
            case 6:
                #客户下单
                $templateCode   ='SMS_135415173';
                break;
            case 7:
                #客户申请退款
                $templateCode   ='SMS_135360173';
                break;
            case 8:
                #好友充值
                $templateCode   ='SMS_135345173';
                break;
            case 9:
                #发货通知
                $templateCode   ='SMS_135355190';
                break;
            case 10:
                #申请退款
                $templateCode   ='SMS_135335177';
                break;
            case 11:
                #提现申请
                $templateCode   ='SMS_135390188';
                break;
        }
        $c = new TopClient;
        $c->appkey = '23446811';
        $c->secretKey = '0380ab9b5e9309d2f6a63518c71bccf8';
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("葡团");
        $req->setSmsParam("{\"code\":\"{$rand}\", \"product\":\"{$product}\"}");
        $req->setRecNum($phone);
        $req->setSmsTemplateCode($templateCode);

        return $c->execute($req);
    }

    /**
     * 取消订单
     *
     */
    public function cancelOrderAction(){
        do{
            $order_no = $this->get('order_no', '');
            $reason   = $this->get('reason', '');
            $myOrder  = DB::table('orders')->where('members_id', '=', $this->user['id'])->where('order_no', '=', $order_no)->first();
            if( empty($myOrder) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	'订单编号有误.',
                );
                break;
            }
            if( $myOrder['status']!=100 ){
                $result	= array(
                    'ret'	=>	'2',
                    'msg'	=>	'订单状态有误,无法取消',
                );
                break;
            }
            try{
                DB::beginTransaction();
                if($myOrder['coupon_id']>0){
                    DB::table('couponlist')->where('id', '=', $myOrder['coupon_id'])->update(['flag'=>0, 'usetime'=>'0000-00-00 00:00:00']);
                }
                DB::table('orders')->where('members_id', '=', $this->user['id'])->where('order_no', '=', $order_no)->update(['status'=>300, 'reason'=>$reason, 'updated_at'=>date('Y-m-d H:i:s')]);
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'取消订单成功.',
                );
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $result	= array(
                    'ret'	=>	'3',
                    'msg'	=>	'取消订单失败.',
                );
            }
        }while(FALSE);

        json($result);
    }

    /**
     * 确认收货
     *
     */
    public function confirmReceiveAction(){
        do{
            $order_no = $this->get('order_no', '');
            $myOrder  = DB::table('orders')->where('members_id', '=', $this->user['id'])->where('order_no', '=', $order_no)->first();
            if( empty($myOrder) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	'订单编号有误.',
                );
                break;
            }
            if( $myOrder['status']!=400 ){
                $result	= array(
                    'ret'	=>	'2',
                    'msg'	=>	'订单状态有误,无法确认收货',
                );
                break;
            }
            try{
                DB::beginTransaction();
                DB::table('orders')->where('members_id', '=', $this->user['id'])->where('order_no', '=', $order_no)->update(['status'=>500,'updated_at'=>date('Y-m-d H:i:s')]);
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'确认收货成功.',
                );
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $result	= array(
                    'ret'	=>	'3',
                    'msg'	=>	'执行确认收货失败.',
                );
            }
        }while(FALSE);

        json($result);
    }
	
	/**
	 *接口名称	我的积分
	 *接口地址	http://api.com/user/points/
	 *接口说明	列出我的积分记录
	 *参数 @param
	 * @status   	整数  0:未读  1：已读
	 * @pagenum		页码 
	 * @pagesize	每页数量
	 * @token		登陆标记
	 *返回 @return
	 * @list		消息列表
	 *
	 **/
	public function pointsAction(){
		$page       =  intval($this->get('page', 1));
        $pagesize   =  intval($this->get('pagesize', 10));
		$offset		=  ($page-1)*$pagesize;
		$rows	= DB::table('scorelog')->where("members_id",'=',$this->user['id'])
										->offset($offset)
										->limit($pagesize)
										->get();						
		$result		= array(
						'ret'	=>	'0',
						'msg'	=>	'数据读取成功',
						'data'	=>	array(
										'page'		=>	$page,
										'pagesize'	=>	$pagesize,
										'rows'		=>	$rows,
									),
					);
		json($result);
	}
	
	public function myMembersAction(){
		$page       =  intval($this->get('page', 1));
        $pagesize   =  intval($this->get('pagesize', 10));
		$offset		=  ($page-1)*$pagesize;
		$rows	= DB::table('members')->where("parent_proxy",'=',$this->user['id'])
										->offset($offset)
										->limit($pagesize)
										->select('phone','avatar','name','gender','birthday','email','company','position','created_at');
		$total  = $rows->count();
		$rows   = $rows->get();
		$result		= array(
						'ret'	=>	'0',
						'msg'	=>	'数据读取成功',
						'data'	=>	array(
										'page'		=>	$page,
										'pagesize'	=>	$pagesize,
										'total'     =>  $total,
										'totalpage' =>  ceil($total/$pagesize),
										'rows'		=>	$rows,
									),
					);
		json($result);
	}

    public function myMembersOrdersAction(){
        do{
            $page        =  intval($this->get('page', 1));
            $pagesize    	=  intval($this->get('pagesize', 10));
            $startpagenum	=  ($page-1) * $pagesize;

            $rows	= ordersModel::join('members','members.id','=','orders.members_id')->where("members.parent_proxy",'=',$this->user['id'])
                                                                                        ->where('orders.shipping_type','<>',3)
                                                                                        ->where('orders.status','=', 500);
            $total  = $rows->count();
            $rows	= $rows->orderBy('orders.id', 'DESC')
                            ->offset($startpagenum)
                            ->limit($pagesize)
                            ->select('orders.*')
                            ->get()
                            ->toArray();
            $result	= array(
                'ret'	=>	'0',
                'msg'	=>	'我的分销订单.',
                'data'	=>	array(
                    'page'		=> $page,
                    'pagesize'	=> $pagesize,
                    'total'     => $total,
                    'totalpage' => ceil($total/$pagesize),
                    'rows'		=> $rows,
                ),
            );
        }while(FALSE);

        json($result);
    }
	
	public function myCommissionAction(){
		$page       =  intval($this->get('page', 1));
        $pagesize   =  intval($this->get('pagesize', 10));
		$offset		=  ($page-1)*$pagesize;
		$rows	= DB::table('commission')->where("members_id",'=',$this->user['id'])
										->offset($offset)
										->limit($pagesize)
										->get();
		foreach ($rows as &$v){
		    $v['created_at'] = substr($v['created_at'], 0, 10);
        }
		$result		= array(
						'ret'	=>	'0',
						'msg'	=>	'数据读取成功',
						'data'	=>	array(
										'page'		=>	$page,
										'pagesize'	=>	$pagesize,
										'rows'		=>	$rows,
									),
					);
		json($result);
	}

    public function withdrawAction(){
        $bankcard_id= $this->get('bankcard_id' , 0);
        $amount 	= $this->get('amount',    0.00);
        $inputs	= array(
            ['name'=>'bankcard_id','value'=>$bankcard_id,'role'=>'required|exists:bankcard.id','fun'=>'isInt','msg'=>'银行卡有误'],
            ['name'=>'amount','value'=>$amount,'role'=>'required|gt:0','msg'=>'提现金额有误'],
        );
        $result	= Validate::check($inputs);
        if(	!empty($result) ){ret(1, $result);}
        if($amount>DB::table('members')->find($this->user['id'])['commission']){
            ret(2, '提现金额不能大于佣金总额');
        }
        $bankcard = DB::table('bankcard')->find($bankcard_id);
        try{
            DB::beginTransaction();
            /***1.更新余额***/
            DB::table('members')->where('id','=', $this->user['id'])->decrement('commission', $amount);
            /***2.更新order表***/
            $rows	=	array(
                    'members_id'    =>  $this->user['id'],
                    'amount'        =>  $amount,
                    'bank'          =>  $bankcard['bank'],
                    'name'          =>  $bankcard['name'],
                    'card'          =>  $bankcard['card'],
                    'created_at'    =>  date('Y-m-d H:i:s'),
            );
            DB::table('withdraw')->insert($rows);
            /***3.添加资金记录日志***/
            $type   =4;
            $remark ='申请提取佣金';
            $rows	=	array(
                'members_id'    =>  $this->user['id'],
                'order_no'		=>	'',
                'type'          =>  $type,
                'fee'			=>	$amount,
                'balance'       =>  DB::table('members')->find($this->user['id'])['money'],
                'status'        =>  0,
                'remark'		=>	$remark,
                'created_at'	=>	date('Y-m-d H:i:s'),
            );
            DB::table('orderslog')->insert($rows);
            DB::commit();
            $result	= array(
                'ret'	=>	'0',
                'msg'	=>	'申请提取佣金成功，请等待审核',
            );
        }catch(Exception $e){
            DB::rollBack();
            $result	= array(
                'ret'	=>	'4',
                'msg'	=>	'申请提取佣金失败，请重试',
            );
        }

        json($result);
    }

    /**
     * 财务明细
     */
    public function myFlowAction(){
        $page       =  intval($this->get('page', 1));
        $pagesize   =  intval($this->get('pagesize', 10));
        $offset		=  ($page-1)*$pagesize;
        $rows	= DB::table('orderslog')->where("members_id",'=',$this->user['id'])
            ->offset($offset)
            ->orderBy('created_at', 'DESC')
            ->limit($pagesize)
            ->get();
        $result		= array(
            'ret'	=>	'0',
            'msg'	=>	'数据读取成功',
            'data'	=>	array(
                'page'		=>	$page,
                'pagesize'	=>	$pagesize,
                'rows'		=>	$rows,
            ),
        );
        json($result);
    }

    /**
     * 调用支付宝接口。
     */
    public function alipayAction(){
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $order_no        =	$this->get('order_no', '');
        $inputs	= array(
            ['name'=>'order_no','value'=>$order_no,'role'=>'required|exists:orders.order_no','msg'=>'订单编号有误'],
        );
        $result	= Validate::check($inputs);
        if(	!empty($result) ){ret(1, $result);}
        $rows = DB::table('orders')->where('members_id','=',$this->user['id'])->where('order_no','=',$order_no)->first();
        if(	empty($rows) ){ret(2, '未找到对应的订单.');}

        Yaf_Loader::import(APP_PATH . '/library/Alipay/lotusphp_runtime/Config.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/lotusphp_runtime/ObjectUtil/ObjectUtil.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/aop/request/AlipayTradeWapPayRequest.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/wappay/service/AlipayTradeService.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/aop/AopClient.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php');
        $total_amount	= round($rows['amount'],2);	//注意单位为元
        $out_trade_no	= $order_no;
        $subject 		= '葡团网-支付宝在线支付';
        $body 			= '支付宝在线支付';
        $timeout_express= "1m";//超时时间

        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);

        $payResponse = new AlipayTradeService($config);
        $result=$payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);
        return ;
    }

    /**
     * 余额密码支付
     */
    public function balancePayAction() {
        do{
            $pay_pwd     = $this->get('pay_pwd', '');
            $order_no	 = $this->get('order_no', '');
            /***参数验证BOF***/
            $inputs	= array(
                ['name'=>'order_no',  'value'=>$order_no,	 'role'=>'required|exists:orders.order_no', 'msg'=>'订单编号有误'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }
            Log::out('balancePay', 'I', md5($pay_pwd). ':'.$this->user['paypassword']);
            $member = DB::table('members')->find($this->user['id']);
            if($member['paypassword']!==md5($pay_pwd)){
                $result	= array(
                    'ret'	=>	'2',
                    'msg'	=>	'支付密码输入有误.',
                );
                break;
            }
            /***参数验证EOF***/
            $orders = DB::table('orders')->where('order_no', '=', $order_no)->first();
            if($orders['status']>100){
                $result = array(
                    'ret'   =>  '5',
                    'msg'   =>  '订单已完成支付.'
                );
                break;
            }
            if($member['money']<$orders['amount']){
                $result	= array(
                    'ret'	=>	'3',
                    'msg'	=>	'余额不足以支付当前的订单',
                );
                break;
            }
            try{
                DB::beginTransaction();
                /***1.更新余额***/
                DB::table('members')->where('id','=', $this->user['id'])->decrement('money', $orders['amount']);
                /***2.更新order表***/
                $rows['paid_type']	=	3;
                $rows['paid_at']	=	date('Y-m-d H:i:s');
                $rows['status']		=	200;
                $rows['transactionno']= '';
                DB::table('orders')->where('order_no','=',$order_no)->update($rows);
                /***3.添加资金记录日志***/
                $type   =1;
                $remark ='余额支付订单';
                $rows	=	array(
                    'members_id'    =>  $orders['members_id'],
                    'order_no'		=>	$order_no,
                    'type'          =>  $type,
                    'fee'			=>	$orders['amount'],
                    'balance'       =>  DB::table('members')->find($this->user['id'])['money'],
                    'status'        =>  1,
                    'remark'		=>	$remark,
                    'created_at'	=>	date('Y-m-d H:i:s'),
                );
                DB::table('orderslog')->insert($rows);
                DB::commit();
                #发支付成功消息
                $rows	=	array(
                    'members_id'    =>  $orders['members_id'],
                    'title'		    =>	'支付成功',
                    'content'       =>  '您的订单已支付成功，我们会尽快为您发货，感谢您的使用!',
                    'status'		=>	0,
                    'created_at'	=>	date('Y-m-d H:i:s'),
                );
                DB::table('message')->insert($rows);
                #给客户经理发订单成功消息
                $consultant_id = DB::table('members')->find($orders['members_id'])['consultant_id'];
                $clientManagerPhone = DB::table('admin')->find($consultant_id)['phone'];
                if(!empty($clientManagerPhone)) {
                    $this->sendSmsPacket('', 6, $clientManagerPhone);
                }
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'订单支付成功',
                );
            }catch(Exception $e){
                DB::rollBack();
                $result	= array(
                    'ret'	=>	'4',
                    'msg'	=>	'订单支付失败，请重试',
                );
            }
        }while(FALSE);

        json($result);
    }



}
