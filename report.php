<?php 
if($action=='report'){
	extract($_POST);

	$sumflds=array();
	$sumflds=array('sales_value','incentive');
	if($report=='sales'){
		extract($_POST);
		if(!$type) $type='M';
		print "<form class='form-inline row' action=?action=$action&report=$report method=post>".monthnav()."
		<select class='form-control' name=type><option value=''>.type".aroptions($incentive_type,$type)."</select>
		<select class='form-control' name=team><option value=''>.team".qoptions("select distinct(incentive_scheme) from territory_update ",$team)."</select>
		<button class='btn btn-sm btn-primary' type=submit>Report</button></form>";
		$m=$mo+1-1;
		$date="$yr-$mo-01";
		if($type=='M') $cond=" and month(t1.date)='$m' ";
		if($type=='Q'){ $cond=" and quarter(t1.date)=quarter('$date') "; $gby.=",quarter($date)"; }
		if($type=='H'){ 
			$cond=" and f_halfyear(t1.date)=f_halfyear('$date') "; 
			$gby.=",f_halfyear($date)";
		}
		if($type=='Y'){ $cond="  "; $gby.=",year($date)";}
		if($team) $cond.=" and t2.team='$team' ";
		
		
		$q=" select t1.date,t2.emp_code,t2.mr_name_surname,t1.territory_code,sum(t1.sales_value) 'sales', sum(t1.sales_target) 'target' from sales as t1,territory_update as t2 where t2.territory_code=t1.territory_code and t2.emp_code<>'E0000' and year(t1.date)='$yr' $cond   group by t1.territory_code $gby ";
		stdreport($q);
	}
	if($report=='incentive'){
		extract($_POST);
		$position_list=array('MR','ASM');
		print "<form class='form-inline row' action=?action=$action&report=$report id=navform method=post>".monthnav()."
		<select class='form-control' name=position><option value=''>.position".aroptions($position_list,$position)."</select>
		<select class='form-control' name=type><option value=''>.type".aroptions($incentive_type,$type)."</select>
		<select class='form-control' name=territory><option value=''>.territory".qoptions("select distinct(territory_code) from territory_update ",$territory)."</select>
		<select class='form-control' name=brand><option value=''>.brand".qoptions("select distinct(t1.group_name) from brand as t1 union (select distinct(total_name) from brand as t2 where t2.total_name<>'') ",$brand)."</select>
		<select class='form-control' name=team><option value=''>.team".qoptions("select distinct(incentive_scheme) from territory_update ",$team)."</select>
		
		<button class='btn btn-sm btn-primary' type=submit>Report</button></form>";
		
		$date="$yr-$mo-01";
		
		if($type=='M') $cond =" and date='$date' ";
		if($type=='Q') $cond=" and quarter(date)=quarter('$date') ";
		if($type=='H') $cond=" and f_halfyear(date)=f_halfyear('$date') "; 
		if($type) $cond.=" and type='$type' ";
		else { $cond=" and date='$date' ";}
		if($territory) $cond.=" and territory_code='$territory' ";
		if($team) $cond.=" and team like '$team%%' ";
		if($brand) $cond.=" and brand='$brand' ";
		if($position) $cond.=" and position='$position' ";
		$q="select id,date,type,position 'Pos',emp_code,territory_code 'territory',brand,sales_value,manual,sales_target,percent_arch 'arch',percent_incentive 'pay_out', ratio,incentive from incentive where year(date)='$yr' $cond order by emp_code ";
		//print $q;
		stdreport($q);
	}
	
	if($report=='incentive_ind'){
		
		print "<form class='form-inline row' action=?action=$action&report=$report method=post>".monthnav()."
		<select class='form-control' name=emp_code><option value=''>.territory".qoptions("select distinct(emp_code) 'emp_code' from territory_update union select distinct(emp_code) 'emp_code' from asm_update  ",$emp_code)."</select>
		<button class='btn btn-sm btn-primary' type=submit>Report</button></form>";
	}
	if($report=='incentive_summary'){
		$mlist=array('1M','2M','3M','3Q','4M','5M','6M','6Q','6H','7M','8M','9M','9Q','10M','11M','12M','12Q','12H','12Y');
		print "<form class='form-inline row' action=?action=$action&report=$report method=post>".yearnav()."
		
		<button class='btn btn-sm btn-primary' type=submit>Report</button></form>";
		$dt=qdt("select distinct(emp_code) 'emp_code' from territory_update where emp_code>'E0001' union select distinct(emp_code) 'emp_code' from asm_update where emp_code>'E0001' ");
		array_push($sumflds,'total');
		
		while(list(,$dr1)=each($dt)){
			extract($dr1);
			$q=" select concat(month(date),type) 'type' ,sum(incentive) 'incentive' from incentive where year(date)='$yr' and emp_code='$emp_code' group by date,type,territory_code order by date ";	
			$dr2=qdr2($q);
			if(count($dr2)>0){
				reset($mlist);
				while(list(,$m)=each($mlist)){
					if(!array_key_exists($m,$dr2)) $dr2[$m]=0;
				}
				$sum=0;
				while(list($fld,$val)=each($dr2)){
					$sum+=$val;
				}
				$dr2[total]=$sum;
				
				$dr3=array_merge($dr1,$dr2);
				$dt2[]=$dr3;
			}
		}
		//print "<pre>";print_r($dt2);print "</pre>";
		report($dt2);
	}

}
function stdreport($q){
	
	$dt=qdt($q);
	report($dt);
}
function report($dt){
	global $realflds,$intflds,$sumflds,$incentive_type,$percentflds,$controlflds;
	while(list($i,$dr)=each($dt)){
		$k=$i+1;
		$tbody.="<tr><td>$k</td>";
		while(list($fld,$val)=each($dr)){
			if(in_array($fld,$controlflds)) continue;
			$ftype='string';
			if(in_array($fld,$intflds)) $ftype='int';
			if(in_array($fld,$realflds)) $ftype='real';
			if(in_array($fld,$sumflds)) $sum[$fld]+=$val;
			if(in_array(substr($fld,strlen($fld)-1,1),$incentive_type)) $ftype='real'; 
			if(in_array($fld,$percentflds)) $ftype='percent';
			$tbody.="<td class='$fld $ftype'>$val</td>";
			$csvb.=$val.",";
			if($i==0){
				$thead.="<td class='$fld $ftype'>".str_replace('_',' ',$fld)."</td>";
				$csvh.=str_replace('_',' ',$fld).",";
				if(in_array(substr($fld,strlen($fld)-1,1),$incentive_type)) array_push($sumflds,$fld);
			} 
		}
		$tbody.="</tr>";
		$csvb.="\n";
	}
	reset($dt);
	list(,$dr)=$dt;
	reset($dr);
	while(list($fld,$val)=each($dr)){
		if(in_array($fld,$controlflds)) continue;
		$val='';$ftype='string';
		if(in_array($fld,$sumflds)){ $val=$sum[$fld]; $ftype='real';}
		$tfoot.="<td class='$ftype'>$val</td>";
		$csvf.=$val.",";
	}
	print "<table class='table table-sm table-border table-striped rpt'>
	<thead><tr><td></td>$thead</tr></thead>
	<tbody>$tbody</tbody>
	<tfoot><tr><td></td>$tfoot</tr></tfoot>
	</table>";
	$csv="$csvh
	$csvb
	$csvf
	";
	$file="csv/report.csv";
	file_put_contents($file,$csv);
	global $rnd;
	print "<a href=$file?$rnd target=_blank>Export to CSV</a>";
}
?>