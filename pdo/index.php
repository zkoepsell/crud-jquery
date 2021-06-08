<?php
require_once "pdo.php";
require_once "util.php";
require_once "head.php";

session_start();

// If the user requested logout go back to index.php
if (isset($_POST['logout'])) {
    session_start();
    unset($_SESSION['name']);
    session_destroy();
    header('Location: logout.php');
    return;
}
$stmt = $pdo->query("SELECT * FROM Profile");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Zachary Koepsell 's Registry Database</title>

</head>

<body>
    <div class="container">
        <h2>Welcome to the Resume Registry</h2>
        <p>
            <?php require_once "head.php";

            if (!isset($_SESSION['name'])) {
                echo ("<p><a href='login.php'>Please log in</a></p>");
                echo ("<p> Attempt to <a href='add.php'>add data</a> without logging in</p>");
            }

            if (isset($_SESSION['name'])) {
                echo("<p><a href='add.php?name='>Add New Entry</a></p>");
                echo ("<p><a href='logout.php' name='logout'>Logout</a></p>");
            }

            flashMessages();

                foreach ($rows as $row) {
                    echo ('<table border="2">' . "\n");
                    echo "<th>Name<td><b>Headline</td><td><b>profile_id</td><td><b>Action</td></b></th><tr><td>";
                    echo '<a href="view.php?profile_id=' . $row['profile_id'] . '" >'; // HERE*****
                    echo (htmlentities($row['first_name'] . " " . $row['last_name']));
                    echo "</a>";
                    echo ("</td><td> ");
                    echo (htmlentities($row['headline']));
                    echo ("</td><td> ");
                    echo (htmlentities($row['profile_id']));
                    echo ("</td><td> ");
                    echo ('<form method="post"><input type="hidden" name="profile_id" value="');
                    echo ('' . $row['profile_id'] . '">' . "\n");
                    echo ('<a href="edit.php?profile_id=' . $row['profile_id'] . '" >Edit </a> / ');
                    echo ('<a href="delete.php?profile_id=' . $row['profile_id'] . '">Delete</a>');
                    // delete button here
                    echo ("\n</form>\n");
                    echo ("</td>\n");
                }
                if (!isset($row)) {
                    echo ("No rows found");
                }
               // echo ("<p><a href='add.php'>Add a new resume</a></p>");
                

            ?>
        </p>
    </div>
</body>