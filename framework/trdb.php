<?php
class trdb{
	
	
	public $_detail = [];
	protected $debug_mode 	= false;
	protected $test_mode 	= false;
	protected $script_mode 	= false;
	protected $csv_mode 	= false;


	public function __construct($_db){
		
		if( isset($_db->_detail) ){
			$info = [];
			$info["database_type"] 	= $_db->_detail["database_type"];
			$info["database_name"] 	= $_db->_detail["database_name"];
			$info["server"] 		= $_db->_detail["server"];
			$info["username"] 		= $_db->_detail["username"];
			$info["password"] 		= $_db->_detail["password"];
		}else{
			$info = [];
			$info["database_type"] 	= $_db["database_type"];
			$info["database_name"] 	= $_db["database_name"];
			$info["server"] 		= $_db["server"];
			$info["username"] 		= $_db["username"];
			$info["password"] 		= $_db["password"];
			$this->_detail			= $info;
		}
		
		$input = [	"database_type"	=>$info["database_type"],
					"database_name"	=>$info["database_name"],
					"server"		=>$info["server"],
					"username"		=>$info["username"],
					"password"		=>$info["password"],
				 ];
        $this->pdo  = new PDO($input["database_type"].':host='.$input["server"].';dbname='.$input["database_name"].';charset=utf8', $input["username"], $input["password"]);	
	}
	
	

	public function debug(){
		$this->debug_mode = true;
		return $this;
	}
	
	

	public function csv(){
		$this->csv_mode = true;
		return $this;
	}
	

	public function test(){
		$this->test_mode = true;
		return $this;
	}
	
	

	public function script(){
		$this->script_mode = true;
		return $this;
	}
	
	
	public function query($input){
		return $this->pdo->query($input);
	}
	
	
	public function prepare($input){
		return $this->pdo->prepare($input);
	}

	
	public function execute($input){
		return $this->pdo->execute($input);
	}

	
	public function lastInsertId(){
		return $this->pdo->lastInsertId();
	}
	
	public function beginTransaction(){
		return $this->pdo->beginTransaction();
	}
	
	public function commit(){
		return $this->pdo->commit();
	}
	
	public function rollback(){
		return $this->pdo->rollback();
	}

	
	
	
	public function update($table, $detail = array(), $where){
		
		$sql = " update {$table}
				 set
				";
		$i   = 0;
		$exe = array();
		foreach( $detail as $key=>$item ){
			$sql .= ($i==0)?"":",";
			$sql .= " {$key} = :{$key} ";
			$exe  = array_merge($exe,array(":{$key}"=>$item));
			$i++;
		}
		$sql.= " where {$where} ";
		
		$sth = $this->pdo->prepare($sql);
		$sth->execute($exe);
		
		if( $this->debug_mode ){
			$this->debug_mode = false;
			return $sth->errorInfo();
		}
		
		return ['success' => 1, 'errorInfo' => $sth->errorInfo()];
	}

	
	
