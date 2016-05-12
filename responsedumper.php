<?php
echo("<br><hr><br>");
echo(file_get_contents('php://input'));
echo("<br><hr><br>");
echo("POST: <br>");
var_dump($_POST);
echo("<br><hr><br>");
echo("GET: <br>");
var_dump($_GET);
echo("<br><hr><br>");
echo("FILES: <br>");
var_dump($_FILES);
echo("<br><hr><br>");
