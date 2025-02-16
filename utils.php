<?php 
// Function to kill the page if $_SESSION['name'] OR $_SESSION['user_id'] not found.
function checkLogin() {
    if ( ! isset($_SESSION['name']) ||  ! isset($_SESSION['user_id']) ) {
    die('ACCESS DENIED');
    return false;
    } else {
        return true;
    }
}

// Function to redirect to index.php if there is no profile_id
function checkProfileId() {
    if ( ! isset($_GET['profile_id'])) {
        $_SESSION ['error'] = "Missing profile_id"; 
        header("Location: index.php");
    }
}

// Error flash function
function error_flash() {
    if ( isset($_SESSION['error']) ) {
        echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
        unset($_SESSION['error']);
    }
}

// Success flash function
function success_flash() {
    if ( isset($_SESSION['success']) ) {
        echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
        unset($_SESSION['success']);
    }
}

// Function to validate position values
function validatePos() {
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['year'.$i]) ) continue;
        if ( ! isset($_POST['desc'.$i]) ) continue;

        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];

        if ( strlen($year) == 0 || strlen($desc) == 0 ) {
            $_SESSION['error'] = "All fields are required";
            return false; 
        }

        if ( ! is_numeric($year) ) {
            $_SESSION['error'] = "Position year must be numeric";
            return false; 
        }
    }
    return true;
}

// Function to SELECT positions for a given profile_id
function loadPos($pdo, $profile_id) {
    $stmt = $pdo->prepare('SELECT * FROM Position WHERE profile_id = :prof ORDER BY rank');
    $stmt->execute(array( ':prof' => $profile_id));
    $positions = array();
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $positions[] = $row;
    }
    return $positions;
}

// Function to INSERT the position entries
function insertPos($pdo, $profile_id) {
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (! isset($_POST['year' . $i])) continue;
        if (! isset($_POST['desc' . $i])) continue;

        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];
        $stmt = $pdo->prepare('INSERT INTO Position
                (profile_id, rank, year, description)
                VALUES ( :pid, :rank, :year, :desc)');

        $stmt->execute(
            array(
                ':pid' => $profile_id,
                ':rank' => $rank,
                ':year' => $year,
                ':desc' => $desc
            )
        );

        $rank++;
    }
    $_SESSION['success'] = "Record edited";
}

// Function to validate education values
function validateEdu() {
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['edu_year'.$i]) ) continue;
        if ( ! isset($_POST['edu_school'.$i]) ) continue;

        $year = $_POST['edu_year'.$i];
        $school = $_POST['edu_school'.$i];

        if ( strlen($year) == 0 || strlen($school) == 0 ) {
            $_SESSION['error'] = "All fields are required";
            return false; 
        }

        if ( ! is_numeric($year) ) {
            $_SESSION['error'] = "Position year must be numeric";
            return false; 
        }
    }
    return true;
}

// Function to SELECT educations for a given profile_id
function loadEdu($pdo, $profile_id) {
    $stmt = $pdo->prepare('SELECT * FROM Education JOIN Institution ON Education.institution_id = Institution.institution_id WHERE profile_id = :prof ORDER BY rank');
    $stmt->execute(array(':prof' => $profile_id));
    $educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $educations; 
}

// Function to INSERT the position entries
function insertEdu($pdo, $profile_id) {
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {

        if (! isset($_POST['edu_year' . $i])) continue;
        if (! isset($_POST['edu_school' . $i])) continue;

        $year = $_POST['edu_year' . $i];
        $school = $_POST['edu_school' . $i];

        // Check if the institution (school) already exists in the DB
        $institution_id = false;
        $stmt = $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :name');
        $stmt->execute(array(':name' => $school));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) $institution_id = $row['institution_id'];

        // If the school doesn't already exist, INSERT new institution in the DB.
        if ($institution_id === false) {
            $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:name)');
            $stmt->execute(array(':name' => $school));
            $institution_id = $pdo->lastInsertId();
        }

        // Insert the education data
        $stmt = $pdo->prepare('INSERT INTO Education
                (profile_id, rank, year, institution_id)
                VALUES ( :pid, :rank, :year, :iid)');

        $stmt->execute(
            array(
                ':pid' => $profile_id,
                ':rank' => $rank,
                ':year' => $year,
                ':iid' => $institution_id
            )
        );
        $rank++;
    }
    $_SESSION['success'] = "Record edited";
}
?>