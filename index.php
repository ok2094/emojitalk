<?php
include('controllers/dbconnector.inc.php');

$error = '';
$message = '';


// Formular wurde gesendet und Besucher ist noch nicht angemeldet.
if ($_SERVER["REQUEST_METHOD"] == "POST"){

    if(isset($_POST["register"])){

        if(isset($_POST['rEmail']) && !empty(trim($_POST['rEmail'])) && strlen(trim($_POST['rEmail'])) <= 100){
            $rEmail = htmlspecialchars(trim($_POST['rEmail']));
            // korrekte emailadresse?
            if (filter_var($rEmail, FILTER_VALIDATE_EMAIL) === false){
              $error .= "Geben Sie bitte eine korrekte Email-Adresse ein<br />";
            }
        } else {
            // Ausgabe Fehlermeldung
            $error .= "Geben Sie bitte eine korrekte Email-Adresse ein.<br />";
        }

        // benutzername vorhanden, mindestens 6 Zeichen und maximal 30 zeichen lang
        if(isset($_POST['rUsername']) && !empty(trim($_POST['rUsername'])) && strlen(trim($_POST['rUsername'])) <= 30){
            $rUsername = trim($_POST['rUsername']);
        } else {
            // Ausgabe Fehlermeldung
            $error .= "Geben Sie bitte einen korrekten Benutzernamen ein.<br />";
        }

        // passwort vorhanden, mindestens 8 Zeichen
        if(isset($_POST['rPassword']) && !empty(trim($_POST['rPassword']))){
            $rPassword = trim($_POST['rPassword']);
            //entspricht das passwort unseren vorgaben? (minimal 8 Zeichen, Zahlen, Buchstaben, keine Zeilenumbrüche, mindestens ein Gross- und ein Kleinbuchstabe)
        } else {
            // Ausgabe Fehlermeldung
            $error .= "Geben Sie bitte einen korrekten Nachnamen ein.<br />";
        }

        echo($rEmail . ' ' . $rPassword . ' ' . $rUsername);
        // wenn kein Fehler vorhanden ist, schreiben der Daten in die Datenbank
        if(empty($error)){
            $rPassword = password_hash($rPassword, PASSWORD_DEFAULT);
            $query = "INSERT INTO user (role, username, password, email) VALUES (?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("isss", $rRole = 1, $rUsername, $rPassword, $rEmail);
            $stmt->execute();
            $stmt->close();
            //header("Location: /login.php");
        }
    } else if(isset($_POST["login"])){
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
    }
	
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.2/css/bulma.min.css">

    <title>😜Emojitalk</title>
</head>

<body class="has-navbar-fixed-top has-navbar-fixed-bottom">
    <!-- Navbar -->
    <nav id="mainNav" class="navbar is-fixed-top is-warning" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="index.php">
                <h2>Emojitalk😜</h2>
            </a>

            <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarBasicExample" class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item">
                    Feed
                </a>
            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="buttons">
                        <a id="btnRegister" class="button is-primary">
                            <strong>Sign up</strong>
                        </a>
                        <a id="btnLogin" class="button is-light">
                            Log in
                        </a>
                        <a id="btnLogin" href="controllers/logout.php" class="button is-light">
                            Log out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-background"></div>
        <div class="modal-card">
        <form action="index.php" method="post">
            <header class="modal-card-head">
                <p class="modal-card-title">Sign up</p>
                <button class="delete exitmodal" aria-label="close"></button>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label" for="regUsername">Username</label>
                    <div class="control has-icons-left">
                        <input name="rUsername" id="regUsername" class="input" type="text">
                        <span class="icon is-small is-left">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="regEmail">Email</label>
                    <div class="control has-icons-left">
                        <input name="rEmail" id="regEmail" class="input" type="email">
                        <span class="icon is-small is-left">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="regPassword">Password</label>
                    <div class="control has-icons-left">
                        <input name="rPassword" id="regPassword" class="input" type="password">
                        <span class="icon is-small is-left">
                            <i class="fas fa-key"></i>
                        </span>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button name="register" type="submit" class="button is-success">Sign up!</button>
            </footer>
        </form>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-background"></div>
        <div class="modal-card">
        <form action="index.php" method="post">
            <header class="modal-card-head">
                <p class="modal-card-title">Log in</p>
                <button class="delete exitmodal" aria-label="close"></button>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label" for="logUsername">Username</label>
                    <div class="control has-icons-left">
                        <input name="username" id="logUsername" class="input" type="text">
                        <span class="icon is-small is-left">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="logPassword">Password</label>
                    <div class="control has-icons-left">
                        <input name="password" id="logPassword" class="input" type="password">
                        <span class="icon is-small is-left">
                            <i class="fas fa-key"></i>
                        </span>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button name="login" type="submit" class="button is-success">Login</button>
            </footer>
        </form>
        </div>
    </div>

    <!-- Main Section -->
    <section class="section">
        <div class="container">
            <div class="columns">
                <div class="column is-hidden-mobile"></div>

                <div class="column is-half">

                    <!-- Post -->
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">
                                User
                            </p>
                        </header>
                        <div class="card-content">
                            <div class="content">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus nec iaculis mauris.
                            </div>
                        </div>
                        <footer class="card-footer">
                            <a href="#" class="card-footer-item">🗑️</a>
                        </footer>
                    </div>

                    <!-- Emoji Input -->
                    <!-- <div class="field has-addons">
                        <div class="control">
                            <label class="input">Test</label>
                        </div>
                        <p class="control">
                            emojikeyboard toggle something
                        </p>
                        <div class="control">
                            <a class="button is-primary">
                                Post
                            </a>
                        </div>
                    </div> -->

                </div>

                <div class="column is-hidden-mobile"></div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <nav class="navbar is-fixed-bottom is-success">
        <div class="navbar-menu is-active level">
            <!-- <div id="createPost" class="dropdown is-up level-item has-text-centered">
                <div class="dropdown-trigger">
                    <button id="btnCreatePost" class="button" aria-haspopup="true">
                        <span>Create Post</span>
                    </button>
                </div>
                <div class="dropdown-menu is-overlay">
                    <div class="dropdown-content">
                        <div class="dropdown-item">
                            <div class="card">
                                <header class="card-header">
                                    <p class="card-header-title">
                                        User
                                    </p>
                                </header>
                                <div class="card-content">
                                    <div class="content">
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus nec iaculis
                                        mauris.
                                        <time datetime="2016-1-1">11:09 PM - 1 Jan 2016</time>
                                    </div>
                                </div>
                                <footer class="card-footer">
                                    <a href="#" class="card-footer-item">❤️</a>
                                    <a href="#" class="card-footer-item">Delete</a>
                                </footer>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <div id="createPost" class="dropdown is-up level-item has-text-centered">
                <button id="btnCreatePost" class="button is-primary">Create Post</button>
            </div>
        </div>
    </nav>

    <!-- Javascript -->
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <script src="http://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>