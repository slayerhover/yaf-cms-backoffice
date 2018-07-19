<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class activityModel extends Model
{
	protected $table 		= 'activity';
	protected $primaryKey	= 'id';	
	protected $appends 		= ['goods', 'timestamp', 'now'];

	public function getGoodsAttribute()
	{	
		$rows	= DB::table('promotion')->join('goods','promotion.goods_id','=','goods.id')
									  ->where('promotion.activity_id','=',$this->attributes['id'])
                                      ->where('goods.status', '=', 1)
                                      ->select('promotion.promotionprice','goods.id','goods.name','goods.price','goods.currentprice','goods.logo')
									  ->orderBy('promotion.id', 'DESC');
		return $rows->get();
	}
	
	public function getTimestampAttribute()
	{
		return strtotime($this->attributes['end_on'])-time();
	}

    public function getNowAttribute()
    {
        return time();
    }
	
}
