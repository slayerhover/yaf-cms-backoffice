<?php
use Illuminate\Database\Capsule\Manager as DB;

class FinancialController extends BCoreController {

    public function flowAction(){
        $this->_view->assign('uniqid',	 uniqid());
        $this->_view->assign('clientmanager',DB::table('admin')->where('roles_id','=',6)->where('status','=',1)->get());
    }

    public function flowGetAction()
    {
        $sort		=$this->get('sort', 'id');
        $order		=$this->get('order','desc');
        $page		=$this->get('page',1);
        $pagesize	=$this->get('rows', 10);
        $offset		=($page-1)*$pagesize;
        $keywords   =$this->get('keywords', '');
        $type       =$this->get('type', 0);
        $clientmanager=$this->get('clientmanager', 0);
        $start_on=	$this->get('start_on', '');
        $end_on	=	$this->get('end_on', '');
        $query	= DB::table('orderslog')->where('status', '=', 1);
        if($keywords!=''){
            $query	=	$query->where(function ($query) use($keywords) {
                $query->whereRaw("members_id in (select id from pt_members where phone like '%{$keywords}%' or name like '%{$keywords}%')")
                      ->orWhere('order_no','like',"%{$keywords}%");
            });
        }
        if($type>0){
            $query	=	$query	->where('type','=',$type);
        }
        if($clientmanager!=0){
            $clients=   DB::table('members')->where('consultant_id','=',$clientmanager)->lists('id');
            $query	=	$query	->whereIn('members_id', $clients);
        }
        if(!empty($start_on)){
            $query	=	$query	->where('created_at','>=',$start_on);
        }
        if(!empty($end_on)){
            $query	=	$query	->where('created_at','<=',$end_on);
        }
        $total		=$query->count();
        $rows	    =$query->orderBy($sort,$order)
                            ->offset($offset)
                            ->limit($pagesize)
                            ->get();
        foreach ($rows as $k=>&$v){
            $v['members'] = (new membersModel)->find($v['members_id']);
            $v['typename']= $this->flowtype($v['type']);
        }
        json(['total'=>$total, 'rows'=>$rows]);
    }
    private function flowtype($type){
        switch ($type){
            case 1:
                return '订单支付';
            case 2:
                return '充值';
            case 3:
                return '退款';
            case 4:
                return '提现';
            case 5:
                return '佣金';
        }
    }

