<b id="head">TRDB</b>

<span  style="float:right;">
<a href="../documentation">Table of Content</a> | <a href="../">Back to Template</a>
</span>
<hr>
<br>
<table style="width:100%;">
<tr>
	<td style="vertical-align:top;width:50%;">
		<b>Function</b>
		<ol>
			<li><a href="#a1">contruct connection</a></li>
			<li><a href="#a2">insert</a></li>
			<li><a href="#a3">update</a></li>
			<li><a href="#a4">search</a></li>
			<li><a href="#a5">array2tempTable</a></li>
			<li><a href="#a6">insertLog</a></li>
			<li><a href="#a7">insertBatch</a></li>
		</ol>
	</td>
	<td style="vertical-align:top;">
		<b>Mode</b>
		<ol>
			<li><a href="#m1">debug</a></li>
			<li><a href="#m2">test</a></li>
			<li><a href="#m3">script</a></li>
		</ol>
	</td>
</tr>
</table>


<hr>

<h1>Function</h1>
<b id="a1">1. contruct connection</b>
<p>After including trdb.php in your code the framework is going to create 2 default connections, they are </p>
<ul>
	<li>$pdo1 = connect with controller database (company_list, user_list, etc)</li>
	<li>$pdo2 = connect with actual transactions</li>
</ul>



<hr>
<br>
<b id="a2">2. insert</b>
<p><b>Syntax:</b> $return = $pdo2->insert($table,$detail = array(),$option=array());</p>
<ul>
	<li>$pdo = established connection</li>
	<li>$table = table name</li>
	<li>$detail = actual transactions</li>
	<li>$option = [update = on duplicate key update, ignore = on duplicate key ignore, update_not_blank = on duplicate key update except blank]</li>
	<li>$return = last insert id</li>
</ul>
<p><b>Example:</b></p>
<pre style="background:#efefef;padding:30px;">
$pdo2->insert("invoice",array(
	["company_id" => $_SESSION["company_id"], "name" => "Hello", "surname" => "World"],
	["company_id" => $_SESSION["company_id"], "name" => "Hello", "surname" => "World"],
	["company_id" => $_SESSION["company_id"], "name" => "Hello", "surname" => "World"],
	["company_id" => $_SESSION["company_id"], "name" => "Hello", "surname" => "World"],
),array(
	"duplicate" => "update"
));
</pre>
<p><a href="#head">Back to Top</a></p>



<hr>
<br>
<b id="a3">3. update</b>
<p><b>Syntax:</b> $return = $pdo2->update($table, $detail = array(), $where);</p>
<ul>
	<li>$pdo = established connection</li>
	<li>$table = table name</li>
	<li>$detail = actual transactions</li>
	<li>$where = condition</li>
	<li>$return = result of update data</li>
</ul>
<p><b>Example:</b></p>
<pre style="background:#efefef;padding:30px;">
$pdo2->update("invoice",
	array(
		"name"		=>$data["name"],
		"surname"	=>$data["surname"]
	), 
	"company_id = '{$_SESSION["company_id"]}' and invoice_id = '{$id}'"
);
</pre>
<p><a href="#head">Back to Top</a></p>



<hr>
<br>
<b id="a4">4. search</b>
<p><b>Syntax:</b> $return = $pdo2->search($table, $condition = [], $option = []);</p>
<ul>
	<li>$pdo = established connection</li>
	<li>$table = table name / can be sql script 
		<br>
		e.g. [select * from invoice a left join invoice_item b on a.invoice_id = b.invoice_id]
	</li>
	<li>$condition = condition for search</li>
	<li>$option = start / limit / group by / order by </li>
	<li>$return = ["success"=>1,"message"=>"Successfully fetch the results","result"=>$result]</li>
