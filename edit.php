<?php 
session_start();
require_once("pdo.php");
include_once("util.php");

if ( ! isset($_SESSION['name']) ) {
    die('ACCESS DENIED');
    return;
}

if ( isset($_POST['cancel']) ) {
    unset($_SESSION['error']);
    header("Location: index.php");
    return;
}

if ( ! isset($_GET['profile_id']) ) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}

// Get data from Profile table
$profile_id = htmlentities($_GET['profile_id']);
$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :id AND user_id = :uid");
$stmt->execute(array(":id" => $profile_id, ":uid" => $_SESSION["user_id"]));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if ( $profile === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

$profile_id = htmlentities($profile['profile_id']);
$user_id = htmlentities($profile['user_id']);
$fn = htmlentities($profile['first_name']);
$ln = htmlentities($profile['last_name']);
$em = htmlentities($profile['email']);
$he = htmlentities($profile['headline']);
$su = htmlentities($profile['summary']);


// Handling POST data from FORM
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) ) {

    $pid = htmlentities($_POST['profile_id']);
    $uid = htmlentities($_POST['user_id']);
    $first_name = htmlentities($_POST['first_name']);
    $last_name = htmlentities($_POST['last_name']);
    $email = htmlentities($_POST['email']);
    $headline = htmlentities($_POST['headline']);
    $summary = htmlentities($_POST['summary']);

    // Validate Profile
    $msg = validateProfile();
    if( is_string($msg)){
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
        return;
    }

    // Validate Position
    $msg = validatePos();
    if( is_string($msg)){
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
        return;
    }

    // Validate Education
    $msg = validateEdu();
    if( is_string($msg)){
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
        return;
    }

    // Update data in Profile, Position and Education tables
    $sql = "UPDATE profile SET first_name= :fn, last_name= :ln, email= :em, headline= :he, summary= :su WHERE profile_id = :pid AND user_id = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':pid' => $pid,
        ':uid' => $uid,
        ':fn' => $first_name,
        ':ln' => $last_name,
        ':em' => $email,
        ':he' => $headline,
        ':su' => $summary)
    );

    // Clear out the old Position entries
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    // Insert the Position entries
    insertPositions($pdo, $_REQUEST["profile_id"]);

    // Insert the Position entries
    // $rank = 1;
    // for($i=1; $i<=9; $i++) {
    //     if ( ! isset($_POST['year'.$i]) ) continue;
    //     if ( ! isset($_POST['desc'.$i]) ) continue;
    //     $year = $_POST['year'.$i];
    //     $desc = $_POST['desc'.$i];

    //     $stmt = $pdo->prepare('INSERT INTO Position
    //         (profile_id, rank, year, description)
    //     VALUES ( :pid, :rank, :year, :desc)');
    //     $stmt->execute(array(
    //         ':pid' => $_REQUEST['profile_id'],
    //         ':rank' => $rank,
    //         ':year' => $year,
    //         ':desc' => $desc)
    //     );
    //     $rank++;
    // }


    // Clear out the old Education entries
    $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    // Insert the Education entries
    insertEducations($pdo, $_REQUEST["profile_id"]);

    $_SESSION['success'] = "Profile updated";
    header("Location: index.php");
    return;
}

