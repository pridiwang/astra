<?php 
if($analyse=='arch-pay'){
	extract($_POST);
	print "<form class='form-inline row'action=?action=$action&analyse=$analyse method=post>
	".monthnav()."
	<select class='form-control' name=type>".aroptions($incentive_type,$type)."</select>
	<select class='form-control' name=brand><option>".qoptions("select distinct(group_name) from brand ",$brand)."</select>
	<button class='btn btn-sm btn-primary'>Analyse</button></form>";
	$codefld="'Y$yr'";
	$date="$yr-$mo-01";
	if($type=='M'){ $codefld="concat('M',month(date))"; 	$cond=" and month(t1.date)='$mo' ";}
	if($type=='Q'){ $codefld="concat('Q',quarter(date))"; 	$cond=" and quarter(t1.date)=quarter('$date')"; }
	if($type=='H'){ $codefld="concat('H',f_halfyear(date))";$cond=" and f_halfyear(t1.date)=f_halfquarter('$date')" ;}
	
	if($brand) $cond.=" and brand='$brand' ";
	$q=" select t1.brand,t1.territory_code,$codefld as 'type',sum(sales_value+manual) 'sales',sum(sales_target) 'sales_target',percent_arch 'arch',percent_incentive 'pay_out',budget,incentive from incentive as t1 where  year(date)='$yr' $cond and type='$type' $cond  group by t1.territory_code ";
	stdreport($q);
}
?>