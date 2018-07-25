<?php
use Illuminate\Database\Capsule\Manager as DB;

class OrdersController extends CoreController {
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
			
	#订单
	public function ordersAction() {		
		$sort		=$this->get('sort', 'created_at');
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;		
		$status		=$this->get('status', 0);
        $keywords   =$this->get('keywords', '');
        $froms      =$this->get('froms', '');
        $account    =$this->get('account','');
        $recorddate =$this->get('recorddate','');
        $consultant_id=$this->get('consultant_id',0);

		$query	= new ordersModel;
		if($consultant_id>0){
		    $clients=   DB::table('members')->where('consultant_id','=',$consultant_id)->lists('id');
            $query	=	$query	->whereIn('members_id', $clients);
        }
        if($keywords!==''){
            $query	=	$query	->where('order_no','like',"%{$keywords}%");
        }
        if($froms!==''){
            $query	=	$query	->where('comefrom','=',$froms);
        }
        if($account!==''){
		    $accounts = DB::table('members')->where('phone','like',"%{$account}%")->orWhere('name','like',"%{$account}%")->lists('id');
            $query	=	$query	->whereIn('members_id',$accounts);
        }
        if(!empty($recorddate)){
            $recorddate = explode(' - ', $recorddate);
            $starton=$recorddate[0];
            $endon	=$recorddate[1];
            $query	=	$query	->where('paid_at','>=',$starton)
                ->where('paid_at','<=',$endon);
        }
        $num0       =clone $query;
        $num100     =clone $query;
        $num200     =clone $query;
        $num300     =clone $query;
        $num400     =clone $query;
        $num500     =clone $query;
        if($status>0){
            if($status=='600'||$status=='700'){
                $query = $query->where(function($query){
                    $query->where('status','=',600)->orWhere('status','=',700);
                });
            }else {
                $query = $query->where('status', '=', $status);
            }
        }
		$total		=$query->count();
		$totalpage	=ceil($total/$pagesize);
		$rows	=$query->orderBy($sort,$order)
									 ->offset($offset)
									 ->limit($pagesize)
									 ->get()
									 ->toArray();
        $statusnums =array(
            'num0'    =>  $num0->count(),
            'num100'  =>  $num100->where('status','=',100)->count(),
            'num200'  =>  $num200->where('status','=',200)->count(),
            'num300'  =>  $num300->where('status','=',300)->count(),
            'num400'  =>  $num400->where('status','=',400)->count(),
            'num500'  =>  $num500->where('status','=',500)->count(),
        );
		ret(0, '订单列表', [ 'sort'=>$sort,
                            'order'=>$order,
                            'page'=>$page,
                            'pagesize'=>$pagesize,
                            'total'=>$total,
                            'totalpage'=>$totalpage,
                            'statusnums'=>$statusnums,
                            'consultant_id'=>$consultant_id,
                            'rows'=>$rows
                           ]
        );
    }

	#订单详情
	public function ordersGetAction() {		
		$id		=$this->get('id', 0);
        $inputs		= array(
            ['name'=>'id','value'=>$id,'role'=>'required|exists:orders.id|gt:0','fun'=>'isInt','msg'=>'订单ID有误'],
        );
        $result		= Validate::check($inputs);
        if(	!empty($result) ){ret(1, '输入参数有误.', $result);}

		$query	= new ordersModel;
		$rows	=$query->find($id);
		$rows['stationList'] = DB::table('station')->get();
		ret(0, '订单详情', $rows);
    }	
	
