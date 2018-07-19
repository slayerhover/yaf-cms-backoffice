<?phpuse Illuminate\Database\Capsule\Manager as DB;class SystemController extends BCoreController{	public function databaseAction(){		    			$this->_view->assign('uniqid',	 uniqid());    }	public function databaseGetAction() {								$tables = DB::select("SHOW TABLE STATUS");				$table_num = $table_rows = $data_size = 0;		$tabledb = array();		foreach($tables as $table){			$data_size = $data_size + $table['Data_length'];			$table_rows = $table_rows + $table['Rows'];			$table['Data_length'] = $this->sizecount($table['Data_length']);			$table_num++;			$tabledb[] = $table;		}		$data_size = $this->sizecount($data_size);		$filename = str_replace('\\', '/', Yaf_Registry::get('config')['application']['dbpath']) . '/' . basename($_SERVER['HTTP_HOST'] . '_DB_' . date('YmdHis') . '.sql');				$this->_view->assign('table_num', 	   $table_num);        $this->_view->assign('tabledb',        $tabledb);		$this->_view->assign('data_size', 	   $data_size);		$this->_view->assign('filename', 	   $filename);				json(['total'=>$table_num, 'rows'=>$tabledb]);	    }		public function dblistAction() {				/***获取所有备份文件BOF***/		$backupdbdir=	Yaf_Registry::get('config')['application']['dbpath'];		$filenames	=	scandir($backupdbdir);		$dbs		=	array();		$k			=	1;		foreach($filenames	as	$v){						if(!is_dir($v)){ 				$filesize=round(abs(filesize($backupdbdir.'/'.$v))/1024,2);				$filetime=date('Y-m-d H:i', filectime($backupdbdir.'/'.$v));				array_push($dbs, array('id'=>$k++, 'name'=>$v, 'size'=>$filesize, 'time'=>$filetime));			}		}		/***获取所有备份文件EOF***/		$this->_view->assign('dataset',		$dbs);    }		public function dbdeleteAction(){				do{			$dbfile	= $this->get('id','');			if(empty($dbfile)){					$result	= array(							'code'	=>	'300',							'msg'	=>	'请选择要删除的备份.',						);				break;			}else{				$path 	= str_replace('\\', '/', Yaf_Registry::get('config')['application']['dbpath']) . '/' . $dbfile;				@unlink($path);				$result		= array(							'code'	=>	'200',							'msg'	=>	'操作成功',						);			}		}while(FALSE);						die(json_encode($result));	}		public function exportdbAction(){					do{			$result = DB::select("SHOW tables");			if (!$result){				$result	= array(							'code'	=>	'300',							'msg'		=>	'数据表查询失败.',						);				break;			}						$datetime = date('YmdHis');			$filename = str_replace('\\', '/', Yaf_Registry::get('config')['application']['dbpath']) . '/' . basename($_SERVER['HTTP_HOST'] . '_DB_' . $datetime . '.sql');			$fp = @fopen($filename, 'w');			if($fp){				$mysqldata = '';				foreach($result as $currow){											$this->sqldumptable(reset($currow), $fp);										}				fclose($fp);			}			$result	= array(							'code'	=>	'200',							'msg'		=>	"备份成功。\r\n文件名: ".basename($_SERVER['HTTP_HOST'] . '_DB_' . $datetime . '.sql'),						);				}while(FALSE);				json($result);	}		public function downloaddbAction(){			do{						$result = DB::select("SHOW tables");			if (!$result){				throw new Exception('数据表查询失败.');				break;			}							$datetime = date('YmdHis');			$filename = str_replace('\\', '/', Yaf_Registry::get('config')['application']['dbpath']) . '/' . basename($_SERVER['HTTP_HOST'] . '_DB_' . $datetime . '.sql');			$fp = @fopen($filename, 'w');			if($fp){				$mysqldata = '';				foreach($result as $currow){					$this->sqldumptable(reset($currow), $fp);				}				fclose($fp);			}						$path = str_replace('\\', '/', Yaf_Registry::get('config')['application']['dbpath']). '/';			$name = basename($_SERVER['HTTP_HOST'] . '_DB_' . $datetime . '.sql');						$file = fopen($path . $name, "r"); // 打开文件			// 输入文件标签			Header("Content-type: application/octet-stream");			Header("Accept-Ranges: bytes");			Header("Accept-Length: ".filesize($path . $name));			Header("Content-Disposition: attachment; filename=" . $name);			// 输出文件内容			echo fread($file,filesize($path . $name));			fclose($file);			exit();		}while(FALSE);	}		public function downloadfileAction(){				$name = $this->get('id','');			if( empty($name) ){				throw new Exception("文件名不能为空.");			}			$path = str_replace('\\', '/', Yaf_Registry::get('config')['application']['dbpath']). '/' . $name;			echo $path;			if( !file_exists($path) ){				throw new Exception("文件{$name}不存在.");			}			$file = fopen($path, "r"); // 打开文件			// 输入文件标签			Header("Content-type: application/octet-stream");			Header("Accept-Ranges: bytes");			Header("Accept-Length: ".filesize($path));			Header("Content-Disposition: attachment; filename=" . $name);			// 输出文件内容			echo fread($file,filesize($path));			fclose($file);			exit();	}		private function sqldumptable($table, $fp=0) {		$tabledump  = "DROP TABLE IF EXISTS $table;\r\n";		$tabledump .= "CREATE TABLE $table (\r\n";			$firstfield=1;		$fields = DB::select("SHOW FIELDS FROM $table");		foreach($fields as $field) {			if (!$firstfield) {				$tabledump .= ",\r\n";			} else {				$firstfield=0;			}			$tabledump .= "   `{$field['Field']}` {$field['Type']}";			if ($field["Default"]!==NULL) {				$tabledump .= " DEFAULT '{$field['Default']}'";			}			if ($field['Null'] != "YES") {				$tabledump .= " NOT NULL";			}			if ($field['Extra'] != "") {				$tabledump .= " {$field['Extra']}";			}		}			$keys = DB::select("SHOW KEYS FROM $table");		foreach($keys as $key){			$kname=$key['Key_name'];			if ($kname != "PRIMARY" && $key['Non_unique'] == 0) {				$kname="UNIQUE|$kname";			}			if(!isset($index[$kname]) || !is_array($index[$kname])) {				$index[$kname] = array();			}			$index[$kname][] = $key['Column_name'];		}			while(list($kname, $columns) = @each($index)) {			$tabledump .= ",\r\n";			$colnames=implode($columns,",");				if ($kname == "PRIMARY") {				$tabledump .= "   PRIMARY KEY ($colnames)";			} else {				if (substr($kname,0,6) == "UNIQUE") {					$kname=substr($kname,7);				}				$tabledump .= "   KEY $kname ($colnames)";			}		}			$tabledump .= "\r\n);\r\n\r\n";		if ($fp) {			fwrite($fp,$tabledump);		}				$fields 	= DB::select("SHOW COLUMNS FROM $table");		$numfields 	= sizeof($fields);		$rows 		= DB::select("SELECT * FROM $table");		foreach($rows as $row){			$tabledump = "INSERT INTO $table VALUES(";						$firstfield=1;			foreach($fields as $field){				if( $firstfield==0 ){					$tabledump.=", ";				}else{					$firstfield=0;				}								if (!isset($row[$field['Field']])) {						$tabledump .= "NULL";					} else {						$tabledump .= "'".addslashes($row[$field['Field']])."'";					}			}									$tabledump .= ");\r\n";				if ($fp) {				fwrite($fp,$tabledump);			} 		}				if ($fp) {			fwrite($fp,"\r\n");		}	}	private function sizecount($size) {		if($size > 1073741824) {			$size = round($size / 1073741824 * 100) / 100 . ' G';		} elseif($size > 1048576) {			$size = round($size / 1048576 * 100) / 100 . ' M';		} elseif($size > 1024) {			$size = round($size / 1024 * 100) / 100 . ' K';		} else {			$size = $size . ' B';		}		return $size;	}			public function usersAction(){    	$this->_view->assign('uniqid',	 uniqid());    }	public function usersGetAction() {		$page   =	$this->getPost('page', 1);		$limit  =	$this->getPost('rows', 10);		$offset	=	($page-1)*$limit;					$sort	=	$this->getPost('sort',  'id');		$order	=	$this->getPost('order', 'asc');		$keywords	= trim($this->getPost('keywords', ''));				$query		= DB::table('admin')->where('roles_id','<>', 6);		if($keywords!==''){			$query	=	$query	->where('username','like',"%{$keywords}%");		}				$total		= $query->count();		$rows 		= $query->orderBy($sort,$order)							->offset($offset)							->limit($limit)							->select('id','username','roles_id',DB::raw('if(status=1,"激活","失效") as status'),'logintimes','lastlogintime','created_at','updated_at')							->get();		if(!empty($rows)&&is_array($rows)){		foreach($rows as $k=>$v){				$rows[$k]['roles']	=	DB::table('roles')->find($v['roles_id'])['rolename'];		}}				json(['total'=>$total, 'rows'=>$rows]);    }	public function usersaddAction(){		$this->_view->assign('roles', DB::table('roles')->get());    }	public function usersincreaseAction(){		do{			if( $this->method!='POST' ){				$result	= array(							'code'=>	'300',							'msg'	=>	'操作失败',																);				break;			}			$username	= $this->getPost('username', '');			$roles_id	= $this->getPost('roles_id', '');			$password	= $this->getPost('password', '');			$repassword	= $this->getPost('repassword', '');			$status		= $this->getPost('status', 		0);			$inputs	= array(                ['name'=>'username', 	'value'=>$username,	 'fun'=>'isUsername', 'msg'=>'用户名格式有误'],                            );            $result		= Validate::check($inputs);			if( $password=='' ){					$result['password']		= '密码不能为空';							}			if( $password!=$repassword ){					$result['repassword']	= '重复密码不一致';							}            if(	!empty($result) ){                $result	= array(                    'code'	=>	'0',                    'msg'	=>	'输入参数有误.',                    'data'	=>	$result,                );                break;            }									$rows	= array(								'username'		=>	$username,								'roles_id'		=>	$roles_id,								'password'		=>	$password,								'status'		=>	$status,								'created_at'	=>	date('Y-m-d H:i:s'),			);			if( DB::table('admin')->insert($rows) ){				$result	= array(							'code'		=>	'200',							'msg'		=>	'操作成功',							);			}else{				$result	= array(							'code'=>	'300',							'msg'	=>	'数据插入失败',							);			}		}while(FALSE);				die(json_encode($result));    }		public	function userseditAction(){		$id			= $this->get('id', NULL);		if($id==NULL){	return false;	}		     	$dataset  	= DB::table('admin')->find(intval($id));		$this->_view->assign('dataset', $dataset);		$this->_view->assign('roles', DB::table('roles')->get());    }    public function usersupdateAction(){		do{			if( $this->method!='POST' ){				$result	= array(							'code'	=>	'300',							'msg'		=>	'操作失败',																);				break;			}			$id			= $this->getPost('id', NULL);			$username	= $this->getPost('username', '');			$roles_id	= $this->getPost('roles_id', '');			$password	= $this->getPost('password', '');			$repassword	= $this->getPost('repassword', '');			$status		= $this->getPost('status', 		0);			$inputs	= array(                ['name'=>'username', 	'value'=>$username,	 'fun'=>'isUsername', 'msg'=>'用户名格式有误'],                            );            $result		= Validate::check($inputs);					if( $password!=$repassword ){					$result['repassword']	= '重复密码不一致';							}            if(	!empty($result) ){                $result	= array(                    'code'	=>	'0',                    'msg'	=>	'输入参数有误.',                    'data'	=>	$result,                );                break;            }									$rows	= array(								'username'		=>	$username,								'roles_id'		=>	$roles_id,																'status'		=>	$status,								'updated_at'	=>	date('Y-m-d H:i:s'),			);			if(!empty($password)){				$rows['password']=	md5($password);			}						if( DB::table('admin')->where('id','=',$id)->update($rows)!==FALSE ){				$result	= array(							'code'		=>	'200',							'msg'		=>	'操作成功',							);			}else{				$result	= array(							'code'		=>	'300',							'msg'		=>	'更新失败',							);			}		}while(FALSE);    					die(json_encode($result));    }		    public function usersdeleteAction(){			do{			if($this->method!='POST'){				$result	= array(							'code'=>	'300',							'msg'	=>	'操作失败',																);				break;							}			$id	= $this->get('id', '');			if( empty($id) ){				$result	= array(							'code'	=>	'300',							'msg'		=>	'参数为空',						);				break;			}			$rows	=	DB::table('admin')->find($id);			if($rows['username']=='admin'){				$result	= array(							'code'	=>	'300',							'msg'	=>	'系统用户不能删除.',						);				break;			}			if(DB::table('admin')->delete($id)){				$result		= array(							'code'	=>	'200',							'msg'		=>	'操作成功',							);									}else{				$result		= array(							'code'	=>	'300',							'msg'		=>	'删除失败',							);			}		}while(FALSE);					die(json_encode($result));    	    }			public function rolesAction(){    	$this->_view->assign('uniqid',	 uniqid());    }	public function rolesGetAction() {		$page   =	$this->getPost('page', 1);		$limit  =	$this->getPost('rows', 10);		$offset	=	($page-1)*$limit;					$sort	=	$this->getPost('sort',  'id');		$order	=	$this->getPost('order', 'asc');		$keywords	= trim($this->getPost('keywords', ''));				$query		= DB::table('roles');		if($keywords!==''){			$query	=	$query	->where('rolename','like',"%{$keywords}%");		}				$total		= $query->count();		$rows 		= $query->orderBy($sort,$order)							->offset($offset)							->limit($limit)														->get();				json(['total'=>$total, 'rows'=>$rows]);    }	public function rolesaddAction(){				/***获取所有权限BOF***/		$query		= DB::table('auths')->where('up','=',0);		$rows 		= $query->orderBy('sortorder','desc')->get();		foreach($rows	as	$k=>$v){				$rows[$k]['children']	=	DB::table('auths')->where('up','=',$v['id'])															  ->orderBy('sortorder','desc')															  ->get();		}		/***获取所有控制器EOF***/		$this->_view->assign('auths', $rows);    }		public function rolesincreaseAction(){		do{			$rolename	=	$this->get('rolename', 	 '');			$auths		=	$this->get('auths', 	 []);			$sortorder	=	$this->get('sortorder',  500);			if( empty($rolename)||empty($auths) ){				$result	= array(							'code'	=>	'300',							'msg'		=>	'角色名或权限列表不能为空',				);				break;			}			$auth_names = [];			foreach($auths as $k=>$v){					$auth = DB::table('auths')->find($v);					if($auth['up']>0){						$auth_names[] = DB::table('auths')->find($auth['up'])['authname'];					}					$auth_names[] = $auth['authname'];			}			$auth_names = array_unique($auth_names);			$rows		= array(					'rolename'		=>	$rolename,					'auth_ids'		=>	implode(',', $auths),					'auth_names'	=>	implode(',', $auth_names),					'sortorder'		=>	$sortorder,					'created_at'	=>	date("Y-m-d H:i:s"),			);			if( DB::table('roles')->insert($rows)){					$result	= array(							'code'	=>	'200',							'msg'	=>	'操作成功',						);			}else{					$result	= array(							'code'	=>	'300',							'msg'	=>	'添加角色失败,请多试几下',					);			}					}while(FALSE);				die(json_encode($result));    }	public function roleseditAction(){		$id	= $this->get('id' , NULL);		if($id==NULL) return FALSE;     	$dataset= DB::table('roles')->find(intval($id));		$auths	= explode(',', $dataset['auth_ids']);				/***获取所有权限BOF***/		$query		= DB::table('auths')->where('up','=',0);		$rows 		= $query->orderBy('sortorder','desc')->get();		foreach($rows	as	$k=>$v){								$rows[$k]['children']	=	DB::table('auths')->where('up','=',$v['id'])															  ->orderBy('sortorder','desc')															  ->get();				foreach($rows[$k]['children']	as	$k1=>&$v1){					if(in_array($v1['id'], $auths)){						$v1['flag'] = 1;					}else{						$v1['flag'] = 0;					}				}		}		/***获取所有控制器EOF***/		$this->_view->assign('auths', $rows);				$this->_view->assign('dataset', $dataset);		    }	    public function rolesupdateAction(){		do{			$id			=	$this->get('id', 		 '');			$rolename	=	$this->get('rolename', 	 '');			$auths		=	$this->get('auths', 	 []);			$sortorder	=	$this->get('sortorder',  500);			if( empty($rolename)||empty($auths) ){				$result	= array(							'code'	=>	'300',							'msg'		=>	'ID,角色名或权限列表不能为空',				);				break;			}			$auth_names = [];			foreach($auths as $k=>$v){					$auth = DB::table('auths')->find($v);					if($auth['up']>0){						$auth_names[] = DB::table('auths')->find($auth['up'])['authname'];					}					$auth_names[] = $auth['authname'];			}			$auth_names = array_unique($auth_names);			$rows		= array(					'rolename'		=>	$rolename,					'auth_ids'		=>	implode(',', $auths),					'auth_names'	=>	implode(',', $auth_names),					'sortorder'		=>	$sortorder,					'created_at'	=>	date("Y-m-d H:i:s"),			);			if( DB::table('roles')->where('id','=',$id)->update($rows)){					$result	= array(							'code'	=>	'200',							'msg'	=>	'操作成功',						);			}else{					$result	= array(							'code'	=>	'300',							'msg'	=>	'添加角色失败,请多试几下',					);			}					}while(FALSE);				die(json_encode($result));		    }	public function rolesdeleteAction(){			do{			if($this->method!='POST'){				$result	= array(							'code'=>	'300',							'msg'	=>	'操作失败',																);				break;							}			$id	= $this->get('id', '');			if( empty($id) ){				$result	= array(							'code'	=>	'300',							'msg'		=>	'参数为空',						);				break;			}			$rows	=	DB::table('roles')->find($id);			if($rows['rolename']=='系统管理员'||$rows['rolename']=='EVERYONE'){				$result	= array(							'code'	=>	'300',							'msg'	=>	'系统权限组不能删除.',						);				break;			}						if(DB::table('roles')->delete($id)){				$result		= array(							'code'	=>	'200',							'msg'		=>	'操作成功',							);									}else{				$result		= array(							'code'	=>	'300',							'msg'		=>	'删除失败',							);			}		}while(FALSE);					die(json_encode($result));    	    }				public function authsAction(){    	$this->_view->assign('uniqid',	 uniqid());    }	public function authsGetAction() {		$page   =	$this->getPost('page', 1);		$limit  =	$this->getPost('rows', 10);		$offset	=	($page-1)*$limit;		$sort	=	$this->getPost('sort',  'sortorder');		$order	=	$this->getPost('order', 'desc');		$keywords	= trim($this->getPost('keywords', ''));				$query		= DB::table('auths')->where('up','=',0);		if($keywords!==''){			$query	=	$query	->where('name','like',"%{$keywords}%");		}		$total		= $query->count();		$rows 		= $query->orderBy($sort,$order)							->offset($offset)							->limit($limit)														->get();		foreach($rows	as	$k=>$v){				$rows[$k]['children']	=	DB::table('auths')->where('up','=',$v['id'])															  ->orderBy($sort,$order)															  ->get();		}		json(['total'=>$total, 'rows'=>$rows]);    }	public function authsaddAction(){				/***获取所有控制器BOF***/		$controllerdir	=	Yaf_Registry::get('config')['application']['directory'].'/modules/'.$this->module.'/controllers';		$filenames		=	scandir($controllerdir);		$controllers	=	array();		foreach($filenames	as	$v){						if(!is_dir($v)){				$controllers[]	=	substr($v, 0, -4);							}		}		/***获取所有控制器EOF***/		$this->_view->assign('controllers', $controllers);				$this->_view->assign('rootlevel', DB::table('auths')->where('up','=',0)->orderBy('sortorder','desc')->get());    }	public function getactsAction(){		do{			$controller = $this->get('controller', '');			if( empty($controller) ){				$result	= array(							'code'	=>	'300',							'msg'	=>	'控制器名不能为空',						);				break;			}						require_once(Yaf_Registry::get('config')['application']['directory'].'/modules/'.$this->module.'/controllers/'.$controller.'.'.Yaf_Registry::get('config')['application']['ext']);						$rows = [];			foreach(get_class_methods($controller.'Controller') as $k=>$v){				if(strstr($v, 'Action')){ $rows[] = substr($v, 0, -6); }  			}			$result	= array(					'code'	=>	'200',					'msg'	=>	'控制器方法列表',					'data'	=>	$rows,			);		}while(FALSE);				die(json_encode($result));					}	public function authsincreaseAction(){		do{			$up			=	$this->getPost('up', 			 0);			$authname	=	$this->getPost('authname', 		'');			$controller	=	$this->getPost('controller',    '');			$action		=	$this->getPost('action',    	[]);            $sortorder	=	$this->getPost('sortorder',     500);			if( empty($authname) ){				$result	= array(							'code'	=>	'300',							'msg'	=>	'权限名不能为空',						);				break;			}						$rows	=	array(										'authname'		=>	$authname,					'up'			=>	$up,					'controller'	=>	$controller,					'action'		=>	implode(',', $action),					'sortorder'     =>  $sortorder,					'created_at'	=>	date("Y-m-d H:i:s"),			);			if( DB::table('auths')->insert($rows)){					$result	= array(							'code'	=>	'200',							'msg'	=>	'操作成功',						);			}else{					$result	= array(							'code'	=>	'300',							'msg'	=>	'添加权限组失败,请多试几下',					);			}		}while(FALSE);				die(json_encode($result));    }	public function authseditAction(){		$id		= $this->get('id' , NULL);		if($id==NULL) return FALSE;     	$dataset= DB::table('auths')->find(intval($id));		$actions= explode(',', $dataset['action']);						$this->_view->assign('dataset', $dataset);				$this->_view->assign('actions', $actions);				/***获取所有控制器BOF***/		$controllerdir	=	Yaf_Registry::get('config')['application']['directory'].'/modules/'.$this->module.'/controllers';		$filenames		=	scandir($controllerdir);		$controllers	=	array();		foreach($filenames	as	$v){						if(!is_dir($v)){				$controllers[]	=	substr($v, 0, -4);							}		}		/***获取所有控制器EOF***/		$this->_view->assign('controllers', $controllers);				$this->_view->assign('rootlevel', DB::table('auths')->where('up','=',0)->orderBy('sortorder','desc')->get());		$rows = [];		if(!empty($dataset['controller'])){			require_once(Yaf_Registry::get('config')['application']['directory'].'/modules/'.$this->module.'/controllers/'.$dataset['controller'].'.'.Yaf_Registry::get('config')['application']['ext']);									foreach(get_class_methods($dataset['controller'].'Controller') as $k=>$v){				if(strstr($v, 'Action')){ 					$act = substr($v, 0, -6);					if(in_array($act, $actions)){						array_push($rows, ['act'=>$act,'flag'=>1]); 					}else{						array_push($rows, ['act'=>$act,'flag'=>0]); 					}				}			}		}		$this->_view->assign('actrows', $rows);    }	    public function authsupdateAction(){    	do{			$id			=	$this->get('id', 		'');			$up			=	$this->getPost('up', 			 0);			$authname	=	$this->getPost('authname', 		'');			$controller	=	$this->getPost('controller',    '');			$action		=	$this->getPost('action',    	[]);            $sortorder	=	$this->getPost('sortorder',     500);            if( empty($id)||empty($authname) ){				$result	= array(							'code'	=>	'300',							'msg'	=>	'ID或权限名不能为空',						);				break;			}						$rows	=	array(										'authname'		=>	$authname,					'up'			=>	$up,					'controller'	=>	$controller,					'action'		=>	implode(',', $action),					'sortorder'     =>  $sortorder,					'updated_at'	=>	date("Y-m-d H:i:s"),			);			if( DB::table('auths')->where('id','=',$id)->update($rows)){					$result	= array(							'code'	=>	'200',							'msg'	=>	'操作成功',						);			}else{					$result	= array(							'code'	=>	'300',							'msg'	=>	'更新权限组失败,请多试几下',					);			}					}while(FALSE);				die(json_encode($result));    }	public function authsdeleteAction(){			do{			if($this->method!='POST'){				$result	= array(							'code'=>	'300',							'msg'	=>	'操作失败',																);				break;							}			$id	= $this->get('id', '');			if( empty($id) ){				$result	= array(							'code'	=>	'300',							'msg'		=>	'参数为空',						);				break;			}					if(DB::table('auths')->delete($id)){				$result		= array(							'code'	=>	'200',							'msg'		=>	'操作成功',							);									}else{				$result		= array(							'code'	=>	'300',							'msg'		=>	'删除失败',							);			}		}while(FALSE);					die(json_encode($result));    	    }	    public function menusAction(){    	$this->_view->assign('uniqid',	 uniqid());    }	public function menusGetAction() {		$page   =	$this->getPost('page', 1);		$limit  =	$this->getPost('rows', 10);		$offset	=	($page-1)*$limit;		$sort	=	$this->getPost('sort',  'sortorder');		$order	=	$this->getPost('order', 'desc');		$keywords	= trim($this->getPost('keywords', ''));				$query		= DB::table('menus')->where('up','=',0);		if($keywords!==''){			$query	=	$query	->where('name','like',"%{$keywords}%");		}		$total		= $query->count();		$rows 		= $query->orderBy($sort,$order)							->offset($offset)							->limit($limit)														->get();		foreach($rows	as	$k=>$v){				$rows[$k]['children']	=	DB::table('menus')->where('up','=',$v['id'])															  ->orderBy($sort,$order)															  ->get();		}		json(['total'=>$total, 'rows'=>$rows]);    }	public function menusaddAction(){		$this->_view->assign('rootlevel', DB::table('menus')->where('up','=',0)->orderBy('sortorder','desc')->get());    }	public function menusincreaseAction(){		do{			$up			=	$this->getPost('up', 			 0);			$title  	=	$this->getPost('title', 		'');			$href	    =	$this->getPost('href',          '');			$sortorder	=	$this->getPost('sortorder',    	500);			if( empty($title) ){				$result	= array(							'code'	=>	'300',							'msg'	=>	'菜单名不能为空',						);				break;			}						$rows	=	array(                    'up'			=>	$up,			        'title'	    	=>	$title,					'href'  	    =>	$href,					'sortorder'     =>  $sortorder,					'created_at'	=>	date("Y-m-d H:i:s"),			);			if( DB::table('menus')->insert($rows)){					$result	= array(							'code'	=>	'200',							'msg'	=>	'操作成功',						);			}else{					$result	= array(							'code'	=>	'300',							'msg'	=>	'添加菜单失败,请多试几下',					);			}		}while(FALSE);				die(json_encode($result));    }	public function menuseditAction(){		$id		= $this->get('id' , NULL);		if($id==NULL) return FALSE;     	$dataset= DB::table('menus')->find(intval($id));        $this->_view->assign('dataset',   $dataset);        $this->_view->assign('rootlevel', DB::table('menus')->where('up','=',0)->orderBy('sortorder','desc')->get());    }	    public function menusupdateAction(){    	do{			$id			=	$this->get('id', 		'');            $up			=	$this->getPost('up', 			 0);            $title  	=	$this->getPost('title', 		'');            $href	    =	$this->getPost('href',          '');            $sortorder	=	$this->getPost('sortorder',    	500);			if( empty($id)||empty($title) ){				$result	= array(							'code'	=>	'300',							'msg'	=>	'ID或菜单名不能为空',						);				break;			}						$rows	=	array(										'title'	    	=>	$title,					'up'			=>	$up,					'href'	        =>	$href,					'sortorder'		=>	$sortorder,					'updated_at'	=>	date("Y-m-d H:i:s"),			);			if( DB::table('menus')->where('id','=',$id)->update($rows)){					$result	= array(							'code'	=>	'200',							'msg'	=>	'操作成功',						);			}else{					$result	= array(							'code'	=>	'300',							'msg'	=>	'更新菜单失败,请多试几下',					);			}					}while(FALSE);				die(json_encode($result));    }	public function menusdeleteAction(){			do{			if($this->method!='POST'){				$result	= array(							'code'=>	'300',							'msg'	=>	'操作失败',																);				break;							}			$id	= $this->get('id', '');			if( empty($id) ){				$result	= array(							'code'	=>	'300',							'msg'		=>	'参数为空',						);				break;			}					if(DB::table('menus')->delete($id)){				$result		= array(							'code'	=>	'200',							'msg'		=>	'操作成功',							);									}else{				$result		= array(							'code'	=>	'300',							'msg'		=>	'删除失败',							);			}		}while(FALSE);					die(json_encode($result));    	    }	}