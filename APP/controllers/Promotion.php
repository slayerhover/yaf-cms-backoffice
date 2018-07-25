<?php
use Illuminate\Database\Capsule\Manager as DB;

class PromotionController extends CoreController {
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
		
	#红包类别
	public function couponAction() {		
		$sort		=$this->get('sort', 'sortorder');	
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$query		= DB::table('coupon');		
		$total		= $query->count();
		$totalpage	= ceil($total/$pagesize);
		$rows 		= $query->orderBy($sort,$order)->offset($offset)->limit($pagesize)->get();
		foreach ($rows as &$v){
		    $v['start_on'] = substr($v['start_on'], 0, 10);
            $v['end_on']   = substr($v['end_on'], 0, 10);
            $v['goods_id'] = explode(',', $v['goods_id']);
        }
		ret(0, '红包类型列表', ['sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }		
	#红包添加
	public function couponAddAction() {
		$name			=$this->get('name',   '');
		$goods		    =$this->get('goods',   []);
		$value			=$this->get('value',   0);
		$min_amount		=$this->get('min_amount', 0.00);
		$start_on		=$this->get('start_on', 0);
		$end_on			=$this->get('end_on',   0);		
		$sortorder		=$this->get('sortorder',500);
		$inputs		= array(
				['name'=>'name','value'=>$name,'role'=>'required','msg'=>'红包类型名称不能空'],
				['name'=>'value','value'=>$value,'role'=>'required|gt:0.00','msg'=>'红包金额不能为0.00'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$goods_id = [];
		foreach ($goods as $v){
		    $goods_id[] = $v['id'];
        }
		$rows	= array(				
				'name'			=>$name,	
				'goods_id'		=>implode(',', $goods_id),
				'value'			=>$value,
				'min_amount'	=>$min_amount,
				'start_on'		=>$start_on,
				'end_on'		=>$end_on,				
				'sortorder'		=>$sortorder,
				'created_at'	=>date('Y-m-d H:i:s'),
		);
		if( DB::table('coupon')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }
	#红包修改
	public function couponEditAction() {
		$id			=	$this->get('id', 0);
		$name			=$this->get('name',   '');
        $goods		    =$this->get('goods',   []);
		$value			=$this->get('value',   0);
		$min_amount		=$this->get('min_amount', 0.00);
		$start_on		=$this->get('start_on', 1);
		$end_on			=$this->get('end_on', '');		
		$sortorder		=$this->get('sortorder',500);
		$inputs		= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:coupon.id','msg'=>'会员ID有误'],
				['name'=>'name','value'=>$name,'role'=>'required','msg'=>'红包类型名称不能空'],
				['name'=>'value','value'=>$value,'role'=>'required|gt:0.00','msg'=>'红包金额不能为0.00'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
        $goods_id = [];
        foreach ($goods as $v){
            $goods_id[] = $v['id'];
        }
		$rows	= array(
				'name'			=>$name,
                'goods_id'		=>implode(',', $goods_id),
				'value'			=>$value,
				'min_amount'	=>$min_amount,
				'start_on'		=>$start_on,
				'end_on'		=>$end_on,				
				'sortorder'		=>$sortorder,
				'updated_at'	=>date('Y-m-d H:i:s'),
		);		
		if( DB::table('coupon')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#红包类型删除
	public function couponDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:coupon.id','fun'=>'isInt','msg'=>'红包类型ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if( DB::table('coupon')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据删除失败');	
    }
	#红包批量删除
	public function couponBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('coupon')->delete($id);
		}
		ret(0, '操作成功');		
    }

	#红包发放列表
	public function couponListAction() {
		$coupon_id	=$this->get('coupon_id',   0);
		$members_id	=$this->get('members_id',  0);
		$flag		=$this->get('flag',       -1);		
		$page		=$this->get('page',		   1);
		$pagesize	=$this->get('pagesize',	  10);
		$offset		=($page-1)*$pagesize;
		$query		=DB::table('couponlist')->join('coupon','coupon.id','=','couponlist.coupon_id');		
		if($coupon_id>0){
			$query	=$query->where('coupon_id','=',$coupon_id);			
		}
		if($members_id>0){
			$query	=$query->where('members_id','=',$members_id);
		}
		if($flag>-1){
			$query	=$query->where('flag','=',$flag);
		}
		$total		= $query->count();
		$rows 		= $query->offset($offset)->limit($pagesize)->get();
		ret(0, '红包发放列表', ['total'=>$total, 'rows'=>$rows]);
    }
	#红包发放
	public function couponSendAction() {
		$coupon_id	=$this->get('coupon_id',    0);
		$members_id	=$this->get('members_id',  0);		
		$inputs		= array(
				['name'=>'coupon_id','value'=>$coupon_id,'role'=>'required|exists:coupon.id','func'=>'isInt','msg'=>'产品ID有误'],
				['name'=>'members_id','value'=>$members_id,'role'=>'required|exists:members.id','func'=>'isInt','msg'=>'会员ID有误'],				
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(
				'coupon_id'		=>	$coupon_id,
				'members_id'	=>	$members_id,
				'sendtime'	=>	date('Y-m-d H:i:s'),
		);
		if( DB::table('comment')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }	
	#红包批量发放
	public function couponBatchSendAction() {
		$coupon_id	=$this->get('coupon_id',    0);
		$rank		=$this->get('rank',  		0);
		$inputs		= array(
				['name'=>'coupon_id','value'=>$coupon_id,'role'=>'required|exists:coupon.id','func'=>'isInt','msg'=>'产品ID有误'],
				['name'=>'rank','value'=>$rank,'role'=>'required|in:0,1,2,3,4,5','func'=>'isInt','msg'=>'会员等级值有误'],				
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if($rank==0){
			$members=DB::table('members')->get();
		}else{
			$ranks	=DB::table('membersrank')->find($rank);
			$members=DB::table('members')->where('score','>=',$ranks['min'])->where('score','<=',$ranks['max'])->get();
		}
		if(!empty($members)){
			$rows	=[];
			foreach($members as $member){
				array_push($rows, array(
						'coupon_id'		=>	$coupon_id,
						'members_id'	=>	$member['id'],
						'sendtime'	=>	date('Y-m-d H:i:s'),
				));
			}
			if( DB::table('couponlist')->insert($rows) ){
					ret(0, '操作成功');
			}else{
				ret(2, '数据插入失败');
			}
		}else{
			ret(3, '匹配用户用空');
		}		
    }
	#红包删除
	public function couponListDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:couponlist.id','fun'=>'isInt','msg'=>'红包类型ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if( DB::table('couponlist')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据删除失败');	
    }
	#红包批量删除
	public function couponListBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('couponlist')->delete($id);
		}
		ret(0, '操作成功');		
    }
	
	#促销商品
	public function promotionAction() {	
		$type		=$this->get('type', 2);
		$sort		=$this->get('sort', 'sortorder');	
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$query		=DB::table('promotion')->join('goods','promotion.goods_id','=','goods.id');		
		if($type<2){
			$query	=$query->where('promotion.type','=',$type);
		}
		$total		=$query->count();
		$totalpage	=ceil($total/$pagesize);
		$rows 		=$query->orderBy($sort,$order)->offset($offset)->limit($pagesize)
						->select('promotion.*','goods.name','goods.englishname','goods.title','goods.logo','goods.images','goods.introduce','goods.price','goods.spec')
						->get();						
		ret(0, '促销商品列表', ['sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }		
	#促销商品添加
	public function promotionAddAction() {
		$type			=$this->get('type', 0);
		$activity_id	=$this->get('activity_id', 0);
		$goods_id		=$this->get('goods_id',   '');
		$promotionprice	=$this->get('promotionprice',    0.00);
		$start_on		=$this->get('start_on', 0);
		$end_on			=$this->get('end_on',   0);
		$sortorder		=$this->get('sortorder',500);
		$inputs		= array(
				['name'=>'goods_id','value'=>$goods_id,'role'=>'required|exists:goods.id','msg'=>'商品ID有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if($type==1 && !empty($activity_id)){
			$activity	=DB::table('activity')->find($activity_id);
			$start_on	=$activity['start_on'];
			$end_on		=$activity['end_on'];
		}
		if(DB::table('promotion')->where('goods_id','=',$goods_id)->count()>0){
            ret(3, '已经设置过促销');
        }
		$rows	= array(				
				'type'			=>$type,
				'activity_id'	=>$activity_id,
				'goods_id'		=>$goods_id,	
				'promotionprice'=>$promotionprice,
				'start_on'		=>$start_on,
				'end_on'		=>$end_on,				
				'sortorder'		=>$sortorder,
				'created_at'	=>date('Y-m-d H:i:s'),
		);
		if( DB::table('promotion')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }
	#促销商品修改
	public function promotionEditAction() {
		$id			=	$this->get('id', 0);
		$type			=$this->get('type', 0);
		$activity_id	=$this->get('activity_id', 0);
		$goods_id		=$this->get('goods_id',   '');
		$promotionprice	=$this->get('promotionprice',    0.00);
		$start_on		=$this->get('start_on', 0);
		$end_on			=$this->get('end_on',   0);
		$sortorder		=$this->get('sortorder',500);
		$inputs		= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:promotion.id','msg'=>'促销品商品ID有误'],
				['name'=>'goods_id','value'=>$goods_id,'role'=>'required|exists:goods.id','msg'=>'商品ID有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if($type==1 && !empty($activity_id)){
			$activity	=DB::table('activity')->find($activity_id);
			$start_on	=$activity['start_on'];
			$end_on		=$activity['end_on'];
		}
		$rows	= array(
				'type'			=>$type,
				'activity_id'	=>$activity_id,
				'goods_id'		=>$goods_id,	
				'promotionprice'=>$promotionprice,
				'start_on'		=>$start_on,
				'end_on'		=>$end_on,				
				'sortorder'		=>$sortorder,
				'updated_at'	=>date('Y-m-d H:i:s'),
		);		
		if( DB::table('promotion')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#促销商品删除
	public function promotionDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:promotion.goods_id','fun'=>'isInt','msg'=>'促销品商品ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if( DB::table('promotion')->where('goods_id','=',$id)->delete() ){
			ret(0, '操作成功');
		}
		ret(2, '数据删除失败');	
    }
	#促销商品批量删除
	public function promotionBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('promotion')->delete($id);
		}
		ret(0, '操作成功');		
    }

	#优惠活动类别
	public function activityAction() {		
		$sort		=$this->get('sort', 'sortorder');	
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$query		=new activityModel;
		$total		= $query->count();
		$totalpage	= ceil($total/$pagesize);
		$rows 		= $query->orderBy($sort,$order)->offset($offset)->limit($pagesize)->get()->toArray();
        foreach ($rows as &$v){
            $v['start_on'] = substr($v['start_on'], 0, 10);
            $v['end_on']   = substr($v['end_on'], 0, 10);
        }
		ret(0, '优惠活动列表', ['sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }
    #优惠活动商品
    public function activityGoodsAction() {
        $id		    =$this->get('id',1);
	    $page		=$this->get('page',1);
        $pagesize	=$this->get('pagesize', 10);
        $sort       =$this->get('sort', 'goods.sortorder');
        $order      =$this->get('sort', 'desc');
        $offset		=($page-1)*$pagesize;
        $query		=DB::table('goods')->join('promotion', 'goods.id','=','promotion.goods_id');
        $query      =$query->where('promotion.activity_id','=',$id);
        $total		= $query->count();
        $totalpage	= ceil($total/$pagesize);
        $rows 		= $query->orderBy($sort,$order)->offset($offset)->limit($pagesize)->get();

        ret(0, '优惠活动商品列表', ['sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }
	#优惠活动添加
	public function activityAddAction() {
		$name			=$this->get('name',   '');
		$start_on		=$this->get('start_on', 0);
		$end_on			=$this->get('end_on',   0);		
		$rank			=$this->get('rank',		0);
		$sortorder		=$this->get('sortorder',500);
		$inputs		= array(
				['name'=>'name','value'=>$name,'role'=>'required','msg'=>'优惠活动名称不能空'],
		);		
		$result		= Validate::check($inputs);		
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(				
				'name'			=>$name,
				'start_on'		=>$start_on,
				'end_on'		=>$end_on,		
				'rank'			=>$rank,
				'sortorder'		=>$sortorder,
				'created_at'	=>date('Y-m-d H:i:s'),
		);
		if( DB::table('activity')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }
	#优惠活动修改
	public function activityEditAction() {
		$id			=	$this->get('id', 0);
		$name			=$this->get('name',   '');
		$start_on		=$this->get('start_on', 0);
		$end_on			=$this->get('end_on',   0);		
		$rank			=$this->get('rank',		0);
		$sortorder		=$this->get('sortorder',500);
		$inputs		= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:activity.id','msg'=>'会员ID有误'],
				['name'=>'name','value'=>$name,'role'=>'required','msg'=>'优惠活动名称不能空'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(
				'name'			=>$name,
				'start_on'		=>$start_on,
				'end_on'		=>$end_on,		
				'rank'			=>$rank,
				'sortorder'		=>$sortorder,
				'updated_at'	=>date('Y-m-d H:i:s'),
		);		
		if( DB::table('activity')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#优惠活动删除
	public function activityDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:activity.id','fun'=>'isInt','msg'=>'优惠活动ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if( DB::table('activity')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据删除失败');	
    }
	#优惠活动批量删除
	public function activityBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('activity')->delete($id);
		}
		ret(0, '操作成功');		
    }


    /**
     * @return mixed
     */
    public function goodsTreeAction()
    {
        $id     =$this->get('id', 0);
        $coupon	=DB::table('coupon')->find($id);
        $goods_id= explode(',', $coupon['goods_id']);

        $rows=DB::table('label')->orderBy('sortorder', 'desc')->select('name as title', 'id')->get();
        foreach ($rows as $k=>&$v){
            $v['expand']   = FALSE;
            $v['children'] = DB::table('goods')->whereRaw('find_in_set(?, `label_ids`)', [$v['id']])->select('id','name as title')->get();
            foreach($v['children'] as &$v1){
                if(in_array($v1['id'], $goods_id)) {
                    $v1['checked'] = TRUE;
                    $v['selected'] = TRUE;
                }
            }
        }
        ret(0, '商品树', $rows);
    }
	
}
