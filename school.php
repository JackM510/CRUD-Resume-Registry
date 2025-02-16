 <?php
    // school.php fetches the prefix for the autocomplete functionality for the 'School' inputs

    session_start();
     if ( ! isset($_SESSION['name']) ||  ! isset($_SESSION['user_id']) ) {
        echo "Must be logged in";
        exit();
    } else if ( ! isset($_REQUEST['term'])) {
        echo "Missing required parameter";
        exit();
    }

    require_once("pdo.php");
    header('Content-Type: application/json; charset=utf-8');

    $stmt = $pdo->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
    $stmt->execute(array( ':prefix' => $_REQUEST['term']."%"));
    $retval = array();
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    $retval[] = $row['name'];
    }
    echo(json_encode($retval, JSON_PRETTY_PRINT));
?>