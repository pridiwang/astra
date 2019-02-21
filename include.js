$(function(){
	$('tbody .real, tfoot .real').number(true,2);
	$('tbody .int, tfoot .int').number(true,0);
	$('table').DataTable({
		paginate:false,
		saveState:true,
		language:{
			info :  "_START_ - _END_ / _TOTAL_" ,
			infoFiltered : "( _MAX_ ) ",
			infoEmpty: " no data ",
		},
	});
	
	$('.month-picker').datepicker({
		changeMonth:true,changeYear:true,
		dateFormat:'mm-yyyy',
		onClose:function(dateText,inst){
			$(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
			
		}
	});
	$('td[text="0.00"]').text('');
});
function yearnav(i){
	yr=$('#yr').val();
	y=parseInt(yr)+i;
	$('#yr').val(y);
	$('#navform').submit();
}
function monthnav(i){
	mo=$('#mo').val();
	yr=$('#yr').val();
	m=parseInt(mo)+i;
	if(m>12){ m=1; yr++;}
	if(m<1){m=12;yr--;}
	
	if(m<10) mo='0'+m;
	else mo=m;
	$('#mo').val(mo);
	$('#yr').val(yr);
	$('.month-picker').val(mo+'-'+yr);
	$('.logs').text('');
	$('#navform').submit();
}