</ul>
<p><b>Example:</b></p>
<pre style="background:#efefef;padding:30px;">
$pdo2->search("importer",array(

	"company_id" 	 => $_SESSION["company_id"],
	
	"type" 		 => $data["type"],
	
	"product_id" 	 => ["type" => "default", "condition"	=> "!=", "value"	=> ""],
				
	"department" 	 => ["type" => "in", "data" => $data["department_list"], "skip" => empty($data["department_list"])],
				
	"date"		 => ["type" => "between", "from" => $data["from"], "to" => $data["to"], "skip" => empty($data["filter"])],
				
	"keyword"	 => ["type" => "search_concat", "column" => ["description","date","note"], "keyword" => $data["keyword"],"skip" => empty($data["filter"])],
	
	"custom1" 	 => ["type" => "custom", "value" => "date != note","skip" => empty($data["filter"])],
),array(
	"start"		 => $data["start"]*50,
	"limit"		 => 50, // if use "limit" => "no", trdb will fetch all results
	"order_by"	 => "date desc, import_id desc"
	"group_by"	 => "import_id",
	"having"	 => "import_id > 0"
));
</pre>
<p><a href="#head">Back to Top</a></p>



<hr>
<br>
<b id="a5">5. array2tempTable</b>
<p><b>Syntax:</b> $return = $pdo2->array2tempTable($array,$prefix="",$company_id);</p>
<ul>
	<li>$pdo = established connection</li>
	<li>$array = array that want to be converted to temporary table</li>
	<li>$prefix = prefix of temporary table e.g. test_temp1234 [test is prefix]</li>
	<li>$company_id = company_id session</li>
</ul>
<p><b>Example:</b></p>
<pre style="background:#efefef;padding:30px;">
$pdo2->array2tempTable(
array(
	["name"=>"pon1","surname"=>"sath","address"=>"1235 ramkhamhaeng"],
	["name"=>"pon2","surname"=>"sath","address"=>"1235 ramkhamhaeng"],
	["name"=>"pon3","surname"=>"sath","address"=>"1235 ramkhamhaeng"],
),
"test", $_SESSION["company_id"]);
</pre>
<p><a href="#head">Back to Top</a></p>



<hr>
<br>
<b id="a6">6. insertLog</b>
<p><b>Syntax:</b> $return = $pdo2->insertLog($detail,$company_id=0);</p>
<ul>
	<li>$pdo = established connection</li>
	<li>$detail = (string) data that want to be kept in log</li>
	<li>$company_id = company_id session</li>
	<li>$return = fasle [fail to insert into log] / true [successfully insert into log]</li>
</ul>
<p><b>Example:</b></p>
<pre style="background:#efefef;padding:30px;">
$pdo2->insertLog(
json_encode(array(
	["name"=>"pon1","surname"=>"sath","address"=>"1235 ramkhamhaeng"],
	["name"=>"pon2","surname"=>"sath","address"=>"1235 ramkhamhaeng"],
	["name"=>"pon3","surname"=>"sath","address"=>"1235 ramkhamhaeng"],
)),
$_SESSION["company_id"]);
</pre>
<p><a href="#head">Back to Top</a></p>



<hr>
<br>
<b id="a7">7. insertBatch</b>
<p><b>Syntax:</b> $return = $pdo2->insertBatch($table, $detail, $option);</p>
<ul>
	<li>$pdo = established connection</li>
	<li>$table = (array) ["head" => "Table Name1", "body" => "Table Name2"]</li>
	<li>
		$detail = array of head + body
<pre>
$detail = [
	array(
		"head"=>["company_id"=>"2","name"=>"PON1","invoice_number"=>"20010001"],
		"body"=>array(
				["company_id"=>"2","product_id"=>"YOLO1"],
				["company_id"=>"2","product_id"=>"YOLO2"],
				["company_id"=>"2","product_id"=>"YOLO3"],
			),
	),
	array(
		"head"=>["company_id"=>"2","name"=>"PON2","invoice_number"=>"20010002"],
		"body"=>array(
				["company_id"=>"2","product_id"=>"YOLO4"],
				["company_id"=>"2","product_id"=>"YOLO5"],
				["company_id"=>"2","product_id"=>"YOLO6"],
			),
	),
	];
</pre>

	</li>
	<li>$option = (array) ["join_key" => "key to join 2 table e.g. invoice_id"]</li>
</ul>
<p><b>Example:</b></p>
<pre style="background:#efefef;padding:30px;">
$pdo2->insertBatch(
	["head" => "Table Name1", "body" => "Table Name2"],
	$detail,
	["join_key"=>"invoice_id"]
);
</pre>
<p><a href="#head">Back to Top</a></p>


<?php
	include("trdb-mode.php");
?>
