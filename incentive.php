<?php 
if($action=='xls-upload'){
	extract($_POST);
	print "<form class=form-inline action=?action=$action&go=1& method=post id=navform>".yearnav()." <button type=submit class='btn btn-sm btn-primary'>View</button></form>";
	$dir="xls/".$yr;
	if(!file_exists($dir)) mkdir($dir);
	$dh=opendir($dir);
	while($file=readdir($dh)){
		if($file=='.') continue;
		if($file=='..') continue;
		$files[]=$file;
		
	}
	asort($files);
	print "<table class='table table-sm'><thead><tr><td>File</td><td>File Size</td><td>File date</td><td>Action</td></thead><tbody>";
	while(list($i,$file)=each($files)){
		$k=$i+1;
		$fsize=filesize("$dir/$file");
		$ftime=filemtime("$dir/$file");
		print "<tr><td>$file </td><td>".number_format(round($fsize/1024))." k</td><td>".date("Y-m-d H:i:s",$ftime)."</td><td>
		<a href=$dir/$file class='fa fa-download'></a>
		<a class='fa  fa-close text-danger ' href=?action=file-delete&dir=$dir&file=$dir/$file onclick=\"return confirm('Confirm Delete $file ?');\"></a></td></tr>";
	}
	print "</tbody></table>"; 
	closedir($dh);
	print "<form action=?action=file-upload&dir=$dir enctype=multipart/form-data method=post>
	<input type=file name=file><button type=submit>Upload</button></form>
	
	<link href=css/uploadfile.css rel=stylesheet>
<script src=js/jquery.uploadfile.min.js></script>
        <div>Drop file(s) below to upload
        <div id=fileuploader></div>
        <button onclick=location.reload(); class='btn btn-success'>Refresh</button>
        </div>
<script>
$(document).ready(function()
{
        $('#fileuploader').uploadFile({
        url:'api.php?action=file-upload&dir=$dir',
        fileName:'file',
        multiple:true,
        dragDrop:true,
        onSuccess:function(files,data,xhr,pd){
                //files: list of files
                //data: response from server
                //xhr : jquer xhr object
                location.reload();
        }
        });
});</script>
        ";
}
if($action=='xls-dump'){
	extract($_POST);
	
	print "<form class=form-inline action=?action=$action&go=1& method=post>".monthnav()." <button type=submit class='btn btn-sm btn-primary'><i class='fa  fa-download'></i> Dump </button></form>";
	if($go==1){
		print "xls dump of $yr $mo ";
		xlsdump($yr,$mo);
	} 
}
if($action=='incentive-cal'){
	extract($_POST);
	print "<form class=form-inline action=?action=$action&go=1 method=post>".monthnav()." <button class='btn btn-primary btn-sm' types=sumbit><i class='fa fa-calculator'></i> Calculate</button></form>";
	if(!$go) $action='';
}
if($action=='cal-check'){
	$q="select year(t1.date) as 'yr',month(t1.date) as 'mo',t1.* from incentive as t1 where t1.id='$id' ";
	print "<h5>Incentive</h5>";
	
	$inc=qdr($q);
	dr2tb($inc);
	extract($inc);
	$sumflds=array('sales_value','sales_target');
	$date=$inc[date];
	if($inc[type]=='M') $cond.=" and month(t1.date)='$mo' ";
	
	$q="select t1.* from sales as t1 where year(t1.date)='$yr' and t1.brand='$inc[brand]' and t1.territory_code='$inc[territory_code]' $cond ";
	if($position=='ASM') $q="select t1.* from sales as t1,territory_tmp as t2 where t2.territory_code=t1.territory_code and year(t1.date)='$yr' and t1.brand='$inc[brand]' and t2.asm_code='$inc[territory_code]' $cond ";
	print "<h5>Sales</h5>";
	stdreport($q); //print $q;
	$sales=qdt($q);
	
	$q="select t1.* from manual as t1 where year(t1.date)='$yr' and t1.brand='$brand' and t1.territory_code='$territory_code' $cond ";
	if($position=='ASM')  $q="select t1.* from manual as t1 ,territory_tmp as t2 where t2.territory_code=t1.territory_code and year(t1.date)='$yr' and t1.brand='$brand' and t2.asm_code='$territory_code' $cond ";
	print "<h5>Manual</h5>";
	stdreport($q); //print $q;
	$sales=qdt($q);
	
	if($position=='MR') $q="select * from territory_update where territory_code='$inc[territory_code]' and date<='$date' order by date desc limit 1 ";
	if($position=='ASM') $q="select * from asm_update where asm_code='$inc[territory_code]' and date<='$date' order by date desc limit 1 ";
	print "<h5>Territory / ASM </h5>";
	$territory=qdr($q); //print $q;
	dr2tb($territory);
	$q="select * from scheme_update where team='$territory[incentive_scheme]' and position='$position' and product='$brand' and start_date<='$date' order by start_date desc limit 1 ";
	print "<h5>Scheme </h5>";
	$scheme=qdr($q);
	dr2tb($scheme);
	
	
	
	$q="select * from paid where emp_code='$inc[emp_code]' and brand='$inc[brand]' ";
	print "<h5>Paid</h5>";
	$paid=qdr($q);
	dr2tb($paid);
	//print_r($paid);
	
	
}
if($action=='incentive-cal'){
	incentive_calculate('M',$yr,$mo);
	$qtmap=array('03'=>1,'06'=>2,'09'=>3,'12'=>4);
	if(array_key_exists($mo,$qtmap)) incentive_calculate('Q',$yr,$mo);
	if(in_array($mo,array('06','12'))) incentive_calculate('H',$yr,$mo);
	if($mo=='12') incentive_calculate('Y',$yr,$mo);
}
function incentive_calculate($type,$yr,$mo){	
	global $db;
	// prepare territory_tmp , asm_temp for that month 
	$date="$yr-$mo-01";
	print "<div class='logs'>";
	$q="update brand set group_name=brand ";
	qexe($q);
	$q="select distinct(product) as 'pg' from scheme_update where start_date='$date' and product like '%%+%%' ";
	$dt=qdt($q); //print $q;
	while(list(,$dr)=each($dt)){
		extract($dr);
		//print "<li> pg $pg ";
		$plist=explode('+',$pg);
		while(list(,$p)=each($plist)){
			//print " p $p ";
			$q="update brand set group_name='$pg' where brand='$p' ";
			qexe($q);
		}
	}
	
	
	print "updating territory $yr $mo ";
	$q=" insert into territory_tmp (territory_code) select distinct(territory_code) from territory_update order by territory_code ";
	$rs=qexe1($q);
	
	$q=" select territory_code 't' from territory_tmp order by territory_code ";
	$dt=qdt($q);
	
	while(list(,$dr1)=each($dt)){
		extract($dr1);
		$q="select * from territory_update where territory_code='$t' and date='$date' order by date limit 1 ";
		$dr=qdr($q);
		$q="update territory_tmp set date='$dr[date]',emp_code='$dr[emp_code]',mr_name_surname='$dr[mr_name_surname]',start_date='$dr[start_date]',end_date='$dr[end_date]',position='$dr[position]', team='$dr[team]',incentive_scheme='$dr[incentive_scheme]', asm_code='$dr[asm_code]' where territory_code='$t' ";
		qexe($q);
	}
	print " done ";
	
	print "updating asm $yr $mo ";
	$q=" insert into asm_tmp (asm_code) select distinct(asm_code) from asm_update order by asm_code ";
	$rs=qexe1($q);
	$q=" select asm_code 't' from asm_tmp order by asm_code ";
	$dt=qdt($q);
	while(list(,$dr1)=each($dt)){
		extract($dr1);
		$last_date=qval("select date from asm_update where date<='$date' order by date desc limit 1 ");
		$q="select * from asm_update where asm_code='$t' and date='$last_date' order by date limit 1 ";
		$dr=qdr($q);
		$q="update asm_tmp set date='$dr[date]',emp_code='$dr[emp_code]',asm_name='$dr[asm_name]', start_date='$dr[start_date]',end_date='$dr[end_date]', position='$dr[position]', team='$dr[team]',incentive_scheme='$dr[incentive_scheme]' where asm_code='$t' ";
		qexe($q);
	}
	print " done ";
	
	
	
	print "<li> calculate for $type $yr - $mo ";
	
	print "<li> clear existing data ";
	$q="delete from incentive where type='$type' and date='$date' ";
	//if($mo=='01') $q="truncate table incentive  ";
	$rs=qexe($q); 
	if($rs) print " OK ".$db->affected_rows." deleted ";
	
	$m=$mo+1-1;
	if(!$type) $type='M';
	if($type=='M') $cond=" and month(t1.date)='$m' ";	
	if($type=='Q'){
		$cond=" and quarter(t1.date)=quarter('$date') ";	
	} 
	if($type=='H'){
		$cond=" and f_halfyear(t1.date)=f_halfyear('$date') ";
		//if($mo==6) $cond=" and month(t1.date)<'7' ";
		//if($mo==12) $cond=" and month(t1.date)>'6' ";
		
	} 
	
	$q=" insert into incentive (type,date,emp_code,position,territory_code,brand,sales_value,sales_target) 
	select '$type','$date',t2.emp_code,'MR',t1.territory_code,t3.group_name,sum(t1.sales_value), sum(t1.sales_target) 'target' from sales as t1,territory_tmp as t2, brand as t3 where t2.territory_code=t1.territory_code and t3.brand=t1.brand and t2.emp_code<>'E0000' and year(t1.date)='$yr' $cond  group by t1.territory_code,t3.group_name  ";
	
	$rs=qexe($q); //print $q;
	print "<li> dump $type sales MR ".$db->affected_rows." items";

	$q=" insert into incentive (type,date,emp_code,position,territory_code,brand,sales_value,sales_target) 
	select '$type','$date',t2.emp_code,'MR',t1.territory_code,t3.total_name
	,sum(t1.sales_value)
	,sum(t1.sales_target) 'target' from sales as t1,territory_tmp as t2, brand as t3 where t2.territory_code=t1.territory_code  and t3.brand=t1.brand and t2.emp_code<>'E0000' and year(t1.date)='$yr' $cond  and t3.total_name<>'' group by t1.territory_code,t3.total_name order by t2.emp_code ";
	$rs=qexe($q); //print $q;
	print "<li> dump $type sales MR total ".$db->affected_rows." items";
	
	$q="  insert into incentive (type,date,emp_code,position,territory_code,brand,sales_value,sales_target)  select '$type','$date', t4.emp_code,'ASM',t2.asm_code,t3.group_name,sum(t1.sales_value), sum(t1.sales_target) 'target' from sales as t1,territory_tmp as t2, brand as t3 ,asm_tmp as t4 where t2.territory_code=t1.territory_code and t3.brand=t1.brand and t4.asm_code=t2.asm_code and year(t1.date)='$yr' $cond group by t2.asm_code,t3.group_name order by t4.emp_code ";
	qexe($q); //print $q;
	print "<li> dump $type sales ASM ".$db->affected_rows." items";
	if($db->affected_rows==0) print $q;
	
	$q="  insert into incentive (type,date,emp_code,position,territory_code,brand,sales_value,sales_target)  select '$type','$date', t4.emp_code,'ASM',t2.asm_code,t3.total_name,sum(t1.sales_value), sum(t1.sales_target) 'target' from sales as t1,territory_tmp as t2, brand as t3 ,asm_tmp as t4 where t2.territory_code=t1.territory_code and t2.date='$date' and t3.brand=t1.brand and t4.asm_code=t2.asm_code and year(t1.date)='$yr' $cond and t3.total_name<>'' group by t2.asm_code,t3.total_name ";
	qexe($q); //print $q;
	print "<li> dump $type sales ASM total ".$db->affected_rows." items"; 
	
	//print " $q rs $rs ";
	$q="select * from incentive where type='$type' and date='$date' order by team,emp_code,brand ";
	$dt=qdt($q);
	
	while(list(,$dr)=each($dt)){
		extract($dr);
		if($position=='MR') $manual=qval("select sum(t1.amount) from manual as t1 where t1.territory_code='$territory_code' and t1.brand='$brand' and year(t1.date)='$yr' $cond ");
		if($position=='ASM') $manual=qval("select sum(t1.amount) from manual as t1 where t1.asm_code='$territory_code' and t1.brand='$brand' and year(t1.date)='$yr' $cond ");
		if($manual){
			$q=" update incentive set manual='$manual' where id='$id' ";
			qexe($q);
			//print "<li> add manual data for $type $position $emp_code $territory_code $brand $manual ";	
			$m++;
		}
		
	}
	print "<li> add manual data for $m items ";	
	$q="update incentive set percent_arch=(sales_value+manual)/sales_target*100 where type='$type' and date='$date' ";
	qexe($q);
	$sch=array(
	 'M'=>array('min'=>90,'max'=>100)
	,'Q'=>array('min'=>90,'max'=>110)
	,'H'=>array('min'=>95,'max'=>120)
	,'Y'=>array('min'=>95,'max'=>120)
	);
	$max=$sch[$type][max];
	$min=$sch[$type][min];
	$q="update incentive set percent_incentive=floor(percent_arch) where type='$type' and date='$date' ";
	qexe($q);
	$q="update incentive set percent_incentive='$max' where type='$type' and date='$date' and percent_arch>'$max' ";
	qexe($q);
	$q="update incentive set percent_incentive='0' where type='$type' and date='$date' and percent_arch<'$min' "; 
	qexe($q);
	print "<li> adjust percent incentive ";
	
	
	$q="select * from incentive where type='$type' and date='$date' ";
	$dt=qdt($q); //print $q;
	while(list(,$dr)=each($dt)){
		extract($dr);
		if($position=='MR') $dr2=qdr("select incentive_scheme 'team',start_date,end_date from territory_tmp where emp_code='$emp_code' ");
		if($position=='ASM') $dr2=qdr("select incentive_scheme 'team',start_date,end_date from asm_tmp where emp_code='$emp_code' ");
		extract($dr2);
		$ratio=qval("select ratio from scheme_update where team='$team' and product='$brand' and position='$position' and start_date<='$date' order by start_date desc limit 1  ");
		if(!$ratio) $ratio=100;
		$payfld=strtolower($position).'_percent';
		$pay_pc=qval("select $payfld from incentive_pay where percent_incentive='$percent_incentive' and start_date<='$date' order by start_date desc limit 1 ");
		$payfld=strtolower($position).'_pay';
		$pay_100=qval("select $payfld from incentive_100 where start_date<='$date' order by start_date desc limit 1 ");
		$pay=$pay_100*$pay_pc/100;
		$inc=round($pay*$ratio/100);
		$budget=$inc;
		$paid='';
		if($type=='Q'){
			$inc=$inc*3;
			$budget=$inc;
			$paid=qval("select sum(t1.incentive) from incentive as t1 where year(t1.date)='$yr' $cond and t1.emp_code='$emp_code' ");
			
			$topay=$inc-$paid;
			if($topay<0) $inc=0;
			else $inc=$topay;
		}
		if($type=='H'){
			$inc=$inc*6;
			$budget=$inc;
			$paid=qval("select sum(t1.incentive) from incentive as t1 where year(t1.date)='$yr' $cond and t1.emp_code='$emp_code' ");
			
			$topay=$inc-$paid;
			if($topay<0) $inc=0;
			else $inc=$topay;
		}
		if($type=='Y'){
			$inc=$inc*12;
			$budget=$inc;
			$paid=qval("select sum(t1.incentive) from incentive as t1 where year(t1.date)='$yr' and t1.emp_code='$emp_code' ");
			
			$topay=$inc-$paid;
			if($topay<0) $inc=0;
			else $inc=$topay;
		}
		if($position=='MR') $tb='territory_tmp';
		if($position=='ASM') $tb='asm_tmp';
		$empdate=qval("select start_date ,end_date from $tb where emp_code='$emp_code' ");
		extract($empdate);
		$sdate="$yr-$mo-05";
		$edate=date('Y-m-d',mktime(0,0,0,$mo+1,-1,$yr));
		
		if($start_date>$sdate){
			print "<br> -- $emp_code start_date $start_date clear sales ";
			$inc=0;
		}
		if(($end_date>'0000-00-00')&&($end_date<$edate)){
			print "<br> -- $emp_code end_date $end_date clear sales ";
			$inc=0;
		} 
		$q="update incentive set team='$team',ratio='$ratio',incentive='$inc',budget='$budget' where id='$id' ";
		qexe($q);
		$m=$mo+1-1;
		$paidfld=$m.$type;
		$chkpaid=qval("select $paidfld from paid where emp_code='$emp_code' and brand='$brand' ");
		$chkpaid=$chkpaid+1-1;
		$inc=$inc+1-1;
		if($chkpaid==$inc){ $chk="<font color=green> Yes </font>"; $correct++;}
		else{ 
			$q="update incentive set diff='1',chk_note='$chkpaid' where id='$id' ";
			qexe($q);
			$chk="<font color=red><b> No </b></font>"; 
			$incorrect++;
		}
		print "<li class=calresult>  - $type $territory_code $emp_code $team $position $brand sales ".number_format($sales_value,2)." manual $manual target ".number_format($sales_target,2)." arch $percent_arch pay_out $percent_incentive % ratio $ratio paid $paid incentive $inc paid $chkpaid <a href=?action=cal-check&id=$id>$chk </a> ";
		
	}
	print "<br><b> ----- done for $type $mo / $yr  ------- correct $correct <font color=red>incorrect $incorrect </font></b></div>";
}

?>