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

    // Add Button in HTML form - check pOST data is valid before INSERT
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
        if ( empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['headline']) || empty($_POST['summary']) ) { // Error if any field is not populated
            $_SESSION['error'] = "All fields are required";
            header("Location: add.php");
            return;
        } else if ( strpos($_POST['email'], '@') === false ) {
            $_SESSION['error'] = 'Email Address must contain @';
            header("Location: add.php");
            return;
        } else if (validatePos() === false || validateEdu() === false) { // validate the position and education input
            header("Location: add.php"); // Redirect back to edit.php if not valid
            return;
        } else { // INSERT data if valid

            // INSERT profile data
            $stmt = $pdo->prepare('INSERT INTO Profile (user_id, first_name, last_name, email, headline, summary) VALUES ( :uid, :fn, :ln, :em, :he, :su)');
            $stmt->execute(array(
            ':uid' => $_SESSION['user_id'],
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary'])
            );

            $profile_id = $pdo->lastInsertId(); // Get the last inserted ID from the previous INSERT
            // INSERT the position entries
            insertPos($pdo, $profile_id);
            //Insert the Education entries
            insertEdu($pdo, $profile_id);
            // All data inserted - Redirect back to index.php
            $_SESSION['success'] = "Profile added";
            header("Location: index.php");
            return;
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
<title>Jack Marshall's Profile Add</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">
    <?php
        // add.php allows the user to add profile, education and position data for a new profile

        if ( isset($_SESSION['name']) ) {
            echo "<h1>Adding Profile for ";
            echo htmlentities($_SESSION['name']);
            echo "</h1>\n";
        }
        error_flash(); // Flash any errors to the user
    ?>
    <form method="post">
        <p>First Name: <input type="text" name="first_name" size="60"></p>
        <p>Last Name: <input type="text" name="last_name" size="60"></p>
        <p>Email: <input type="text" name="email" size="30"></p>
        <p>Headline: <br><input type="text" name="headline" size="80"></p>
        <p>Summary: <br><textarea name="summary" rows="8" cols="80"></textarea></p>
        <p>Education: <input type="button" id ="addEdu" value="+"><div id="edu_fields"></div></p> 
        <p>Position: <input type="submit" id="addPos" value="+"><div id="position_fields"></div></p>
        <p>
            <input type="submit" value="Add">
            <input type="submit" name="cancel" value="Cancel">
        </p>
    </form>
    <!-- The below script is used for onClick functionality for position and education input -->
    <!-- The script allows new position and education entries to be added OR to remove any existing entries on the page -->
    <script>
        countPos = 0;
        countEdu = 0;

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