<?php

function getDbConnect(){

	try{
		
		$dsn = DSN;
		$user = DB_USER;
		$password = DB_PASSWORD;

		$dbh = new PDO($dsn, $user, $password);
	} catch (PDOException $e){
		
		echo($e->getMessage());
		die();
	}

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

function getCategory($dbh){

	$sql = "select * from category where disp = 1 order by name_kana";

	$stmt = $dbh->prepare($sql);

	$stmt->execute();
	
	$data = null;
	
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		
		$data[] = $row;	
	}

	return $data;
}

function getCount($dbh, $tag = null, $search = null){

	$search_keys = array();
	if(isset($search)){
		$search  = preg_replace("/(　)/", " ", $search );
		$search_keys = explode(' ', $search);
	}

	if(isset($tag)){
		$sql = "select count(*) from image where FIND_IN_SET(:tag,tag)";
	} else if(isset($search)){
		$sql = "select count(*) from image where ";

		$sql_where = array();
		$num = 1;
		foreach($search_keys as $key){
			$sql_where[] = "(title like :search" . $num . " or description like :search" . $num . ")";
			$num = $num + 1;
		}

		$sql = $sql . implode(' and ', $sql_where);
	} else {
		$sql = 'select count(*) from image';
	}
	$stmt = $dbh->prepare($sql);

	if(isset($tag)){
		$stmt->bindValue(':tag', $tag);
	} else if (isset($search)){
		$num = 1;
		foreach($search_keys as $key){
			$stmt->bindValue(':search' . $num, '%' . $key . '%');
			$num = $num + 1;
		}
	}

	$stmt->execute();
	$count = $stmt -> fetchColumn();

  	return $count;
}


function getList($dbh, $tag = null, $search = null, $page = 0){

	$search_keys = array();
	if(isset($search)){
		$search  = preg_replace("/(　)/", " ", $search );
		$search_keys = explode(' ', $search);
	}

	if(isset($tag)){
		$sql = "select * from image where FIND_IN_SET(:tag,tag) order by inserted_at desc, id desc";
	} else if(isset($search)){
		$sql = "select * from image where ";

		$sql_where = array();
		$num = 1;
		foreach($search_keys as $key){
			$sql_where[] = "(title like :search" . $num . " or description like :search" . $num . ")";
			$num = $num + 1;
		}

		$sql = $sql . implode(' and ', $sql_where);
		$sql = $sql . ' order by inserted_at desc, id desc';
	} else {
		$sql = 'select * from image order by inserted_at desc, id desc';
	}

	$sql = $sql . ' LIMIT ' . ($page - 1) * 30 . ',30';
	$stmt = $dbh->prepare($sql);

	if(isset($tag)){
		$stmt->bindValue(':tag', $tag);
	} else if (isset($search)){
		$num = 1;
		foreach($search_keys as $key){
			$stmt->bindValue(':search' . $num, '%' . $key . '%', PDO::PARAM_STR);
			$num = $num + 1;
		}
	}

	$stmt->execute();
	
	$data = null;
	
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		
		$data[] = $row;	
	}

	return $data;
}

function getRelation($dbh, $tags){

	$sql_tag_select = array();
	$sql_tag_where = array();

	$num = 1;
	foreach($tags as $tag){

		$sql_tag_select[] = 'case when FIND_IN_SET(:tag' . $num .',tag) > 0 then 1 else 0 end';
		$sql_tag_where[] = 'FIND_IN_SET(:tag' . $num .',tag)';
		$num = $num + 1;
	}
	
	$sql_tag_select_str = implode(' + ',$sql_tag_select) . ' as result';
	$sql_tag_where_str = implode(' or ',$sql_tag_where);
	$sql = "select *, $sql_tag_select_str from image where $sql_tag_where_str order by result desc limit 12";
	$stmt = $dbh->prepare($sql);

	$num = 1;
	foreach($tags as $tag){

		$stmt->bindValue(':tag' . $num, $tag);
		$num = $num + 1;
	}

	$stmt->execute();
	$data = null;
	
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		
		$data[] = $row;	
	}

	return $data;
}

function getNewImage($dbh){
	
	$sql = 'select * from image order by inserted_at desc, id desc limit 12';
	$stmt = $dbh->prepare($sql);
	$stmt->execute();
	
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		
		$data[] = $row;	
	}

	return $data;
}

function getPickupImage($dbh){
	
	$sql = 'select * from image where pickup = 1';
	$stmt = $dbh->prepare($sql);
	$stmt->execute();
	
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		
		$data[] = $row;	
	}

	return $data;
}

function getImage($dbh, $id){

	$sql = 'select * from image where id = :id';

	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(':id', $id, PDO::PARAM_STR);
	$stmt->execute();
	$data = $stmt->fetch(PDO::FETCH_ASSOC);

	return $data;
}


?>