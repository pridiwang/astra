<?php 
ini_set('display_errors','Off');
require "include.php";
error_reporting(E_ALL ^ E_NOTICE);
require_once 'excel_reader2.php';
if(!$yr) $yr=2018;
if(!$mo) $mo='02';
$data = new Spreadsheet_Excel_Reader("$yr/$mo.xls",false,'TIS-620');
$sheets=array('sales','manual','territory_update','asm_update','scheme_update');
$vrows=array(2,2);
while(list($s,$t)=each($sheets)){
//$s=0;
//$t=$sheets[$s];
	$rows=$data->rowcount($s);
	$cols=$data->colcount($s);
	print "s $s t $t rows $rows cols $cols ";
	for($j=1;$j<=$rows;$j++){
		for($i=1;$i<=$cols;$i++){
			
			$val=$data->val($j,$i,$s);
			//print "<li> $j $i $val ";
			$val=str_replace(',','',$val);
			if(substr($val,0,1)=='('){
				$val='-'.substr($val,1,strlen($val)-2);
			}
			if($j==1){
				$fld=fldname($val);
				$flds[$i]=$fld;
			} 
			else $dt[$j][$i]=$val;
		}
		
	}
	reset($flds);
	while(list($i,$fld)=each($flds)) {
		if($i>1) $iflds.=',';
		$iflds.=$fld;
	}
	while(list(,$dr)=each($dt)){
		reset($flds);
		$vals='';
		while(list($i,$val)=each($dr)){
			if($i>1) $vals.=",";
			$vals.="'".addslashes($val)."'";
		}
		$q="insert into $t ( $iflds ) values ( $vals ) ";
		qexe($q);
		print "<li> $q";
	}

}

?>

