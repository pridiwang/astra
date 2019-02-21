<?php 
$adminpwd='hxDA6kE9FNpeEGp';
if($action=='user-logout'){
	session_destroy();
	unset($_SESSION);
	
}
if($action=='user-log'){
	extract($_POST);
	if($log=='in'){
		if(($username=='admin')&&($password==$adminpwd)){
			$_SESSION[user]=$username;
		}else{
			$msg='user/pwd not correct';
		}
	}
}

if(!$_SESSION[user]) $action='user-login';
if($action=='user-login'){
	print "<form action=?action=user-log&log=in method=post class='cols-xs-6 col-sm-4 col-lg-3' style='margin:5rem auto;padding:30px;background:#371643;color:#fff'>
	<img src=img/banner_730x204.png width=100%>
	<center><h3>Incentive Calculation</h3>$msg</center>
	
	<input type=text name=username class='form-control' placeholder='username'>
	<input type=password name=password class='form-control' placeholder='password'>
	
	<button type=submit class='btn btn-sm btn-primary float-right'>Login</button>
	<br>
	</form><style>body{background:#aaa;}input{margin:10px 0;}</style>";
	exit;
}
?>