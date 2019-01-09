<?php
include('controllers/dbconnector.inc.php');
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST"){

    //SIGN UP
    if(isset($_POST["register"])){
        if(isset($_POST['rEmail']) && !empty(trim($_POST['rEmail'])) && strlen(trim($_POST['rEmail'])) <= 100){
            $rEmail = htmlspecialchars(trim($_POST['rEmail']));
            // korrekte emailadresse?
            if (filter_var($rEmail, FILTER_VALIDATE_EMAIL) === false){
              $error .= "Please enter a valid email";
            }
        } else {
            $error .= "Please enter a valid email";
        }

        // benutzername check
        if(isset($_POST['rUsername']) && !empty(trim($_POST['rUsername'])) && strlen(trim($_POST['rUsername'])) <= 30 && strlen(trim($_POST['rUsername'])) >= 3){
            $rUsername = htmlspecialchars(trim($_POST['rUsername']));
        } else {
            $error .= "Please enter a valid username";
        }

        // passwort vorhanden, mindestens 8 Zeichen
        if(isset($_POST['rPassword']) && !empty(trim($_POST['rPassword'])) && strlen(trim($_POST['rPassword'])) >= 8 && strlen(trim($_POST['rPassword'])) <= 200){
            $rPassword = trim($_POST['rPassword']);
        } else {
            $error .= "Please enter a valid password";
        }

        // wenn kein Fehler vorhanden ist, schreiben der Daten in die Datenbank
        if(empty($error)){
            $query = "SELECT * from user where username = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $rUsername);
            $stmt->execute();
            $result=$stmt->get_result();
            if($result->num_rows === 0) {
                $rPassword = password_hash($rPassword, PASSWORD_DEFAULT);
                $query = "INSERT INTO user (role, username, password, email) VALUES (?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $rRole = 1;
                $stmt->bind_param("isss", $rRole, $rUsername, $rPassword, $rEmail);
                $stmt->execute();

                echo "<script type='text/javascript'>alert('Sign up successful');</script>";
            }
            else {
                $error .= "Username taken";
            }
            $stmt->close();
        }
    } 

    //LOGIN
    if(isset($_POST["login"])){
        // username
        if(!empty(trim($_POST['logUsername']))){
            $username = htmlspecialchars(trim($_POST['logUsername']));
        } else {
            $error .= "Please enter a username";
        }

        // password
        if(!empty(trim($_POST['logPassword']))){
            $password = trim($_POST['logPassword']);
        } else {
            $error .= "Please enter a password";
        }

        // kein fehler
        if(empty($error)){
            $query = "SELECT * from user where username = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result=$stmt->get_result();
            if($result->num_rows === 0) {
                $error .= "User not found";
            }
            else {
                while($row = $result->fetch_assoc()){
                    if(password_verify($password, $row['password'])){
                        $_SESSION['login'] = true;
                        $_SESSION['userid'] = $row['id'];
                        $_SESSION['userrole'] = $row['role'];
                        session_regenerate_id();
                    }
                    else{
                        $error .= "Password wrong";
                    }
                }
            }

            $result->free();
            $stmt->close();
        }
    }

    //CREATE POST
    if(isset($_POST["createPost"])){
        if(isset($_POST['content']) && !empty(trim($_POST['content'])) && strlen(trim($_POST['content'])) <= 20){
            $content = htmlspecialchars(trim($_POST['content']));
        } else {
            $error .= "Please review your input";
        }

        if(empty($error)){
            $query = "INSERT INTO post (content, created_at, created_by) VALUES (?,now(),?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ss", $content, $_SESSION['userid']);
            $stmt->execute();
            $stmt->close();
        }
    }

    //DELETE POST
    if(isset($_POST["deletePost"])){
        if(isset($_POST['deleteId']) && !empty(trim($_POST['deleteId']))){
            $deleteId = trim($_POST['deleteId']);
            $query = "SELECT * from post where id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $deleteId);
            $stmt->execute();
            $result=$stmt->get_result();
            if($result->num_rows === 0) {
                $error .= "Post not found";
            } else {
                while($row = $result->fetch_assoc()){ 
                    if($row['created_by'] == $_SESSION['userid'] || $_SESSION['userrole'] == 2) {
                        $query = "DELETE FROM post WHERE id = ?";
                        $stmt = $mysqli->prepare($query);
                        $stmt->bind_param("i", $deleteId);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        $error .= "You don't have sufficient rights to delete this post";
                    }
                }
            }
            $result->free();
        }
    }

    //CHANGE PASSWORD
    if(isset($_POST["changePwd"])){
        if(isset($_POST['oldPwd']) && !empty(trim($_POST['oldPwd'])) && strlen(trim($_POST['oldPwd'])) <= 200){
            $oldPwd = trim($_POST['oldPwd']);
            $query = "SELECT * from user where id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $_SESSION['userid']);
            $stmt->execute();
            $result=$stmt->get_result();
            if($result->num_rows === 0) {
                $error .= "User not found";
            } else {
                while($row = $result->fetch_assoc()){
                    if(password_verify($oldPwd, $row['password'])){
                        if(isset($_POST['newPwd']) && !empty(trim($_POST['newPwd'])) && strlen(trim($_POST['newPwd'])) >= 8 && strlen(trim($_POST['newPwd'])) <= 200){
                            // neues passwort setzen
                            $newPwd = trim($_POST['newPwd']);
                            $newPwd = password_hash($newPwd, PASSWORD_DEFAULT);
                            $query = "UPDATE user SET password = ? WHERE id = ?";
                            $stmt = $mysqli->prepare($query);
                            $stmt->bind_param("si", $newPwd, $_SESSION['userid']);
                            $stmt->execute();
                            $stmt->close();

                            echo "<script type='text/javascript'>alert('Password change successful');</script>";
                        } else {
                            $error .= "Please enter a valid new password";
                        }
                    }
                    else{
                        $error .= "Password wrong";
                    }
                }
            }
            $result->free();
        } else {
            $error .= "Please enter your current password";
        }

        session_regenerate_id();
    }

    //ERROR MESSAGE
    if(!empty($error)) {
        echo "<script type='text/javascript'>alert('$error');</script>";
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
    <link rel="stylesheet" href="stylesheets/main.css">

    <title>Emojitalk😜</title>
</head>

<body class="has-navbar-fixed-top has-navbar-fixed-bottom">
    <!-- Navbar -->
    <nav id="mainNav" class="navbar is-fixed-top is-warning" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="index.php">
                <img src="assets\img\logo.png" alt="Emojitalk😜" title="Emojitalk😜">
            </a>

            <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarBasic" class="navbar-menu">
            <div class="navbar-start">
            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="buttons">
                        <?php
                        if (!isset($_SESSION['login'])) {
                        ?>
                        <a id="btnRegister" class="button is-primary">
                            <strong>Sign up</strong>
                        </a>
                        <a id="btnLogin" class="button is-light">
                            Log in
                        </a>
                        <?php
                        } else {
                        ?>
                        <a id="btnChangePwd" class="button is-danger">
                            Change Password
                        </a>
                        <a href="controllers/logout.php" class="button is-light">
                            Log out
                        </a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-background"></div>
        <div class="modal-card">
        <form name="registerForm" method="post">
            <header class="modal-card-head">
                <p class="modal-card-title">Sign up</p>
                <button class="delete exitmodal" aria-label="close"></button>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label" for="regUsername">Username</label>
                    <div class="control has-icons-left">
                        <input name="rUsername" id="regUsername" class="input" type="text" maxlength="30" minlength="3" required>
                        <span class="icon is-small is-left">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="regEmail">Email</label>
                    <div class="control has-icons-left">
                        <input name="rEmail" id="regEmail" class="input" type="email" maxlength="100" required>
                        <span class="icon is-small is-left">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="regPassword">Password</label>
                    <div class="control has-icons-left">
                        <input name="rPassword" id="regPassword" class="input" type="password" maxlength="200" minlength="8" required>
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
        <form name="loginForm" method="post">
            <header class="modal-card-head">
                <p class="modal-card-title">Log in</p>
                <button class="delete exitmodal" aria-label="close"></button>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label" for="logUsername">Username</label>
                    <div class="control has-icons-left">
                        <input name="logUsername" id="logUsername" class="input" type="text" maxlength="30" required>
                        <span class="icon is-small is-left">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="logPassword">Password</label>
                    <div class="control has-icons-left">
                        <input name="logPassword" id="logPassword" class="input" type="password" maxlength="200" required>
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

    <!-- Change Password Modal -->
    <div id="changePwdModal" class="modal">
        <div class="modal-background"></div>
        <div class="modal-card">
        <form name="changePwdForm" method="post">
            <header class="modal-card-head">
                <p class="modal-card-title">Change Password</p>
                <button class="delete exitmodal" aria-label="close"></button>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label" for="oldPwd">Current Password</label>
                    <div class="control has-icons-left">
                        <input name="oldPwd" id="oldPwd" class="input" type="password" maxlength="200" required>
                        <span class="icon is-small is-left">
                            <i class="fas fa-key"></i>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="newPwd">New Password</label>
                    <div class="control has-icons-left">
                        <input name="newPwd" id="newPwd" class="input" type="password" maxlength="200" minlength="8" required>
                        <span class="icon is-small is-left">
                            <i class="fas fa-key"></i>
                        </span>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button name="changePwd" type="submit" class="button is-success">Confirm</button>
            </footer>
        </form>
        </div>
    </div>

    <?php
    if (isset($_SESSION['login'])) {
    ?>
    <!-- Post Modal -->
    <div id="postModal" class="modal">
        <div class="modal-background"></div>
        <div class="modal-card">
        <form name="postForm" method="post">
            <header class="modal-card-head">
                <input name="content" pattern="[^a-zA-Z0-9 @.,_+-?!]*" maxlength="20" class="input is-rounded emojiinput"></input>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <div class="emojipicker"></div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button name="createPost" type="submit" class="button is-success">Submit</button>
            </footer>
        </form>
        </div>
    </div>
    <?php
    }
    ?>

    <!-- Main Section -->
    <section class="section">
        <div class="container">
            <div class="columns">
                <div class="column is-hidden-mobile"></div>

                <div class="column is-half">

                    <?php
                    $query = "SELECT p.id, p.content, p.created_at, p.created_by, u.role, u.username from post p inner join user u on p.created_by = u.id ORDER BY created_at DESC";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    $result=$stmt->get_result();
                    while($row = $result->fetch_assoc()){
                    ?>
                    <!-- Post -->
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">
                                <?php echo $row['username']; ?>
                            </p>
                        </header>
                        <div class="card-content">
                            <div class="content">
                                <?php echo $row['content']; ?>
                            </div>
                        </div>
                        <footer class="card-footer">
                            <?php 
                            if(isset($_SESSION['login']) && ($_SESSION['userid'] == $row['created_by'] || $_SESSION['userrole'] == 2))
                            {
                            ?>
                                <form name="deletePostForm" class="card-footer-item" method="post">
                                    <input type="hidden" name="deleteId" value="<?php echo $row['id']; ?>"/>
                                    <button name="deletePost" type="submit" class="button button is-small">🗑️</button>
                                </form>
                            <?php } ?>
                        </footer>
                    </div>
                    <?php
                    }
        
                    $result->free();
                    $stmt->close();
                    ?>
                    

                </div>

                <div class="column is-hidden-mobile"></div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <nav class="navbar is-fixed-bottom is-warning">
        <div class="navbar-menu is-active level">
            <?php
            if (isset($_SESSION['login'])) {
            ?>
            <div id="createPost" class="dropdown is-up level-item has-text-centered">
                <button id="btnCreatePost" class="button is-success">Create Post</button>
            </div>
            <?php
            }
            ?>
        </div>
    </nav>

    <!-- Javascript -->
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <script src="http://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>