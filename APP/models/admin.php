<?phpuse Illuminate\Database\Eloquent\Model;use Illuminate\Database\Capsule\Manager as DB;
class adminModel extends Model{
		protected $table 		= 'admin';	protected $primaryKey	= 'id';
		
	public function checkUsername($username) {		return $this->where('username','=',$username)->count()>0;
	}

	public function checkPassword($username, $password) {		return $this->where('username','=',$username)->where('password','=',md5($password))->count()>0;
	}
	
	public function getRolename($roles_id){
		return DB::table('roles')->find($roles_id)['rolename'];
	}
	
	public function setUserLogin($username, $password){
		if( $user = $this->where('username','=',$username)->where('password','=',md5($password))->first() ){
			$rows	= array(
							'loginedtimes'	=>	intval($user['loginedtimes'])+1,
							'lasttime'		=>	time(),
						);
			$this->where('id','=',$user['id'])->update($rows);
			$data	= array(
							'user_id'	=>	$user['id'],
							'name'		=>	$user['username'],
							'role'		=>	$user['roles_id'],
						);									global $auth;				
			$auth->login($data);
			$auth->write();			return $auth->isLogin();	
		}else{
			return false;
		}
	}
	
	public function getRoles(){
		$rows		= DB::table('roles')->where('rolename','<>','EVERYONE')->select('id','rolename')->get();		
		return $rows;
	}
	
	public function getUsers($conditions, $sort, $startpagenum,$pageSize){
		$rows	=	$this->findAll($conditions, $sort, array($startpagenum,$pageSize));
		
		$roles	=	new Table('roles');
		foreach($rows as $k=>$v){
			$rows[$k]['rolesname']	=	$roles->find($v['roles_id'])['rolename'];
		}
		return $rows;
	}
	
	public function getUser($id){
		return $this->find($id);
	}
	
	public function getUsersNum($conditions){
		return $this->findCount($conditions);
	}
	
}
