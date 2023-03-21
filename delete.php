<?php 
session_start();
require_once("pdo.php");
include_once("util.php");

if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to index.php
    header("Location: index.php");
    return;
}

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM profile WHERE profile_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $_POST['profile_id']));
    $_SESSION['success'] = 'Profile deleted';
    header( 'Location: index.php' ) ;
    return;
}

// Guardian: Make sure that user_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT profile_id, first_name, last_name FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Kaung Sat Hein's Delete Page</title>
        <?php include_once("head.php") ?> 
    </head>
    <body>
        <div class="container">
            <h1>Deleting Profile for  <?= htmlentities($_SESSION["name"]) ?></h1>
            <p>First Name: <?= $row['first_name'] ?></p> 
            <p>Last Name: <?= $row['last_name'] ?></p>

            <form method="post">
                <input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
                <input type="submit" value="Delete" name="delete">
                <input type="submit" value="Cancel" name="cancel">
            </form>
        </div>
    </body>
    <?php include_once("foot.php") ?>
</html>