	#订单修改
	public function ordersEditAction() {
		$id			=$this->get('id', 0);
		$status		=$this->get('status',   0);
		$reason		=$this->get('reason',   '');
		$shipping_type	=$this->get('shipping_type', 	'');
		$station_id		=$this->get('station_id', 		'');
		$shipping_name	=$this->get('shipping_name', 	'');
		$shipping_phone	=$this->get('shipping_phone',   '');
		$shipping_province=$this->get('shipping_phone', '');
		$shipping_city	=$this->get('shipping_city',    '');
		$shipping_area	=$this->get('shipping_area',    '');
		$shipping_address=$this->get('shipping_address','');
		$express_name	=$this->get('express_name',     '');
		$express_code	=$this->get('express_code',     '');
		$express_no		=$this->get('express_no',       '');
		$remark			=$this->get('remark',           '');
		$inputs		= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:orders.id|gt:0','fun'=>'isInt','msg'=>'订单ID有误'],
				['name'=>'status','value'=>$status,'role'=>'in:100,200,300,400,500,600,700,800,900','msg'=>'商品状态有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array('status'=>$status, 'updated_at'=>date('Y-m-d H:i:s'));	
		if($reason!==''){	$rows['reason']			=$reason;}
		if($shipping_type!==''){	$rows['shipping_type']	=$shipping_type;}
		if($station_id!==''){		$rows['station_id']	=$station_id;}
		if($shipping_name!==''){	$rows['shipping_name']	=$shipping_name;}
		if($shipping_phone!==''){	$rows['shipping_phone']	=$shipping_phone;}
		if($shipping_province!==''){$rows['shipping_province']=$shipping_province;}
		if($shipping_city!==''){	$rows['shipping_city']	=$shipping_city;}
		if($shipping_area!==''){	$rows['shipping_area']	=$shipping_area;}
		if($shipping_address!==''){	$rows['shipping_address']=$shipping_address;}
		if($express_name!==''){		$rows['express_name']	=$express_name;}
		if($express_code!==''){		$rows['express_code']	=$express_code;}
		if($express_no!==''){		$rows['express_no']		=$express_no;}
		if($remark!==''){			$rows['remark']			=$remark;}		
		if( DB::table('orders')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#订单删除
	public function ordersDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:orders.id|gt:0','fun'=>'isInt','msg'=>'订单ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$orderStatus =DB::table('orders')->find($id)['status'];
		if($orderStatus!=100&&$orderStatus!=300){
			ret(3, '已支付订单无法删除');	
		}
		if( DB::table('orders')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据删除失败');	
    }
	#订单批量删除
	public function ordersBatchDelAction() {
		$ids = $this->get('ids', []);		
		DB::table('orders')->whereIn('id',$ids)
							->where(function($query){
								$query->where('status','=',100)
									  ->orWhere('status','=',300);
							})
							->delete();		
		ret(0, '操作成功');		
    }

	/**
	  * 自提点列表
	  *
	  */
	public function stationAction(){
		do{
			$rows = DB::table('station')->get();			
			$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'自提点列表.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}	
	/**
	  * 新加自提点
	  */
	public function stationAddAction(){
		do{	
			$name  		=  trim($this->get('name', ''));
			$phone    	=  trim($this->get('phone', ''));
			$province  	=  trim($this->get('province', ''));
			$city    	=  trim($this->get('city', ''));
			$area    	=  trim($this->get('area', ''));
			$address    =  trim($this->get('address', ''));		
			$inputs	= array(
                ['name'=>'name', 	'value'=>$name, 'role'=>'unique:station.name|required',	'msg'=>'自提点名称格式有误'],
				['name'=>'phone', 	'value'=>$phone, 'role'=>'required',	'msg'=>'电话不能为空'],
				['name'=>'city', 	'value'=>$city, 'role'=>'required',	'msg'=>'市不能为空'],
				['name'=>'area', 	'value'=>$area, 'role'=>'required',	'msg'=>'区不能为空'],
				['name'=>'address', 	'value'=>$address, 'role'=>'required',	'msg'=>'地址不能为空'],				
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
				'name'			=>	$name,
				'phone'			=>	$phone,
				'province'		=>	$province,
				'city'			=>	$city,
				'area'			=>	$area,
				'address'		=>	$address,
				'created_at'	=>	date('Y-m-d H:i:s'),
			);
			
			if($addressId=DB::table('station')->insertGetId($rows)){			
				$rows['id'] = $addressId;
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'提交新自提点成功.',
						'data'	=>	$rows,
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'提交新自提点失败，请重试.',
						'data'	=>	$rows,
				);
			}
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 修改自提点
	  *
	  */
	public function stationEditAction(){
		do{
			$id =  trim($this->get('id',   0));
			$name  		=  trim($this->get('name', ''));
			$phone    	=  trim($this->get('phone', ''));
			$province  	=  trim($this->get('province', ''));
			$city    	=  trim($this->get('city', ''));
			$area    	=  trim($this->get('area', ''));
			$address    =  trim($this->get('address', ''));
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:station.id|gt:0', 'msg'=>'ID格式有误']
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
				'name'			=>	$name,
				'phone'			=>	$phone,
				'province'		=>	$province,
				'city'			=>	$city,
				'area'			=>	$area,
				'address'		=>	$address,
				'updated_at'	=>	date('Y-m-d H:i:s'),
			);			
			if(DB::table('station')->where('id','=',$id)->update($rows)){
				$rows['id'] = $id;
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'更新自提点成功.',
						'data'	=>	$rows,
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'更新自提点失败，请重试.',
						'data'	=>	$rows,
				);
			}
		}while(FALSE);		
		json($result);
	}	
	/**
	  * 删除自提点
	  *
	  */
	public function stationDelAction(){
		do{	
			$id  	= $this->get('id',   0);
			$inputs	= array(
				['name'=>'id', 		'value'=>$id,	 'fun'=>'isInteger', 'role'=>'exists:station.id|gt:0', 'msg'=>'ID格式有误'],
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
			if(DB::table('station')->where('id','=',$id)->delete()){
				$result	= array(
						'ret'	=>	'0',
						'msg'	=>	'删除自提点成功.',
				);
			}else{
				$result	= array(
						'ret'	=>	'2',
						'msg'	=>	'更新自提点失败，请重试.',
				);
			}
		}while(FALSE);
		
		json($result);
	}
	/**
	  * 批量删除自提点
	  *
	  */
	public function stationBatchDelAction(){
		DB::table('station')->whereIn('id',$ids)->delete($id);
		ret(0, '操作成功');
	}
	
}