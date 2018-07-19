<?php
use Illuminate\Database\Capsule\Manager as DB;

class FinancialController extends CoreController {
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
			
	#发票
	public function invoiceAction() {
		$sort		=$this->get('sort', 'id');	
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
        $keywords	=$this->get('keywords', '');
		$status		=$this->get('status', 0);
        $type		=$this->get('type', 0);
        $class		=$this->get('class', 0);
        $recorddate =$this->get('recorddate','');

		$query	= DB::table('invoice');
		if($status==0||$status==1){
			$query=$query->where('status', '=', $status);
		}
        if($type>0){
            $query=$query->where('type', '=', $type);
        }
        if($class>0){
            $query=$query->where('class', '=', $class);
        }
        if($keywords!==''){
            $query	=	$query	->where(function($query)use($keywords){
                $query->where('title','like',"%{$keywords}%")
                        ->orWhere('credit_code','like',"%{$keywords}%");
            });
        }
        if(!empty($recorddate)){
            $recorddate = explode(' - ', $recorddate);
            $starton=$recorddate[0];
            $endon	=$recorddate[1];
            $query	=	$query	->where('created_at','>=',$starton)
                ->where('created_at','<=',$endon);
        }
		$total		=$query->count();
		$totalpage	=ceil($total/$pagesize);
		$rows	    =$query->orderBy($sort,$order)
									 ->offset($offset)
									 ->limit($pagesize)
									 ->get();
		
		ret(0, '发票列表', [ 'sort'=>$sort,
                            'order'=>$order,
                            'page'=>$page,
                            'pagesize'=>$pagesize,
                            'total'=>$total,
                            'totalpage'=>$totalpage,
                            'rows'=>$rows
                           ]
        );
    }

	#发票详情
	public function invoiceGetAction() {
		$id		=$this->get('id', 0);
        $inputs		= array(
            ['name'=>'id','value'=>$id,'role'=>'required|exists:invoice.id','fun'=>'isInt','msg'=>'发票ID有误'],
        );
        $result		= Validate::check($inputs);
        if(	!empty($result) ){ret(1, '输入参数有误.', $result);}

		$rows	=DB::table('invoice')->find($id);
		ret(0, '发票详情', $rows);
    }

    #资金流水
    public function flowAction() {
        $sort		=$this->get('sort', 'id');
        $order		=$this->get('order','desc');
        $page		=$this->get('page',1);
        $pagesize	=$this->get('pagesize', 10);
        $offset		=($page-1)*$pagesize;
        $phone      =$this->get('account', '');
        $status		=$this->get('status', 2);
        $recorddate =$this->get('recorddate','');

        $query	= DB::table('orderslog');
        if($status==0||$status==1){
            $query=$query->where('status', '=', $status);
        }
        if($phone!==''){
            $query	=	$query	->whereIn('members_id', DB::table('members')->where('phone','=',$phone)->lists('id'));
        }
        if(!empty($recorddate)){
            $recorddate = explode(' - ', $recorddate);
            $starton=$recorddate[0];
            $endon	=$recorddate[1];
            $query	=	$query	->where('created_at','>=',$starton)
                ->where('created_at','<=',$endon);
        }
        $total		=$query->count();
        $totalpage	=ceil($total/$pagesize);
        $rows	    =$query->orderBy($sort,$order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();

        ret(0, '资金流水', [ 'sort'=>$sort,
                         'order'=>$order,
                         'page'=>$page,
                         'pagesize'=>$pagesize,
                         'total'=>$total,
                         'totalpage'=>$totalpage,
                         'rows'=>$rows
            ]
        );
    }

    #佣金管理
    public function rebateAction() {
        $sort		=$this->get('sort', 'id');
        $order		=$this->get('order','desc');
        $page		=$this->get('page',1);
        $pagesize	=$this->get('pagesize', 10);
        $offset		=($page-1)*$pagesize;
        $phone      =$this->get('account', '');
        $recorddate =$this->get('recorddate','');

        $query	= DB::table('orderslog')->where('type','=',5);
        if($phone!==''){
            $query	=	$query	->whereIn('members_id', DB::table('members')->where('phone','=',$phone)->lists('id'));
        }
        if(!empty($recorddate)){
            $recorddate = explode(' - ', $recorddate);
            $starton=$recorddate[0];
            $endon	=$recorddate[1];
            $query	=	$query	->where('created_at','>=',$starton)
                ->where('created_at','<=',$endon);
        }
        $total		=$query->count();
        $totalpage	=ceil($total/$pagesize);
        $rows	    =$query->orderBy($sort,$order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();

        ret(0, '佣金明细', [ 'sort'=>$sort,
                         'order'=>$order,
                         'page'=>$page,
                         'pagesize'=>$pagesize,
                         'total'=>$total,
                         'totalpage'=>$totalpage,
                         'rows'=>$rows
            ]
        );
    }
	
}