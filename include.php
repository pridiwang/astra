<?php
require "config.php";
$db=mysqli_connect($dbserver,$dbuser,$dbpwd,$dbname); 
if($action=='file-delete'){
	unlink($file);
	$action='xls-upload';
}
if($action=='file-upload'){
	if($_FILES){
			extract($_POST);
			$tfile=$dir.'/'.$_FILES[file][name];
			$rs=move_uploaded_file($_FILES[file][tmp_name],$tfile);
			$json[response]=$rs;
			$json[tfile]=$tfile;

	}
	$yr=$_POST[yr];
	$mo=substr($_FILES[file][name],0,2);
	xlsdump($yr,$mo);
	$action='xls-upload';
}
if($action=='update'){
	if(!$id) {
		$rs=qexe("insert into $tb (date) values (now()) ");
		$id=$db->insert_id;
	}
	$q=" update $tb set ";
	$i=0;
	while(list($fld,$val)=each($_POST)){
		if($i>0) $q.=", ";
		$q.=" $fld='$val' ";
		$i++;
	}
	$q.=" where id='$id' ";
	$rs=qexe($q); print $q;
	$action='browse';
}
function edit($tb,$id){
	global $realflds,$intflds,$sumflds,$controlflds;
	if($id) $q="select * from $tb where id='$id' ";
	else  $q="select * from $tb limit 1 ";
	$dr=qdr($q);
	print "<form class='form' action=?action=update&tb=$tb&id=$id method=post><div class='row'>";
	while(list($fld,$val)=each($dr)){
		if(in_array($fld,$controlflds)) continue;
		if(!$id){
			$val='';
		}
		print "<div class='form-group col-sm-3'><label for=4fld>$fld</label><input id=$fld class='form-control' type=text name=$fld value='$val' ></div>";
		
	}
	print "</div><button type=submit class='btn btn-primary'>Save</button></form>";
}
function dr2tb($dr){
	while(list($fld,$val)=each($dr)){
		if(in_array($fld,$controlflds)) continue;
		if($i==0) $thead.="<td>".str_replace('_',' ',$fld)."</td>";
		$ftype='string';
		if(in_array($fld,$intflds)) $ftype='int';
		if(in_array($fld,$realflds)) $ftype='real';
		if(in_array($fld,$sumflds)) $sum[$fld]+=$val;
		if(strlen($val)>20) $val=substr($val,0,20);
		$tbody.="<tr ><td>$fld</td><td class='$fld $ftype'>$val</td></tr>";
	}
	print "<table class='table table-sm table-border rpt'>
	<tbody>$tbody</tbody>
	</table>";
	
}
function qbrowse($q,$tb){
	global $realflds,$intflds,$sumflds,$controlflds;
	$dt=qdt($q);
	while(list($i,$dr)=each($dt)){
		$k=$i+1;
		$tbody.="<tr onclick=window.location.href='?action=edit&tb=$tb&id=$dr[id]';><td >$k</td>";
		while(list($fld,$val)=each($dr)){
			if(in_array($fld,$controlflds)) continue;
			if($i==0) $thead.="<td>".str_replace('_',' ',$fld)."</td>";
			$ftype='string';
			if(in_array($fld,$intflds)) $ftype='int';
			if(in_array($fld,$realflds)) $ftype='real';
			if(in_array($fld,$sumflds)) $sum[$fld]+=$val;
			if(strlen($val)>20) $val=substr($val,0,20);
			$tbody.="<td class='$fld $ftype'>$val</td>";
		}
		$tbody.="</tr>";
	}
	reset($dt);
	list(,$dr)=$dt;
	reset($dr);
	while(list($fld,$val)=each($dr)){
		if(in_array($fld,$controlflds)) continue;
		$val='';$ftype='string';
		if(in_array($fld,$sumflds)){ $val=$sum[$fld]; $ftype='real';}
		$tfoot.="<td class='$ftype'>$val</td>";
	}
	print "<br><a class='btn btn-sm btn-success pull-right' href=?action=edit&tb=$tb > + add</a> <table class='table table-sm table-border rpt'>
	<thead><tr><td></td>$thead</tr></thead>
	<tbody>$tbody</tbody>
	<tfoot><tr>$tfoot</tr></tfoot>
	</table>";
}

