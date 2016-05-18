<?php

include ("controllore/conf.php");
switch ($_POST["comando"]) {
    case "login":
        $username = strtolower($_POST['parametri']['username']);
        $password = sha1($_POST["parametri"]['password']);
        $query = "SELECT UTENTE.Username FROM UTENTE 
                WHERE UTENTE.Username = '$username' AND UTENTE.Password = '$password'";
        $query1 = "SELECT UTENTE.Username FROM UTENTE 
                WHERE UTENTE.Username = '$username' AND UTENTE.Password = '$password' AND UTENTE.Id_tipo = '2'";
        $result = mysql_query($query);
        if (mysql_num_rows($result) === 1) {
            session_start();
            $_SESSION["username"] = $username;
            if (mysql_num_rows(mysql_query($query1)) === 1) {
                $_SESSION["utente_tipo"] = 2;
            } else {
                $_SESSION["utente_tipo"] = 1;
            }
            echo json_encode(array("errore" => false, "risposta" => array("redirect" => "giocatore")));
        } else {
            echo json_encode(array("errore" => true, "risposta" => null));
        }
        break;
    case "registrazione":
        $username = strtolower($_POST['parametri']['username']);
        $password = sha1($_POST["parametri"]['password']);
        $email = $_POST["parametri"]['email'];
        $queryU = "SELECT UTENTE.Username FROM UTENTE WHERE UTENTE.Username='$username';";
        $resultU = mysql_query($queryU);
        $queryE = "SELECT UTENTE.Email FROM UTENTE WHERE UTENTE.Email='$email';";
        $resultE = mysql_query($queryE);
        if (mysql_num_rows($resultU) === 0) {
            if (mysql_num_rows($resultE) === 0) {
                include ("controllore/email.php");
                $query = "INSERT INTO `UTENTE` (`Username`, `Password`, `Email`, `Check`, `Token`, `Id_tipo`)
                      VALUE ('$username','$password','$email','0','$code','2');";
                mysql_query($query);
                $to = $email;
                mail($to, $subject, $message, $headers);
                echo json_encode(array("errore" => false, "risposta" => NULL));
            } else {
                echo json_encode(array("errore" => true, "risposta" => array("testo" => "Indirizzo email scelto non è disponibile")));
            }
        } else {
            if (mysql_num_rows($resultE) === 0) {
                echo json_encode(array("errore" => true, "risposta" => array("testo" => "Username scelto non è disponibile")));
            } else {
                echo json_encode(array("errore" => true, "risposta" => array("testo" => "Username e indirizzo email scelti non sono disponibili")));
            }
        }
        break;
    case "creare_lg":
        session_start();
        $username = $_SESSION["username"];
        $utente_tipo = $_SESSION["utente_tipo"];
        if ($username !== null && $utente_tipo === 2) { // controlla che se ha effettuato l accesso
            $titolo = $_POST["titolo"];
            $queryCheckLG = "SELECT LIBRO_GAME.Id FROM LIBRO_GAME WHERE LIBRO_GAME.Titolo = '$titolo' AND LIBRO_GAME.Username = '$username';";
            if (mysql_num_rows(mysql_query($queryCheckLG)) > 0) {
                die(json_encode(array("errore" => TRUE, "risposta" => NULL))); // libro game con quel titolo e per quel creatore e stato gia creato
            }
            $visibile = "0";
            if ($_POST["visibile"]) {
                $visibile = "1";
            }
            $checkImageC = FALSE;
            $checkImageM = False;
            $target_dir = "/membri/gameofbooksonline/image/";
            $queryInsertLG = "INSERT INTO `LIBRO_GAME` (`Id`, `Titolo`, `Img_copertina`,`Img_mappa`, `Visibile`, `Username`) "
                        . "VALUE (NULL,'$titolo',NULL,NULL,'$visibile','$username');";
            if (file_exists($_FILES["img_copertina"]["tmp_name"])) { // CONTROLLO IMMAGINE
                $target_file = $target_dir . basename($_FILES["img_copertina"]["name"]);
                $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
                $name_file = $username."&".(time()+1).".".$imageFileType;
                if (($imageFileType !== "jpg" && $imageFileType !== "jpeg") || $_FILES["img_copertina"]["size"] > 5120000) {
                    die(json_encode(array("errore" => TRUE, "risposta" => NULL))); // immagine troppo grande o formato non supportato
                }
                $checkImageC = TRUE;
                $queryInsertLG = "INSERT INTO `LIBRO_GAME` (`Id`, `Titolo`, `Img_copertina`,`Img_mappa`, `Visibile`, `Username`) "
                        . "VALUE (NULL,'$titolo','image/$name_file',NULL,'$visibile','$username');";
            }
            if (file_exists($_FILES["img_mappa"]["tmp_name"])) { // CONTROLLO IMMAGINE
                $target_fileM = $target_dir . basename($_FILES["img_mappa"]["name"]);
                $imageFileTypeM = pathinfo($target_fileM, PATHINFO_EXTENSION);
                $name_fileM = $username."&".time().".".$imageFileTypeM;
                if (($imageFileTypeM !== "jpg" && $imageFileTypeM !== "jpeg") || $_FILES["img_mappa"]["size"] > 5120000) {
                    die(json_encode(array("errore" => TRUE, "risposta" => NULL))); // immagine troppo grande o formato non supportato
                }
                $checkImageM = TRUE;
                if ($checkImageC && $checkImageM){
                    $queryInsertLG = "INSERT INTO `LIBRO_GAME` (`Id`, `Titolo`, `Img_copertina`,`Img_mappa`, `Visibile`, `Username`) "
                        . "VALUE (NULL,'$titolo','image/$name_file','image/$name_fileM','$visibile','$username');";
                }  elseif ($checkImageM) {
                    $queryInsertLG = "INSERT INTO `LIBRO_GAME` (`Id`, `Titolo`, `Img_copertina`,`Img_mappa`, `Visibile`, `Username`) "
                        . "VALUE (NULL,'$titolo',NULL,'image/$name_fileM','$visibile','$username');";
                }   
            }
            if (mysql_query($queryInsertLG)){ // salva il libro game e eventualmente la immagine
                if($checkImageC){
                    move_uploaded_file($_FILES["img_copertina"]["tmp_name"], $target_dir.$name_file);
                }
                if($checkImageM){
                    move_uploaded_file($_FILES["img_mappa"]["tmp_name"], $target_dir.$name_fileM);
                }
                $row = mysql_fetch_assoc(mysql_query($queryCheckLG));
                $_SESSION["id_lg_m"] = $row["Id"];
                die(json_encode(array("errore" => FALSE, "risposta" => NULL)));
            }else{
                die(json_encode(array("errore" => TRUE, "risposta" => NULL))); 
            }
        }else{
            die(json_encode(array("errore" => TRUE, "risposta" => NULL)));// non ha eseguito l'accesso
        }
        break;
    case "lista_lg":
        session_start();
        $username = $_SESSION["username"];
        if($username !== NULL){
            $query = "SELECT LIBRO_GAME.Id AS id_lg, LIBRO_GAME.Titolo AS nome_lg, LIBRO_GAME.Copertina AS immagine_copertina
                FROM LIBRO_GAME 
                WHERE LIBRO_GAME.Visibile = 1
                ORDER BY LIBRO_GAME.Titolo;";
            $result = mysql_query($query);
            if ($result){
                for($i = 0; $array[$i] = mysql_fetch_assoc($result); $i++) ;
                echo json_encode(array("errore" => false, "risposta" => $array));
            }else{
                echo json_encode(array("errore" => true, "risposta" => NULL)); // errore database o esecuzione query
            }
        } else {
            echo json_encode(array("errore" => true, "risposta" => NULL)); // non ha eseguito l'accesso
        }
        break;
    case "inserisci_paragrafo":
        session_start();
        $username = $_SESSION["username"];
        $utente_tipo = $_SESSION["utente_tipo"];
        $id_lg_m = $_SESSION["id_lg_m"];
        $target_dir = "/membri/gameofbooksonline/image/";
        $queryInsertP = "INSERT INTO `PARAGRAFO` (`Id`, `Contenuto`, `Immagine`,`Id_lg`, `Inizio`) "
                        . "VALUE (NULL,'$contenuto',NULL,'$id_lg_m','0');";
        if ($username !== null && $utente_tipo === 2 && $id_lg_m !== null) {
            $contenuto = $_POST["contenuto"];
            if (file_exists($_FILES["immagine"]["tmp_name"])) { // CONTROLLO IMMAGINE
                $target_file = $target_dir . basename($_FILES["immagine"]["name"]);
                $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
                $name_file = $username."&".time().".".$imageFileType;
                if (($imageFileType !== "jpg" && $imageFileType !== "jpeg") || $_FILES["immagine"]["size"] > 5120000) {
                    echo 'errore 1 <br>';
                    die(json_encode(array("errore" => TRUE, "risposta" => NULL))); // immagine troppo grande o formato non supportato
                }
                $checkImage = TRUE;
                $queryInsertP = "INSERT INTO `PARAGRAFO` (`Id`, `Contenuto`, `Immagine`,`Id_lg`, `Inizio`) "
                        . "VALUE (NULL,'$contenuto','image/$name_file','$id_lg_m','0');";
            }
            if(mysql_query($queryInsertP)){
                if($checkImage){
                    move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_dir.$name_file);
                }
                die(json_encode(array("errore" => FALSE, "risposta" => NULL)));
            }else{
                die(json_encode(array("errore" => TRUE, "risposta" => NULL)));
            }
        }
        break;
    case "test":
        die(json_encode(array(
            "comando" => $_POST["comando"], 
            "titolo" => $_POST["parametri"]["titolo"],
            "visibile" => $_POST["parametri"]["visibile"],
            "img_copertina size" => $_FILES["img_copertina"]["size"]
            )));
        break;
    case "cambia_password":
        $newPassword = sha1($_POST["newPassword"]);
        $oldPassword = sha1($_POST["oldPassword"]);
        $username = strtolower($_POST["username"]);
        $query = "UPDATE UTENTE
          SET UTENTE.Password = '$newPassword'
          WHERE UTENTE.Username='$username' AND UTENTE.Password='$oldPassword'";
        $result = mysql_query($query);
        if ($result) {
            echo '{"comando":"ok","redirect":"creatore/giocatore"}';
        } else {
            echo '{"comando":"error","testo":"link non valido"}';
        }
        break;
    default:
        if($_GET["email"] !== "" && $_GET["code"] !== "" ){
            $email = $_GET["email"];
            $codice = $_GET["code"];
            $query = "UPDATE UTENTE
                      SET UTENTE.Check = '1', UTENTE.Id_tipo ='2'
                      WHERE UTENTE.Email='$email' AND UTENTE.Token='$codice'";
            $result = mysql_query($query);
            if ($result) {
                echo json_encode(array("errore" => false, "risposta" => NULL));
            } else {
                echo json_encode(array("errore" => true, "risposta" => array("testo" => "link non valido o scaduto")));
            }
        }
        break;
}