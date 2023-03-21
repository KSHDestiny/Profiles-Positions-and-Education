<?php // Do not put any HTML above this line
session_start();
require_once("pdo.php");

if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to index.php
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';
$stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // Pw is php123

$failure = false;  // If we have no POST data

// Check to see if we have some POST data, if we do process it
if ( isset($_POST['email']) && isset($_POST['pass']) ) {
    $email = htmlentities($_POST['email']);
    $pass = htmlentities($_POST['pass']);
        if ( strlen($email) < 1 || strlen($pass) < 1 ) {
            $_SESSION['error'] = "All fields are required";
            header("Location: login.php");
            return;
        } 
    
        elseif ( filter_var($email, FILTER_VALIDATE_EMAIL)) {    
            $check = hash('md5', $salt.$pass);

            $sql = 'SELECT user_id, name FROM users WHERE email = :em AND password = :pw';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ( $row !== false ) {
                $_SESSION['name'] = $row['name'];
                $_SESSION['user_id'] = $row['user_id'];
                // Redirect the browser to index.php
                header("Location: index.php");
                return;
            }
            else {    
                error_log("Login fail ".$_POST['email']." $check");            
                $_SESSION['error'] = "Incorrect password";
                header("Location: login.php");
                return;
            }            
        }

        else {    
            $_SESSION['error'] = "Email address must contain @";
            header("Location: login.php");
            return;
        }    
  }

// Fall through into the View
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Kaung Sat Hein's Login Page</title>
        <?php include_once("head.php") ?>
        <!-- Custom styles for this template -->
        <link href="starter-template.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <h1>Please Log In</h1>
            <?php
            // Note triple not equals and think how badly double
            // not equals would work here...
            if (isset($_SESSION["error"]) ) {
                echo "<p style='color: red;'>" . htmlentities($_SESSION['error']) . "</p>\n";
                unset($_SESSION["error"]);
            }
            ?>
            <form method="POST">
                <b>Email</b> <input type="text" id="email" name="email"><br/>
                <b>Password</b> <input type="password" name="pass" id="id_1723"> <br>
                <input type="submit" onclick="return doValidate();" value="Log In">
                <input type="submit" name="cancel" value="Cancel">
            </form>
            <p>
            For a password hint, view source and find an account and password hint in the HTML comments.
            <!-- Hint: The password is the four character sound a cat
            makes (all lower case) followed by 123. -->
            </p>
        </div>
    </body>
    <?php include_once("foot.php") ?>
    <script>
        function doValidate() {
            console.log('Validating...');
            try {
                em = document.getElementById('email').value;
                pw = document.getElementById('id_1723').value;
                console.log("Validating pw=" + pw);
                if (em == null || em == "" || pw == null || pw == "") {
                    alert("Both fields must be filled out");
                    return false;
                }
                return true;
            } catch(e) {
                return false;
            }
            return false;
        }
    </script>
</html>
