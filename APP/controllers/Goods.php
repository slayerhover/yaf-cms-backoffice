<?php
use Illuminate\Database\Capsule\Manager as DB;

class GoodsController extends CoreController {
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
	
	#产品品牌列表
	public function brandAction() {		
		$keywords	=$this->get('keywords', '');
		$sort		=$this->get('sort', 'sortorder');
		$order		=$this->get('order','desc');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize',10);
		$offset		=($page-1)*$pagesize;
		$query		= DB::table('brand');
		if($keywords!==''){
			$query	=	$query	->where('name','like',"%{$keywords}%");
		}		
		$total		= $query->count();
		$rows 		= $query->orderBy($sort,$order)->offset($offset)->limit($pagesize)->get();
		ret(0, '商品品牌', ['total'=>$total, 'rows'=>$rows]);
    }	
	#产品品牌添加
	public function brandAddAction() {		
		$name	=	$this->get('name','');
		$desc	=	$this->get('desc', '');
		$logo	=	$this->get('logo', '');
		$sortorder= $this->get('sortorder', 500);
		$inputs		= array(
				['name'=>'name','value'=>$name,'role'=>'required|unique:brand.name','msg'=>'产品品牌名称有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(
				'name'		=>	$name,
				'desc'		=>	$desc,
				'logo'		=>	$logo,
				'sortorder'	=>	$sortorder,
				'created_at'=>	date('Y-m-d H:i:s'),
		);
		if( DB::table('brand')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }
	#产品品牌修改
	public function brandEditAction() {
		$id		=	$this->get('id', 0);
		$name	=	$this->get('name','');
		$desc	=	$this->get('desc', '');
		$logo	=	$this->get('logo', '');
		$sortorder= $this->get('sortorder', 500);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:brand.id','fun'=>'isInt','msg'=>'产品品牌ID有误'],
				['name'=>'name','value'=>$name,'role'=>'required','msg'=>'产品品牌名字为空'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}		
		$rows	=	array(
				'name'		=>	$name,
				'desc'		=>	$desc,
				'logo'		=>	$logo,
				'sortorder'	=>	$sortorder,
				'updated_at'=>	date('Y-m-d H:i:s'),
		);
		if( DB::table('brand')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#产品品牌删除
	public function brandDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:brand.id','fun'=>'isInt','msg'=>'产品品牌ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}		
		if( DB::table('brand')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
		
	#目录列表
	public function catAction() {		
		$keywords=urldecode($this->get('keywords',''));		
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$sort=$this->get('sort','sortorder');
		$order=$this->get('order','desc');
		$query	 = new goodscatModel;;
		if($keywords!==''){
			$query	=	$query	->where('title','like',"%{$keywords}%");
		}else{
			$query	=	$query	->where('up','=','0');
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
	#目录添加
	public function catAddAction() {
		$title	=	$this->get('title','');
		$up		=	$this->get('up', 0);
		$sortorder= $this->get('sortorder', 500);
		$logo	=	$this->get('logo', '');
		$status	=	$this->get('status', 1);
		$attributes=$this->get('attributes', []);
		$inputs		= array(
				['name'=>'title','value'=>$title,'role'=>'required|unique:goods_cat.title','msg'=>'产品目录标题有误'],
				['name'=>'up','value'=>$up,	'fun'=>'isInt', 'msg'=>'上级目录ID有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}		
		$rows	= array(
				'title'		=>	$title,
				'up'		=>	$up,
				'status'	=>	$status,
				'sortorder'	=>	$sortorder,
				'attributes'=>	implode(',', $attributes),
                'created_at'=>	date('Y-m-d H:i:s'),
		);
		if(!empty($logo)&&$image=$this->uploader($logo)){
				$rows['logo'] = $image;
		}
		if( DB::table('goods_cat')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }
	#目录修改
	public function catEditAction() {
		$id		=	$this->get('id', 0);
		$title	=	$this->get('title','');
		$up		=	$this->get('up', 0);
		$logo	=	$this->get('logo', '');
		$sortorder= $this->get('sortorder', 500);
		$attributes=$this->get('attributes', []);
		$status	=	$this->get('status', 1);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:goods_cat.id|gt:0','fun'=>'isInt','msg'=>'产品目录ID有误'],
				['name'=>'title','value'=>$title,'role'=>'required','msg'=>'产品目录标题有误'],
				['name'=>'up','value'=>$up,'role'=>'neq:'.$id,'fun'=>'isInt','msg'=>'上级目录ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	=	array(
				'title'		=>	$title,
				'up'		=>	$up,
				'status'	=>	$status,
				'sortorder'	=>	$sortorder,
				'attributes'=>	implode(',', $attributes),
				'updated_at'=>	date('Y-m-d H:i:s'),
		);
		if(!empty($logo)&&$image=$this->uploader($logo)){
				$rows['logo'] = $image;
		}
		if( DB::table('goods_cat')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#目录删除
	public function catDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:goods_cat.id|gt:0','fun'=>'isInt','msg'=>'产品目录ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if( DB::table('goods_cat')->where('up','=',$id)->count()>0 ){
			ret(3, '有下级目录存在，无法删除.');
		}
		if( DB::table('goods_cat')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }

    #目录列表
    public function labelAction() {
        $keywords=urldecode($this->get('keywords',''));
        $page		=$this->get('page',1);
        $pagesize	=$this->get('pagesize', 10);
        $offset		=($page-1)*$pagesize;
        $sort=$this->get('sort','sortorder');
        $order=$this->get('order','desc');
        $query	 = DB::table('label');
        if($keywords!==''){
            $query	=	$query	->where('name','like',"%{$keywords}%");
        }
        $total		= $query->count();
        $rows 		= $query->orderBy($sort,$order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();
        ret(0, '商品标签', ['total'=>$total, 'rows'=>$rows]);
    }
    #标签添加
    public function labelAddAction() {
        $name	=	$this->get('name','');
        $sortorder= $this->get('sortorder', 500);
        $logo	=	$this->get('logo', '');
        $inputs		= array(
            ['name'=>'name','value'=>$name,'role'=>'required|unique:label.name','msg'=>'产品标签输入有误'],
        );
        $result		= Validate::check($inputs);
        if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
        $rows	= array(
            'name'		=>	$name,
            'sortorder'	=>	$sortorder,
            'created_at'=>	date('Y-m-d H:i:s'),
        );
        if(!empty($logo)&&$image=$this->uploader($logo)){
            $rows['logo'] = $image;
        }
        if( DB::table('label')->insert($rows) ){
            ret(0, '操作成功');
        }
        ret(2, '数据插入失败');
    }
    #标签修改
    public function labelEditAction() {
        $id		=	$this->get('id', 0);
        $name	=	$this->get('name','');
        $logo	=	$this->get('logo', '');
        $sortorder= $this->get('sortorder', 500);
        $inputs	= array(
            ['name'=>'id','value'=>$id,'role'=>'required|exists:label.id|gt:0','fun'=>'isInt','msg'=>'产品标签ID有误'],
            ['name'=>'name','value'=>$name,'role'=>'required','msg'=>'产品标签有误'],
        );
        $result	= Validate::check($inputs);
        if(	!empty($result) ){ret(1, '输入参数有误.', $result);}

        $rows	=	array(
            'name'		=>	$name,
            'sortorder'	=>	$sortorder,
            'updated_at'=>	date('Y-m-d H:i:s'),
        );
        if(!empty($logo)&&$image=$this->uploader($logo)){
            $rows['logo'] = $image;
        }
        if( DB::table('label')->where('id','=',$id)->update($rows)!==FALSE ){
            ret(0, '操作成功');
        }
        ret(2, '数据插入失败');
    }
    #目录删除
    public function labelDelAction() {
        $id = $this->get('id', 0);
        $inputs	= array(
            ['name'=>'id','value'=>$id,'role'=>'required|exists:label.id|gt:0','fun'=>'isInt','msg'=>'产品标签ID有误'],
        );
        $result	= Validate::check($inputs);
        if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
        if( DB::table('label')->delete($id) ){
            ret(0, '操作成功');
        }
        ret(2, '数据插入失败');
    }
	
	#产品属性列表
	public function attrAction() {		
		$keywords	=$this->get('keywords', '');
		$sort		=$this->get('sort', 'sortorder');
		$order		=$this->get('order','desc');
		$query		= DB::table('attribute');
		if($keywords!==''){
			$query	=	$query	->where('name','like',"%{$keywords}%")
								->orWhere('values', 'like', "%{$keywords}%");
		}		
		$total		= $query->count();
		$rows 		= $query->orderBy($sort,$order)->get();						
		foreach($rows as &$v){
			$v['values']	=	explode(';', str_replace('；',';', $v['values']));
		}
		ret(0, '商品属性', ['total'=>$total, 'rows'=>$rows]);
    }	
	#产品属性添加
	public function attrAddAction() {		
		$name	=	$this->get('name','');
		$values	=	$this->get('values', '');
		$sortorder= $this->get('sortorder', 500);
		$input_type	=$this->get('input_type', 1);
		$inputs		= array(
				['name'=>'name','value'=>$name,'role'=>'required|unique:attribute.name','msg'=>'产品属性名称有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(
				'name'		=>	$name,
				'values'	=>	str_replace('；',';', $values),
				'sortorder'	=>	$sortorder,
				'input_type'=>	$input_type,
				'created_at'=>	date('Y-m-d H:i:s'),
		);
		if( DB::table('attribute')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }
	#产品属性修改
	public function attrEditAction() {
		$id		=	$this->get('id', 0);
		$name	=	$this->get('name','');
		$values	=	$this->get('values', '');
		$sortorder= $this->get('sortorder', 500);
		$input_type	=$this->get('input_type', 1);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:attribute.id','fun'=>'isInt','msg'=>'产品目录ID有误'],
				['name'=>'name','value'=>$name,'role'=>'required','msg'=>'产品目录标题有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}		
		$rows	=	array(
				'name'		=>	$name,
				'values'	=>	str_replace('；',';', $values),
				'sortorder'	=>	$sortorder,
				'input_type'=>	$input_type,
				'updated_at'=>	date('Y-m-d H:i:s'),
		);
		if( DB::table('attribute')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#产品属性删除
	public function attrDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:attribute.id','fun'=>'isInt','msg'=>'产品目录ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}		
		if( DB::table('attribute')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	
	#产品列表
	public function goodsAction() {		
		$status		=intval($this->get('status',2));
		$itemno		=$this->get('itemno','');
		$cat_id		=intval($this->get('cat_id', 0));
        $label_id   =$this->get('label_id', 0);
		$keywords	=$this->get('keywords', '');		
		$isnew		=intval($this->get('isnew',2));
		$ishot		=intval($this->get('ishot',2));
		$istop		=intval($this->get('istop',2));
		$trashed	=intval($this->get('trashed', 0));
		$sort		=$this->get('sort', 'created_at');
		$order		=$this->get('order','DESC');
		$page		=$this->get('page',1);
		$pagesize	=$this->get('pagesize', 10);
		$offset		=($page-1)*$pagesize;
		$query		= new goodsModel;
		if($status===0||$status===1){
			$query	=	$query	->where('status','=',$status);
		}
		if($itemno!==''){
			$query	=	$query	->where('itemno','like',"%{$itemno}%");
		}
		if($cat_id>0){
			$query	=	$query	->whereRaw('FIND_IN_SET(?,cat_id)', [$cat_id]);
		}
		if($label_id>0){
            $query	=	$query	->whereRaw('FIND_IN_SET(?,label_ids)', [$label_id]);
        }
		if($keywords!==''){
			$query	=	$query	->where('name','like',"%{$keywords}%")
								->orWhere('keywords', 'like', "%{$keywords}%")
								->orWhere('introduce','like', "%{$keywords}%");
		}
		$stock	=$this->get('stock','');
		if($stock!==''){
			$query	=	$query->where('stock','<=',$stock);
		}
		if($isnew===0||$isnew===1){
			$query	=	$query	->where('isnew','=',$isnew);
		}
		if($ishot===0||$ishot===1){
			$query	=	$query	->where('ishot','=',$ishot);
		}
		if($istop===0||$istop===1){
			$query	=	$query	->where('istop','=',$istop);
		}
		if($trashed===1){
			$query	=	$query	->where('deleted_at','<>','0000-00-00 00:00:00');
		}else{
			$query	=	$query	->where('deleted_at','=','0000-00-00 00:00:00');
		}
		$recorddate	=$this->get('recorddate', '');
		if(!empty($recorddate)){
			$recorddate = explode(' - ', $recorddate);
			$starton=$recorddate[0];
			$endon	=$recorddate[1];
			$query	=	$query	->where('created_at','>=',$starton)
								->where('created_at','<=',$endon);
		}
		$total		= $query->count();
		$totalpage	= ceil($total/$pagesize);
		$rows 		= $query->orderBy($sort,$order)->orderBy('id','desc')->offset($offset)->limit($pagesize)->get()->toArray();

		ret(0, '商品列表', ['cat_id'=>$cat_id,'keywords'=>$keywords,'isnew'=>$isnew,'ishot'=>$ishot,'istop'=>$istop,'trashed'=>$trashed,'sort'=>$sort,'order'=>$order,'stock'=>$stock,'page'=>$page,'pagesize'=>$pagesize,'total'=>$total,'totalpage'=>$totalpage,'rows'=>$rows]);
    }
	#产品添加
	public function goodsAddAction() {
		$rows	= array(
				'cat_id'	    =>$this->get('cat_id', 0),
				'label_ids'     =>implode(',', $this->get('label_ids', [])),
				'name'		    =>$this->get('name',   ''),
				'englishname'   =>$this->get('englishname',   ''),
				'keywords'	    =>$this->get('keywords', ''),
				'title'		    =>$this->get('title', ''),
				'description'   =>$this->get('description', ''),
				'logo'		    =>$this->get('logo', ''),
				'images'	    =>$this->get('images', []),
				'introduce'	    =>$this->get('introduce',''),
				'price'		    =>$this->get('price',0.00),
				'currentprice'  =>$this->get('currentprice',0.00),
				'stock'		    =>$this->get('stock',100),
				'unit'          =>$this->get('unit', '瓶'),
				'recommend'     =>$this->get('recommend',0),
				'isnew'		    =>$this->get('isnew',0),
				'ishot'		    =>$this->get('ishot',0),
				'hits'		    =>$this->get('hits',0),
				'score'		    =>$this->get('score',0),
				'iscrossborder' =>$this->get('iscrossborder',0),
				'istop'		    =>$this->get('istop',0),
				'minquantity'   =>$this->get('minquantity',1),
                'maxpurchase'   =>$this->get('maxpurchase',0),
                'maxordersquantity'=>$this->get('maxordersquantity',0),
				'itemno'	    =>$this->get('itemno',''),
				'rank'		    =>$this->get('rank',0.00),
                'rank_money'    =>$this->get('rank_money',0.00),
				'producthtml'   =>$this->get('producthtml',''),
				'weight'	    =>$this->get('weight',0.00),
				'netweight'	    =>$this->get('netweight',0.00),
				'specid'	    =>$this->get('specid',0),
				'spec'		    =>$this->get('spec', ''),
				'sortorder'	    =>$this->get('sortorder',500),
				'status'	    =>$this->get('status',0),
				'created_at'    =>date('Y-m-d H:i:s'),
		);
        $inputs		= array(
            ['name'=>'name','value'=>$rows['name'],'role'=>'required|unique:attribute.name','msg'=>'产品名称有误'],
            ['name'=>'cat_id','value'=>$rows['cat_id'],'role'=>'required|existed:goods_cat.id','msg'=>'产品目录ID有误'],
        );
        $result		= Validate::check($inputs);
        if(	!empty($result) ){ret(1, $result);}
		if( $goods_id=DB::table('goods')->insertGetId($rows) ){
				$attr		=$this->get('attr', []);
				if(!empty($attr)){
					#$attr = json_decode($attr, TRUE);
					foreach($attr as $k=>$v){
					    if(empty($v['attr_value'])) continue;
						$rows =array(
							'goods_id'	=>$goods_id,
							'attr_id'	=>$v['id'],
							'attr_value'=>$v['attr_value'],
							'attr_price'=>0.00,
						);
						DB::table('goods_attr')->insert($rows);
					}
				}
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
    }
	#产品修改
	public function goodsEditAction() {
		$id		=	$this->get('id', 0);
        $cat_id		=$this->get('cat_id', 0);
        $label	    =$this->get('label', []);
		$name		=$this->get('name',   '');
		$englishname=$this->get('englishname',   '');
		$keywords	=$this->get('keywords', '');
		$title		=$this->get('title', '');
		$description=$this->get('description', '');
		$logo		=$this->get('logo', '');
		$images		=$this->get('images', []);
		$introduce	=$this->get('introduce','');
		$price		=$this->get('price',0.00);
		$currentprice=$this->get('currentprice',0.00);
		$stock		=$this->get('stock',100);
        $unit       =$this->get('unit', '瓶');
		$recommend	=$this->get('recommend',0);
		$isnew		=$this->get('isnew',0);
		$ishot		=$this->get('ishot',0);
		$hits		=$this->get('hits',0);
		$score		=$this->get('score',0);
		$iscrossborder=$this->get('iscrossborder',0);
		$istop		=$this->get('istop',0);
		$minquantity=$this->get('minquantity',1);
        $maxpurchase=$this->get('maxpurchase',0);
        $maxordersquantity=$this->get('maxordersquantity',0);
		$itemno		=$this->get('itemno','');
		$rank		=$this->get('rank',0.00);
        $rank_money	=$this->get('rank_money',0.00);
		$producthtml=$this->get('producthtml','');
		$weight		=$this->get('weight',0.00);
		$netweight	=$this->get('netweight',0.00);		
		$sortorder	=$this->get('sortorder',500);
		$status		=$this->get('status',0);
		$inputs		= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:goods.id','msg'=>'产品ID有误'],
				['name'=>'name','value'=>$name,'role'=>'required|unique:attribute.name','msg'=>'产品名称有误'],
                ['name'=>'cat_id','value'=>$cat_id,'role'=>'required|existed:goods_cat.id','msg'=>'产品目录ID有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
        $label_ids=[];
        if(is_array($label)){
		    foreach ($label as $v){
		        if($v['flag']=='true'){
		            $label_ids[] = $v['id'];
                }
            }
        }
        $label_ids = implode(',', $label_ids);
		$rows	= array(
				'cat_id'	=>	$cat_id,
                'label_ids' =>  $label_ids,
				'name'		=>	$name,				
				'englishname'=>	$englishname,				
				'keywords'	=>$keywords,
				'title'		=>$title,
				'description'=>$description,
				'logo'		=>$logo,
                'images'	=>implode(',',$images),
				'introduce'	=>$introduce,
				'price'		=>$price,
				'currentprice'=>$currentprice,
				'stock'		=>$stock,
                'unit'      =>$unit,
				'recommend'=>$recommend,
				'isnew'		=>$isnew,
				'ishot'		=>$ishot,
				'hits'		=>$hits,
				'score'		=>$score,
				'iscrossborder'=>$iscrossborder,
				'istop'		=>$istop,
				'minquantity'=>$minquantity,
                'maxpurchase'=>$maxpurchase,
                'maxordersquantity'=>$maxordersquantity,
				'itemno'	=>$itemno,
				'rank'		=>$rank,
                'rank_money'=>$rank_money,
				'producthtml'=>$producthtml,
				'weight'	=>$weight,
				'netweight'	=>$netweight,
				'sortorder'	=>$sortorder,
				'status'	=>$status,
				'updated_at'=>date('Y-m-d H:i:s'),
		);		
		if( DB::table('goods')->where('id','=',$id)->update($rows)!==FALSE ){
			$attr		=$this->get('attr', []);
			if(!empty($attr)){
				$attr_id_arr = [];
				foreach($attr as $k=>$v){
                    if(empty($v['attr_value'])) continue;
                    $rows =array(
                        'goods_id'	=>$id,
                        'attr_id'	=>$v['id'],
                        'attr_value'=>$v['attr_value'],
                        'attr_price'=>0.00,
                    );
					$attr_id_arr[] =$v['id'];
					if(DB::table('goods_attr')->where('goods_id','=',$id)->where('attr_id','=',$v['id'])->count()>0){
						DB::table('goods_attr')->where('goods_id','=',$id)->where('attr_id','=',$v['id'])->update($rows);
					}else{
						DB::table('goods_attr')->where('goods_id','=',$id)->where('attr_id','=',$v['id'])->insert($rows);
					}
				}
				#DB::table('goods_attr')->where('goods_id','=',$id)->whereNotIn('attr_id',$attr_id_arr)->delete();
			}
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#产品状态属性修改
	public function goodsStatusEditAction() {
		$id		=	$this->get('id', 0);		
		$type	=	$this->get('type','');
		$value	=	$this->get('value', 0);
		$inputs		= array(
			['name'=>'id','value'=>$id,'role'=>'required|exists:goods.id','msg'=>'产品ID有误'],
			['name'=>'type','value'=>$type,'role'=>'required|in:recommend,isnew,ishot,iscrossborder,istop,status','msg'=>'产品状态属性有误'],
			['name'=>'value','value'=>$value,'role'=>'required','func'=>'isBool','msg'=>'状态值有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}					
		$rows = [$type=>$value, 'updated_at'=>date('Y-m-d H:i:s')];
		if( DB::table('goods')->where('id','=',$id)->update($rows)!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
    #产品排序修改
    public function goodsSortorderEditAction() {
        $id		    =	$this->get('id', 0);
        $sortorder	=	$this->get('sortorder', 0);
        $inputs		= array(
            ['name'=>'id','value'=>$id,'role'=>'required|exists:goods.id','msg'=>'产品ID有误'],
            ['name'=>'sortorder','value'=>$sortorder,'role'=>'required','func'=>'isInt','msg'=>'数值有误'],
        );
        $result		= Validate::check($inputs);
        if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
        $rows = ['sortorder'=>$sortorder, 'updated_at'=>date('Y-m-d H:i:s')];
        if( DB::table('goods')->where('id','=',$id)->update($rows)!==FALSE ){
            ret(0, '操作成功');
        }
        ret(2, '数据插入失败');
    }
	#产品放入回收站
	public function goodsTrashAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:goods.id','fun'=>'isInt','msg'=>'产品ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if( DB::table('goods')->where('id','=',$id)->update(['deleted_at'=>date('Y-m-d H:i:s')]) ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#产品撤消回收站
	public function goodsUnTrashAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:goods.id','fun'=>'isInt','msg'=>'产品ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if( DB::table('goods')->where('id','=',$id)->update(['deleted_at'=>0])!==FALSE ){
			ret(0, '操作成功');
		}
		ret(2, '数据更新失败');	
    }
	#产品删除
	public function goodsDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:goods.id','fun'=>'isInt','msg'=>'产品ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if( DB::table('goods')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#产品批量删除
	public function goodsBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('goods')->delete($id);
		}
		ret(0, '操作成功');	
    }
	
	
	#产品的属性添加
	public function goodsAttrAddAction() {		
		$goods_id	=	$this->get('goods_id','');
		$attr_id	=	$this->get('attr_id','');
		$attr_value =	$this->get('attr_value', '');
		$attr_price =	$this->get('attr_price', 0.00);
		$inputs		= array(
				['name'=>'goods_id','value'=>$goods_id,'role'=>'required|exists:goods.id','msg'=>'产品ID有误'],
				['name'=>'attr_id','value'=>$attr_id,'role'=>'required|exists:attribute.id','msg'=>'产品属性ID有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(
				'goods_id'		=>	$goods_id,
				'attr_id'		=>	$attr_id,
				'attr_value'	=>	$attr_value,
				'attr_price'	=>	$attr_price,
		);
		if( DB::table('goods_attr')->insert($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据插入失败');
	}
	#产品的属性编辑
	public function goodsAttrEditAction() {		
		$id		=	$this->get('id','');
		$goods_id	=	$this->get('goods_id','');
		$attr_id	=	$this->get('attr_id','');
		$attr_value =	$this->get('attr_value', '');
		$attr_price =	$this->get('attr_price', 0.00);
		$inputs		= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:goods_attr.id','msg'=>'产品属性ID有误'],
				['name'=>'goods_id','value'=>$goods_id,'role'=>'required|exists:goods.id','msg'=>'产品ID有误'],
				['name'=>'attr_id','value'=>$attr_id,'role'=>'required|exists:attribute.id','msg'=>'属性ID有误'],
		);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		$rows	= array(
				'goods_id'		=>	$goods_id,
				'attr_id'		=>	$attr_id,
				'attr_value'	=>	$attr_value,
				'attr_price'	=>	$attr_price,
		);
		if( DB::table('goods_attr')->where('id','=',$id)->update($rows) ){
				ret(0, '操作成功');
		}
		ret(2, '数据更新失败');
	}
	#产品属性删除
	public function goodsattrDelAction() {
		$id = $this->get('id', 0);
		$inputs	= array(
				['name'=>'id','value'=>$id,'role'=>'required|exists:goods_attr.id','msg'=>'产品属性ID有误'],
		);
		$result	= Validate::check($inputs);
		if(	!empty($result) ){ret(1, '输入参数有误.', $result);}
		if( DB::table('goods_attr')->delete($id) ){
			ret(0, '操作成功');
		}
		ret(2, '数据插入失败');	
    }
	#产品属性批量删除
	public function goodsattrBatchDelAction() {
		$ids = $this->get('ids', []);
		foreach($ids as $id){
			DB::table('goods_attr')->delete($id);
		}
		ret(0, '操作成功');	
    }
	
	public function goodsimagesAddAction(){
		$goods_id=$this->get('goods_id', 0);
		$files	= $this->get('image', '');
		if(empty($files)){
			ret(3, '图片内容为空');
		}
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $files, $base64result)){
			$type	  = $base64result[2];
			if($type=='jpeg') $type='jpg';
			$config	  = Yaf_Registry::get('config');
			$filename = 'logo-t' . time() . '.' . $type;		
			$path	  = '/logo/' . date('Ym') . '/';
			$descdir  = $config['application']['uploadpath'] . $path;
			if(!is_dir($descdir)){ mkdir($descdir, 0777, TRUE); }
			$realpath = $descdir . $filename;				
			$webpath  = $config['application']['uploadwebpath'] . $path . $filename;
			if(!file_put_contents($realpath, base64_decode(str_replace(' ', '+', str_replace($base64result[1], '', $files))))){				
				ret(4, '储存图片出错.');
			}
			$cdnfilename = 'Img-t' . time().rand(1000,9999) . '.' . $type;
			if( $image = $this->uploadToCDN($realpath, $cdnfileName) ){
				$newimgs=[];
				$images= explode(',', DB::table('goods')->find($goods_id)['images']);
				foreach($images as $v){
					if(!empty($v)) $newimgs[] = $v;
				}
				$newimgs[] = $image;
				DB::table('goods')->where('id','=',$goods_id)->update(['images'=>implode(',', $newimgs)]);
				ret(0, '上传图片成功', $image);
			}else{
				ret(1, '上传图片失败');
			}
		}else{
			ret(2, '上传图片格式有误');
		}
	}
	public function goodsimagesDelAction(){
		$goods_id=$this->get('goods_id', 0);
		$files	= $this->get('image', '');
		if(empty($files)){
			ret(3, '图片url为空');
		}
		
		$newimgs=[];
		$images= explode(',', DB::table('goods')->find($goods_id)['images']);
		foreach($images as $v){				
			if($v==$files) continue;
			if(!empty($v)) $newimgs[] = $v;
		}
		DB::table('goods')->where('id','=',$goods_id)->update(['images'=>implode(',', $newimgs)]);
		ret(0, '删除图片成功', $files);
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
			$filename = 'logo-t' . time() . '.' . $type;		
			$path	  = '/logo/' . date('Ym') . '/';
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
		
	
}
