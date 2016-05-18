<?php
	$dbhost='localhost';
	$dbuser='gameofbooksonline';
	$dbpass='gob5bi2016';
	$dbname='my_gameofbooksonline';
	$conn=mysql_connect($dbhost,$dbuser,$dbpass) or die ('connessione al server impossibile');
	mysql_select_db($dbname) or die ('impossibile trovare database');
?>