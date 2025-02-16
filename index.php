<?php
    require_once "pdo.php";
    require_once "utils.php";
    session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once "head.php"; ?>
    <title>Jack Marshall's Resume Registry</title>
</head>
<body>
    <div class="container">
        <h1>Jack Marshall's Resume Registry</h1><br>
        <?php
            // index.php displays a table of profiles whether you are logged in or not
            // If there is no profile data in the table 'No rows found' is rendered
            // If you are not logged the table will render as read-only
            // If you are logged in the table will render with links to perform CRUD operations
 
            $stmt = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM Profile");
            // Not logged in
            if ( ! isset($_SESSION['name']) ||  ! isset($_SESSION['user_id']) )  {
                error_flash();
                echo('<p><a href="login.php">Please log in</a></p>');

                // No rows returned -> Render no rows found
                if ($stmt->rowCount() === 0) {
                    echo("<p>No rows found</p>");
                } else { // Rows returned -> Render a table
                    echo('<table border="1">'); 
                    echo("<thead><tr>");
                    echo('<th style="padding: 5px;">Name</th>');
                    echo('<th style="padding: 5px;">Headline</th>');
                    echo("</tr></thead>");
    
                    // Loop over each row returned and insert it into the table as table data
                    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
                        echo ('<tr><td style="padding: 5px;">');
                        echo ('<a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']). ' '.htmlentities($row['last_name']).'</a>');
                        echo ('</td><td style="padding: 5px;">');
                        echo (htmlentities($row['headline']));
                        echo("</td></tr>"); 
                    }
                    echo("</table><br>"); 
                }
                return;
            } else { // Logged in
                error_flash();
                success_flash();
                echo('<p><a href="logout.php">Logout</a></p>'); // Log out link
            
                // No rows returned -> Render no rows found
                if ($stmt->rowCount() === 0) {
                    echo("<p>No rows found</p>");
                } else { // Rows returned -> Render a table
                    echo('<table border="1">');
                    echo("<thead><tr>");
                    echo('<th style="padding: 5px;">Name</th>');
                    echo('<th style="padding: 5px;">Headline</th>');
                    echo('<th style="padding: 5px;">Action</th>');
                    echo("</tr></thead>");
    
                    // Loop over each row returned and insert it into the table as table data
                    // Create a link to edit.php and delete.php for each profile returned
                    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
                        echo ('<tr><td style="padding: 5px;">');
                        echo ('<a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']). ' '.htmlentities($row['last_name']).'</a>');
                        echo ('</td><td style="padding: 5px;">');
                        echo (htmlentities($row['headline']));
                        echo ('</td><td style="padding: 5px;">');
                        echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / '); // Link to edit.php to UPDATE entries in the DB
                        echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>'); // Link to delete.php to DELETE entries in the DB
                        echo("</td></tr>");
                    }
                    echo("</table><br>");
                }
                echo('<p><a href="add.php">Add New Entry</a></p>'); // Link to add.php to CREATE new entries in the DB
            } 
        ?>
</div>
</body>