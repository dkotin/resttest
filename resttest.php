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
 	<input type="submit">
 	<br>
</form>
	<?php if(isset($_POST['headers']) || isset($_POST['data'])){
		$headers = trim($_POST['headers'], "\r\n");
		$headers = escapeshellarg($headers);
		$data = escapeshellarg($_POST['data']);
		$method = escapeShellarg($_POST['method']);
		$url = escapeshellarg($_POST['url']);
		$command = "curl --header $headers --request $method $url --data $data";
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
    				$iterations = array('url', 'headers', 'data', 'method');
    				foreach($iterations as $item){
    					if(!isset($_POST[$item])){
    						?>
    							var stored = window.localStorage.getItem('<?=$item?>_input');
                                document.getElementById('<?=$item?>_input').value = stored;
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