// Get data from Position table where profile_id is same
$positions = loadPos($pdo, $_REQUEST["profile_id"]);
$schools = loadEdu($pdo, $_REQUEST["profile_id"]);
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Kaung Sat Hein's Edit Page</title>
        <?php include_once("head.php") ?>
    </head>

    <body>
        <div class="container">
            <h1 class="my-3">Editing Profile for <?= htmlentities($_SESSION["name"]) ?></h1>
            <?php 
            // Flash pattern
                flashMessages();
            ?>

            <form method="post">
                <input type="hidden" name="profile_id" value="<?= $profile_id ?>"/>
                <input type="hidden" name="user_id" value="<?= $user_id ?>"/>
                <p>
                    <label for="first_name">First Name: </label>
                    <input type="text" id="first_name" name="first_name" value="<?= $fn ?>" size="60"/>
                </p>
                <p>
                    <label for="last_name">Last Name: </label>
                    <input type="text" id="last_name" name="last_name" value="<?= $ln ?>" size="60"/>
                </p>
                <p>
                    <label for="email">Email: </label>
                    <input type="text" id="email" name="email" value="<?= $em ?>"/>
                </p>
                <p>
                    <label for="headline">Headline: </label>
                    <input type="text" id="headline" name="headline" value="<?= $he ?>"/>
                </p>
                <p>
                    Summary: <br>
                    <textarea name="summary" cols="80" rows="8"><?= $su ?></textarea>
                </p>
                <p>
                    Education: <button id="addEdu" class="btn btn-light border"><i class="fa-regular fa-plus"></i></button>
                    <div id="educationContainer">
                    <?php
                    $edu = 0;
                        foreach($schools as $school){
                            if(!isset($school['year'])) continue;
                            if(!isset($school['name'])) continue;
                            $year = htmlentities($school['year']);
                            $school = htmlentities($school['name']);

                            $edu++;
                            echo(                    
                                "<div id='school$edu''>
                                <p>
                                    Year: <input type='text' name='edu_year$edu'' value='$year'/>
                                    <input type='button' value='-' onclick='$('#school$edu').remove(); return false;'>
                                </p>
                                <p>
                                    School: <input type='text' size='80' name='edu_school$edu' class='school' value='$school'/>
                                </p>
                            </div>");
                        }
                    ?>
                    </div>
                </p>
                <p>
                    Position: <button id="addPos" class="btn btn-light border"><i class="fa-regular fa-plus"></i></button>
                    <div id="positionContainer">
                    <?php
                    $pos = 0;
                        foreach($positions as $position){
                            if(!isset($position['year'])) continue;
                            if(!isset($position['description'])) continue;
                            $year = htmlentities($position['year']);
                            $desc = htmlentities($position['description']);
                            $pos++;
                            echo(                    
                                "<div id='position$pos''>
                                <p>
                                    Year: <input type='text' name='year$pos'' value='$year'>
                                    <input type='button' value='-' onclick='$('#position$pos').remove(); return false;'>
                                </p>
                                <textarea name='desc$pos' rows='8' cols='80'>$desc</textarea>
                            </div>");
                        }
                    ?>
                    </div>
                </p>
                <input type="submit" name="submit" value="Save">
                <input type="submit" name="cancel" value="Cancel">
            </form>
        </div>
    </body>
    <?php include_once("foot.php") ?>
    <script>
        var countPos = <?= $pos ?>;
        var countEdu = <?= $edu ?>;

        $(document).ready(function(){
            $("#addPos").click(function(event){
                event.preventDefault();     // Prevent a submit button from submitting a form, Prevent a link from following the URL
                if(countPos >=9){
                    alert("Maximum of nine position entries exceeded");
                    return;
                }
                countPos++;
                console.log("Adding position " + countPos);
                $("#positionContainer").append(
                    `<div id="position`+countPos+`">
                        <p>
                            Year: <input type="text" name="year`+countPos+`" value="">
                            <input type="button" value="-" onclick="$('#position`+countPos+`').remove(); return false;">
                        </p>
                        <textarea name="desc`+countPos+`" rows="8" cols="80"></textarea>
                    </div>`
                );
            });

            $("#addEdu").click(function(event){
                event.preventDefault();     // Prevent a submit button from submitting a form, Prevent a link from following the URL
                if(countEdu >=9){
                    alert("Maximum of nine position entries exceeded");
                    return;
                }
                countEdu++;
                console.log("Adding position " + countEdu);

                // Grab some HTML with hot spots and insert into the DOM
                var source = $("#edu-template").html();
                $("#educationContainer").append(
                    `<div id="school`+countEdu+`">
                        <p>
                            Year: <input type="text" name="edu_year`+countEdu+`" value="">
                            <input type="button" value="-" onclick="$('#school`+countEdu+`').remove(); return false;">
                        </p>
                        <p>School:
                            <input type="text" size="80" name="edu_school`+countEdu+`" class="school" value="" />
                        </p>
                    </div>`
                );

                // Add the even handler to the new ones
                $('.school').autocomplete({
                    source: "school.php";
                });
            });
            $('.school').autocomplete({
                source: "school.php";
            });
        });
    </script>

    <!-- HTML with Substitution hot spots -->
    <!-- <script id="edu-template" type="text">
        <div id="edu@COUNT@">
            <p>Year: 
                <input type="text" name="edu_year@COUNT@" value="" />
                <input type="button" value="-" onclick="$('#edu@COUNT@').remove(); return false;"/><br/>
            </p>
            <p>School:
                <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
            </p>
        </div>
    </script> -->
</html>