	public function insert($table, $detail = array(), $option = array() ){
		
		
		$revise 				= [];
		$revise["duplicate"]	= !empty($option["duplicate"])?$option["duplicate"]:""; // update, ignore, update_not_blank
		
		
		
		$sql = " INSERT INTO `{$table}`
				(
				";
				
		$exe 		= array();
		$i	 		= 0;
		$ignore_key = "";
		foreach( $detail[0] as $key=>$item ){
			if( $i == 0 ){
				$ignore_key = $key;
			}
			
			$sql .= ($i == 0)?"":" , ";
			$sql .= " `{$key}` ";
			$i++;
		}	
		$sql .= " ) VALUES ";
		
		
		$i	  = 0;	
		foreach( $detail as $item ){

			$temp = array();
			
			$sql .= ($i==0)?"":" , ";
			$sql .= " ( ";
			
			$j = 0;
			foreach( $item as $key=>$it ){
				$sql 				.= ($j==0)?"":" , ";
				$sql 				.= " :{$key}{$i} ";
				$temp[":{$key}{$i}"] = empty($it)?"":$it;
				$j++;
			}
			$sql .= " ) ";
			$exe  = array_merge($exe,$temp);
			$i++;
		}
		
		if( $i > 0 ){
			
			if( $this->script_mode ){
				$this->script_mode = false;
				return ["sql"=>$sql,"exe"=>$exe];
			}
			
			if( $this->test_mode ){
				$this->test_mode = false;
				foreach( array_reverse($exe) as $key => $item ){
					$sql = str_replace($key, "'{$item}'", $sql);
				}
				return $sql;
			}
			
			
			
			if( empty($revise["duplicate"]) ){
				
				//~ do nothing
			}
			else if( $revise["duplicate"] == "update"){
				
				$sql	.= " on duplicate key update ";
				$i	 = 0;
				foreach( $detail[0] as $key=>$item ){
					$sql .= ($i == 0)?"":" , ";
					$sql .= " `{$key}` = values(`{$key}`) ";
					$i++;
				}	
				
			}
			else if( $revise["duplicate"] == "ignore"){
				
				$sql	.= " on duplicate key update `{$ignore_key}` = `{$ignore_key}` ";
			}
			else if( $revise["duplicate"] == "update_not_blank"){
				
				$sql	.= " on duplicate key update ";
				$i	 = 0;
				foreach( $detail[0] as $key=>$item ){
					$sql .= ($i == 0)?"":" , ";
					$sql .= " CASE WHEN '' <> VALUES(`{$key}`) THEN VALUES(`{$key}`) ELSE `{$key}` END ";
					$i++;
				}	
			}

			
			$sth 		= $this->pdo->prepare($sql);
			$check 		= $sth->execute($exe);
			$last_id	= $this->pdo->query("select last_insert_id();")->fetchColumn();
			$last_id 	= empty($last_id) ? 0 : $last_id;
			$return 	= ['last_id' => $last_id, 'error_info' => $sth->errorInfo()];
			
		
			if( $this->debug_mode ){
				$this->debug_mode = false;
				return $sth->errorInfo();
			}
		}else{
			$check  = 0;
			$return = 0;
		}
		
		return $return;
	}

	
	public function search_concat($columns = array(),$keywords){
		
		$ks  	= explode(" ",trim($keywords));
		$concat	= " concat(".implode(",' ',",$columns).") ";
		
		$i   = 0;
		$exe = array();
		$sql = "";
		foreach( $ks as $key => $item ){
			if($item == "") continue;
			$sql .= $key == 0 ? "" : " and ";
			$sql .= " {$concat} like :keyword{$i} ";
			$exe  = array_merge($exe,array(":keyword{$i}"=>"%{$item}%"));
			$i++;
		}
		
		if( trim($keywords) != "" )
			$return = array("text"=>$sql,"array"=>$exe);
		else
			$return = array("text"=>"","array"=>array());
		
		return $return;
	}

	
	public function search_condition($columns = array(), $like = "="){

		$exe = array();
		$sql = "";
		$i   = 0;
		foreach( $columns as $key=>$item ){
			
			if( !empty($item) ){
			
				$col = $key;
				$val = $item;
				
				$sql .= " and {$col} {$like} :search_condition{$i} ";
				$exe  = array_merge($exe,array(":search_condition{$i}"=>(($like=="=")?"{$val}":"%{$val}%")));
				$i++;
			}
		}
		
		return array("text"=>$sql,"array"=>$exe,"columns"=>$columns);
	}
	
	
	public function get( $table, $condition = [], $option = [] ){
		$revise 			= $option;
		$revise["one_row"]	= true;
		$return				= $this->search($table,$condition,$revise);
		return empty($return)?[]:$return["result"];
	}
	
	
	
	public function search($table, $condition = [], $option = [] ){
		
		
		if( !empty($option["limit"]) ){
			$option["limit"] = ($option["limit"]=="no"||!isset($option["limit"]))?"no":$option["limit"]+1;
		}
		
		$option = [ "start" 	=> (empty($option["start"])?0:$option["start"]),
					"limit" 	=> (empty($option["limit"])?51:$option["limit"]),
					"order_by" 	=> (empty($option["order_by"])?"":$option["order_by"]),
					"group_by" 	=> (empty($option["group_by"])?"":$option["group_by"]),
					"having" 	=> (empty($option["having"])?"":$option["having"]),
					"one_row" 	=> (empty($option["one_row"])?"":true),
					];
		
		$revise = [];
		foreach( $condition as $key => $item ){
			
			$skip			= 0;
			$temp 			= [];
			$temp["column"] = $key;

			if( is_array($item) ){
				
				if( !empty($item["skip"])) $skip = 1;
				
				if( empty($item["type"]) ){
                    $temp["type"]           = "default";
                    $temp["condition"]      = empty($item["condition"])?"=":$item["condition"];
                    $temp["value"]          = $item["value"];
	            }
               	else if( $item["type"] == "between" ){
					$temp["type"]	= "between";
					$temp["from"]	= $item["from"];
					$temp["to"]		= $item["to"];
				}
				else if( $item["type"] == "search_concat" ){
					if(trim($item["keyword"]) == "") continue;
					$temp["type"]	= "search_concat";
					$re				= $this->search_concat($item["column"],$item["keyword"]);
					$temp["sql"]	= $re["text"];
					$temp["exe"]	= $re["array"];
					
				}
				else if( $item["type"] == "in" ){
					$temp["type"]	= "in";
					$_col			= str_replace(".","_",$key);
					
					$temp["exe"]	= [];
					$j				= 0;
					$re				= "";
					if( !empty($item["data"]) && count($item["data"]) > 0 ){
						foreach( $item["data"] as $it ){
							$re			 .= ($j==0)?"":",";
							$re			 .= ":{$_col}_{$j}";
							$temp["exe"] = array_merge($temp["exe"],array(":{$_col}_{$j}"=>$it));
							$j++;
						}
						$temp["sql"]	= "(".$re.")";
					}
					else{
						$temp["sql"]	= "";
					}
				}
				else if( $item["type"] == "custom" ){
					$temp["type"]	= "custom";
					$temp["value"]	= $item["value"];
				}
				else{
					$temp["type"]		= "default";
					$temp["condition"]	= empty($item["condition"])?"=":$item["condition"];
					$temp["value"]		= $item["value"];
				}
			}else{
				$temp["type"]		= "default";
				$temp["condition"]	= "=";
				$temp["value"]		= $item;
			}
			
			if( $skip == 0 ) array_push($revise,$temp);
		}
		
		
		
		
		if( strtolower(substr(trim($table),0,6)) == "select" ){
			$sql 	= "{$table} ";
		}
		else if( strtolower(substr(trim($table),0,6)) == "update" ){
			$sql 	= "{$table} ";
		}
		else if( strtolower(substr(trim($table),0,6)) == "insert" ){
			$sql 	= "{$table} ";
		}
		else{
			$sql 	= "select * from {$table} ";
		}

		if(count($revise) > 0) $sql .= " where ";
		
		$exe	= [];
		$i		= 0;
		foreach( $revise as $item ){
			
			$sql.= ($i==0)?"":" and ";
			
			$col	= str_replace(".","`.`",$item["column"]);
			$_col	= str_replace(".","_",$item["column"]);
			
			
			if( $item["type"] == "default" ){
				
				$sql.= " `{$col}` {$item["condition"]} :{$_col} ";
				$exe = array_merge($exe,array(
							":{$_col}" => $item["value"]
						));
						
			}
			else if( $item["type"] == "custom" ){
				
				$sql.= " {$item["value"]} ";
			
			}
			else if( $item["type"] == "between" ){
				
				$sql.= " (`{$col}` between :{$_col}_from and :{$_col}_to) ";
				$exe = array_merge($exe,array(
							":{$_col}_from" => $item["from"],
							":{$_col}_to"   => $item["to"]
						));
			
			}
			else if( $item["type"] == "in" ){
				
				if( !empty($item["sql"]) ){
					$sql.= " `{$col}` in ".$item["sql"];
					$exe = array_merge($exe,$item["exe"]);
				}
			
			}
			else if( $item["type"] == "search_concat" ){
				
				$sql.= " ".$item["sql"];
				$exe = array_merge($exe,$item["exe"]);
			
			}
			$i++;
		}
		
		
		$sql  .= !empty($option["group_by"])?" group by {$option["group_by"]} ":"";
		$sql  .= !empty($option["having"])?" having {$option["having"]} ":"";
		$sql  .= !empty($option["order_by"])?" order by {$option["order_by"]} ":"";

		
		if( isset($option["limit"]) && $option["limit"] == "no" ){
			$sql  .= " ";
		}else{
			$sql  .= " limit {$option["start"]},{$option["limit"]};";
		}
		
		
		if( $this->test_mode ){
			$this->test_mode = false;
			$sth   = $this->pdo->prepare($sql);
			$check = $sth->execute($exe);
			foreach( array_reverse($exe) as $key => $item ){
				$sql = str_replace($key, "'{$item}'", $sql);
			}
			return ["result"=>$sql, "errorInfo"=>$sth->errorInfo()];
		}
		
		
		if( $this->script_mode){
			$this->script_mode = false;
			return ["sql"=>$sql,"exe"=>$exe];
		}
		
		
		if( $this->csv_mode){
			$sth   = $this->pdo->prepare($sql);
			$check = $sth->execute($exe);
			return $sth;
		}
		
		
		$sth   = $this->pdo->prepare($sql);
		$check = $sth->execute($exe);
		
		
		if( $this->debug_mode ){
			$this->debug_mode = false;
			return $sth->errorInfo();
		}
		
		
		if( $check == 0 ){
			$return = ["success"=>0,"message"=>"Error: execution is wrong"];
		}else if( !empty($option["one_row"]) && $option["one_row"] == true ){
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			$return = ["success"=>1,"message"=>"Successfully fetch the results","result"=>$result];
		}else{
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			$return = ["success"=>1,"message"=>"Successfully fetch the results","result"=>$result];
		}
		
		return $return;
	}
	
	
	
	
	public function all($table, $condition = []){
		
		$sql 	= "	select * from {$table} where ";
		$exe	= [];
		$i		= 0;
		foreach( $revise as $key => $item ){
			
			$sql.= ($i==0)?"":" and ";
			$sql.= " `{$key}` = :{$key} ";
			$exe = array_merge($exe,array(
						":{$key}" => $item
					));
			$i++;
		}
		
		
		if( $this->test_mode ){
			$this->test_mode = false;
			foreach( $exe as $key => $item ){
				$sql = str_replace($key, "'{$item}'", $sql);
			}
			return $sql;
		}
		
		
		if( $this->script_mode ){
			$this->script_mode = false;
			return ["sql"=>$sql,"exe"=>$exe];
		}
		
		
		$sth   = $this->pdo->prepare($sql);
		$check = $sth->execute($exe);
		
		
		if( $this->debug_mode ){
			$this->debug_mode = false;
			return $sth->errorInfo();
		}
		
		
		
		
		if( $this->csv_mode ){
			
			$this->csv_mode = false;
			$this->sql2csv(["name"=>"pon.csv","sth"=>$sth]);
			
		}else if( $check == 0 ){
			$return = ["success"=>0,"message"=>"Error: execution is wrong"];
		}else{
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			$return = ["success"=>1,"message"=>"Successfully fetch the results","result"=>$result];
		}
		
		return $return;
	}
	
	
	
	public function insertLog($detail,$company_id=0){
		
		$company_id = !empty($company_id)?$company_id:$_SESSION["company_id"];
		$user_id 	= !empty($_SESSION["user_id"])?$_SESSION["user_id"]:0;
		$sth 		= $this->pdo->prepare("
						insert into log 
						( `company_id`, `user_id`, `dt`, `engine`, `detail`) values 
						( '{$company_id}', '{$user_id}', now(), :engine, :detail);
						");

		$exe = array(":engine" => $_SERVER["REQUEST_URI"],":detail" => $detail);
		return $sth->execute($exe);
	}
	
	
	
	public function array2tempTable($array,$prefix="",$company_id){

		$tableName 	= "random_".$prefix.$company_id.time();
		$array		= empty($array)?array():$array;
		//~ create temp table
		if(count($array)>0){
			$sql	= "	CREATE TEMPORARY TABLE `{$tableName}` (
						  `company_id` int NOT NULL
						";
						
			$isql 	= "INSERT INTO `{$tableName}` (`company_id` ";
			foreach( $array[0] as $key=>$item ){
			if( $key == "company_id" ) continue;
			$sql	.= ", `{$key}` varchar(255) NOT NULL ";
			$isql	.= ", `{$key}` ";
			}
			$sql	.= " ) ENGINE='MEMORY';";			
			$isql	.= " ) VALUES ";
			$this->pdo->query($sql);
		}


		//~ insert data to table
		if(count($array)>0){
			
			$i = 0; $exe = array();
			foreach( $array as $item ){
				
				$tmp   = array();
				$isql .= ($i==0)?"":",";
				$isql .= "('{$company_id}' ";
				foreach( $array[0] as $k => $it ){
				if( $k == "company_id" ) continue;
				$isql .= ", :{$k}{$i}";	
				$tmp   = array_merge($tmp,array(":{$k}{$i}"=>(empty($item[$k])?"":$item[$k])));
				}
				$isql .= ")";
				$exe  = array_merge($exe,$tmp);
				$i++;
			}
			if($i > 0){

				if( $this->test_mode ){
					$this->test_mode = false;
					foreach( array_reverse($exe) as $key => $item ){
						$isql = str_replace($key, "'{$item}'", $isql);
					}
					return ["create_code"=>$sql, "insert_code"=>$isql];
				}


				$sth = $this->pdo->prepare($isql);
				$sth->execute($exe);	
			}
		}
		return (count($array)>0)?$tableName:false;
	}
	
	
	public function sql2csv($option){

		$revise = [	"name"	=>(empty($option["name"])?"sql.csv":$option["name"]),
					"sth"	=>$option["sth"],
					"head"	=>$option["head"]
				  ];

		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename={$revise["name"]}");
		$fp = fopen('php://output', 'wb');
		
		if( !empty($revise["head"]) ){
			fputcsv($fp, $revise["head"]);
		}
		
		while($line = $revise["sth"]->fetch(PDO::FETCH_ASSOC)){
			fputcsv($fp, $line);
		}
		fclose($fp);	
	}


	public function line_notify($option){

		$revise					= [];
		$revise["message"]		= empty($option["message"])?"test":$option["message"];
		$revise["user_id"]		= empty($option["user_id"])?"":$option["user_id"];
		

		$message	= $revise["message"];

		if( empty($revise["user_id"]) ) return 1;

		$code		= "";
		
		$sth		= $this->query("select line_code from user where user_id in ( {$revise["user_id"]} ) ");

		while ($item = $sth->fetch(PDO::FETCH_ASSOC)){
			
			if( empty($item["line_code"]) ){
				//~ do nothing
			}
			else{
				
				$code			= $item["line_code"];
				$message		= empty($message)?"test":$message;
				$curl 			= curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL 	=> "https://notify-api.line.me/api/notify",
				  
				  CURLOPT_DNS_CACHE_TIMEOUT => 10,
				  CURLOPT_CONNECTTIMEOUT 	=> 1,
				  CURLOPT_TIMEOUT 			=> 1,
				  CURLOPT_RETURNTRANSFER 	=> true,
				  CURLOPT_ENCODING 			=> "",
				  CURLOPT_MAXREDIRS 		=> 10,
				  CURLOPT_HTTP_VERSION 		=> CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST 	=> "POST",
				  CURLOPT_POSTFIELDS 		=> "message={$message}",
				  CURLOPT_HTTPHEADER 		=> array(
					"authorization: Bearer {$code}",
					"cache-control: no-cache",
					"content-type: application/x-www-form-urlencoded"
				  ),
				));
				$response = curl_exec($curl);
			}
		}
		return 1;
	}
	



