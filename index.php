<?php 
session_start();
ini_set('display_errors','On');
error_reporting(E_ERROR);
?>
<html><head><title>Astra</title>
<link rel='icon' href=favicon.ico />
<link rel=stylesheet href=https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.2.1/css/bootstrap.min.css />
<link rel=stylesheet href=https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css />
<link rel=stylesheet href=https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css />
<link rel=stylesheet href=https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css />
<script src=https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js ></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.2.1/js/bootstrap.min.js ></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.6/jquery.number.min.js ></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js  ></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.js ></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js></script>
<?php 

$rnd=rand(0,9999);
print "<link rel=stylesheet href=style.css?$rnd />
<script src=include.js?$rnd ></script>
</head><body>";

extract($_GET);
require "include.php";
require "user.php";
$alist=array('xls-upload'=>'Excel Upload','xls-dump'=>'XLS dump','incentive-cal'=>'Incentive Calculate','analyse'=>'Analysis','report'=>'Report','browse'=>'Data');
print "<div class=topbar style=height:46px;>
<div class=container><img src=img/logo_330.png style=height:42px;margin:2px; align=left>
<div><b> Menu: </b>"; 
while(list($a,$t)=each($alist)) print "<a href=?action=$a> $t </a> | ";
//'password'=>'Password',
$alist=array('user-logout'=>'Logout');
print "<span class='pull-right'> User: $_SESSION[user] ";
while(list($a,$t)=each($alist)) print " | <a href=?action=$a> $t </a>  ";
print "</span>";
print "</div>";


if($action=='browse'){
	print "<div ><b> Data : </b>";
	$sheets=array('sales','manual','territory_update','asm_update','scheme_update','incentive');
	while(list(,$t)=each($sheets)) print "<a href=?action=browse&tb=$t>".ucwords(str_replace('_',' ',$t))."</a> | ";
	print "</div>";
}
if($action=='report'){
	$rlist=array('incentive','sales','incentive_summary');
	print "<div><b> Report : </b>";
	while(list(,$a)=each($rlist)) print "<a href=?action=$action&$action=$a style=text-transform:capitalize;> ".str_replace('_',' ',$a)."  </a> | ";
	print "</div>";
}
if($action=='analyse'){
	$rlist=array('arch-pay'=>'Archivement & Payout');
	print "<div><b> Analyse : </b>";
	while(list($a,$t)=each($rlist)) print "<a href=?action=$action&$action=$a style=text-transform:capitalize;> ".$t."  </a> | ";
	print "</div>";
}
print "</div></div><div class='container main'>";
require "report.php";
require "analyse.php";
require "incentive.php";

if(($action=='browse')&&($tb)){
	extract($_POST);
	print "<br><form class='form-inline float-left' action=?action=$action&tb=$tb&go=1 method=post id=navform>".monthnav()." <button type=submit class='btn btn-sm btn-primary'>List</button></form>";
	$datefld='date';
	if($tb=='scheme_update') $datefld='start_date';
	$q="select * from $tb where year($datefld)='$yr' and month($datefld)='$mo'  $cond ";
	qbrowse($q,$tb);
}
if($action=='edit'){
	//$q="select * from $tb where 1 $cond ";
	edit($tb,$id);
}

?>
</div>
</body>
</html>