<?php
header('content-type:text/html;charset=utf-8');
include('db.php');
$dbset    =    array(
                'dsn'        =>    'mysql:host=localhost;dbname=putuanwang',
                'name'        =>    'root',
                'password'    =>    'maxueyan796',
            ); 
$db        =    new DB($dbset);
try{
    $sql   ="select id,logo,images from pt_goods";
    $rows  =$db->getAll($sql);
    foreach($rows as $k=>$v1){
	if(!empty($v1['images'])){
		$images = explode(',', $v1['images']);
		foreach($images as $v){
			$url="http://www.pushenkuajing.com/attached/".$v;
			echo $url . "\n";
			$file=$v;
			$rt=pathinfo($file);
			if(!is_dir($rt['dirname'])){
				echo $rt['dirname']."\n";
				mkdir($rt['dirname'],0777,TRUE);
			}
			exec("wget {$url} -P {$rt['dirname']}");
		}
	}
    }
    echo 'Done'."\n";
}catch(Exception $e){
    echo "Failed: " . $e->getMessage();
}
