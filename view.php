<?php
    session_start();
    require_once "pdo.php";
    require_once "utils.php";
    // Check for profile_id otherwise redirect back to index.php
    checkProfileId();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jack Marshall's Profile View</title>
    <?php require_once "head.php"; ?>
</head>
<body>
    <div class="container">
        <h1>Profile information</h1>
        <?php
            // view.php displays profile, education, and position data for a given profile_id

            $stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :id");
            $stmt->execute((array(":id" => $_GET['profile_id'])));
            // Loop over each row of profile data and render it to the screen.
            while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
                echo "<p>First Name: ".htmlspecialchars($row['first_name'])."</p>";
                echo "<p>Last Name: ".htmlspecialchars($row['last_name'])."</p>";
                echo "<p>Email: ".htmlspecialchars($row['email'])."</p>";
                echo "<p>Headline: "."</br>".htmlspecialchars($row['headline'])."</p>";
                echo "<p>Summary: ". "</br>".htmlspecialchars($row['summary'])."</p>";
            }

            // Return any education entries for a profile_id
            // loadEdu() performs the SELECT statement
            $educations = loadEdu($pdo, $_REQUEST['profile_id']);
            $edu = 0;
            echo('<p>Education:</p><ul>');
            echo('');
            // Render any rows returned as a list item
            foreach ($educations as $education) {
                echo('<li>'.$education['year'].': '.$education['name']);
            }
            echo('</ul>');

            // Return any position entries for a profile_id
            // loadPos() performs the SELECT statement
            $positions = loadPos($pdo, $_REQUEST['profile_id']);
            $pos = 0;
            echo('<p>Position:</p><ul>');
            // Render any rows returned as a list item
            foreach ($positions as $position) {
                echo('<li>'.$position['year'].': '.htmlentities($position['description']).'</li>');
            }
            echo('</ul>');
            ?>
        <a href="index.php">Done</a>
    </div>
</body>
</html>