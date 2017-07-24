<?php
class Pager{ 	
	var $url;//IE地址栏地址  	
	var $total; //记录总条数
	var $page; 	//总页数 
	var $thestr; 	//分页数字链接 
	var $backstr; 	//首页、上一页链接 
	var $nextstr; 	//尾页、下一页链接 
	var $pg; 	//当前页码 
	var $pageSize; 	//每页显示记录数量
	var $anchor;

//构造函数，实例化该类的时候自动执行该函数 
function __construct($total,$pageSize,$currentPage=1,$anchor=''){ 
	//记录数与每页显示数不能整队时，页数取余后加1 
	$this->total 	= $total; 
	$this->pageSize = $pageSize;
	if ($this->total%$this->pageSize!=0){ 
		$this->page=sprintf("%d",$this->total/$this->pageSize)+1; 
	}else{ 
		$this->page=$this->total/$this->pageSize; 
	}
	
	$this->anchor=$anchor;
	$this->pg=$currentPage; 
	//保证pg在未指定的情况下为从第1页开始 
	if (!preg_match("#^\d+$#",$this->pg) || empty($this->pg)){ 
		$this->pg=1; 
	} 
	//页码超出最大范围，取最大值 
	if ($this->pg>$this->page){ 
		$this->pg=$this->page; 
	} 
	//得到当前的URL。具体实现请看最底部的函数实体 
	$this->url = Pager::getUrl();
	//替换错误格式的页码为正确页码 
	if(isset($currentPage) && $currentPage!=$this->pg){ 
	$this->url=str_replace("?page=".$currentPage,"?page=$this->pg",$this->url); 
	$this->url=str_replace("&page=".$currentPage,"&page=$this->pg",$this->url); 
	} 
	//生成12345等数字形式的分页。 
	if ($this->page<=10){ 
		for ($i=1;$i<$this->page+1;$i++){ 
			$this->thestr=$this->thestr.Pager::makepg($i,$this->pg); 
		} 
	}else{ 
	if ($this->pg<=5){ 
		for ($i=1;$i<10;$i++){ 
			$this->thestr=$this->thestr.Pager::makepg($i,$this->pg); 
		} 
	}else{ 
		if (6+$this->pg<=$this->page){ 
			for ($i=$this->pg-4;$i<$this->pg+6;$i++){ 
				$this->thestr=$this->thestr.Pager::makepg($i,$this->pg); 
			} 
		}else{ 
			for ($i=$this->pg-4;$i<$this->page+1;$i++){ 
			$this->thestr=$this->thestr.Pager::makepg($i,$this->pg); 
			} 
	
		} 
	} 
	} 
	//生成上页下页等文字链接 
	$this->backstr = Pager::gotoback($this->pg); 
	$this->nextstr = Pager::gotonext($this->pg,$this->page); 
} 

function getPager(){
	//return (" 共".$this->total." 条,每页".$this->pageSize."条，共".$this->page."页".$this->backstr.$this->thestr.$this->nextstr); 
	return $this->backstr.$this->thestr.$this->nextstr." <p class='skip'>第{$this->pg}页/共{$this->page}页</p>";
}

//生成数字分页的辅助函数 
function makepg($i,$pg){ 
	if ($i==$pg){ 
	return " <a class='current'>{$i}</a> ";
	}else{ 
	return " <a href=".Pager::replacepg($this->url,5,$i)."><u>".$i."</u></a>"; 
	} 
} 
//生成上一页等信息的函数 
function gotoback($pg){ 
	if ($pg-1>0){ 
	return $this->gotoback=" <a href=".Pager::replacepg($this->url,3,0).">首页</a> <a href=".Pager::replacepg($this->url,2,0).">上页</a>"; 
	}else{ 
	return $this->gotoback=" <a class='disabled'>首页</a><a class='disabled'>上页</a>";
	} 
} 
//生成下一页等信息的函数 
function gotonext($pg,$page){ 
	if ($pg < $page){ 
	return " <a href=".Pager::replacepg($this->url,1,0).">下页</a> <a href=".Pager::replacepg($this->url,4,0).">尾页</a>"; 
	}else{ 
	return " <a class='disabled'>下页</a><a class='disabled'>尾页</a>"; 
	} 
} 
//处理url中$pg的方法,用于自动生成pg=x 
function replacepg($url,$flag,$i){ 
	if ($flag == 1){ 
	$temp_pg = $this->pg; 
	return str_replace("page=".$temp_pg,"page=".($this->pg+1),$url); 
	}else if($flag == 2) { 
	$temp_pg = $this->pg; 
	return str_replace("page=".$temp_pg,"page=".($this->pg-1),$url); 
	}else if($flag == 3) { 
	$temp_pg = $this->pg; 
	return str_replace("page=".$temp_pg,"page=1",$url); 
	}else if($flag == 4){ 
	$temp_pg = $this->pg; 
	return str_replace("page=".$temp_pg,"page=".$this->page,$url); 
	}else if($flag == 5){ 
	$temp_pg = $this->pg; 
	return str_replace("page=".$temp_pg,"page=".$i,$url); 
	}else{ 
	return $url; 
	} 
} 
//获得当前URL的方法 
function getUrl(){ 
	$url="http://".$_SERVER["HTTP_HOST"]; 
	if(isset($_SERVER["REQUEST_URI"])){ 
		$url.=$_SERVER["REQUEST_URI"]; 
	}else{ 
		$url.=$_SERVER["PHP_SELF"]; 
		if(!empty($_SERVER["QUERY_STRING"])){ 
			$url.="?".$_SERVER["QUERY_STRING"]; 
		} 
	} 
	//在当前的URL里加入page=x字样 
	if (!preg_match("#page\=#is", $url)){ 
		if (!strpos($url,"?")){ 
			$url = $url."?page=1"; 
		}else{ 
			$url = $url."&page=1"; 
		} 
	} 
	return $url . $this->anchor; 
}
 
} 
?>