<?php 
extract($_GET);
$json[response]=true;
if($action=='file-upload'){
	$json[dir]=$dir;
	if($_FILES){
		foreach($_FILES as $file){
		  $rs=move_uploaded_file($file['tmp_name'], "./$dir/".$file["name"]);
		  //$json[rs][$file[name]]=$rs;
		}

	}
}

if($json){
	header("content-type:application/json;charset=UTF-8");
	print json_encode($json);
}
?>