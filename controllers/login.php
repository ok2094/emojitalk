<?php

// username
if(!empty(trim($_POST['username']))){

    $username = trim($_POST['username']);
    
    // prüfung benutzername
    
} else {
    $error .= "Geben Sie bitte den Benutzername an.<br />";
}
// password
if(!empty(trim($_POST['password']))){
    $password = trim($_POST['password']);
    // passwort gültig?
    
} else {
    $error .= "Geben Sie bitte das Passwort an.<br />";
}

// kein fehler
if(empty($error)){
    $query = "SELECT * from user where username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result=$stmt->get_result();
    if($result->num_rows === 0) {
        echo 'shit';
    }
    else {
        while($row = $result->fetch_assoc()){
            if(password_verify($password, $row['password'])){
                session_start();
                $_SESSION['login'] = true;
                $_SESSION['username'] = $row['username'];
            }
            else{
                echo"shit";
            }
        }
    }

    $result->free();
    $stmt->close();
    // header("Location: /index.php");
}


?>