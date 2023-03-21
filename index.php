<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kaung Sat Hein's Resume Registry</title>
    <?php include_once("head.php") ?>
    <style>
        table, tr, th, td {
            border: 1px solid black;
        }
    </style>
</head>
<body>
    <div class="container mt-2">
        <h1>Kaung Sat Hein's Resume Registry</h1>
        <?php 
            session_start(); 
            require_once("pdo.php");
            include_once("util.php");
            if ( ! isset($_SESSION['name']) ) {
                echo "<div><a href='login.php'>Please log in</a></div><br>";
                include_once("read.php");
                echo "<p><b>Note: </b>Your implementation should retain data across multiple logout/login sessions. This sample implementation clears all its data on logout - which you should not do in your implementation.</p>";
                die();
            }
        ?>
        <p>
            <a href="logout.php">Logout</a>
        </p>
        <?php
            $stmt = $pdo->query("SELECT * FROM profile ORDER BY profile_id");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($rows == true){
                flashMessages();
            
        ?>
        <table>
            <tr>
                <th><b>Name</b></th>
                <th><b>Headline</b></th>
                <th><b>Action</b></th>
            </tr>
                <?php 
                    foreach($rows as $row) {
                        $id = htmlentities($row['profile_id']);
                        echo "<tr><td><a href='view.php?profile_id=$id'>";
                        echo (htmlentities($row['first_name']) ." ". htmlentities($row['last_name']) . "</a></td><td>");
                        echo (htmlentities($row['headline']) . "</td><td>");
                        if($row["user_id"] === $_SESSION["user_id"] ){
                            echo "<a href='edit.php?profile_id=$id";
                            echo "'>Edit</a> / <a href='delete.php?profile_id=$id";
                            echo "'>Delete</a></td></tr>";
                        }
                    }
                }
                ?>
        </table>
        <p>
            <a href="add.php">Add New Entry</a>
        </p>
        <p>
            <b>Note: </b>Your implementation should retain data across multiple logout/login sessions. This sample implementation clears all its data periodically - which you should not do in your implementation.
        </p>
    </div>
</body>
<?php include_once("foot.php") ?>
</html>