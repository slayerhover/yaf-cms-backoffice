<?phpuse Illuminate\Database\Eloquent\Model;
class productsModel extends Model{	protected $table 		= 'products';	protected $primaryKey	= 'id';	public function Categories()	{		return $this->belongsTo('categoriesModel', 'categories_id', 'id');	}  
	
	public function getAllProducts($categories_id, $sort, $size=20){
		
		$conditions =($categories_id>0) ? " WHERE a.categories_id='".intval($categories_id)."' " : '';				
		$sortorder	=" ORDER BY {$sort} ";		if( is_array($size) ){$size = implode(',', $size);}
		$limit		=" LIMIT {$size}";
		
		$sql	=	"SELECT a.*,b.title as classname 
					 FROM `{products}` a INNER JOIN `{categories}` b 
					 ON a.categories_id=b.id 
					 {$conditions} {$sortorder} {$limit}";
		
		$rows	=	$this->getAll($sql);
		return $rows;
	}
	
	public function getRecommendProducts($categories_id, $sort, $size=20){
		
		$conditions =" WHERE a.`status`=1 AND a.`recommend`=1 ";
		$conditions.=($categories_id>0) ? " AND a.categories_id='{$categories_id}' " : '';				
		$sortorder	=" ORDER BY {$sort} ";		if( is_array($size) ){$size = implode(',', $size);}
		$limit		=" LIMIT {$size}";
		
		$sql	=	"SELECT a.*,b.title as classname 
					 FROM `{products}` a INNER JOIN `{categories}` b 
					 ON a.categories_id=b.id 
					 {$conditions} {$sortorder} {$limit}";
		
		$rows	=	$this->getAll($sql);
		return $rows;
	}
	
	
	public function getproducts($id){
		$conditions	= " WHERE a.id='".intval($id)."'";
		$sql	=	"SELECT a.*, b.title as classname 
					 FROM `{products}` a  INNER JOIN `{categories}` b 
					 ON a.categories_id=b.id 
					 {$conditions}";
		
		$rows	=	$this->getRow($sql);
		return $rows;
	}
	
	public function getproductsNum($categories_id=0){
		$conditions	=($categories_id>0) ? " categories_id='{$categories_id}' " : '';
		return (new Table('products'))->findCount($conditions);
	}

	public function addProducts($rows){		
		if( empty($rows['logo']) && preg_match('#<img\s+src\=[\"\']([^\"\']*)[\"\']#is', stripslashes($rows['info']), $imagesurl) ){
			$pathinfo	=	pathinfo($imagesurl[1]);			
			if( stripos($pathinfo['dirname'], 'http://')===FALSE ){
				$thumbnail	=	$pathinfo['dirname'].'/'.$pathinfo['filename'].'_thum.'.$pathinfo['extension'];				
				$rows['logo']	=	ImageManager::thumbnail($imagesurl[1], $thumbnail, 200, $pathinfo['extension']);			
			}else{
				$config	= Yaf_Registry::get('config');
				$filename = 'products-t' . time();
				$descdir  = $config['application']['uploadpath'] . '/Products/';
				if( !is_dir($descdir) ){ mkdir($descdir); }
				$descdir  = $config['application']['uploadpath'] . '/Products/' . date('Ym') . '/';
				if( !is_dir($descdir) ){ mkdir($descdir); }
				$realpath = $descdir . $filename . '.' . $pathinfo['extension'];
				file_put_contents($realpath, file_get_contents($imagesurl[1]));
				$thumbnail	=	$descdir . $filename . '_thum.' . $pathinfo['extension'];				
				$rows['logo']	=	ImageManager::thumbnail($realpath, $thumbnail, 200, $pathinfo['extension']);
			}
		}
		return	(new Table('products'))->add($rows);
	}
	
	public function updateProducts($rows){
		$_DB	=	new Table('products');
		$data	=	$_DB->find($rows['id']);
		if(empty($data))
			return FALSE;
		
		if( empty($data['logo']) && empty($rows['logo']) && preg_match('#<img\s+src\=[\"\']([^\"\']*)[\"\']#is', stripslashes($rows['info']), $imagesurl) ){
			$pathinfo	=	pathinfo($imagesurl[1]);						
			if( stripos($pathinfo['dirname'], 'http://')===FALSE ){
				$thumbnail	=	$pathinfo['dirname'].'/'.$pathinfo['filename'].'_thum.'.$pathinfo['extension'];				
				$rows['logo']	=	ImageManager::thumbnail($imagesurl[1], $thumbnail, 200, $pathinfo['extension']);			
			}else{
				$config	= Yaf_Registry::get('config');
				$filename = 'products-t' . time();
				$descdir  = $config['application']['uploadpath'] . '/Products/';
				if( !is_dir($descdir) ){ mkdir($descdir); }
				$descdir  = $config['application']['uploadpath'] . '/Products/' . date('Ym') . '/';
				if( !is_dir($descdir) ){ mkdir($descdir); }
				$realpath = $descdir . $filename . '.' . $pathinfo['extension'];
				file_put_contents($realpath, file_get_contents($imagesurl[1]));
				$thumbnail	=	$descdir . $filename . '_thum.' . $pathinfo['extension'];				
				$rows['logo']	=	ImageManager::thumbnail($realpath, $thumbnail, 200, $pathinfo['extension']);
			}
		}
		return	$_DB->update($rows);
	}
	
	
	public function updateproductsSort($rows){
		$sql	 	= "INSERT INTO {products}(id, sortorder) VALUES";
		$conditions	= "";
		foreach($rows as $key=>$value){					
			$conditions	.=	"({$key}, {$value}),";
		}
		$conditions = substr($conditions, 0, -1);
		$sql	.=	$conditions;
		$sql	.=	" ON   Duplicate  KEY  UPDATE sortorder=VALUES(sortorder)";
		return	$this->execute($sql);
	}
	
	
}
