<?php
session_start();
require_once "pdo.php";
require_once "utils.php";
unset($_SESSION['name']);
unset($_SESSION['user_id']);


// Cancel button in HTML form
if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_'; // Random salt added to the password $stored_hash in the DB

// Check there is valid POST data from the username and password HTML form
if (isset($_POST['email']) && isset($_POST['pass'])) {
    if (strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: login.php");
        return;
    } else if (strpos($_POST['email'], '@') === false) {
        $_SESSION['error'] = "Email must have an at-sign (@)";
        header("Location: login.php");
        return;
    } else { // If the username and password is valid input
        $check = hash('md5', $salt . $_POST['pass']); // MD5 hash the password input with the random $salt
        $stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw'); // Return profile name and profile_id if the email and stored hash match users input
        $stmt->execute(array(':em' => $_POST['email'], ':pw' => $check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row !== false) { // SQL SELECT returned data same as user input - Login success
            $_SESSION['name'] = $row['name'];
            $_SESSION['user_id'] = $row['user_id'];
            header("Location: index.php");
            return;
        } else { // SQL Select did not return data same as user input - Login failure
            $_SESSION['error'] = "Incorrect password";
            header("Location: login.php");
            return;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <?php require_once "head.php"; ?>
    <title>Jack Marshalls Login Page</title>
</head>

<body>
    <div class="container">
        <h1>Please Log In</h1>
        <?php error_flash(); // Render any errors on top of login page ?>
        <form method="POST" action="login.php">
            <label for="email">Email</label>
            <input type="text" name="email" id="email"><br/>
            <label for="pass-field">Password</label>
            <input type="password" name="pass" id="pass-field"><br/>
            <input type="submit" onclick="return doValidate();" value="Log In">
            <input type="submit" name="cancel" value="Cancel">
        </form>
    <script>
        // Function to check email and password input in hTML form
        function doValidate() {
            console.log('Validating...');
            try {
                addr = document.getElementById('email').value;
                pw = document.getElementById('pass-field').value;
                console.log("Validating addr=" + addr + " pw=" + pw);
                if (addr == null || addr == "" || pw == null || pw == "") {
                    alert("Both fields must be filled out");
                    return false;
                }
                if (addr.indexOf('@') == -1) {
                    alert("Invalid email address");
                    return false;
                }
                return true;
            } catch (e) {
                return false;
            }
            return false;
        }
    </script>
    </div>
</body>

</html>