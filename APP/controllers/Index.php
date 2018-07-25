<?php
use Illuminate\Database\Capsule\Manager as DB;

class IndexController extends CoreController {
	
	public function init() {	
		parent::init();
		Yaf_Dispatcher::getInstance()->autoRender(FALSE);		
	}
	
	/**
	 *接口名称	APP欢迎页	 
	 *接口地址	http://api.com/public/index/
	 *接口说明	显示欢迎页图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function indexAction(){
		$data  =  remember('homepage', 3600, function(){
			return array(
                    'title'			=>'葡团APP',
                    'scrollingImg'	=>$this->getImgs(3,5),
                    'label'		    =>DB::table('label')->orderBy('sortorder', 'desc')->offset(0)->limit(8)->get(),
                    'yzyx'          =>$this->getYzyxGoods(6),
                    'promotion'		=>$this->getPromotion(),
                    'mingzhuangxl'	=>$this->getGoods('recommend', 6),
                    'chaozhijx'		=>$this->getGoods('isnew', 6),
                    'remaitj'		    =>$this->getGoods('ishot', 6),
            );
		});
		ret(0, 'ok', $data);
	}
	/**
	 * type 图片类型
	 * 1: 滚动图片 
	 * 2: 广告
	 * 3: 专题
	 **/	
	public function imagesAction(){		
		$type	=$this->get('type', 1);
		$rows	=	DB::table('images')->where('type','=',   $type)
									 ->orderBy('sortorder', 'desc')
									 ->get();
		$title	='';							 
		switch($type){
			case 1:	$title = '首页动图';	break;
			case 2:	$title = '广告图片';	break;
			case 3:	$title = '活动专题';	break;
		}
		ret(0, $title, $rows);
	}
    public function imagesGetAction(){
        $id	    =$this->get('id', 0);
        $rows	=	DB::table('images')->find($id);
        $title	='';
        switch($rows['type']){
            case 1:	$title = '首页动图';	break;
            case 2:	$title = '广告图片';	break;
            case 3:	$title = '活动专题';	break;
        }
        ret(0, $title. '内容', $rows);
    }
	/**
	 * type 图片类型
	 * 0: 启动图片
	 * 1: 首页滚动图片
	 **/
	private function getImgs($type=0, $limit=5){		
		return	DB::table('images')  ->where('type','=', $type)
									 ->where('status','=', 1)
									 ->orderBy('sortorder', 'desc')
									 ->limit(5)
                                     ->select('id','image','title','links','type','status','sortorder')
									 ->get();
	}
	
