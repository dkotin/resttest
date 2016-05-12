<html>
<body class="small">
<style type="text/css">
	body {
		font-size: small; 
		font-family: Consolas,Menlo,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New,monospace,sans-serif;
	}
	input {font-size: 7pt; margin: 5px; }
	textarea {font-size: 7pt; margin: 2px; }
	select {font-size: 7pt; margin: 2px; }
	.small{font-size: 7pt;}
	.red{color: red;}
</style>
<?php
	$methods = array('GET', 'POST', 'PUT', 'DELETE');
?>
This is a simple draft utility for rest API testing <br>
<form method = POST>
	<input type="text" size=100 name="url" id="url_input" value="<?=htmlspecialchars(@$_POST['url'])?>"  placeholder="url"> 
	<br>
 	<textarea name="headers" id="headers_input" cols = 100 rows = 3  placeholder="headers"><?=htmlspecialchars(@$_POST['headers'])?></textarea>
 	<br>
 	<textarea name="data" id="data_input" cols = 100 rows = 10  placeholder="json-encoded data"><?=htmlspecialchars(@$_POST['data'])?></textarea>
 	<br>
 	<select name="method">
 		<?php foreach($methods as $method):?>
 		<option value="<?=$method?>" id="method_input" <?php if($method==@$_POST['method']) echo('selected');?> ><?=$method?></option>
 		<?php endforeach;?>
 	</select>
 	<br>
 	<input type="checkbox" name="curldebug" id="curldebug_input"> CURL debug output 
 	<br>
 	File: <input type="text" size=100 name="file" id="file_input" value="<?=htmlspecialchars(@$_POST['file'])?>"  placeholder="Absolute path to file"> 
	<br>
 	<input type="submit">
 	<br>
</form>
	<?php if(isset($_POST['headers']) || isset($_POST['data'])){
		$headers = trim($_POST['headers'], "\r\n");
		$headers = escapeshellarg($headers);
		$data = $_POST['data'];
		$method = escapeShellarg($_POST['method']);
		$url = escapeshellarg(trim($_POST['url']));
		$file = trim($_POST['file']);
		$command = "curl  -v --header $headers --request $method $url ";
		if(!(trim($file))){
			$data = escapeshellarg($data);
		    $command .= " -d $data ";
		}else{
			$exploded=explode('&', $data);
			foreach ($exploded as $item){
				$command .= " -F $item ";
			}
			$command .= " -F \"file=@$file\" ";
			if ($method != "'POST'"){
				echo("<br><span style='color:red'>YOU ARE TRYING TO SEND A FILE WITH $method</span><br>");
			}
		}
		if(isset($_POST['curldebug'])){
			$command .= " 2>&1 ";
		}
		exec($command, $output);
		echo("<div style='font-size: 7pt; border: 1px dashed blue;'>$command</div>");
		$jOutput = implode("\n", $output);
		echo('<div style="border: 1px dashed gray;">');
			if(
				json_decode($jOutput)
			){
				echo("Output is json-encoded: <br>");
			 	var_dump(json_decode($jOutput));
			 } else {
				echo("<br><div class='small red'>Output can't be  json-decoded. Providing it as is: </div>");
			 	echo($jOutput);
			 }
		echo('</div>');
	}?>

	<script type="text/javascript">
		window.onload = function(){
    		setTimeout(function(){
    			<?php 
    				$iterations = array('url', 'headers', 'data', 'method', 'curldebug', 'file');
    				foreach($iterations as $item){
    					if(!isset($_POST[$item])){
    						?>
    						
    							var stored = window.localStorage.getItem('<?=$item?>_input');
   							    switch (document.getElementById('<?=$item?>_input').type == 'checkbox'){
   							        case 'checkbox':
     							            document.getElementById('<?=$item?>_input').checked = true;
   							        		console.log('<?=$item?>');
   							        	break;
   							        default:
		                                document.getElementById('<?=$item?>_input').value = stored;
   							    }
    						<?php
    					}
    				}
    			
    				foreach($iterations as $item){
    						?>
                                var current = document.getElementById('<?=$item?>_input').value;
    							window.localStorage.setItem('<?=$item?>_input', current);
    						<?php
    				}
    			?>
    		},500);	
		}
	</script>

</body>
</html>                                    	