function aroptions($ar,$val){
	while(list(,$v)=each($ar)){
		$sl='';
		if($v==$val) $sl=' selected ';
		$o.="<option $sl >$v";
	}
	return $o;
}
function qoptions($q,$val){
	$dt=qexe($q);
	while($dr=$dt->fetch_array()){
		$sl='';
		if($dr[0]==$val) $sl=' selected ';
		if(!$dr[1]) $dr[1]=$dr[0];
		$o.="<option $sl value='$dr[0]'>$dr[1]";
	}
	return $o;
}
function monthnav(){
	global $mo,$yr,$moyr;
	if(!$mo) $mo='01';
	if(!$yr) $yr=2018;
	$pyr=$yr;
	$pmo=$mo-1;
	if($pmo<1){$pmo=12;$pyr--;}
	if($pmo<10) $pmo='0'.$pmo;
	$nyr=$yr;
	$nmo=$mo+1;
	if($nmo>12){$nmo=12;$nyr++;}
	if($nmo<10) $nmo='0'.$nmo;
	
	return "<input type=hidden name=mo id=yr value=$yr><input type=hidden name=mo id=mo value=$mo>
	<i class='fa fa-2x fa-arrow-circle-left text-primary' onclick=monthnav(-1);></i> 
	<input class='form-control input-sm month-picker ' size=6 type=text name=moyr  value='$mo-$yr' > 
	<i class='fa fa-2x fa-arrow-circle-right text-primary' onclick=monthnav(1);></i> 
	";
}
function yearnav(){
	global $yr;
	if(!$yr) $yr=2018;
	return "<i class='fa fa-2x fa-arrow-circle-left text-primary' onclick=yearnav(-1);></i> 
	<input class='form-control input-sm year-picker ' size=3 type=text id=yr name=yr  value='$yr' > 
	<i class='fa fa-2x fa-arrow-circle-right text-primary' onclick=yearnav(1);></i> 
	";
}

function qdr2($q){
	$dt=qdt2($q);
	while(list(,$dr)=each($dt)){
		$dr2[$dr[0]]=$dr[1];
	}
	return $dr2;
}
function qdt2($q){
	$ck=qexe($q);
	if(!$ck) return false;
	while($dr=$ck->fetch_array()){
		$dt[]=$dr;
	}
	return $dt;
}
function qdt($q){
	$ck=qexe($q);
	if(!$ck) return false;
	while($dr=$ck->fetch_assoc()){
		$dt[]=$dr;
	}
	return $dt;
}
function qdr($q){
	$ck=qexe($q);
	if(!$ck) return false;
	return $ck->fetch_assoc();
}
function qval($q){
	$ck=qexe($q);
	if(!$ck) return false;
	list($out)=$ck->fetch_array();
	return $out;
}
function qexe($q){
	global $db;
	$ck=$db->query($q);
	if(!$ck){
		print $db->error;
		return false;
	}
	return $ck;
}
function qexe1($q){
	global $db;
	$ck=$db->query($q);
	return $ck;
}

function xlsdump($yr,$mo){
	require_once 'excel_reader2.php';
	$file="xls/$yr/$mo.xls";
	print "file $file ";
	$data = new Spreadsheet_Excel_Reader($file,false,'TIS-620');
	$sheets=array('sales','manual','territory_update','asm_update','scheme_update');
	$vrows=array(2,2);
	while(list($s,$t)=each($sheets)){
		$q="delete from $t where date_format(date,'%Y-%m')='$yr-$mo' ";
		if(!qexe1($q)){
			$q="delete from $t where date_format(start_date,'%Y-%m')='$yr-$mo' ";
			qexe($q);
		}
		print "<li> - ";
		$rows=$data->rowcount($s);
		$cols=$data->colcount($s);
		print "<li> $t $rows rows $cols cols ";
		unset($dt);
		unset($flds);
		for($j=1;$j<=$rows;$j++){
			for($i=1;$i<=$cols;$i++){
				//print " - - row $j col $i ";
				$val=$data->val($j,$i,$s);
				//print "<li> $j $i $val ";
				$val=str_replace(',','',$val);
				if(substr($val,0,1)=='('){
					$val='-'.substr($val,1,strlen($val)-2);
				}
				if(substr($val,0,1)=='*') $val=substr($val,2,strlen($val));
				if($j==1){
					$fld=fldname($val);
					$flds[$i]=$fld;
				} 
				else $dt[$j][$i]=$val;
			}
			
		}
		reset($flds);
		unset($iflds);
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

			$val=trim($val);
			$q="insert into $t ( $iflds ) values ( $vals ) ";
			$rs=qexe1($q);
			if($rs){ print ". "; $count++;}
			//else print "<li> $q";
			//if($t=='sales') print "<li> $q";
		}
		print "<br> - $count items ";
	}
}
function fldname($in){
	
	$out=trim($in);
	$out=str_replace(' ','_',$out);
	$out=str_replace('.','',$out);
	$out=strtolower($out);
	if($out=='tt_code') $out='emp_code';
	return $out;
}

?>