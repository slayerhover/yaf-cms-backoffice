<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class membersModel extends Model{
	
	protected $table 		= 'members';
	protected $primaryKey	= 'id';

	public function setUserLogin($phone, $password){
		if( $user = $this->where('phone','=',$phone)->where('password','=',md5($password))->first() ){
				$rows	= array(
								'logintimes'	=>	intval($user['logintimes'])+1,
								'logined_at'	=>	date('Y-m-d H:i:s'),
				);
				$this->where('id','=',$user['id'])->update($rows);
				$data	= array(
								'user_id'	=>	$user['id'],
								'name'		=>	$user['phone'],
								'role'		=>	$user['roles_id'],
							);				
				$token	= 'auth' . md5($user['id'].$user['phone'].$user['company_id'].$user['logintimes'].$user['logined_at']);
				if( Cache::getInstance()->set($token, $user['id'], Yaf_Registry::get('config')['cache']['redis']['expire']) ){						
						return $token;
				}				
		}
		
		return false;		
	}
	
	public function checkphone($phone) {
		return $this->where('phone','=',$phone)->count()>0;
	}
	public function checkPassword($phone, $password){		
		return $this->where('phone','=',$phone)->where('password','=',md5($password))->count()>0;
	}
	
	public function getUser($id){
		$rows	=	DB::table('members')
										->where('members.id', '=', $id)
										->first();
		if($rows['company_id']>0){
			$rows['company']	=	DB::table('company')->find($rows['company_id']);
		}
		return $rows;
	}
}