	public function insertBatch($table = array(), $detail = array(), $option = array() ){
		
		
		if( empty($table["head"]) OR empty($table["body"]) ){
			$return = ["success"=>0,"message"=>"table head & body cannot be blank"];
			return $return;
		}
		else{
			$head 		= $table["head"];
			$body 		= $table["body"];
		}


		if( !empty($option["join_key"]) ){
			$join_key 	= $option["join_key"];
		}
		else{
			$return = ["success"=>0,"message"=>"option join_key cannot be blank"];
			return $return;
		}
		



		$this->pdo->beginTransaction();

		$this->pdo->query("SET @ids = 0;");

		$test_script = "SET @ids = 0;\n";


		foreach ($detail as $k => $it) {
			

			// insert head
			if(true){
				$sql  = "INSERT INTO `{$head}` SET ";
				
				$exe  = array();
				$i	  = 0;
				foreach( $it["head"] as $key=>$item ){
					$sql .= ($i == 0)?"":" ,\n";
					$sql .= " `{$key}` = :{$key}_head_{$i} ";
					$exe  = array_merge($exe,array(":{$key}_head_{$i}"=>is_null($item)?"":$item));
					$i++;
				}	
				$sql .= ";";

				$sth  = $this->pdo->prepare($sql);
				$sth->execute($exe);				

				$test_script .= $sql."\n";
				foreach( array_reverse($exe) as $key => $item ){
					$test_script = str_replace($key, "'{$item}'", $test_script);
				}

				$this->pdo->query("SET @last_id = LAST_INSERT_ID();");
				$this->pdo->query("SET @ids = concat(@ids,',',@last_id);");

				$test_script .= "SET @last_id = LAST_INSERT_ID();\n";
				$test_script .= "SET @ids = concat(@ids,',',@last_id);\n";

			}
			


			// insert body
			if(true){

				$sql = " INSERT INTO `{$body}`
						( `$join_key` ";
				foreach( $it["body"][0] as $key=>$item ){
					$sql .= ", `{$key}` ";
				}	
				$sql .= " ) VALUES ";


				$i 	 = 0;
				$exe = array();
				foreach( $it["body"] as $key=>$item ){

					$temp = array();
					$sql .= ($i==0)?"\n":",\n";
					$sql .= " ( @last_id ";
					
					foreach( $item as $k=>$it ){
						$sql 						.= ", :{$key}_body_{$i} ";
						$temp[":{$key}_body_{$i}"] 	 = empty($it)?"":$it;
						$i++;
					}
					$sql .= " )";
					$exe  = array_merge($exe,$temp);
				}	
				$sql .= ";";

				$sth  = $this->pdo->prepare($sql);
				$sth->execute($exe);


				$test_script .= $sql."\n";
				foreach( array_reverse($exe) as $key => $item ){
					$test_script = str_replace($key, "'{$item}'", $test_script);
				}
			}


		}




		if( isset($this->test_mode) && $this->test_mode == true ){
			$this->test_mode = false;
			return $test_script;
		}
		else{

			$this->pdo->commit();

			$sth	= $this->pdo->query("SELECT @ids;");
			$ids	= $sth->fetchColumn();

			$return = ["success"=>1,"message"=>"successfully insert data","result"=>$sth->errorInfo(),"ids"=>substr($ids, 2)];
			return $return;
		}


	}
}


?>