	public function selectProvinceAction(){
		$rows	= DB::table('city')->where('up',0)->select('id','name as value')->get();
		foreach($rows as $k=>&$v){
				$v['childs']= DB::table('city')->where('level','=',2)
										 ->where('up','=',$v['id'])
										 ->orderBy('sortorder','desc')
										 ->select('id','name as value')
										 ->get();
				
				foreach($v['childs'] as $k1=>&$v1){					
						$v1['childs']	= DB::table('city')->where('level','=',3)
														 ->where('up','=',$v1['id'])
														 ->orderBy('sortorder','desc')
														 ->select('id','name as value')
														 ->get();
				}				
		}
		$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'选择省份',
							'data'	=>	$rows,
						);
		json($result);
	}	
	public function selectCityAction(){	
		do{
			$up		= intval($this->get('up', 	1));
			if( empty($up) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'传递参数错误',
							'data'	=>	array(
									'up'	=>	'上级参数不能为空.',
							),
						);
				break;
			}
			$rows	= DB::table('city')->where('up','=',$up)->get();
			$result	= array(
								'code'	=>	'1',
								'msg'	=>	'选择城市',
								'data'	=>	$rows,
							);		
		}while(FALSE);
		
		json($result);
	}	
	public function selectAreaAction(){	
		do{
			$up		= intval($this->get('up', 	2));
			if( empty($up) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'传递参数错误',
							'data'	=>	array(
									'up'	=>	'上级参数不能为空.',
							),
						);
				break;
			}			
			$rows	= DB::table('city')->where('up','=',$up)->get();
			$result	= array(
								'code'	=>	'1',
								'msg'	=>	'选择区域',
								'data'	=>	$rows,
							);		
		}while(FALSE);
		
		json($result);
	}

    public function selectZoneAction(){
        $rows	= DB::table('city')->where('up',0)->select('id','name')->get();
        foreach($rows as $k=>&$v){
            $v['sub']= DB::table('city')->where('level','=',2)
                ->where('up','=',$v['id'])
                ->orderBy('sortorder','desc')
                ->select('id','name')
                ->get();

            foreach($v['sub'] as $k1=>&$v1){
                $v1['sub']	= DB::table('city')->where('level','=',3)
                    ->where('up','=',$v1['id'])
                    ->orderBy('sortorder','desc')
                    ->select('id','name')
                    ->get();
            }
        }
        json($rows);
    }

    private function is_runnian($year){
        if($year%100==0){
            return $year%400==0&&$year%3200!=0;
        }else{
            return $year%4==0&&$year%100!=0;
        }
    }
    public function selectDateAction(){
	    $years   = [];
	    for ($year=1950; $year<=2010; $year++){
            $months  = [];
            for ($month=1; $month<=12; $month++){
                $days    = [];
                if(in_array($month, [1,3,5,7,8,10,12])){
                    $fullday = 31;
                }elseif(in_array($month, [4,6,9,11])){
                    $fullday = 30;
                }else{
                    $fullday =$this->is_runnian($year) ? 29 : 28;
                }
                for ($day=1; $day<=$fullday; $day++){
                    array_push($days, ['name'=>sprintf("%02d", $day)]);
                }
                array_push($months, ['name'=>sprintf("%02d", $month), 'sub'=>$days]);
            }
	        array_push($years, ['name'=>$year, 'sub'=>$months]);
	    }
	    json($years);
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
	 * 促销商品
	 **/
	private function getPromotion($limit=5){
		$query		=new activityModel;		
		$result		=$query->where('start_on','<',date('Y-m-d H:i:s'))->where('end_on','>',date('Y-m-d H:i:s'))->first();
		return	$result;
	}

	public function expressAction(){
	    $no = $this->get('no', '');
	    if(empty($no)){ ret(1, '运单编号为空'); }
        $host = "https://cexpress.market.alicloudapi.com";
        $path = "/cexpress";
        $method = "GET";
        $appcode = "";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "no={$no}";
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $data = curl_exec($curl);

        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods", "PUT,POST,GET,OPTIONS,DELETE");
        header("Access-Control-Allow-Headders", "content-type");
        header("Content-type: application/json;charset=utf-8");
        die($data);
    }
	
	#秒杀活动
	public function activityAction() {		
		$sort		=$this->get('sort', 'sortorder');
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$query		=DB::table('activity')
                        ->join('promotion','promotion.activity_id','=','activity.id')
                        ->join('goods','promotion.goods_id','=','goods.id')
                        ->where('goods.status', '=', 1)
                        ->select('activity.*','promotion.promotionprice','goods.*','goods.id as id');
		$total		= $query->count();
		$totalpage	= ceil($total/$pagesize);
		$rows 		= $query->orderBy('goods.'.$sort,$order)->offset($offset)->limit($pagesize)->get();
        $timestamp  = strtotime($rows[0]['end_on'])-time();
        foreach ($rows as $k=>&$v){
            $introduce = preg_replace('/\s+/', ' ', strip_tags($v['introduce']));
            $more   = mb_strlen($introduce)>40 ? '...' : '';
            $v['introduce'] = mb_substr($introduce, 0, 22) . $more;
            $v['rate'] = ($v['stock']+$v['salenum']==0) ? 100 : round($v['salenum'] / $v['stock']+$v['salenum'], 2);
            $v['rateLine'] = floor($v['rate']*80 / 100);
        }
		ret(0, '优惠活动列表', ['sort'=>$sort,'order'=>$order,'end_on'=>$rows[0]['end_on'],'timestamp'=>$timestamp,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }		
		
	/**
	 * 热点商品
	 **/
	private function getGoods($type, $limit){
			return	DB::table('goods')->where($type, '=', 1)
									  ->limit($limit)
									  ->orderBy('currentprice', 'ASC')
									  ->get();
	}
    private function getYzyxGoods($limit){
        return	DB::table('goods')->whereRaw('find_in_set(?, `label_ids`)', [77])
            ->limit($limit)
            ->orderBy('sortorder', 'DESC')
            ->orderBy('currentprice', 'ASC')
            ->get();
    }

    public function getGoodsByLabelAction(){
	    $label_id   =$this->get('label_id', 77);
        $sort		=$this->get('sort', 'sortorder');
        $order		=$this->get('order','desc');
        $page		=$this->get('page',1);
        $pagesize	=$this->get('pagesize', 10);
        $offset		=($page-1)*$pagesize;
        $rows   =goodsModel::whereRaw('find_in_set(?, `label_ids`)', [$label_id])
                ->limit($pagesize)
                ->orderBy($sort, $order)
                ->get()->toArray();
        ret(0, '根据标签获取产品', ['label_id'=>$label_id,'sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'rows'=>$rows]);
    }

    public function getGoodsByAttributeAction(){
        $attr_id    =$this->get('attr_id', 13);
        $attr_value =$this->get('attr_value', '干红');
        $sort		=$this->get('sort', 'sortorder');
        $order		=$this->get('order','desc');
        $page		=$this->get('page',1);
        $pagesize	=$this->get('pagesize', 10);
        $offset		=($page-1)*$pagesize;
        $rows   =goodsModel::join('goods_attr', 'goods_attr.goods_id', '=', 'goods.id')
            ->where('attr_id','=',$attr_id)
            ->where('attr_value','=',$attr_value)
            ->limit($pagesize)
            ->orderBy($sort, $order)
            ->get()->toArray();
        ret(0, '根据属性值获取产品', ['attr_id'=>$attr_id,'attr_value'=>$attr_value,'sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'rows'=>$rows]);
    }

	public function startImgAction(){
		$result	= array(
				'ret'	=>	0,
				'msg'	=>	'起始页图片',
				'data'	=>	$this->getImgs(0),
		);
		json($result);
	}
	#热门搜索
    public function hotSearchAction(){
        $result	= array(
            'ret'	=>	0,
            'msg'	=>	'热门搜索',
            'data'	=>	DB::table('keywords')->orderby('times', 'DESC')->limit(10)->get(),
        );
        json($result);
    }

	#商品标签
    public function labelAction(){
        ret(0, '商品分类', DB::table('label')->orderBy('sortorder', 'desc')->get());
    }
	#目录列表
	public function goodscatAction() {		
		$keywords=urldecode($this->get('keywords',''));		
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$sort=$this->get('sort','sortorder');
		$order=$this->get('order','desc');
        $id      =$this->get('id', 0);
		$query	 = new goodscatModel;
		if($keywords!==''){
			$query	=	$query	->where('title','like',"%{$keywords}%");
		}else{
			$query	=	$query	->where('up','=','0');
		}
		if($id>0){
            $query	=	$query	->where('id','=',$id);
        }
		$total		= $query->count();
		$rows 		= $query->orderBy($sort,$order)							
							->select('*',DB::raw('if(status=1,"激活","失效") as status'))
							->offset($offset)
							->limit($pagesize)
							->get()
                            ->toArray();
		ret(0, '商品目录', ['total'=>$total, 'rows'=>$rows]);
    }	
	
	/**
	 * 商品列表
	 **/
	public function goodsAction(){
		$sort	=$this->get('sort','sortorder');
		$order	=$this->get('order',    'DESC');
		$page	=$this->get('page', 	  1);
		$pagesize=$this->get('pagesize', 10);		
		$keywords=$this->get('keywords', '');
		$cat_id	=$this->get('cat_id', 0);
        $label_id   =$this->get('label_id', 0);
        $attr_params=$this->get('attr_params', '');
        if(!empty($attr_params)) $attr_params=json_decode($attr_params, TRUE);
        $stock      =$this->get('stock', 0);

		$inputs	= array(
				['name'=>'order','value'=>$order,'role'=>'in:ASC,DESC','msg'=>'排序顺序'],
				['name'=>'page','value'=>$page,'role'=>'gt:0','fun'=>'isInt','msg'=>'页码'],
				['name'=>'pagesize','value'=>$pagesize,'role'=>'gt:0','fun'=>'isInt','msg'=>'页量'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, $result);}

		$param	=[
				'status'=>1,
                'attr_params' =>$attr_params,
                'label_id'  =>$label_id,
				'sort'	    =>$sort,
				'order'	    =>$order,
				'offset'    =>($page-1)*$pagesize,
				'limit'	    =>$pagesize,
				'keywords'  =>$keywords,
                'stock'     =>$stock,
				'cat_id'    =>$cat_id,
		];
		$rows	=(new goodsModel)->goods($param);

		$data	=array(
            'attr_params' =>$attr_params,
			'label_id'  =>$label_id,
			'cat_id'    =>$cat_id,
			'stock'     =>$stock,
			'sort'		=>$sort,
			'order'		=>$order,
			'page'		=>$page,
			'pagesize'	=>$pagesize,
			'total'		=>$rows['total'],
			'totalpage'	=>ceil($rows['total']/$pagesize),
			'keywords'	=>$keywords,
			'rows'		=>$rows['list'],
		);						  
		ret(0, '商品列表', $data);
	}

    /**
     * 商品列表
     **/
    public function goodsCommentAction(){
        $sort	=$this->get('sort',     'created_at');
        $order	=$this->get('order',    'DESC');
        $page	=$this->get('page', 	  1);
        $pagesize=$this->get('pagesize', 10);
        $goods_id=$this->get('goods_id',  0);

        $inputs	= array(
            ['name'=>'order','value'=>$order,'role'=>'in:ASC,DESC','msg'=>'排序顺序'],
            ['name'=>'page','value'=>$page,'role'=>'gt:0','fun'=>'isInt','msg'=>'页码'],
            ['name'=>'pagesize','value'=>$pagesize,'role'=>'gt:0','fun'=>'isInt','msg'=>'页量'],
            ['name'=>'goods_id','value'=>$goods_id,'role'=>'exists:goods.id','fun'=>'isInt','msg'=>'商品'],
        );
        $result	= Validate::check($inputs);
        if(	!empty($result) ){ret(1, $result);}

        $rows	=DB::table('comment')->where('goods_id','=',$goods_id);
        $total  =$rows->count();
        $rows   =$rows->orderBy($sort, $order)->get();
        foreach ($rows as $k=>&$v){
            $v['photos'] = empty($v['photos'])?[]:explode(',', $v['photos']);
            $v['members']= DB::table('members')->select('phone','avatar','name','gender','company','position')->find($v['members_id']);
        }
        $data	=array(
            'goods_id'  =>$goods_id,
            'sort'		=>$sort,
            'order'		=>$order,
            'page'		=>$page,
            'pagesize'	=>$pagesize,
            'total'		=>$total,
            'totalpage'	=>ceil($total/$pagesize),
            'rows'		=>$rows,
        );
        ret(0, '商品评论列表', $data);
    }
	
	#促销商品列表
	public function promotionAction() {		
		$sort		=$this->get('sort', 'sortorder');	
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$query		=DB::table('promotion')->join('goods','promotion.goods_id','=','goods.id');		
		$total		=$query->count();
		$totalpage	=ceil($total/$pagesize);
		$rows 		=$query->orderBy($sort,$order)->offset($offset)->limit($pagesize)
						->select('promotion.*','goods.name','goods.englishname','goods.title','goods.logo','goods.images','goods.introduce','goods.price','goods.currentprice','goods.spec')
						->get();						
		ret(0, '促销商品列表', ['sort'=>$sort,'order'=>$order,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }
	
	/**
	 * 商品详情
	 **/
	public function goodsDetailAction(){
		$id		=	$this->get('id', 0);		
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:goods.id','fun'=>'isInt','msg'=>'产品'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, $result);}
		$rows	= (new goodsModel)->goodsDetail($id);		
		#已登陆，添加是否已收藏字段
		$token = $this->get('token', '');
		if(!empty($token)){
		    $user  = Cache::get('auth_'.$token);
            $rows['is_favorited'] = DB::table('favorite')->where('members_id','=',$user['id'])->where('goods_id','=',$id)->count()>0?1:0;
            if(!empty($user['id'])){
                DB::table('records')->where('members_id','=',$user['id'])->where('goods_id','=',$id)->delete();
                DB::table('records')->insert(['members_id'=>$user['id'], 'goods_id'=>$id, 'created_at'=>date('Y-m-d H:i:s')]);
            }
            if(!empty($rows['coupon'])) {
                foreach ($rows['coupon'] as &$v) {
                    $v['flag'] = DB::table('couponlist')->where('members_id', '=', $user['id'])->where('coupon_id', '=', $v['id'])->count() > 0;
                }
            }
		}else{
            $rows['is_favorited'] = 0;
        }
		ret(0, '商品详情', $rows);
	}
	
	/**
	 * 商品筛选
	 **/
	public function filterAction(){
		$data	=DB::table('attribute')->get();				  		
		foreach($data as $k=>&$v){
			$v['values']=	explode(';', str_replace('；',';', $v['values']));
		}
		ret(0, '商品列表', $data);
	}
	
	public function testAction(){
		$a = $this->get('a', 0);
		$b = $this->get('b', 0);
		$c = $this->get('c', 0);
		ret(0, 'Token同步完成', $a+$b+$c);
	}
	
	public function sendSmsAction(){
		do{
			$phone	= $this->get('phone', 	'');
            $type   = $this->get('type', 1);
			$inputs	= array(
				    ['name'=>'phone',  'value'=>$phone,	'role'=>'min:11|max:11|required', 'fun'=>'isPhone', 'msg'=>'手机号码'],
                    ['name'=>'type',  'value'=>$type,	'role'=>'in:1,2,3,4|required', 'fun'=>'isInt', 'msg'=>'短信类型'],
			);
			$result		= Validate::check($inputs);
			if(	!empty($result) ){
				$result	= array(
							'ret'	=>	'1',
							'msg'	=>	$result,
				);
				break;
			}
			/***测试环境，不发短信bof***/			
			if( $this->config->application->debug ){
				$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'短信发送成功',
							'data'	=>	array(
											'phone'		=>	$phone,
											'code'		=>	'1111',
										),
				);
				break;
			}
			/***测试环境，不发短信eof***/
			$rand	= rand(1000, 9999);
            $resp = $this->sendSmsPacket($rand, $type, $phone);
            if($resp->result->success) {
                Cache::set('sms' . $phone, $rand);
                $result = array(
                    'ret' => '0',
                    'msg' => '短信发送成功',
                    'data' => array(
                        'phone' => $phone,
                        'code' => $rand,
                        'resp' => $resp,
                    )
                );
            }else{
                $result = array(
                    'ret' => '2',
                    'msg' => '短信发送失败,' . $resp->sub_msg,
                    'data' => array(
                        'resp' => $resp,
                    )
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
        $c->appkey = '';
        $c->secretKey = '';
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("");
        $req->setSmsParam("{\"code\":\"{$rand}\", \"product\":\"{$product}\"}");
        $req->setRecNum($phone);
        $req->setSmsTemplateCode($templateCode);

        return $c->execute($req);
    }

	#短信校验
	private function checksmscode($phone, $smscode){
		if($this->config->application->debug==TRUE&&$smscode=='1111')	return TRUE;			
		
		return (Cache::get('sms' . $phone)==$smscode);
	}
	
	/**
	 *接口名称	APP注册
	 *接口地址	http://api.com/public/register/
	 *接口说明	APP客户端注册
	 *POST参数 @param
	 * @phone    	手机号码
	 * @password  	登陆密码
	 * @repassword	重复密码
	 * @invite	  	邀请码
	 *返回 @return
	 * @token   	令牌
	 *
	 **/
	public function registerAction() {	
			$phone		= $this->get('phone', 		'');
			$smscode	= $this->get('smscode', 	'');
			$password	= $this->get('password', 	'');
			$repassword	= $this->get('repassword',	'');
			$inviter_id = $this->get('inviter_id',	'');
			#参数验证
			$inputs	= array(					
					['name'=>'phone',  'value'=>$phone,	 'role'=>'required|unique:members.phone', 'fun'=>'isPhone', 'msg'=>'手机号码'],
					['name'=>'smscode','value'=>$smscode,'role'=>'gte:1000|lte:9999', 'fun'=>'isInteger','msg'=>'短信验证码'],
					['name'=>'password','value'=>$password,	'role'=>'min:6|max:32|required', 'msg'=>'密码'],
					['name'=>'repassword','value'=>$repassword,	'role'=>'required|eq:'.$password, 'msg'=>'重复密码'],
                    ['name'=>'inviter_id','value'=>$inviter_id,	'role'=>'exists:members.id', 'msg'=>'邀请人ID'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){ret(1, $result);}
			#验证smscode
			if( !$this->checksmscode($phone, $smscode) ){ret(2, '短信验证码不正确.');}
			if(empty($inviter_id)){
			    $intention = DB::table('intention')->where('phone','=',$phone)->first();
			    if(!empty($intention)){
			        $inviter_id = $intention['inviter_id'];
                }
            }
			#注册用户
			$now  = time();
			$ip	  = getIp();
			$token= md5($phone.$now.$ip);
			$uid = DB::transaction(function () use ($phone,$password,$token,$ip,$now,$inviter_id){
				$rows = [
					'phone'			=> $phone,
					'password'		=> md5($password),
					'token'			=> $token,
					'logined_ip'	=> $ip,
                    'consultant_id' => DB::table('admin')->where('roles_id','=',6)->where('status','=',1)->orderbyRaw('rand()')->first()['id'],
                    'parent_proxy'  => $inviter_id,
					'created_at'	=> date('Y-m-d H:i:s', $now),
				];				
				$uid = DB::table('members')->insertGetId($rows);									
				return $uid;
			});
			#设置登陆token
			if($uid>0){
				$mdMembers=(new membersModel);
				$user = $mdMembers->find($uid);
				if($tokenuser=$mdMembers->setUserLogin($user)){
                    if(empty($inviter_id)){
                        $inviter = DB::table('members')->find($inviter_id);
                        if(!empty($inviter)&&!empty($inviter['phone'])){
                            $this->sendSmsPacket('', 5, $inviter['phone']);
                        }
                    }
					ret(0, '注册并登陆成功', $tokenuser);
				}
			}
			ret(3, '注册失败，请重试.');
	}
	
	public function inviterRegisterAction(){
		$this->_view->assign('inviter',  0);
		$this->_view->display('index/register.html');
	}

    public function intentionAction(){
        $this->_view->assign('inviter_id',  $this->get('inviter_id', 0));
        $this->_view->display('index/intention.html');
    }
    public function inviterIntentionAction(){
        $phone		= $this->get('phone', 		'');
        $inviter_id = $this->get('inviter_id',	'');
        #参数验证
        $inputs	= array(
            ['name'=>'phone',  'value'=>$phone,	 'role'=>'required|unique:members.phone', 'fun'=>'isPhone', 'msg'=>'手机号码'],
            ['name'=>'inviter_id','value'=>$inviter_id,	'role'=>'exists:members.id', 'msg'=>'邀请人'],
        );
        $result		= Validate::check($inputs);
        if(	!empty($result) ){ret(1, $result);}
        if(DB::table('intention')->where('phone','=',$phone)->count()>0){
            ret(0, '手机号已领取过优惠券，请登陆使用.');
        }
        #注册用户
        $uid = DB::transaction(function () use ($phone,$inviter_id){
            $rows = [
                'phone'			=> $phone,
                'inviter_id'    => $inviter_id,
                'created_at'	=> date('Y-m-d H:i:s', $now),
            ];
            $uid = DB::table('intention')->insertGetId($rows);
            return $uid;
        });
        #设置登陆token
        if($uid>0){
                ret(0, '领取优惠券成功');
        }
        ret(3, '领取优惠券失败，请重试.');
    }
    public function downloadAppAction(){
        $this->_view->assign('inviter',  0);
        $this->_view->display('index/downloadApp.html');
    }
	
	/**
	 *接口名称	APP登陆
	 *接口地址	http://api.com/public/login/
	 *接口说明	生成token，用户登陆
	 *参数 @param
	 * @phone 	用户名
	 * @password 	密码
	 *返回 @return	
	 * @token   	登陆标记
	 * @status		登陆状态
	 **/
	public function loginAction(){
			$phone  	= $this->get('phone', 	 '');
			$password	= $this->get('password', '');
			/***验证参数BOF***/
			$inputs		= array(
					['name'=>'phone',  'value'=>$phone,	 	'role'=>'required|exists:members.phone','fun'=>'isPhone', 'msg'=>'手机号码'],
					['name'=>'password','value'=>$password,	'role'=>'min:6|max:32|required', 'msg'=>'密码'],
			);						
			$result		= Validate::check($inputs);
			if(	!empty($result) ){
			    ret(1, $result);
			}
			$mdMembers = new membersModel;
			$user = $mdMembers->checkUserValid(0, $phone, $password);
			if($tokenuser=$mdMembers->setUserLogin($user)){
				ret(0, '登陆成功', $tokenuser);
			}
			ret(4, '登陆失败.');
	}
	
	/**
	 *接口名称	APP登陆
	 *接口地址	http://api.com/public/login/
	 *接口说明	生成token，用户登陆
	 *参数 @param
	 * @phone 	用户名
	 * @password 	密码
	 *返回 @return	
	 * @token   	登陆标记
	 * @status		登陆状态
	 **/
	public function smsloginAction(){
			$phone  	= $this->get('phone', 	 '');
			$smscode	= $this->get('smscode', '');			
			/***参数验证BOF***/
			$inputs	= array(					
					['name'=>'phone',  'value'=>$phone,	 	'role'=>'required','fun'=>'isPhone', 'msg'=>'手机号码'],
					['name'=>'smscode','value'=>$smscode,'role'=>'gte:1000|lte:9999|required', 'fun'=>'isInteger','msg'=>'短信验证码'],
			);
			$result		= Validate::check($inputs);
			if(	!empty($result) ){ret(1, $result);}
			/***验证参数EOF***/
			$mdMembers = new membersModel;
			$user = $mdMembers->checkUserValid(1, $phone, $smscode);
			if($tokenuser=$mdMembers->setUserLogin($user)){
				ret(0, '登陆成功', $tokenuser);			
			}
			ret(4, '登陆失败.');
			
	}
	/**
	 *接口名称	找回密码，修改密码，重置密码
	 *接口地址	http://api.com/user/resetpwd/
	 *接口说明	清除token，退出登陆
	 *参数 @param无
	 *返回 @return无
	 **/	
	public function resetPasswdAction(){
		do{	
			$phone		= $this->get('phone', 		'');
			$smscode	= $this->get('smscode', 	'');
			$password	= $this->get('password', 	'');
			$repassword	= $this->get('repassword',	'');			
			/***参数验证BOF***/
			$inputs	= array(					
					['name'=>'phone',  'value'=>$phone,	 'role'=>'required|exists:members.phone','fun'=>'isPhone', 'msg'=>'手机号码'],
					['name'=>'smscode','value'=>$smscode,'role'=>'gte:1000|lte:9999', 'fun'=>'isInteger','msg'=>'短信验证码'],
					['name'=>'password','value'=>$password,	'role'=>'min:6|max:32|required', 'msg'=>'密码'],
					['name'=>'repassword','value'=>$repassword,	'role'=>'eq:'.$password, 'msg'=>'重复密码'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			/***验证smscodeBOF***/
			if($this->checksmscode($phone, $smscode)==FALSE){	
					$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'短信验证码不正确.',
							);
					break;
			}
			/***验证smscodeEOF***/
			/***更新密码BOF***/
			$myuser		=	DB::table('members')->where('phone','=',$phone);
			$rows	=	array(
					'password'	=>	md5($password),
					'updated_at'=>	date('Y-m-d H:i:s'),
			);
			if ($myuser->update($rows)!==FALSE) {
						$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'更新密码成功.',						
						);			
			}else{
						$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'更新失败.',
						);
			}								
			/***更新密码EOF***/
		}while(FALSE);

		json($result);
	}

    public function resetPayPasswdAction(){
        do{
            $phone		= $this->get('phone', 		'');
            $smscode	= $this->get('smscode', 	'');
            $password	= $this->get('password', 	'');
            $repassword	= $this->get('repassword',	'');
            /***参数验证BOF***/
            $inputs	= array(
                ['name'=>'phone',  'value'=>$phone,	 'role'=>'required|exists:members.phone','fun'=>'isPhone', 'msg'=>'手机号码'],
                ['name'=>'smscode','value'=>$smscode,'role'=>'gte:1000|lte:9999', 'fun'=>'isInteger','msg'=>'短信验证码'],
                ['name'=>'password','value'=>$password,	'role'=>'min:6|max:32|required', 'msg'=>'支付密码'],
                ['name'=>'repassword','value'=>$repassword,	'role'=>'eq:'.$password, 'msg'=>'重复支付密码'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                break;
            }
            /***参数验证EOF***/
            /***验证smscodeBOF***/
            if($this->checksmscode($phone, $smscode)==FALSE){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	'短信验证码不正确.',
                );
                break;
            }
            /***验证smscodeEOF***/
            /***更新密码BOF***/
            $myuser		=	DB::table('members')->where('phone','=',$phone);
            $rows	=	array(
                'paypassword'	=>	md5($password),
                'updated_at'=>	date('Y-m-d H:i:s'),
            );
            if ($myuser->update($rows)!==FALSE) {
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'更新支付密码成功.',
                );
            }else{
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	'更新失败.',
                );
            }
            /***更新密码EOF***/
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
	public function qrcodeAction(){
		$url = urldecode($this->get("data", ''));
		QRcode::png($url, false, 1, 4);
	}

    /**
     * @return mixed
     */
    public function inviterImgAction()
    {
        $url = urldecode($this->get("data", ''));
        $config	  = Yaf_Registry::get('config');
        $path	  = '/qrcode/';
        $descdir  = $config['application']['uploadpath'] . $path;
        if( !is_dir($descdir) ){ mkdir($descdir, 0777); }

        $imgfile = $descdir . 'qrcod_' . time() . '.png';
        QRcode::png($url, $imgfile, 1, 7);
        ret(0, '邀请图片', $this->makeimage($imgfile, 1));
    }

    #图片上加字体并旋转
    private function makeimage($logofile, $returnfilepath=0){
        $bgImgPath = APP_PATH . '/public/images/erweimabg.jpg';
        $img = imagecreatefromstring(file_get_contents($bgImgPath));
        $qCodeImg = imagecreatefromstring(file_get_contents($logofile));
        list($qCodeWidth, $qCodeHight, $qCodeType) = getimagesize($logofile);
        imagecopy($img, $qCodeImg, 400, 900, 0, 0, $qCodeWidth, $qCodeHight);

        list($bgWidth, $bgHight, $bgType) = getimagesize($bgImgPath);
        if($returnfilepath==0){
            switch ($bgType) {
                case 1: //gif
                    header('Content-Type:image/gif');
                    imagegif($img);
                    break;
                case 2: //jpg
                    header('Content-Type:image/jpg');
                    imagejpeg($img);
                    break;
                case 3: //jpg
                    header('Content-Type:image/png');
                    imagepng($img);
                    break;
                default:
                    break;
            }
            imagedestroy($img);
        }else{
            $config	  = Yaf_Registry::get('config');
            $path	  = '/inviter/';
            $descdir  = $config['application']['uploadpath'] . $path;
            if( !is_dir($descdir) ){ mkdir($descdir, 0777); }
            switch ($bgType) {
                case 1: //gif
                    $filename = 'inviter-t' . time() . rand(100,999) . '.gif';
                    $realpath = $descdir . $filename;
                    imagegif($img,$realpath);
                    break;
                case 2: //jpg
                    $filename = 'inviter-t' . time() . rand(100,999) . '.jpg';
                    $realpath = $descdir . $filename;
                    imagejpeg($img,$realpath);
                    break;
                case 3: //jpg
                    $filename = 'inviter-t' . time() . rand(100,999) . '.png';
                    $realpath = $descdir . $filename;
                    imagepng($img,$realpath);
                    break;
                default:
                    break;
            }
            imagedestroy($img);
            return $config['application']['uploadwebpath'] . $path . $filename;
        }
    }
	
	/**
	 *接口名称	APP注册
	 *接口地址	http://api.com/public/register/
	 *接口说明	APP客户端注册
	 *POST参数 @param
	 * @phone    	手机号码
	 * @password  	登陆密码
	 * @repassword	重复密码
	 * @invite	  	邀请码
	 *返回 @return
	 * @token   	令牌
	 *
	 **/
	public function checkphoneAction() {
		do{	
			$phone		= $this->get('phone', 		'');
			/***参数验证BOF***/
			$inputs	= array(					
					['name'=>'phone',  'value'=>$phone,	 'role'=>'required', 'fun'=>'isPhone', 'msg'=>'手机号码'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			if( DB::table('members')->where('phone','=',$phone)->count()>0 ){
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'手机号存在.',
							'data'	=>	[
								'phone'	=>	$phone,
							],
					);
					break;
			}			
			$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'手机号不存在.',
							'data'	=>	[
								'phone'	=>	$phone,
							],
			);
		}while(FALSE);
		
		json($result);
	}
	
	public function checkTokenAction(){
		do{	
			$token		= $this->get('token', 		'');
			/***参数验证BOF***/
			$inputs	= array(					
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'token不能为空'],
			);
			$result		= Validate::check($inputs);
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			if($this->checkTokenValid($token)){
				$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'Token有效.',							
							'data'	=>	[
								'token'	=>	$token,
							],
				);
				break;
			}			
			$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'Token无效.',
							'data'	=>	[
								'token'	=>	$token,
							],
			);
		}while(FALSE);
		
		json($result);
	}

	/**
	 *接口名称	公告列表
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function noticeAction(){			
		do{	
			$page		= $this->get('page', 		1);
			$pagesize	= $this->get('pagesize', 	20);
			/***参数验证BOF***/
			$inputs	= array(
					['name'=>'page',   'value'=>$page,	 'role'=>'required|gte:1', 'fun'=>'isInteger',   'msg'=>'页码'],
					['name'=>'pagesize','value'=>$pagesize,'role'=>'required|gte:1', 'fun'=>'isInteger', 'msg'=>'页量'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			$notice = DB::table('t_notice')->select('id','title','create_date')			
										  ->where('is_use','=',1)
										  ->orderby('create_date', 'DESC');
			$total	= $notice->count();							  
			$notice = $notice->offset(($page-1)*$pagesize)
						  ->limit($pagesize)
						  ->get();			
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'公告列表',
							'data'	=>	array(
										'page'		=>	$page,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($total/$pagesize),
										'notice'	=>	$notice,
							),
			);
		}while(FALSE);
		
		json($result);
	}
	/**
	 *接口名称	公告详情
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function noticeViewAction(){			
		do{	
			$id			= $this->get('id', 		1);
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'id',  'value'=>$id,	 'role'=>'required|gte:1', 'msg'=>'公告'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/			
			$notice = DB::table('t_notice')->select('id','title','introduce','content','create_date')->find($id);
			$preid	= DB::table('t_notice')->select('id')->where('create_date','<',$notice['create_date'])->orderby('create_date', 'DESC')->first()['id'];
			$nextid	= DB::table('t_notice')->select('id')->where('create_date','>',$notice['create_date'])->orderby('create_date', 'ASC')->first()['id'];
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'公告详情',
							'data'	=>	array(						
										'notice'	=>	$notice,
										'preid'		=>	$preid,
										'nextid'	=>	$nextid,
							),
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	会员消息列表
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function messageAction(){			
		do{	
			$token		= $this->get('token', 		'');
			$page		= $this->get('page', 		1);
			$pagesize	= $this->get('pagesize', 	20);
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识'],
					['name'=>'page',   'value'=>$page,	 'role'=>'required|gte:1', 'fun'=>'isInteger',   'msg'=>'页码'],
					['name'=>'pagesize','value'=>$pagesize,'role'=>'required|gte:1', 'fun'=>'isInteger', 'msg'=>'页量'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	$result,
					);
					break;
			}		
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			$message= DB::table('user_notice')->select('id','content','has_read','created_at')
										  ->where('user_id','=',$myuser['uid'])
										  ->orderby('created_at', 'DESC');
			$total	= $message->count();							  
			$message= $message->offset(($page-1)*$pagesize)
										  ->limit($pagesize)
										  ->get();
			if(!empty($message)&&is_array($message)){
			foreach($message as $k=>&$v){	
					$v['content']	= mb_substr($v['content'], 0, 20, 'utf-8');
			}}
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'个人列表',
							'data'	=>	array(
										'page'		=>	$page,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($total/$pagesize),
										'message'	=>	$message,
							),
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	会员消息详情
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function messageViewAction(){			
		do{	
			$token		= $this->get('token', 		'');
			$id			= $this->get('id', 			0);
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识'],
					['name'=>'id',   	'value'=>$id,	 'role'=>'required|gte:1',  'fun'=>'isInteger',   'msg'=>'消息'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	$result,
					);
					break;
			}		
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
					);
					break;
			}
			/***参数验证EOF***/
			$message= DB::table('user_notice')->select('id','content','has_read','created_at')
										  ->where('user_id','=',$myuser['uid'])
										  ->where('id','=',$id)
										  ->first();
			if(empty($message)){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'只能读自己的消息.',
					);
					break;
			}
			if($message['has_read']==0){
				if(DB::table('user_notice')->where('user_id','=',$myuser['uid'])->where('id','=',$id)->update(['has_read'=>1])){
					$message['has_read']	=	1;
				}
			}
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'个人列表',
							'data'	=>	array(										
										'message'	=>	$message,
							),
			);
		}while(FALSE);
		
		json($result);
	}

    /**
     *接口名称	ueditor编辑器上传图片
     *参数 @param
     * @logo 		图片文件
     * @token		登陆标记
     *返回 @return
     * @status		更新状态
     **/
    public function uploadImageAction(){
        if( $rows = $this->uploadFileToCDN('upfile') ){
            exit(json_encode($rows));
        }else{
            json(['code'=>1, 'msg'=>'上传图片失败']);
        }
    }

    /**
     * 上传图片base64
     */
    public function uploadImageBase64Action(){
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
                    $result	= array(
                        'ret'	=>	'0',
                        'msg'	=>	'上传图片成功.',
                        'data'	=>	$image,
                    );
            }else{
                $result	= array(
                    'ret'	=>	'4',
                    'msg'	=>	'上传图片失败.',
                );
            }
        }while(FALSE);

        json($result);
    }
	
	#留言反馈
	public function feedbackAction(){		
		$token		= $this->get('token', 		'');
		$message	= $this->get('message', 	'');
		/***参数验证BOF***/
		$inputs	= array(			
				['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识'],
				['name'=>'message','value'=>$message,'role'=>'required', 'msg'=>'反馈内容'],
		);
		$result		= Validate::check($inputs);			
		if(!empty($result)){
			ret(1, $result);
		}
		$myuser	= $this->checkTokenValid($token);
		if($myuser==FALSE){
			ret(1, '请重新登陆.');				
		}
		/***参数验证EOF***/
		if(DB::table('sc_feedbackmessage')->insert(['userid'=>$myuser['uid'],'message'=>$message])===FALSE){
			ret(1, '提交意见失败，请重试.');				
		}
		ret(0, '意见已提交成功，感谢您的反馈.');		
	}
	
	#微信支付
	public function makeorderAction(){			
		$token		= $this->get('token', 		'');
		/***参数验证BOF***/
		$inputs	= array(			
				['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识'],
		);
		$result		= Validate::check($inputs);			
		if(!empty($result)){
			ret(1, $result);
		}
		$myuser	= $this->checkTokenValid($token);
		if($myuser==FALSE){
			ret(1, '请重新登陆.');				
		}
		/***参数验证EOF***/
		$orders = DB::table('orders')->where('user_id','=',$myuser['uid'])->where('type','=',1)->where('pay_status','=',1)->where('pay_type','=',1)->first();		
		if(!empty($orders)){
			$out_trade_no	= $orders['out_trade_no'] . mt_rand(10000, 99999);
			$orders['out_trade_no']	=$out_trade_no;
		}else{
			$out_trade_no	= date('YmdHis') . mt_rand(100000, 999999);
			$total_fee		= $this->config['application']['domain']=="cfb.scsj.net.cn" ? 10000.00 : 0.01;
			$orders = [
				'out_trade_no' => $out_trade_no,
				'total_amount' => $total_fee,
				'subject'      => '保证金',		
				'pay_type'	   => 1,
				'user_id'	   => $myuser['uid'],
				'type'		   => 1,
				'pay_status'   => 1,
				'gateway_type' => 2,
				'created_at'   => date('Y-m-d H:i:s'),
			];
			if(DB::table('orders')->insert($orders)===FALSE){				
				ret(1, '插入保证金订单失败');
			}
		}				
		ret(0, '保证金订单创建成功.', $orders);
	}
	/***签名BOF***
	public function signAction(){
		$rows	= array(
			'out_trade_no'	=>	'123456784515',
			'total_amount'	=>	0.01,
			'transaction_id'=>	'98494984894984189189148948489',
		);
		$result = array(
			'data'	=> (new Encrypt)->encode($rows),
		);
		ret(0, $result, '编码签名.');
	}
	***签名EOF***/

    /**
     * APP调用支付宝接口。
     */
    public function alipayAppAction(){
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $order_no        =	$this->get('order_no', '');
        $inputs	= array(
            ['name'=>'order_no','value'=>$order_no,'role'=>'required|exists:orders.order_no','msg'=>'订单编号'],
        );
        $result	= Validate::check($inputs);
        if(	!empty($result) ){ret(1, $result);}
        $rows = DB::table('orders')->where('order_no','=',$order_no)->first();
        if(	empty($rows) ){ret(2, '未找到对应的订单.');}


        Yaf_Loader::import(APP_PATH . '/library/Alipay/config.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/aop/AopClient.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/aop/request/AlipayTradeAppPayRequest.php');

        $total_amount	= round($rows['amount'],2);	//注意单位为元
        $out_trade_no	= $order_no;
        $subject 		= '支付宝在线支付';
        $body 			= '支付宝在线支付';
        $timeout_express= "1m";//超时时间

        $aop = new AopClient;
        $aop->gatewayUrl = $config['gatewayUrl'];
        $aop->appId = $config['app_id'];
        $aop->rsaPrivateKey = $config['merchant_private_key'];
        $aop->format = "json";
        $aop->charset = $config['charset'];
        $aop->signType = $config['sign_type'];
        $aop->alipayrsaPublicKey = $config['alipay_public_key'];
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"".$body."\","
            . "\"subject\": \"".$subject."\","
            . "\"out_trade_no\": \"".$out_trade_no."\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"".$total_amount."\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        $request->setNotifyUrl($config['notify_url']);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        // 注意：这里不需要使用htmlspecialchars进行转义，直接返回即可

        ret(0, 'alipayPlus支付编码', $response);
    }

    //支付宝支付回调
    public function alipayNotifyAction(){
        Log::out('alipay', 'I', json_encode($_POST,JSON_UNESCAPED_UNICODE));

        Yaf_Loader::import(APP_PATH . '/library/Alipay/config.php');
        Yaf_Loader::import(APP_PATH . '/library/Alipay/wappay/service/AlipayTradeService.php');

        $arr=$_POST;
        $alipaySevice = new AlipayTradeService($config);
        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($arr);

        /* 实际验证过程建议商户添加以下校验。
        1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
        4、验证app_id是否为该商户本身。
        */
        if($result) {//验证成功
            //请在这里加上商户的业务逻辑程序代
            $trade_no 		= $_POST['trade_no'];	//支付宝交易号
            $trade_status	= $_POST['trade_status'];	//交易状态
            $order_no		= $_POST['out_trade_no'];	//商户订单号_内部订单号

            $data	= DB::table('orders')->where('order_no','=',$order_no)->first();
            if(floatval($data['amount'])!==floatval($_POST['total_amount'])){
                exit('fail'); /***支付额与订单额不符***/
            }
            if($data['status']>100){
                exit('success');
            }
            try{
                DB::beginTransaction();
                /***1.更新order表***/
                $rows['paid_type']	=	1;
                $rows['paid_at']	=	date('Y-m-d H:i:s');
                $rows['status']		=	200;
                $rows['transactionno']= $trade_no;
                DB::table('orders')->where('order_no','=',$order_no)->update($rows);
                /***2.添加资金记录日志***/
                $type   =1;
                $remark ='支付宝在线支付订单';
                if($data['shipping_type']==3){
                    $type =2;
                    if(!empty($data['shipping_phone'])){
                        #好友充值
                        $remark = '给好友' . $data['shipping_phone'] . '充值';
                        $hymembers_id = DB::table('members')->where('phone','=',$data['shipping_phone'])->first()['id'];
                        DB::table('members')->where('id','=',$hymembers_id)->increment('money', $data['amount']);
                        $this->sendSmsPacket('', 8, $data['shipping_phone']);
                    }else{
                        #自己充值
                        $remark = '账户充值';
                        DB::table('members')->where('id','=',$data['members_id'])->increment('money', $data['amount']);
                    }
                }else{
                    #发支付成功消息
                    $rows	=	array(
                        'members_id'    =>  $data['members_id'],
                        'title'		    =>	'支付成功',
                        'content'       =>  '您的订单已支付成功，我们会尽快为您发货，感谢您的使用!',
                        'status'		=>	0,
                        'created_at'	=>	date('Y-m-d H:i:s'),
                    );
                    DB::table('message')->insert($rows);
                    #给客户经理发订单成功消息
                    $consultant_id = DB::table('members')->find($data['members_id'])['consultant_id'];
                    $clientManagerPhone = DB::table('admin')->find($consultant_id)['phone'];
                    if(!empty($clientManagerPhone)) {
                        $this->sendSmsPacket('', 6, $clientManagerPhone);
                    }
                }
                $rows	=	array(
                    'members_id'    =>  $data['members_id'],
                    'order_no'		=>	$order_no,
                    'type'          =>  $type,
                    'fee'			=>	$data['amount'],
                    'balance'       =>  DB::table('members')->find($data['members_id'])['money'],
                    'status'        =>  1,
                    'remark'		=>	$remark,
                    'created_at'	=>	date('Y-m-d H:i:s'),
                );
                DB::table('orderslog')->insert($rows);
                DB::commit();
                exit('success');
            }catch(Exception $e){
                DB::rollBack();
                exit('fail');	//请不要修改或删除
            }
            exit('fail');	//请不要修改或删除
        }else {
            exit('fail');	//验证失败
        }
    }

	#前端页面提示
	public function pageTipsAction(){
		$allTips = DB::table('scsj_language')->select('code','string')->get();
		$data	 = [];
		if(!empty($allTips)&&is_array($allTips)){
		foreach($allTips as $k=>$v){
				$data[$v['code']] = $v['string'];
		}}
		echo "<script>var tips=JSON.parse('";
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		echo "');</script>";		
	}

	/**
	 *接口名称	退出登陆
	 *接口地址	http://api.com/public/logout/
	 *接口说明	清除token，退出登陆
	 *参数 @param无
	 *返回 @return无
	 **/
	public function logoutAction(){	
		do{
			$token		= $this->get('token', 		'');			
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'不在登陆状态.'
					);
					break;
			}
			if( Cache::exists('auth_'.$token) ){
					#DB::table('t_user')->where('token','=',$token)->update(['token'=>'']);
					#Cache::delete('auth_'.$token);
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'退出成功.',
					);
					break;
			}
			$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'已经退出.'
					);
			break;			
		}while(FALSE);
		
		json($result);
	}

    /**
     * 订单详情
     */
    public function ordersDetailAction() {
        do{
            $order_no	= trim($this->get('order_no', ''));
            $inputs	= array(
                ['name'=>'order_no',  'value'=>$order_no,	 'role'=>'required|exists:orders.order_no', 'msg'=>'订单号'],
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
                'msg'	=>	'订单详情.',
                'data'	=>	$rows
            );
        }while(FALSE);

        json($result);
    }
    /**
     * 接收礼物
     */
    public function receiveGiftAction()
    {
        do{
            $order_no = $this->get('order_no', '');
            $station_id = $this->get('station_id', '');
            $station_at = $this->get('station_at', '');
            $shipping_name = $this->get('shipping_name', '');
            $shipping_phone = $this->get('shipping_phone', '');
            $shipping_zone = $this->get('shipping_zone', '');
            $shipping_address = $this->get('shipping_address', '');
            Log::out('order', 'I', $order_no);
            $myOrder  = DB::table('orders')->where('order_no', '=', $order_no)->first();
            if( empty($myOrder) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	'订单编号有误.',
                );
                break;
            }
            if(!empty($myOrder['shipping_phone']) && !empty($myOrder['shipping_address'])){
                $result	= [
                    'ret'	=>	'2',
                    'msg'	=>	'此礼品单已被人领取.',
                ];
                break;
            }
            if(empty($shipping_phone) || empty($shipping_address) || empty($shipping_name) || empty($shipping_zone)){
                $result	= [
                    'ret'	=>	'3',
                    'msg'	=>	'名称、手机号码和配送地址不能为空.',
                ];
                break;
            }
            $inputs	= array(
                ['name'=>'shipping_phone',  'value'=>$shipping_phone,	 'role'=>'required', 'fun'=>'isPhone', 'msg'=>'手机号码'],
            );
            $result		= Validate::check($inputs);
            if(!empty($result)){
                ret(1, '手机号码格式有误.', $result);
            }
            if( $myOrder['status']!=200 ){
                $result	= array(
                    'ret'	=>	'4',
                    'msg'	=>	'订单状态有误',
                );
                break;
            }
            $rows = array(
                'station_id'       => $station_id,
                'station_at'       => $station_at,
                'shipping_name'    => $shipping_name,
                'shipping_phone'   => $shipping_phone,
                'shipping_address' => $shipping_address,
            );
            if(!empty($shipping_zone)){
                $shipping_zone = explode(' ', $shipping_zone);
                $rows['shipping_province']  =$shipping_zone[0];
                $rows['shipping_city']      =$shipping_zone[1];
                $rows['shipping_area']      =$shipping_zone[2];
            }
            try{
                DB::beginTransaction();
                DB::table('orders')->where('order_no', '=', $order_no)->update($rows);
                $result	= array(
                    'ret'	=>	'0',
                    'msg'	=>	'确认接收礼物地址信息成功.',
                );
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $result	= array(
                    'ret'	=>	'3',
                    'msg'	=>	'执行接收礼物失败.',
                );
            }
        }while(FALSE);

        json($result);
	}


    /**
     * @return mixed
     */
    public function paySuccessAction()
    {
        $order_no = $this->get('order_no', '');
        $this->_view->assign('order_no',$order_no);
        $this->_view->display('index/paySuccess.html');
    }


    /**
     * @return mixed
     */
    public function kuizengAction()
    {
        $order_no = $this->get('order_no', '');
        $this->_view->assign('order_no',$order_no);
        $this->_view->display('index/kuizeng.html');
    }


    #微信内支付
    public function wxpayAction(){
        try{
            require_once "../library/Wxpay/lib/WxPay.Api.php";
            require_once "../library/Wxpay/WxPay.JsApiPay.php";
            //echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['QUERY_STRING']; exit;

            $tools = new JsApiPay();
            $openId = $tools->GetOpenid(urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['QUERY_STRING']));

            $order_no = $this->get('order_no', '');
            $inputs		= array(
                ['name'=>'order_no', 'value'=>$order_no, 'role'=>'required|exists:orders.order_no',	'msg'=>'订单编号'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'code'	=>	'0',
                    'msg'	=>	$result,
                );
                json($result);
            }
            $orders = DB::table('orders')->where('order_no', '=', $order_no)->first();
            $total_fee		=	intval($orders['amount'] * 100);
            if(	$total_fee==0 ){
                $result	= array(
                    'code'	=>	'0',
                    'msg'	=>	'订单金额有误.',
                );
                json($result);
            }
            $out_trade_no	=	$orders['order_no'] . rand(100000, 999999);
            $subject		=	'订单支付';
            $data = array(
                'out_trade_no'	=> $out_trade_no,
                'total_amount'	=> $total_fee,
                'subject'   	=> $subject,
                'return_url'	=> 'http://putuan.com/index/paySuccess/?order_no='.$orders['order_no'],
                'notify_url'	=> 'http://putuan.com/index/wxnotify',
            );

            $input = new WxPayUnifiedOrder();
            $input->SetBody($data['subject']);
            $input->SetAttach($data['subject']);
            $input->SetOut_trade_no($data['out_trade_no']);
            $input->SetTotal_fee($data['total_amount']);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag($data['subject']);
            $input->SetNotify_url($data['notify_url']);
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($openId);
            $order = WxPayApi::unifiedOrder($input);
            $jsApiParameters = $tools->GetJsApiParameters($order);

            $this->_view->assign('order_no',$order_no);
            $this->_view->assign('fee', 	$orders['amount']);
            $this->_view->assign('jsApiParameters', 	$jsApiParameters);
            $this->_view->assign('return_url', 	'http://putuan.com/index/paySuccess/?order_no='.$orders['order_no']);
            $this->_view->display('index/wxpay.html');
        }catch(Exception $e){
            exit(json_encode(['err_msg'=>$e->getMessage(), 'url'=>'']));
        }
    }
    #微信APP支付
    public function wxpayAppAction(){
        try{
            $order_no = $this->get('order_no', '');
            $inputs		= array(
                ['name'=>'order_no', 'value'=>$order_no, 'role'=>'required|exists:orders.order_no',	'msg'=>'订单编号'],
            );
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'ret'	=>	'1',
                    'msg'	=>	$result,
                );
                json($result);
            }
            $orders = DB::table('orders')->where('order_no', '=', $order_no)->first();
            $total_fee		=	intval($orders['amount'] * 100);
            if(	$total_fee==0 ){
                $result	= array(
                    'ret'	=>	'2',
                    'msg'	=>	'订单金额有误.',
                );
                json($result);
            }
            $out_trade_no	=	$orders['order_no'] . rand(100000, 999999);
            $subject		=	'订单支付';
            require_once "../library/Wxpay/wxAppPay.php";
            require_once "../library/Wxpay/lib/WxPay.Config.php";
            $appid = WxPayConfig::APPID;
            $mch_id = WxPayConfig::MCHID;//商户号
            $notify_url = WxPayConfig::NOTIFYURL;//回调地址
            $key = WxPayConfig::KEY;//商户key
            $wxAppPay = new wxAppPay($appid, $mch_id, $notify_url, $key);
            $params['body'] = $subject;           //商品描述
            $params['out_trade_no'] = $out_trade_no;    //自定义的订单号
            $params['total_fee'] = $total_fee;                       //订单金额 只能为整数 单位为分
            $params['trade_type'] = 'APP';                   //交易类型 JSAPI | NATIVE | APP | WAP
            $params['scene_info'] = '{"app_info": "","trade_name": "订单支付"}';
            $result = $wxAppPay->unifiedOrder( $params );
            if($result['return_code']=='SUCCESS') {
                $data= $wxAppPay->getAppPayParams($result['prepay_id']);
                exit(json_encode($data));
            }else{
                exit(json_encode($result));
            }
        }catch(Exception $e){
            exit(json_encode(['err_msg'=>$e->getMessage()]));
        }
    }
    #微信分享页面内容
    public function wxsharepageviewAction(){
        do{
            $id=$this->get('id', 0);
            if($id==0){
                $result	= array(
                    'code'	=>	'0',
                    'msg'	=>	'id为空',
                );
                break;
            }
            $share=DB::table('share')->find($id);
            $result= array(
                'code'	=>	1,
                'msg'	=>	'获取分享页面内容',
                'data'	=>	$share,
            );
        }while(FALSE);

        json($result);
    }

    #微信支付回调
    public function wxNotifyAction(){
        require_once "../library/Wxpay/WxPayPubHelper/WxPayPubHelper.php";
        $xml = file_get_contents("php://input");
        if(empty($xml)){ exit('FAIL');}
        Log::out('wxpay', 'I', "【支付回调】:\n".$xml."\n");
        $notify = new Notify_pub();
        $notify->saveData($xml);
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();

        if($notify->checkSign() == TRUE)
        {
            if ($notify->data["return_code"] == "FAIL") {
                exit('FAIL');
            }
            elseif($notify->data["result_code"] == "FAIL"){
                exit('FAIL');
            }else{
                Log::out('wxpay', 'I', "【支付成功】:\n".$notify->data["out_trade_no"]."\n");
                $out_trade_no = substr($notify->data["out_trade_no"], 0, 20);

                $orders = DB::table('orders')->where('order_no', '=', $out_trade_no)->first();
                if( $orders['status']>100 ){
                    ob_clean();
                    exit($returnXml);
                }
                if (!empty($orders) && ($orders['amount']*100==$notify->data['total_fee']) ) {

                    try{
                        DB::beginTransaction();
                        /***1.更新order表***/
                        $rows = array(
                            'paid_type'		=>	2,
                            'status'		=>	200,
                            'paid_at'		=>	date('Y-m-d H:i:s'),
                            'transactionno'	=>	$notify->data['transaction_id'],
                            'out_trade_no'	=>	$notify->data['out_trade_no'],
                            'updated_at'    =>	date('Y-m-d H:i:s'),
                        );
                        DB::table('orders')->where('order_no','=',$out_trade_no)->update($rows);
                        /***2.添加资金记录日志***/
                        $type   =1;
                        $remark ='微信在线支付订单';
                        if($orders['shipping_type']==3){
                            $type =2;
                            if(!empty($orders['shipping_phone'])){
                                #好友充值
                                $remark = '给好友' . $orders['shipping_phone'] . '充值';
                                $hymembers_id = DB::table('members')->where('phone','=',$orders['shipping_phone'])->first()['id'];
                                DB::table('members')->where('id','=',$hymembers_id)->increment('money', $orders['amount']);
                                $this->sendSmsPacket('', 8, $orders['shipping_phone']);
                            }else{
                                #自己充值
                                $remark = '账户充值';
                                DB::table('members')->where('id','=',$orders['members_id'])->increment('money', $orders['amount']);
                            }
                        }else{
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
                        }
                        $rows	=	array(
                            'members_id'    =>  $orders['members_id'],
                            'order_no'		=>	$out_trade_no,
                            'type'          =>  $type,
                            'fee'			=>	$orders['amount'],
                            'balance'       =>  DB::table('members')->find($orders['members_id'])['money'],
                            'status'        =>  1,
                            'remark'		=>	$remark,
                            'created_at'	=>	date('Y-m-d H:i:s'),
                        );
                        DB::table('orderslog')->insert($rows);
                        DB::commit();
                        ob_clean();
                        exit($returnXml);
                    }catch(Exception $e){
                        DB::rollBack();
                        exit('FAIL');
                    }
                }
            }
        }
        exit('FAIL');
    }

    #微信分享
    public function wxshareAction(){
        //1.获取你的AppID和AppSecret
        $AppID = 'wx57a8ea9c50364f65';
        $AppSecret	=	'3b13c96952df3f6d04f16b7ae1dfce83';
        //2.获取令牌 wx_get_token
        $token = $this->wx_get_token($AppID, $AppSecret);
        //3.获取jsapi的ticket wx_get_jsapi_ticket
        $wxticket= $this->wx_get_jsapi_ticket($token);
        //4.签名 将jsapi_ticket、noncestr、timestamp、分享的url按字母顺序连接起来，进行sha1签名。noncestr是你设置的任意字符串。timestamp为时间戳。
        $timestamp = time();
        $wxnonceStr= uniqid();
        $shareurl  = $this->get('shareurl', "");
        $wxOri = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $wxticket, $wxnonceStr, $timestamp, $shareurl);
        $wxSha1 = sha1($wxOri);
        //echo $wxOri, "\r\n", $wxSha1;	exit;

        $result = array(
            'code'	=>	1,
            'msg'	=>	'获取微信分享参数',
            'data'	=>	array(
                "appId"  	=> $AppID,
                "timestamp" => $timestamp,
                "nonceStr"	=> $wxnonceStr,
                "signature"	=> $wxSha1,
                "link"      => "http://putuan.com/index/kuizeng/?order_no=".$this->get('order_no',''),
                "jsApiList" => "['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ']",
            )
        );
        json($result);
    }
    private function wx_get_token($AppID, $AppSecret) {
        $myCache= Cache::getInstance();
        $token  = $myCache->get('access_token');
        if (!$token) {
            $res = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$AppID.'&secret='.$AppSecret);
            $res = json_decode($res, true);
            $token = $res['access_token'];
            $myCache->set('access_token', $token, 3600);
        }
        return $token;
    }
    public function getUFOAction(){
        $openid = $this->get('openid', '');
        $AppID = 'wx57a8ea9c50364f65';
        $AppSecret	=	'3b13c96952df3f6d04f16b7ae1dfce83';
        //2.获取令牌 wx_get_token
        $access_token = $this->wx_get_token($AppID, $AppSecret);
        $urlStr = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN';
        $url = sprintf($urlStr,$access_token,$openid);
        $result = json_decode(file_get_contents($url),true);
        dump($result);
    }
    private function wx_get_jsapi_ticket($token){
        $ticket = "";
        do{
            $myCache= Cache::getInstance();
            $ticket = $myCache->get('wx_ticket');
            if (!empty($ticket)) {
                break;
            }
            $url2 = sprintf("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi", $token);
            $res = file_get_contents($url2);
            $res = json_decode($res, true);
            $ticket = $res['ticket'];
            $myCache->set('wx_ticket', $ticket, 3600);
        }while(FALSE);
        return $ticket;
    }

    //获取用户信息
    public function wxGetUserInfoAction(){
        $code = $this->get("code", '');//预定义的 $_GET 变量用于收集来自 method="get" 的表单中的值。
        if (!empty('code')){
            $userinfo = $this->getUserInfo($code);
            if($userinfo){
                if(!empty($userinfo['openid'])){
                    $user = DB::table('members')->where('openid','=',$userinfo['openid'])->first();
                    if(!empty($user)){
                        #设置登陆
                        $info=(new membersModel)->setAutoLogin($userinfo['openid']);
                        ret(0, '登陆成功', $info);
                    }else{
                        ret(2, '新微信用户', array(
                            'name'			=>	$userinfo['nickname'],
                            'gender'        =>  $userinfo['sex'],
                            'avatar'		=>	$userinfo['headimgurl'],
                            'openid'		=>	$userinfo['openid'],
                        ));
                    }
                }
            }
        }
        ret(1, '获取用户信息失败', ['code'=>$code]);
    }

    private function getUserInfo($code)
    {
        $appid = 'wx6b3786e8bf9bd501';
        $appsecret	=	'838c55d51b4bc5f108b3be16a5827092';
        $access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appsecret}&code={$code}&grant_type=authorization_code";
        $access_token_json = $this->https_request($access_token_url);//自定义函数
        $access_token_array = json_decode($access_token_json,true);//对 JSON 格式的字符串进行解码，转换为 PHP 变量，自带函数
        $access_token = $access_token_array['access_token'];//获取access_token对应的值
        $openid = $access_token_array['openid'];//获取openid对应的值
        //Get user info
        $userinfo_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid";
        $userinfo_json = $this->https_request($userinfo_url);
        $userinfo_array = json_decode($userinfo_json,ture);
        return $userinfo_array;
    }

    private function https_request($url)//自定义函数,访问url返回结果
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl,  CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)){
            return 'ERROR'.curl_error($curl);
        }
        curl_close($curl);
        return $data;
    }

}
