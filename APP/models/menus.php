<?phpuse Illuminate\Database\Eloquent\Model;
class menusModel extends Model{	protected $table 		= 'menus';	protected $primaryKey	= 'id';		public function submenu()	{		return $this->hasMany('menusModel', 'up', 'id');	}  
	
}
