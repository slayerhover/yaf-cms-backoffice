<?phpuse Illuminate\Database\Capsule\Manager as DB;class PublicController extends CoreController{		public function loginAction(){		
		$this->_view->assign('title', DB::table('info')->find(1)['value']);
	}
	
	public function huadongyzAction(){
		$qaptcha_key	=	$this->getPost('qaptcha_key', NULL);
		if($qaptcha_key!=NULL){
			$this->session->qaptcha_key	=	$qaptcha_key;
			die('100');
		}
	}
	
	public function logoutAction(){		global $auth;				$auth->logout();
		redirect(url('public', 'login'));
	}
	
	public function checkloginAction(){
		$lockFlag= $this->getCookie('lockFlag', 0);
		if($lockFlag==1){
						$result	=	array(
												'code'		=>	'800',
												'message'	=>	'重试次数过多了， 20分钟后再重试吧.',
									);
						$this->session->del('try_times');
		}else{			
			if(!empty($_POST)){	
				do {	
					$qaptcha_key = $this->session->qaptcha_key;
					if( empty($qaptcha_key) ) {	
						$result	=	array(
										'code'		=>	'300',
										'message'	=>	'滑动验证失败.',
									);
						break;
					}
					
					$username = $this->getPost('username', NULL);
					$password = $this->getPost('password', NULL);
					if( $username==NULL || $password==NULL ){
						$result	=	array(
										'code'		=>	'400',
										'message'	=>	'用户名或者密码为空.',
									);
						break;
					}			
					$sysusers =new adminModel();
					if ($sysusers->checkUsername($username)==FALSE) {
						$result	=	array(
										'code'		=>	'500',
										'message'	=>	'未找到匹配用户名.',
									);
						break;
					}		
					if ($sysusers->checkPassword($username, $password)==FALSE){						
						if(!isset($this->session->try_times)){$this->session->try_times=0;}
						$this->session->try_times++;
						if($this->session->try_times>10){
							$result	  = array(
										'code'		=>	'800',
										'message'	=>	'重试次数过多了， 20分钟后再重试吧.',
									);
							setcookie('lockFlag', 1, time()+60*20);
							$this->session->del('try_times');
						}else{
							$result	  = array(
										'code'		=>	'600',
										'message'	=>	'密码有误.',
									);
						}
						break;
					}							
					if( $sysusers->setUserLogin($username, $password) ){					
						$this->session->del('qaptcha_key');
						$this->session->del('try_times');
						$result	=	array(
										'code'		=>	'200',
										'message'	=>	'登陆成功.',
									);					
						break;					
					}else{
						$result	=	array(
										'code'		=>	'100',
										'message'	=>	'登陆失败.',
									);
						break;
					}								
				}while(FALSE);
			}else{
				$this->session->del('qaptcha_key');
				$result	=	array(
										'code'		=>	'700',
										'message'	=>	'登陆方式失效.',
									);
			}	
		}
		
		die(json_encode($result));
	}
	
	public function yzcodeAction(){
		Captcha::generate(3);
	}
	
}
