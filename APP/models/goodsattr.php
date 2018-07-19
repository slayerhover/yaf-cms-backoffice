<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class goodsattrModel extends Model
{
	protected $table 		= 'goods_attr';
	protected $primaryKey	= 'id';

    public function goodsattr($goods_id, $cat_id)
	{
        $result =(new goodscatModel)->select('id','title','attributes','up','status')->find($cat_id)['attr'];
        $myAttr =$this->where('goods_id','=',$goods_id)->get()->toArray();
        foreach($result as $k=>&$v){
            #$v['values']    =explode(';', $v['values']);
            $v['attr_value'] = '';
            foreach ($myAttr as $k1 => $v1) {
                if ($v['id'] == $v1['attr_id']) {
                    $v['attr_value'] = $v1['attr_value'];
                }
            }
            $v['valueList'] = [];
            foreach ($v['values'] as $k2 => $v2) {
                array_push($v['valueList'], [
                    'index' => $k2,
                    'name'  => $v2,
                    'flag'  => $v['attr_value'] == $v2,
                ]);
            }
        }
		return $result;
	}
  
	

}

