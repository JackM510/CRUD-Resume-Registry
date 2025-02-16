<?php
    session_start();
    require_once "pdo.php";
    require_once "utils.php";

    checkLogin(); // Check user logged in

    // Cancel button in HTML form
    if ( isset($_POST['cancel']) ) {
        header("Location: index.php"); 
        return;
    }

    // Save Button in HTML form - check POST data is valid before UPDATE
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
        if ( empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['headline']) || empty($_POST['summary']) ) { // Error if any field is not populated
            $_SESSION['error'] = "All fields are required";
            header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
            return;
        } else if (validatePos() === false || validateEdu() === false) { // validate the position and education input
            header("Location: edit.php?profile_id=".$_REQUEST['profile_id']); // Redirect back to edit.php if not valid
            return;
        } else { // UPDATE DB entries if data is valid

            // SQL UPDATE for profile data
            $stmt = $pdo->prepare("UPDATE profile SET first_name = :fn, last_name = :ln, email = :em, headline = :he, summary =:su WHERE profile_id = :id");
            $stmt->execute(array(':fn' => $_POST['first_name'], ':ln' => $_POST['last_name'], ':em' => $_POST['email'], ':he' => $_POST['headline'], ':su' => $_POST['summary'], ':id' => $_POST['profile_id']));
            
            // Position data
            // DELETE the old position entries before performing an updated INSERT statement
            $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
            $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
            // Check position entries are valid before INSERT
            validatePos() === true ? insertPos($pdo, $_REQUEST['profile_id']) : header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
            
            // Education data
            // DELETE the old education entries before performing an updated INSERT statement
            $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
            $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
            // Check education entries are valid before INSERT
            validateEdu() === true ? insertEdu($pdo, $_REQUEST['profile_id']) : header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
            
            // All data updated - Redirect back to index.php
            $_SESSION['success'] = "Record updated";
            header("Location: index.php"); 
            return;
        }
    }

    checkProfileId(); // Check for profile_id otherwise redirect back to index.php
?>

<!DOCTYPE html>
<html>
<head>
<title>Jack Marshall's Profile Edit</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">
    <?php
        // edit.php allows the user to edit profile, education and position data for a given profile_id

        if ( isset($_SESSION['name']) ) {
            echo "<h1>Editing Profile for ";
            echo htmlentities($_SESSION['name']);
            echo "</h1>\n";
        }
        error_flash(); // Flash any errors to the user

        // SELECT Statement to fetch profile data
        $stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :id");
        $stmt->execute((array(":id" => $_GET['profile_id'])));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Populate profile data in HTML form
        $first_name = htmlentities($row['first_name']);
        $last_name = htmlentities($row['last_name']);
        $email = htmlentities($row['email']);
        $headline = htmlentities($row['headline']);
        $summary = htmlentities($row['summary']);
    ?>
    <form method="post" action="edit.php">
        <p>First Name: <input type="text" name="first_name" size="60" value="<?php echo $first_name ?>"></p>
        <p>Last Name: <input type="text" name="last_name" size="60" value="<?php echo $last_name ?>"></p>
        <p>Email: <input type="text" name="email" size="30" value="<?php echo $email ?>"></p>
        <p>Headline:<br><input type="text" name="headline" size="80" value="<?php echo $headline ?>"></p>
        <p>Summary: <br><textarea name="summary" rows="8" cols="80"><?php echo $summary ?></textarea></p>
        <?php
            // Education data
            // Use loadEdu() function to perform the SELECT statement
            // Returns an array of education entries from the DB
            $schools = loadEdu($pdo, $_REQUEST['profile_id']);
            $edu = 0;
            echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
            echo('<div id="edu_fields">'."\n");
            // Loop over each education row and it the data to the HTML form
            foreach ($schools as $school) {
                $edu++;
                echo('<div id="edu'.$edu.'">'."\n");
                echo('<p>Year: <input type="text" name="edu_year'.$edu.'" value="'.$school['year'].'"/>'."\n"); 
                echo('<input type="button" value="-" onclick="$(\'#edu'.$edu.'\').remove();return false;"></p>'."\n"); 
                echo('<p>School: <input type="text" name="edu_school'.$edu.'" size="80" class="school" value="'.htmlentities($school['name']).'"/>'."\n"); 
                echo("</div></p>\n");
            } 
            echo("</div></p>\n");


            // Position data
            // Use loadPos() function to perform the SELECT statement
            // Returns an array of position entries from the DB
            $positions = loadPos($pdo, $_REQUEST['profile_id']);
            $pos = 0;
            echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
            echo('<div id="position_fields">'."\n");
            // Loop over each position row and it the data to the HTML form
            foreach ($positions as $position) {
                $pos++;
                echo('<div id="position'.$pos.'">'."\n");
                echo('<p>Year: <input type="text" name="year'.$pos.'" value="'.$position['year'].'"/>'."\n"); 
                echo('<input type="button" value="-" onclick="$(\'#position'.$pos.'\').remove();return false;"></p>'."\n"); 
                echo('<textarea name="desc'.$pos.'" rows="8" cols="80">'."\n"); 
                echo(htmlentities($position['description'])."\n");
                echo("</textarea>\n</div>\n");
            }
            echo("</div></p>\n");
        ?>
        <p>
            <input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']); ?>">
            <input type="submit" value="Save">
            <input type="submit" name="cancel" value="Cancel">
        </p>
    </form>
    <!-- The below script is used for onClick functionality for position and education input -->
    <!-- The script allows new position and education entries to be added OR to remove any existing entries on the page -->
    <script>
        countPos = <?= $pos ?>;
        countEdu = <?= $edu ?>;

        $(document).ready(function(){
            window.console && console.log('Document ready called');
            // Add Pos function
            $('#addPos').click(function(event){
                event.preventDefault();
                if ( countPos >= 9 ) {
                    alert("Maximum of nine position entries exceeded");
                    return;
                }
                countPos++;
                window.console && console.log("Adding position "+countPos);
                $('#position_fields').append(
                    '<div id="position'+countPos+'"> \
                    <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
                    <input type="button" value="-" \
                        onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
                    <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
                    </div>');
            });

            // Add Edu function
            $('#addEdu').click(function(event){
                event.preventDefault();
                if ( countEdu >= 9 ) {
                    alert("Maximum of nine education entries exceeded");
                    return;
                }
                countEdu++;
                window.console && console.log("Adding education "+countEdu);
                // Grab some HTML with hot spots and insert into the DOM
                var source  = $("#edu-template").html();
                $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));
                // Add the even handler to the new ones
                $('.school').autocomplete({
                    source: "school.php"
                });

            });
            // Add autocomplete to school element
            $('.school').autocomplete({
                source: "school.php"
            });
        });
    </script>
    <!-- The below is a HTML template for new education entries -->
    <!-- This template is used to insert a template of HTML to the form if a new education entry is added to the page--> 
    <script id="edu-template" type="text">
        <div id="edu@COUNT@">
            <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
            <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
            <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
            </p>
        </div>
    </script>
</div>
</body>
</html>