	#发票
    public function invoiceAction(){
        $this->_view->assign('uniqid',	 uniqid());
        $this->_view->assign('clientmanager',DB::table('admin')->where('roles_id','=',6)->where('status','=',1)->get());
    }
	public function invoiceGetAction() {
        $sort		=$this->get('sort', 'id');
        $order		=$this->get('order','desc');
        $page		=$this->get('page',1);
        $pagesize	=$this->get('rows', 10);
        $offset		=($page-1)*$pagesize;
        $status     =$this->get('status', 0);
        $keywords   =$this->get('keywords', '');
        $type       =$this->get('type', 0);
        $clientmanager=$this->get('clientmanager', 0);
        $start_on=	$this->get('start_on', '');
        $end_on	=	$this->get('end_on', '');
        $query	= DB::table('invoice');
        if($keywords!=''){
            $query	=	$query->where(function ($query) use($keywords) {
                $query->whereRaw("members_id in (select id from pt_members where phone like '%{$keywords}%' or name like '%{$keywords}%')")
                    ->orWhere('order_no','like',"%{$keywords}%");
            });
        }
        if($status>-1){
            $query	=	$query	->where('status','=',$status);
        }
        if($type>0){
            $query	=	$query	->where('type','=',$type);
        }
        if($clientmanager!=0){
            $clients=   DB::table('members')->where('consultant_id','=',$clientmanager)->lists('id');
            $query	=	$query	->whereIn('members_id', $clients);
        }
        if(!empty($start_on)){
            $query	=	$query	->where('created_at','>=',$start_on);
        }
        if(!empty($end_on)){
            $query	=	$query	->where('created_at','<=',$end_on);
        }
        $total		=$query->count();
        $rows	    =$query->orderBy($sort,$order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();
        foreach ($rows as $k=>&$v){
            $v['members'] = (new membersModel)->find($v['members_id']);
        }

        json(['total'=>$total, 'rows'=>$rows]);
    }
	#发票详情
	public function invoiceViewAction() {
		$id		=$this->get('id', 0);
        $inputs		= array(
            ['name'=>'id','value'=>$id,'role'=>'required|exists:invoice.id','fun'=>'isInt','msg'=>'发票ID有误'],
        );
        $result		= Validate::check($inputs);
        if(	!empty($result) ){ret(1, $result);}

		$dataset	=DB::table('invoice')->find($id);
        $this->_view->assign('dataset',	 $dataset);
    }
    public function invoiceupdateAction(){
        do{
            $dataset = $this->get('dataset', []);
            try{
                DB::beginTransaction();
                DB::table('invoice')->where('id', '=', $dataset['id'])->update($dataset);
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

    public function refundAction(){
        $this->_view->assign('uniqid',	 uniqid());
        $this->_view->assign('clientmanager',DB::table('admin')->where('roles_id','=',6)->where('status','=',1)->get());
    }
    public function refundGetAction() {
        $page   =	$this->getPost('page', 1);
        $limit  =	$this->getPost('rows', 10);
        $offset	=	($page-1)*$limit;
        $sort	=	$this->getPost('sort',  'created_at');
        $order	=	$this->getPost('order', 'desc');
        $keywords=	$this->get('keywords', '');
        $clientmanager=$this->get('clientmanager', 0);
        $start_on=	$this->get('start_on', '');
        $end_on	=	$this->get('end_on', '');

        $query	= new ordersModel;
        $query	= $query->whereIn('orders.status',[600, 700]);

        if($keywords!=''){
            $query	=	$query->where(function ($query) use($keywords) {
                $query->whereRaw("members_id in (select id from pt_members where phone like '%{$keywords}%' or name like '%{$keywords}%')")
                    ->orWhere('orders.order_no','like',"%{$keywords}%");
            });
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
    public function refundViewAction(){
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
    public function refundupdateAction(){
        do{
            $order_no = $this->get('order_no', '');
            $myOrder  = DB::table('orders')->where('order_no', '=', $order_no)->first();
            if( empty($myOrder) ){
                $result	= array(
                    'ret'	=>	1,
                    'msg'	=>	'订单编号有误.',
                );
                break;
            }
            if( $myOrder['status']!=600 ){
                $result	= array(
                    'ret'	=>	2,
                    'msg'	=>	'订单状态有误,无法退款.',
                );
                break;
            }
            if( $myOrder['amount']<=0.00 ){
                $result	= array(
                    'ret'	=>	3,
                    'msg'	=>	'退款金额为0.00',
                );
                break;
            }
            switch ($myOrder['paid_type']){
                case 1:
                    $this->refundAlipay($myOrder["transactionno"], $myOrder['amount']);
                    break;
                case 2:
                    $this->refundWxpay($myOrder["transactionno"], $myOrder['amount']);
                    break;
                case 3:
                    $this->refundBalance($myOrder["members_id"], $myOrder['amount']);
                    break;
            }
            try{
                DB::beginTransaction();
                DB::table('orders')->where('order_no', '=', $order_no)->update(['status'=>700, 'updated_at'=>date('Y-m-d H:i:s')]);
                $rows = array(
                    'members_id'    =>  $myOrder['members_id'],
                    'order_no'      =>  $myOrder['order_no'],
                    'type'          =>  3,
                    'fee'           =>  $myOrder['amount'],
                    'balance'       =>  DB::table('members')->find($myOrder['members_id'])['money'],
                    'status'        =>  1,
                    'remark'        =>  '订单退款',
                    'created_at'    =>  date('Y-m-d H:i:s'),
                );
                DB::table('orderslog')->insert($rows);
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

    private function refundWxpay($transactionno, $amount){
        if(empty($transactionno)) ret(3, '交易流水号为空');
        require_once "../library/Wxpay/lib/WxPay.Api.php";
        $total_fee = $amount*100;
        $refund_fee= $amount*100;
        $input = new WxPayRefund();
        $input->SetTransaction_id($transactionno);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no(WxPayConfig::MCHID.date("YmdHis"));
        $input->SetOp_user_id(WxPayConfig::MCHID);
        $data = WxPayApi::refund($input);
        Log::out('refund', 'I', "【微信退款】:\n".json_encode($data, JSON_UNESCAPED_UNICODE)."\n");
        if($data['return_code']=='SUCCESS'){
            return TRUE;
        }else{
            ret(4, $data['return_msg']);
        }
    }
    private function refundAlipay($transactionno, $amount){
        if(empty($transactionno)) ret(3, '交易流水号为空');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/config.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/aop/SignData.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/aop/AopClient.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/aop/request/AlipayTradeRefundRequest.php');

        $aop = new AopClient;
        $aop->gatewayUrl = $config['gatewayUrl'];
        $aop->appId = $config['app_id'];
        $aop->rsaPrivateKey = $config['merchant_private_key'];
        $aop->alipayrsaPublicKey = $config['alipay_public_key'];
        $aop->apiVersion = '1.0';
        $aop->signType = $config['sign_type'];
        $aop->format = "json";
        $aop->charset = $config['charset'];

        $request = new AlipayTradeRefundRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"out_trade_no\":\"".$transactionno."\","
            . "\"refund_amount\": \"".$amount."\","
            . "\"refund_reason\": \"正常退款\""
            . "}";
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $result = $aop->execute($request);
        Log::out('refund', 'I', "【支付宝退款】:\n".json_encode($result, JSON_UNESCAPED_UNICODE)."\n");
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return TRUE;
        } else {
            ret(4, $result->$responseNode->sub_msg);
        }
    }
    private function refundBalance($members_id, $amount){
        DB::table('members')->where('id', '=', $members_id)->increment('money', $amount);
    }

    public function withdrawAction(){
        $this->_view->assign('uniqid',	 uniqid());
        $this->_view->assign('clientmanager',DB::table('admin')->where('roles_id','=',6)->where('status','=',1)->get());
    }
    public function withdrawGetAction() {
        $page   =	$this->getPost('page', 1);
        $limit  =	$this->getPost('rows', 10);
        $offset	=	($page-1)*$limit;
        $sort	=	$this->getPost('sort',  'created_at');
        $order	=	$this->getPost('order', 'desc');
        $keywords=	$this->get('keywords', '');
        $clientmanager=$this->get('clientmanager', 0);
        $start_on=	$this->get('start_on', '');
        $end_on	=	$this->get('end_on', '');
        $status     =$this->get('status', 0);

        $query	= DB::table('withdraw');
        if($status>-1){
            $query	=	$query	->where('status','=',$status);
        }
        if($keywords!=''){
            $query	=	$query->where(function ($query) use($keywords) {
                $query->whereRaw("members_id in (select id from pt_members where phone like '%{$keywords}%' or name like '%{$keywords}%')");
            });
        }
        if($clientmanager!=0){
            $clients=   DB::table('members')->where('consultant_id','=',$clientmanager)->lists('id');
            $query	=	$query	->whereIn('members_id', $clients);
        }
        if(!empty($start_on)){
            $query	=	$query	->where('created_at','>=',$start_on);
        }
        if(!empty($end_on)){
            $query	=	$query	->where('created_at','<=',$end_on);
        }
        $total		=	$query->count();
        $rows 		= 	$query->offset($offset)
            ->limit($limit)
            ->orderBy($sort, $order)
            ->orderBy('created_at','desc')
            ->get();
        foreach ($rows as $k=>&$v){
            $v['members'] = (new membersModel)->find($v['members_id']);
        }
        json(['total'=>$total, 'rows'=>$rows]);
    }
    #发票详情
    public function withdrawViewAction() {
        $id		=$this->get('id', 0);
        $inputs		= array(
            ['name'=>'id','value'=>$id,'role'=>'required|exists:withdraw.id','fun'=>'isInt','msg'=>'提现记录'],
        );
        $result		= Validate::check($inputs);
        if(	!empty($result) ){ret(1, $result);}

        $dataset	=DB::table('withdraw')->find($id);
        $this->_view->assign('dataset',	 $dataset);
        $this->_view->assign('members',  (new membersModel)->find($dataset['members_id']));
    }
    public function withdrawupdateAction(){
        do{
            $dataset = $this->get('dataset', []);
            try{
                DB::beginTransaction();
                DB::table('withdraw')->where('id', '=', $dataset['id'])->update($dataset);
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