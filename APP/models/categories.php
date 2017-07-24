<?phpuse Illuminate\Database\Eloquent\Model;
class categoriesModel extends Model{	protected $table 		= 'categories';	protected $primaryKey	= 'id';		public function Products()	{		return $this->hasMany('productsModel', 'categories_id', 'id');	}  
	
}
