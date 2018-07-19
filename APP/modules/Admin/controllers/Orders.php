<?php
use Illuminate\Database\Capsule\Manager as DB;

class OrdersController extends BCoreController{
	
	public function ordersAction(){		
		$this->_view->assign('uniqid',	 uniqid());
		$this->_view->assign('clientmanager',DB::table('admin')->where('roles_id','=',6)->where('status','=',1)->get());		
    }
	public function ordersGetAction() {
		$page   =	$this->getPost('page', 1);
		$limit  =	$this->getPost('rows', 10);
		$offset	=	($page-1)*$limit;			
		$sort	=	$this->getPost('sort',  'created_at');
		$order	=	$this->getPost('order', 'desc');
		$status	=	$this->getPost('status', 0);
		$keywords=	$this->get('keywords', '');
		$shipping_type=$this->get('shipping_type', -1);
		$clientmanager=$this->get('clientmanager', 0);
		$start_on=	$this->get('start_on', '');
		$end_on	=	$this->get('end_on', '');

		$query	= new ordersModel;
		if($status>0){
			$query	= $query->where('orders.status','=',$status);
		}else{
			$query	= $query->where('orders.status','>',100);
		}
		if($keywords!=''){
			$query	=	$query->where(function ($query) use($keywords) {
										$query->whereRaw("members_id in (select id from pt_members where phone like '%{$keywords}%' or name like '%{$keywords}%')")
											  ->orWhere('orders.order_no','like',"%{$keywords}%");
									});
		}
		if($shipping_type!=-1){
			$query	=	$query->where('shipping_type', '=', $shipping_type);
		}
		if($clientmanager!=0){
			$clients=   DB::table('members')->where('consultant_id','=',$clientmanager)->lists('id');
            $query	=	$query	->whereIn('members_id', $clients);
		}
		if(!empty($start_on)){            
            $query	=	$query	->where('paid_at','>=',$start_on);
        }
		if(!empty($end_on)){            
            $query	=	$query	->where('paid_at','<=',$end_on);
        }
		$total		=	$query->count();	
		$rows 		= 	$query->offset($offset)
							->limit($limit)
                            ->orderBy($sort, $order)
							->orderBy('orders.created_at','desc')
							->get();
							
		json(['total'=>$total, 'rows'=>$rows]);		
    }
	public function ordersViewAction(){    
		$id			= $this->get('id', 0);
		$dataset  	= (new ordersModel)->find($id)->toArray();
		$this->_view->assign('dataset', $dataset);
		
		$province	=	DB::table('city')->where('up','=',0)->where('level','=',1)->orderBy('id', 'asc')->get();		
		$this->_view->assign('province',$province);				
		$province_id=	DB::table('city')->where('name','=',$dataset['shipping_province'])->where('level','=',1)->first()['id'];
		$city		=	DB::table('city')->where('up','=',$province_id)->where('level','=',2)->orderBy('id', 'asc')->get();
		$this->_view->assign('city', 	$city);
		$city_id	=	DB::table('city')->where('name','=',$dataset['shipping_city'])->where('level','=',2)->first()['id'];
		$area		=	DB::table('city')->where('up','=',$city_id)->where('level','=',3)->orderBy('id', 'asc')->get();		
		$this->_view->assign('area', 	$area);
		
		$this->_view->assign('station', DB::table('station')->get());
    }
	public function getCityAction() {
		$province= $this->get('province', '北京');
		$province_id=	DB::table('city')->where('name','=',$province)->where('level', '=', 1)->first()['id'];
		$city		= DB::table('city')->where('up','=',$province_id)->where('level', '=', 2)->orderBy('id', 'asc')->get();
		ret(0, '获取城市列表', $city);
    }
	public function getAreaAction() {
		$city	= $this->get('city', '北京');
		$city_id	=	DB::table('city')->where('name','=',$city)->where('level', '=', 2)->first()['id'];
		$area		= DB::table('city')->where('up','=',$city_id)->where('level', '=', 3)->orderBy('id', 'asc')->get();
        ret(0, '获取区域列表', $area);
    }
	public function ordersdeleteAction(){	
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:orders.id|gt:0','fun'=>'isInt','msg'=>'订单ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, $result);}
		$orderStatus =DB::table('orders')->find($id)['status'];
		if($orderStatus!=100&&$orderStatus!=300){
			ret(3, '已支付订单无法删除');	
		}
		if( DB::table('orders')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据删除失败');	
    }


    /**
     * 订单状态
     *
     */
    public function orderStatusAction(){
        do{
            $order_no = $this->get('order_no', '');
            $statusCode= $this->get('statusCode',  100);
            $myOrder  = DB::table('orders')->where('order_no', '=', $order_no)->first();
            if( empty($myOrder) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	'订单编号有误.',
                );
                break;
            }
            try{
                DB::beginTransaction();
                if($statusCode==300&&$myOrder['coupon_id']>0){
                    DB::table('couponlist')->where('id', '=', $myOrder['coupon_id'])->update(['flag'=>0, 'usetime'=>'0000-00-00 00:00:00']);
                }
                DB::table('orders')->where('order_no', '=', $order_no)->update(['status'=>$statusCode, 'updated_at'=>date('Y-m-d H:i:s')]);
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'操作成功.',
                    'data'  =>  (new ordersModel)->where('order_no', '=', $order_no)->first(),
                );
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $result	= array(
                    'ret'	=>	'2',
                    'msg'	=>	'操作失败.',
                );
            }
        }while(FALSE);

        json($result);
    }

    public function updateShippingAddressAction(){
        do{
            $dataset = $this->get('dataset', []);
            $rows   = array(
                'shipping_name'     =>  $dataset['shipping_name'],
                'shipping_phone'    =>  $dataset['shipping_phone'],
                'shipping_province' =>  $dataset['shipping_province'],
                'shipping_city'     =>  $dataset['shipping_city'],
                'shipping_area'     =>  $dataset['shipping_area'],
                'shipping_address'  =>  $dataset['shipping_address'],
            );
            try{
                DB::beginTransaction();
                DB::table('orders')->where('id', '=', $dataset['id'])->update($rows);
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'操作成功.',
                );
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $result	= array(
                    'ret'	=>	'2',
                    'msg'	=>	'操作失败.',
                );
            }
        }while(FALSE);

        json($result);
    }
    public function updateExpressAction(){
        do{
            $dataset = $this->get('dataset', []);
            $rows   = array(
                'express_name'      =>  $dataset['express_name'],
                'express_no'        =>  $dataset['express_no'],
            );
            try{
                DB::beginTransaction();
                DB::table('orders')->where('id', '=', $dataset['id'])->update($rows);
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'操作成功.',
                );
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $result	= array(
                    'ret'	=>	'2',
                    'msg'	=>	'操作失败.',
                );
            }
        }while(FALSE);

        json($result);
    }
		
}
