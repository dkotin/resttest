<html>
<body class="small">
<!-- BEAUTIFY STARTS -->
<script>
	(function() {

		function createShiftArr(step) {

			var space = '    ';

			if ( isNaN(parseInt(step)) ) {  // argument is string
				space = step;
			} else { // argument is integer
			    space = '';
				for(var i=1; i<step; i++) {
					space += ' ';
				}
			}

			var shift = ['\n']; // array of shifts
			for(ix=0;ix<100;ix++){
				shift.push(shift[ix]+space);
			}
			return shift;
		}

		function vkbeautify(){
			this.step = '    '; // 4 spaces
			this.shift = createShiftArr(this.step);
		};

		vkbeautify.prototype.xml = function(text,step) {

			var ar = text.replace(/>\s{0,}</g,"><")
					.replace(/</g,"~::~<")
					.replace(/\s*xmlns\:/g,"~::~xmlns:")
					.replace(/\s*xmlns\=/g,"~::~xmlns=")
					.split('~::~'),
				len = ar.length,
				inComment = false,
				deep = 0,
				str = '',
				ix = 0,
				shift = step ? createShiftArr(step) : this.shift;

			for(ix=0;ix<len;ix++) {
				// start comment or <![CDATA[...]]> or <!DOCTYPE //
				if(ar[ix].search(/<!/) > -1) {
					str += shift[deep]+ar[ix];
					inComment = true;
					// end comment  or <![CDATA[...]]> //
					if(ar[ix].search(/-->/) > -1 || ar[ix].search(/\]>/) > -1 || ar[ix].search(/!DOCTYPE/) > -1 ) {
						inComment = false;
					}
				} else
				// end comment  or <![CDATA[...]]> //
				if(ar[ix].search(/-->/) > -1 || ar[ix].search(/\]>/) > -1) {
					str += ar[ix];
					inComment = false;
				} else
				// <elm></elm> //
				if( /^<\w/.exec(ar[ix-1]) && /^<\/\w/.exec(ar[ix]) &&
					/^<[\w:\-\.\,]+/.exec(ar[ix-1]) == /^<\/[\w:\-\.\,]+/.exec(ar[ix])[0].replace('/','')) {
					str += ar[ix];
					if(!inComment) deep--;
				} else
				// <elm> //
				if(ar[ix].search(/<\w/) > -1 && ar[ix].search(/<\//) == -1 && ar[ix].search(/\/>/) == -1 ) {
					str = !inComment ? str += shift[deep++]+ar[ix] : str += ar[ix];
				} else
				// <elm>...</elm> //
				if(ar[ix].search(/<\w/) > -1 && ar[ix].search(/<\//) > -1) {
					str = !inComment ? str += shift[deep]+ar[ix] : str += ar[ix];
				} else
				// </elm> //
				if(ar[ix].search(/<\//) > -1) {
					str = !inComment ? str += shift[--deep]+ar[ix] : str += ar[ix];
				} else
				// <elm/> //
				if(ar[ix].search(/\/>/) > -1 ) {
					str = !inComment ? str += shift[deep]+ar[ix] : str += ar[ix];
				} else
				// <? xml ... ?> //
				if(ar[ix].search(/<\?/) > -1) {
					str += shift[deep]+ar[ix];
				} else
				// xmlns //
				if( ar[ix].search(/xmlns\:/) > -1  || ar[ix].search(/xmlns\=/) > -1) {
					str += shift[deep]+ar[ix];
				}

				else {
					str += ar[ix];
				}
			}

			return  (str[0] == '\n') ? str.slice(1) : str;
		}

		vkbeautify.prototype.json = function(text,step) {

			var step = step ? step : this.step;

			if (typeof JSON === 'undefined' ) return text;

			if ( typeof text === "string" ) return JSON.stringify(JSON.parse(text), null, step);
			if ( typeof text === "object" ) return JSON.stringify(text, null, step);

			return text; // text is not string nor object
		}

		vkbeautify.prototype.css = function(text, step) {

			var ar = text.replace(/\s{1,}/g,' ')
					.replace(/\{/g,"{~::~")
					.replace(/\}/g,"~::~}~::~")
					.replace(/\;/g,";~::~")
					.replace(/\/\*/g,"~::~/*")
					.replace(/\*\//g,"*/~::~")
					.replace(/~::~\s{0,}~::~/g,"~::~")
					.split('~::~'),
				len = ar.length,
				deep = 0,
				str = '',
				ix = 0,
				shift = step ? createShiftArr(step) : this.shift;

			for(ix=0;ix<len;ix++) {

				if( /\{/.exec(ar[ix]))  {
					str += shift[deep++]+ar[ix];
				} else
				if( /\}/.exec(ar[ix]))  {
					str += shift[--deep]+ar[ix];
				} else
				if( /\*\\/.exec(ar[ix]))  {
					str += shift[deep]+ar[ix];
				}
				else {
					str += shift[deep]+ar[ix];
				}
			}
			return str.replace(/^\n{1,}/,'');
		}

//----------------------------------------------------------------------------

		function isSubquery(str, parenthesisLevel) {
			return  parenthesisLevel - (str.replace(/\(/g,'').length - str.replace(/\)/g,'').length )
		}

		function split_sql(str, tab) {

			return str.replace(/\s{1,}/g," ")

				.replace(/ AND /ig,"~::~"+tab+tab+"AND ")
				.replace(/ BETWEEN /ig,"~::~"+tab+"BETWEEN ")
				.replace(/ CASE /ig,"~::~"+tab+"CASE ")
				.replace(/ ELSE /ig,"~::~"+tab+"ELSE ")
				.replace(/ END /ig,"~::~"+tab+"END ")
				.replace(/ FROM /ig,"~::~FROM ")
				.replace(/ GROUP\s{1,}BY/ig,"~::~GROUP BY ")
				.replace(/ HAVING /ig,"~::~HAVING ")
				//.replace(/ SET /ig," SET~::~")
				.replace(/ IN /ig," IN ")

				.replace(/ JOIN /ig,"~::~JOIN ")
				.replace(/ CROSS~::~{1,}JOIN /ig,"~::~CROSS JOIN ")
				.replace(/ INNER~::~{1,}JOIN /ig,"~::~INNER JOIN ")
				.replace(/ LEFT~::~{1,}JOIN /ig,"~::~LEFT JOIN ")
				.replace(/ RIGHT~::~{1,}JOIN /ig,"~::~RIGHT JOIN ")

				.replace(/ ON /ig,"~::~"+tab+"ON ")
				.replace(/ OR /ig,"~::~"+tab+tab+"OR ")
				.replace(/ ORDER\s{1,}BY/ig,"~::~ORDER BY ")
				.replace(/ OVER /ig,"~::~"+tab+"OVER ")

				.replace(/\(\s{0,}SELECT /ig,"~::~(SELECT ")
				.replace(/\)\s{0,}SELECT /ig,")~::~SELECT ")

				.replace(/ THEN /ig," THEN~::~"+tab+"")
				.replace(/ UNION /ig,"~::~UNION~::~")
				.replace(/ USING /ig,"~::~USING ")
				.replace(/ WHEN /ig,"~::~"+tab+"WHEN ")
				.replace(/ WHERE /ig,"~::~WHERE ")
				.replace(/ WITH /ig,"~::~WITH ")

				//.replace(/\,\s{0,}\(/ig,",~::~( ")
				//.replace(/\,/ig,",~::~"+tab+tab+"")

				.replace(/ ALL /ig," ALL ")
				.replace(/ AS /ig," AS ")
				.replace(/ ASC /ig," ASC ")
				.replace(/ DESC /ig," DESC ")
				.replace(/ DISTINCT /ig," DISTINCT ")
				.replace(/ EXISTS /ig," EXISTS ")
				.replace(/ NOT /ig," NOT ")
				.replace(/ NULL /ig," NULL ")
				.replace(/ LIKE /ig," LIKE ")
				.replace(/\s{0,}SELECT /ig,"SELECT ")
				.replace(/\s{0,}UPDATE /ig,"UPDATE ")
				.replace(/ SET /ig," SET ")

				.replace(/~::~{1,}/g,"~::~")
				.split('~::~');
		}

		vkbeautify.prototype.sql = function(text,step) {

			var ar_by_quote = text.replace(/\s{1,}/g," ")
					.replace(/\'/ig,"~::~\'")
					.split('~::~'),
				len = ar_by_quote.length,
				ar = [],
				deep = 0,
				tab = this.step,//+this.step,
				inComment = true,
				inQuote = false,
				parenthesisLevel = 0,
				str = '',
				ix = 0,
				shift = step ? createShiftArr(step) : this.shift;;

			for(ix=0;ix<len;ix++) {
				if(ix%2) {
					ar = ar.concat(ar_by_quote[ix]);
				} else {
					ar = ar.concat(split_sql(ar_by_quote[ix], tab) );
				}
			}

			len = ar.length;
			for(ix=0;ix<len;ix++) {

				parenthesisLevel = isSubquery(ar[ix], parenthesisLevel);

				if( /\s{0,}\s{0,}SELECT\s{0,}/.exec(ar[ix]))  {
					ar[ix] = ar[ix].replace(/\,/g,",\n"+tab+tab+"")
				}

				if( /\s{0,}\s{0,}SET\s{0,}/.exec(ar[ix]))  {
					ar[ix] = ar[ix].replace(/\,/g,",\n"+tab+tab+"")
				}

				if( /\s{0,}\(\s{0,}SELECT\s{0,}/.exec(ar[ix]))  {
					deep++;
					str += shift[deep]+ar[ix];
				} else
				if( /\'/.exec(ar[ix]) )  {
					if(parenthesisLevel<1 && deep) {
						deep--;
					}
					str += ar[ix];
				}
				else  {
					str += shift[deep]+ar[ix];
					if(parenthesisLevel<1 && deep) {
						deep--;
					}
				}
				var junk = 0;
			}

			str = str.replace(/^\n{1,}/,'').replace(/\n{1,}/g,"\n");
			return str;
		}


		vkbeautify.prototype.xmlmin = function(text, preserveComments) {

			var str = preserveComments ? text
				: text.replace(/\<![ \r\n\t]*(--([^\-]|[\r\n]|-[^\-])*--[ \r\n\t]*)\>/g,"")
					.replace(/[ \r\n\t]{1,}xmlns/g, ' xmlns');
			return  str.replace(/>\s{0,}</g,"><");
		}

		vkbeautify.prototype.jsonmin = function(text) {

			if (typeof JSON === 'undefined' ) return text;

			return JSON.stringify(JSON.parse(text), null, 0);

		}

		vkbeautify.prototype.cssmin = function(text, preserveComments) {

			var str = preserveComments ? text
				: text.replace(/\/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+\//g,"") ;

			return str.replace(/\s{1,}/g,' ')
				.replace(/\{\s{1,}/g,"{")
				.replace(/\}\s{1,}/g,"}")
				.replace(/\;\s{1,}/g,";")
				.replace(/\/\*\s{1,}/g,"/*")
				.replace(/\*\/\s{1,}/g,"*/");
		}

		vkbeautify.prototype.sqlmin = function(text) {
			return text.replace(/\s{1,}/g," ").replace(/\s{1,}\(/,"(").replace(/\s{1,}\)/,")");
		}

		window.vkbeautify = new vkbeautify();

	})();
</script>
<!-- BEAUTIFY ENDS -->

<script>
	function syntaxHighlight(json) {
		if (typeof json != 'string') {
			json = JSON.stringify(json, undefined, 2);
		}
		json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
			var cls = 'number';
			if (/^"/.test(match)) {
				if (/:$/.test(match)) {
					cls = 'key';
				} else {
					cls = 'string';
				}
			} else if (/true|false/.test(match)) {
				cls = 'boolean';
			} else if (/null/.test(match)) {
				cls = 'null';
			}
			return '<span class="' + cls + '">' + match + '</span>';
		});
	}
</script>

<style type="text/css">
	body {
		background: #EFEFEF;
		color: #000000;
		font-size: 12pt;
		font-family: Consolas, Menlo, Monaco, Lucida Console, Liberation Mono, DejaVu Sans Mono, Bitstream Vera Sans Mono, Courier New, monospace, sans-serif;
	}

	input {
		font-size: 10pt;
		margin: 5px;
	}

	textarea {
		font-size: 10pt;
		margin: 2px;
	}

	select {
		font-size: 10pt;
		margin: 2px;
	}

	.small {
		font-size: 10pt;
	}

	.red {
		color: red;
	}

	pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }
	.string { color: green; }
	.number { color: #006191; }
	.boolean { color: #776e38; }
	.null { color: magenta; }
	.key { color: #524d53; }
</style>
<?php
$methods = array('GET', 'POST', 'PUT', 'DELETE');
?>
This is a simple draft utility for rest API testing <br>
<form method=POST>
	<input type="text" size=100 name="url" id="url_input" value="<?= htmlspecialchars(@$_POST['url']) ?>"
		   placeholder="url">
	<br>
	<textarea name="headers" id="headers_input" cols=100 rows=3
			  placeholder="headers"><?= htmlspecialchars(@$_POST['headers']) ?></textarea>
	<br>
	<textarea name="data" id="data_input" cols=100 rows=10
			  placeholder="json-encoded data"><?= htmlspecialchars(@$_POST['data']) ?></textarea>
	<br>
	<select name="method">
        <?php foreach ($methods as $method): ?>
			<option value="<?= $method ?>" id="method_input" <?php if ($method == @$_POST['method']) {
                echo('selected');
            } ?> ><?= $method ?></option>
        <?php endforeach; ?>
	</select>
	<input type="checkbox" name="curldebug" id="curldebug_input"> CURL debug output
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="curlresponseheaders" id="curlresponseheaders_input"> Return HTTP response headers
	<br>
	File:
	<input type="text" size=20 name="filename" id="filename_input" value="<?= htmlspecialchars(@$_POST['filename']) ?>"
		   placeholder="File variable name">
	<input type="text" size=100 name="file" id="file_input" value="<?= htmlspecialchars(@$_POST['file']) ?>"
		   placeholder="Absolute path to file">
	<br>
	<input type="submit">
	<br>
</form>
<?php if (isset($_POST['headers']) || isset($_POST['data'])) {
    $headers = trim($_POST['headers'], "\r\n");
    $headers = escapeshellarg($headers);
    $data = $_POST['data'];
    $method = escapeShellarg($_POST['method']);
    $url = escapeshellarg(trim($_POST['url']));
    $file = trim($_POST['file']);
    $filename = trim($_POST['filename']);
    $command = "curl  -v -g --header $headers --request $method $url ";
    if (!(trim($file))) {
        $data = escapeshellarg($data);
        /*
        $ch = curl_init($_POST['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_POST['method']);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        $response = curl_exec($ch);
        */
        $command .= " -d $data";
    } else {
        $exploded = explode('&', $data);
        foreach ($exploded as $item) {
            // $command .= " -F $item ";
        }
        $command .= " -F \"$filename=@$file\" ";
        if ($method != "'POST'") {
            echo("<br><span style='color:red'>YOU ARE TRYING TO SEND A FILE WITH $method</span><br>");
        }
    }

    if (isset($_POST['curlresponseheaders'])) {
        $command .= " -i ";
    }

    if (isset($_POST['curldebug'])) {
        $command .= " 2>&1 ";
    }

    exec($command, $output);
    echo("<div style='font-size: 10pt; border: 1px dashed blue;'>$command</div>");
    $jOutput = implode("\n", $output);
    echo('<div style="border: 1px dashed gray;">');
    if (json_decode($jOutput)) {
        echo("Output is json-encoded: <br>");
        //var_dump(json_decode($jOutput));
        echo('<div id="outputArea">' . $jOutput . '</div><pre id="jsonOutputArea"></pre>');
        ?>
			<script>
				var src = document.getElementById('outputArea').innerHTML;
				var result = vkbeautify.json(src);
				result = syntaxHighlight(result);
				document.getElementById('jsonOutputArea').innerHTML = result;
				document.getElementById('outputArea').setAttribute("style", "display: none");
			</script>
		<?php
    } else {
        echo("<br><div class='small red'>Output can't be  json-decoded. Providing it as is: </div>");
        echo("<code>" . nl2br($jOutput) . "</code>");
    }
    echo('</div>');
} ?>

<script type="text/javascript">
	window.onload = function () {
		setTimeout(function () {
            <?php
            $iterations = array('url', 'headers', 'data', 'method', 'curldebug', 'file', 'filename');
            foreach($iterations as $item){
            if(!isset($_POST[$item])){
            ?>

			var stored = window.localStorage.getItem('<?=$item?>_input');
			switch (document.getElementById('<?=$item?>_input').type == 'checkbox') {
				case 'checkbox':
					document.getElementById('<?=$item?>_input').checked = true;
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
		}, 500);
	}
</script>

</body>
</html>                                    	
