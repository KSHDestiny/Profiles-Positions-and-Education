<div class='container mt-2'>
    <?php 
        require_once('pdo.php');
        $stmt = $pdo->query('SELECT * FROM profile ORDER BY profile_id');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($rows == true){
    ?>
        <table>
            <tr>
                <th><b>Name</b></th>
                <th><b>Headline</b></th>
            </tr>
                <?php 
                    foreach($rows as $row) {
                        $id = htmlentities($row['profile_id']);
                        echo '<tr><td>';
                        echo ("<a href='view.php?profile_id=$id'>" . htmlentities($row['first_name']) ." ". htmlentities($row['last_name']) . '</a></td><td>');
                        echo (htmlentities($row['headline']) . '</td></tr>');
                    }
                }
                ?>
        </table>
</div>