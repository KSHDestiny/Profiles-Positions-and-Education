<?php

    // Flash pattern function
    function flashMessages(){
        if( isset($_SESSION["error"]) ) {
            echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
            unset($_SESSION['error']);
        }
        if( isset($_SESSION["success"]) ) {
            echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
            unset($_SESSION['success']);
        }
    }

    // Validate Profile function
    function validateProfile(){
        if(strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) {
            return "All fields are required";
        }
        elseif( strpos($_POST['email'], "@") === false){
            return "Email address must contain @";
        }
        else{
            return true;
        }
    }

    // Validate Position function
    function validatePos(){
        for($i=1; $i<=9; $i++){
            if(!isset($_POST["year".$i])) continue;
            if(!isset($_POST["desc".$i])) continue;
            $year = $_POST["year".$i];
            $desc = $_POST["desc".$i];
            if(strlen($year) == 0 || strlen($desc) == 0){
                return "All fields are required";
            }
            elseif(!is_numeric($year)){
                return "Year must be numeric";
            }
            else{
                return true;
            }
        }
    }

    // Validate Education function
    function validateEdu(){
        for($i=1; $i<=9; $i++){
            if(!isset($_POST["edu_year".$i])) continue;
            if(!isset($_POST["edu_school".$i])) continue;
            $year = $_POST["edu_year".$i];
            $school = $_POST["edu_school".$i];
            if(strlen($year) == 0 || strlen($school) == 0){
                return "All fields are required";
            }
            elseif(!is_numeric($year)){
                return "Education year must be numeric";
            }
            else{
                return true;
            }
        }
    }

    // Get data from Position table function
    function loadPos($pdo, $profile_id){
        $sql = "SELECT * FROM position WHERE profile_id = :prof ORDER BY rank";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":prof" => $profile_id));
        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $positions;
    }

    // Get data from Position table function
    function loadEdu($pdo, $profile_id){
        $sql = "SELECT year,name FROM Education JOIN Institution ON Education.institution_id = Institution.institution_id WHERE profile_id = :prof ORDER BY rank";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":prof" => $profile_id));
        $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $schools;
    }

    // Insert the Position entries function
    function insertPositions($pdo, $profile_id){
        $rank = 1;
        for($i=1; $i<=9; $i++) {
            if ( ! isset($_POST['year'.$i]) ) continue;
            if ( ! isset($_POST['desc'.$i]) ) continue;
            $year = $_POST['year'.$i];
            $desc = $_POST['desc'.$i];

            $stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
            $stmt->execute(array(
                ':pid' => $profile_id,
                ':rank' => $rank,
                ':year' => $year,
                ':desc' => $desc)
            );
            $rank++;
        }
    }

    // Insert the Education entries function
    function insertEducations($pdo, $profile_id){
        $rank = 1;
        for($i=1; $i<=9; $i++) {
            if(!isset($_POST["edu_year".$i])) continue;
            if(!isset($_POST["edu_school".$i])) continue;
            $year = $_POST["edu_year".$i];
            $school = $_POST["edu_school".$i];
    
            // Lookup the school if it is there:
            $institution_id = false;
            $stmt = $pdo->prepare("SELECT institution_id FROM Institution WHERE name = :name");
            $stmt->execute(array(":name" => $school));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row !== false) $institution_id = $row["institution_id"];

            // if there was no institution, insert it
            if($institution_id === false){
                $stmt = $pdo->prepare("INSERT INTO Institution (name) VALUES (:name)");
                $stmt->execute(array(":name"=>$school));
                $institution_id = $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare('INSERT INTO Education (profile_id, institution_id, rank, year) VALUES ( :pid, :iid, :rank, :year)');
            $stmt->execute(array(
                ':pid' => $profile_id,
                ':iid' => $institution_id,
                ':rank' => $rank,
                ':year' => $year)
            );
            $rank++;
        }
    }
?>