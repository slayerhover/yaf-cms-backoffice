<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class InvoiceModel extends Model
{
	protected $table 		= 'invoice';
	protected $primaryKey	= 'id';	
	protected $appends 		= ['ivoice_status_name'];
	
	public function getGoodsAttribute($value)
	{	
		return json_decode($value, TRUE);	
	}
	
	public function getInvoiceStatusNameAttribute($value)
	{
        return $value==1 ? '已开具发票' : '未开具发票';
	}
	
	public function getInvoice($param){
	
		$rows	= DB::table('invoice')->join('orders','invoice.order_no','=','orders.order_no')
									  ->where('orders.members_id','=',$param['members_id'])
									  ->where('orders.status','<>',100)
									  ->where('orders.status','<>',300)									  
									  ->orderBy('orders.id', 'DESC')
									  ->offset($param['offset'])
									  ->limit($param['pagesize'])
									  ->select('invoice.*','orders.amount','orders.paid_type','orders.paid_at','orders.transactionno','orders.shipping_name','orders.shipping_phone','orders.shipping_province','orders.shipping_city','orders.shipping_area','orders.shipping_address','orders.remark');
		if($param['status']<2){
			$rows	=	$rows->where('invoice.status',$param['status']);
		}
		return $rows->get();
	}
}