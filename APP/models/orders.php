<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class ordersModel extends Model
{
	protected $table 		= 'orders';
	protected $primaryKey	= 'id';	
	protected $appends 		= ['status_name','invoice','coupon','station','shipping_type_name','members'];
	
	public function getGoodsAttribute($value)
	{
        $goods = json_decode($value, TRUE);
        if(is_array($goods)){
        foreach ($goods as &$v){
            $v['cat_id']= DB::table('goods')->find($v['goods_id'])['cat_id'];
        }}
		return $goods;
	}
	
	public function getInvoiceAttribute()
	{	
		$order_no = $this->attributes['order_no'];
		return DB::table('invoice')->where('order_no',$order_no)->first();
	}

    public function getMembersAttribute()
    {
        $rows = DB::table('members')->select('id','phone','name','consultant_id','company','position','created_at')->find($this->attributes['members_id']);
        $rows['clientmanager'] = DB::table('admin')->where('id','=',$rows['consultant_id'])->pluck('name');
        return $rows;
    }
	
	public function getUpdatedAtAttribute($value)
	{	
		return $value<0 ? '0000-00-00 00:00:00' : $value;	
	}

    public function getPaidAtAttribute($value)
    {
        return $value=='0000-00-00 00:00:00'? '未支付' : $value;
    }

    public function getDeliveredAtAttribute($value)
    {
        return $value=='0000-00-00 00:00:00'? '未发货' : $value;
    }
	
	public function getStationAttribute()
	{
		return $this->attributes['station_id']>0 ? DB::table('station')->find($this->attributes['station_id']) : '';
	}

    public function getCouponAttribute()
    {
        return $this->attributes['coupon_id']>0 ? DB::table('couponlist')->join('coupon','couponlist.coupon_id','=','coupon.id')->where('couponlist.id','=',$this->attributes['coupon_id'])->first() : '';
    }
		
	public function getStatusNameAttribute()
	{		
		$statusName = '';
		switch($this->attributes['status']){
				case '100':
					$statusName	=	'待付款';
					break;
				case '200':
					if($this->attributes['shipping_type']==3){
						$statusName	=	'已完成';	
					}else{
						$statusName	=	'待发货';	
					}					
					break;
				case '300':
					$statusName	=	'已取消';
					break;
				case '400':
					$statusName	=	'待收货';
					break;
				case '500':
					$statusName	=	'待评价';
					break;
				case '600':
					$statusName	=	'退款中';
					break;
                case '700':
                    $statusName	=	'已退款';
                    break;
                case '800':
                    $statusName	=	'已完成';
                    break;
			}	
		return $statusName;	
	}
	
	public function getShippingTypeNameAttribute()
	{		
		$typeName = '';
		switch($this->attributes['shipping_type']){
				case '0':
					$typeName	=	'快递';
					break;
				case '1':
					$typeName	=	'自提';
					break;
				case '2':
					$typeName	=	'馈赠';
					break;
                case '3':
                    $typeName	=	'充值';
                    break;
		}
		return $typeName;	
	}
}