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

    // Delete Button in HTML form
    if ( isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM Profile WHERE profile_id = :id"); // Delete profile for given profile_id
        $stmt->execute(array(':id' => $_POST['profile_id']));
        $_SESSION['success'] = "Profile deleted";
        header("Location: index.php");
        return;
    }

    checkProfileId(); // Check for profile_id otherwise redirect back to index.php
?>

<!DOCTYPE html>
<html>
<head>
<title>Jack Marshall's Automobile Tracker</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">
    <?php
        // delete.php allows the user to delete a profile from the DB for a given profile_id

        $stmt = $pdo->prepare("SELECT first_name, last_name FROM Profile WHERE profile_id = :id");
        $stmt->execute((array(":id" => $_GET['profile_id'])));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $fn = htmlentities($row['first_name']);
        $ln = htmlentities($row['last_name']);

        echo "<h1>Deleting Profile</h1>";
        echo "<p>First Name: $fn</p>";
        echo "<p>Last Name: $ln </p>";
    ?>

    <form method="post" action="delete.php">
        <input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']); ?>">
        <input type="submit" name ="delete" value="Delete">
        <input type="submit" name="cancel" value="Cancel">
    </form>
</div>
</body>
</html>