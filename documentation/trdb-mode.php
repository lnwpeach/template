<hr>
<br>

<h1>Extra Mode</h1>

<b id="m1">Mode1: debug</b>
<p><b>Syntax:</b> $return = $pdo2->debug()->function();</p>
<ul>
	<li>$pdo = established connection</li>
	<li>function = any function in trdb</li>
	<li>$return = error message from pdo->errorInfo</li>
</ul>
<p><b>Example:</b></p>
<pre style="background:#efefef;padding:30px;">
$pdo2->debug()->update("invoice",
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

<b id="m2">Mode2: test</b>
<p><b>Syntax:</b> $return = $pdo2->test()->function();</p>
<ul>
	<li>$pdo = established connection</li>
	<li>function = any function in trdb</li>
	<li>$return = return sql to be used in testing with adminer</li>
</ul>
<p><b>Example:</b></p>
<pre style="background:#efefef;padding:30px;">
$pdo2->test()->update("invoice",
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

<b id="m3">Mode3: script</b>
<p><b>Syntax:</b> $return = $pdo2->script()->function();</p>
<ul>
	<li>$pdo = established connection</li>
	<li>function = any function in trdb</li>
	<li>$return = return $sql script &amp; $execute array</li>
</ul>
<p><b>Example:</b></p>
<pre style="background:#efefef;padding:30px;">
$pdo2->script()->update("invoice",
	array(
		"name"		=>$data["name"],
		"surname"	=>$data["surname"]
	), 
	"company_id = '{$_SESSION["company_id"]}' and invoice_id = '{$id}'"
);
</pre>
<p><a href="#head">Back to Top</a></p>
