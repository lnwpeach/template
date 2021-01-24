<?php 
	session_start();
	$page 		= "gen_input";
	$sub_page 	= "gen_input input";
	$lang		= "lang.csv";
	$script		= "";
	include("component/header.php");
?>

<style>
	input.date{text-align:center;}
	table.mytable{ width: 95%; }
	table.mytable tr{ height: 40px; }
	table.mytable td{ padding: 5px; }
</style>

<div class="main">
    <h2>Gen Input</h2>

	<div class="row">
		<div class="col-md-6">
			<h3>Input</h3>
			
			<!-- Right -->
			<button class='btn btn-sm btn-primary' onclick='generate()' style='float:right;margin-top:-25px;'>Generate</button>
			<button class='btn btn-sm btn-light' onclick='setting_box()' style='float:right;margin-top:-25px;margin-right:15px;'>Setting</button>

			<hr style="border:1px solid #ddd;">
			
			<textarea name="input" style="width:100%;height:500px;"></textarea>

			<h3 class="mt-3">Javascript</h3>
			<hr style="border:1px solid #ddd;">
			<textarea name="js" style="width:100%;height:500px;"></textarea>
		</div>

		<div class="col-md-6">
			
		
			<h3>HTML</h3>
			<hr style="border:1px solid #ddd;">

			<textarea name="html" style="width:100%;height:1000px;"></textarea>
		</div>
	</div>
	
	<br><br>
</div>


<!-- Modal -->
<div id='setting_html' style='display:none;'>
	<div name='setting_content'>
		<table class='mytable'>
			<tr>
				<td colspan=2>
					<b>HTML Template</b>
					<textarea name="html_template" style='width:100%;height:200px;'></textarea>
				</td>
			</tr>
			<tr>
				<td style='width: 200px;'><b>JS Save Variable</b></td>
				<td><input type='text' name='js_save_variable' style='width:100%;'></td>
			</tr>
			<tr>
				<td><b>JS Retrieve Variable</b></td>
				<td><input type='text' name='js_retrieve_variable' style='width:100%;'></td>
			</tr>

		</table>
	</div>
</div>
<?php
	include("component/footer.php");
?>
<script>
function generate() {
	var js_save_variable = window.setting.js_save_variable;
	var js_retrieve_variable = window.setting.js_retrieve_variable;
	var input = $('[name=input]').val();
	var html = "";
	var js_save = `// save_function\nvar ${js_save_variable} = {};\n`;
	var js_retrieve = `// retrieve\nvar ${js_retrieve_variable} = res.${js_retrieve_variable};\n`;
	var js_save_space_arr = [];

	var input_arr = input.split('\n');
	$.each(input_arr, function(k, row) {
		var temp = window.setting.html_template;
		var col = row.split(';');

		temp = temp.replace(/\*\*0/g, letter(col[0]));
		temp = temp.replace(/\*0/g, col[0]);
		temp = temp.replace(/\*1/g, col[1]);
		temp = temp.replace(/\*2/g, col[2]);
		html += temp;

		// JS Save
		js_save_index = `${js_save_variable}['${col[0]}']`;
		js_save_space_arr.push(js_save_index.length);
		js_save 	+= `${js_save_index}--space${k}--= $('[name=${col[0]}]').val();\n`;

		js_retrieve += `$('[name=${col[0]}]').val(${js_retrieve_variable}.${col[0]});\n`;
	});

	var tab = 4;
	var max_space = Math.max(...js_save_space_arr);
	if(max_space % tab == 0) space = max_space + tab;
	else space = max_space + (tab - (max_space % tab));
	
	$.each(js_save_space_arr, function(k, v) {
		js_save = js_save.replace(`--space${k}--`, new Array((space - v) + 1).join(' '));
	});

	$('[name=html]').val(html);
	$('[name=js]').val(js_save+'\n'+js_retrieve);
}

function letter(str) {
	str = str.split('_');
	$.each(str, function(k, v) {
		str[k] = v.charAt(0).toUpperCase() + v.slice(1);
	});
	return str.join(' ');
}

function setting_box() {
	var content = $('#setting_html').html();
	content = $(content);

	$('[name=html_template]', content).val(window.setting['html_template']);
	$('[name=js_save_variable]', content).val(window.setting['js_save_variable']);
	$('[name=js_retrieve_variable]', content).val(window.setting['js_retrieve_variable']);

	bootbox.dialog({
        message: content,
        title: "System Response",
		size: "large",
        buttons: {
            danger: {
                label: "<i class='fa fa-close'></i> Close [ESC]",
                className: "btn-light"
            },
            success: {
                label: "<i class='fa fa-save'></i> Save",
                className: "btn-success",
                callback: function(){
                    save_setting();
                }
            }
        },
        onEscape: function(){}
    });
}

function save_setting() {
	var content = $(".bootbox [name=setting_content]");
	var setting_html = $("#setting_html [name=setting_content]");

	window.setting['html_template'] 		= $('[name=html_template]', content).val();
	window.setting['js_save_variable'] 		= $('[name=js_save_variable]', content).val();
	window.setting['js_retrieve_variable'] 	= $('[name=js_retrieve_variable]', content).val();
}

$(document).ready(function() {
	var html = `<tr>\n`+
				`	<td><b>**0</b></td>\n`+
				`	<td><input type='text' name='*0' style='width:100%;'></td>\n`+
				`</tr>\n`;
	window.setting = {
		'html_template' : html,
		'js_save_variable' : 'head',
		'js_retrieve_variable' : 'head',
	};

	var input = `product_id\n`+
				`product_name\n`+
				`sale_name\n`+
				`address\n`+
				`remark`;
	$('[name=input]').val(input);
});
</script>
