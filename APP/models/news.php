<?phpuse Illuminate\Database\Eloquent\Model;
class newsModel extends Model{	protected $table 		= 'news';	protected $primaryKey	= 'id';		public function content()	{		return $this->hasOne('newscontentModel', 'id', 'id');	}
	
	public function getAllNews($newsclass_id, $sort, $size=20){
		
		$conditions	=($newsclass_id>0) ? " WHERE a.newsclass_id='{$newsclass_id}' " : '';				
		$sortorder	=" ORDER BY {$sort} ";		if( is_array($size) ){$size = implode(',', $size);}
		$limit		=" LIMIT {$size}";
		
		$sql	=	"SELECT a.*,b.title as classname, c.content
					 FROM `{news}` a INNER JOIN `{newsclass}` b INNER JOIN `{newscontent}` c
					 ON a.newsclass_id=b.id AND a.id=c.id 
					 {$conditions} {$sortorder} {$limit}";
		
		$rows	=	$this->getAll($sql);
		return $rows;
	}
	
	public function getRecommendNews($newsclass_id, $sort, $size=20){
		
		$conditions =" WHERE a.`status`=1 AND a.`recommend`=1 ";
		$conditions.=($newsclass_id>0) ? " AND a.newsclass_id='{$newsclass_id}' " : '';				
		$sortorder	=" ORDER BY a.{$sort} ";		if( is_array($size) ){$size = implode(',', $size);}
		$limit		=" LIMIT {$size}";
		
		$sql	=	"SELECT a.id,a.newsclass_id,a.title,a.logo,a.addtime,c.content 
					 FROM `{news}` a INNER JOIN `{newsclass}` b INNER JOIN `{newscontent}` c
					 ON a.newsclass_id=b.id AND a.id=c.id
					 {$conditions} {$sortorder} {$limit}";
		
		$rows	=	$this->getAll($sql);
		foreach($rows as $key=>$onerow){
			$rows[$key]['content']	=	mb_substr(strip_tags($onerow['content']), 0, 200);
		}
		return $rows;
	}
	
	public function getNews($id){
		$conditions	= " WHERE a.id='{$id}'";
		$sql	=	"SELECT a.*,b.title as classname, c.content 
					 FROM `{news}` a INNER JOIN `{newsclass}` b INNER JOIN `{newscontent}` c 
					 ON a.newsclass_id=b.id AND a.id=c.id 
					 {$conditions}";
		
		$rows	=	$this->getRow($sql);
		return $rows;
	}
	
	public function getNewsNum($newsclass_id=0){
		$conditions	=($newsclass_id>0) ? " newsclass_id='{$newsclass_id}' " : '';
		return (new Table('news'))->findCount($conditions);
	}
	
	public function addNews($rows1, $rows2){		
		if( empty($rows1['logo']) && preg_match('#<img\s+src\=[\"\']([^\"\']*)[\"\']#is', stripslashes($rows2['content']), $imagesurl) ){
			$pathinfo	=	pathinfo($imagesurl[1]);			
			if( stripos($pathinfo['dirname'], 'http://')===FALSE ){
				$thumbnail	=	$pathinfo['dirname'].'/'.$pathinfo['filename'].'_thum.'.$pathinfo['extension'];				
				$rows1['logo']	=	ImageManager::thumbnail($imagesurl[1], $thumbnail, 200, $pathinfo['extension']);
			}else{
				$config	= Yaf_Registry::get('config');
				$filename = 'news-t' . time();
				$descdir  = $config['application']['uploadpath'] . '/News/';
				if( !is_dir($descdir) ){ mkdir($descdir); }
				$descdir  = $config['application']['uploadpath'] . '/News/' . date('Ym') . '/';
				if( !is_dir($descdir) ){ mkdir($descdir); }
				$realpath = $descdir . $filename . '.' . $pathinfo['extension'];
				file_put_contents($realpath, file_get_contents($imagesurl[1]));
				$thumbnail	=	$descdir . $filename . '_thum.' . $pathinfo['extension'];				
				$rows1['logo']	=	ImageManager::thumbnail($realpath, $thumbnail, 200, $pathinfo['extension']);
			}
		}	
		if( $id=(new Table('news'))->add($rows1) ){
			$rows2['id']	=	$id;
			if( (new Table('newscontent'))->add($rows2)!==FALSE ){
				return TRUE;
			}
		}
		return FALSE;
	}
	
	public function updateNews($rows1, $rows2){
		$_DB	=	new Table('news');
		$data	=	$_DB->find($rows1['id']);
		if(empty($data))
			return FALSE;
		
		if( empty($data['logo']) && empty($rows1['logo']) && preg_match('#<img\s+src\=[\"\']([^\"\']*)[\"\']#is', stripslashes($rows2['content']), $imagesurl) ){
			$pathinfo	=	pathinfo($imagesurl[1]);
			if( stripos($pathinfo['dirname'], 'http://')===FALSE ){
				$thumbnail	=	$pathinfo['dirname'].'/'.$pathinfo['filename'].'_thum.'.$pathinfo['extension'];
				$rows1['logo']	=	ImageManager::thumbnail($imagesurl[1], $thumbnail, 200, $pathinfo['extension']);
			}else{
				$config	= Yaf_Registry::get('config');
				$filename = 'news-t' . time();
				$descdir  = $config['application']['uploadpath'] . '/News/';
				if( !is_dir($descdir) ){ mkdir($descdir); }
				$descdir  = $config['application']['uploadpath'] . '/News/' . date('Ym') . '/';
				if( !is_dir($descdir) ){ mkdir($descdir); }
				$realpath = $descdir . $filename . '.' . $pathinfo['extension'];
				file_put_contents($realpath, file_get_contents($imagesurl[1]));
				$thumbnail	=	$descdir . $filename . '_thum.' . $pathinfo['extension'];				
				$rows1['logo']	=	ImageManager::thumbnail($realpath, $thumbnail, 200, $pathinfo['extension']);
			}
		}	
		if( $_DB->update($rows1)===FALSE )
			return FALSE;			
		if( (new Table('newscontent'))->update($rows2)===FALSE)
			return FALSE;
		
		return TRUE;
	}
	
	public function updateNewsSort($rows){
		$sql	 	= "INSERT INTO {news}(id, sortorder) VALUES";
		$conditions	= "";
		foreach($rows as $key=>$value){					
			$conditions	.=	"({$key}, {$value}),";
		}
		$conditions = substr($conditions, 0, -1);
		$sql	.=	$conditions;
		$sql	.=	" ON   Duplicate  KEY  UPDATE sortorder=VALUES(sortorder)";
		return	$this->execute($sql);
	}
	
	public function deleteNews($ids){
		if( (new Table('news'))->delete($ids)===FALSE )
			return FALSE;			
		if( (new Table('newscontent'))->delete($ids)===FALSE)
			return FALSE;
		
		return TRUE;
	}
	
}
