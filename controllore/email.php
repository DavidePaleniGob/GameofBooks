<?php

$num = rand(1000, 65535);
$code = dechex($num);
$to = "";
// subject
$subject = 'Conferma Email';

// message
$message ="
        <HTML>
	<HEAD>
		<title> Conferma mail </title>
		<style>
			body{
				font-style:italic;
			}
		</style>
	</HEAD>
	<BODY>
		<div align='center'>
			<img src='http://gameofbooksonline.altervista.com/controllore/logo_b.png' alt='testo' style='width:150px; height:150px ; margin-top:5; margin-left:5' />
		</div>
		<div id='text' align='center'>
			<h3>Ciao $username,<h3>
			<h2>Conferma la tua iscrizione: <h2><br>
			<h3>L'iscrizione non sara' attiva fin quando non verra' confermata.<h3>
			<br>
			<h3>Per confermare la tua iscrizione per favore 
				<a href='http://gameofbooksonline.altervista.com/controller.php?email=$email&code=$code'>
					CLICCA QUI
				</a>
				o nel link qui di seguito.<h3>
			<br>
			<h3 href='http://gameofbooksonline.altervista.com/controller.php?email=$email&codice=$code'>http://gameofbooksonline.altervista.com/controller.php?email=$email&code=$code<h3>
			<br>
			<h3>Grazie!<h3>
		</div>	
	</BODY>
</HTML>";

// To send HTML mail, the Content-type header must be set
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'From: GameOfBooksOnline <no-reply@gameofbooksonline.altervista.com>' . "\r\n";

