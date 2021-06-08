<?php
require_once "pdo.php";
require_once "util.php";
require_once "head.php";

session_start();

// If the user requested logout go back to index.php
if (isset($_POST['done'])) {
    header('Location: index.php');
    return;
}

// load up the profile in question
$stmt = $pdo->prepare('SELECT * FROM Profile WHERE profile_id = :pid AND user_id= :uid');
$stmt->execute(array(
    ':pid' => $_REQUEST['profile_id'],
    ':uid' => $_SESSION['user_id']
));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ($profile === false) {
    $_SESSION['error'] = "Could not load profile edit";
    header("Location: index.php");
    return;
}

// load up the position rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$educations = loadEdu($pdo, $_REQUEST['profile_id']);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Zachary Koepsell </title>
    <?php require_once "head.php"; ?>
</head>

<body>
    <div class="container">
        <h1>Profile Information</h1>
        <p>
            First Name: <value><?= $profile['first_name'] ?></value>
        </p>
        <p>
            Last Name: <value><?= $profile['last_name'] ?></value>
        </p>
        <p>
            Email: <value><?= $profile['email'] ?></value>
        </p>
        <p>
            Headline: <value><?= $profile['headline'] ?></value>
        </p>
        <p>
            Summary: <value><?= $profile['summary'] ?></value>
        </p>
        <p>
            Education: <value>
                <?php
                echo ('<div id="education_fields">' . "\n");
                foreach ($educations as $education) {
                    echo ('<p>Rank: ');
                    echo ('<value name="rank">' . "\n");
                    echo (htmlentities($education['rank']) . "\n");
                    echo ("\n</value></p>\n</div>\n");
                    echo ('<p>Year: ');
                    echo ('<value name="year">' . "\n");
                    echo (htmlentities($education['year']) . "\n");
                    echo ("\n</value></p>\n</div>\n");
                    echo ('<p>School: ');
                    echo ('<value name="desc" rows="8" cols="80">' . "\n");
                    echo (htmlentities($education['school']) . "\n");
                    echo ("\n</value></p>\n</div>\n");
                }
                echo ("</div><p>\n");
                ?>
            </value>
        </p>
        <p>
            Position: <value></value>
        </p>
        <div id="position_fields">
            <?php
            echo ('<div id="position_fields">' . "\n");
            foreach ($positions as $position) {
                echo ('<p>Rank: ');
                echo ('<value name="rank">' . "\n");
                echo (htmlentities($position['rank']) . "\n");
                echo ("\n</value></p>\n</div>\n");
                echo ('<p>Year: ');
                echo ('<value name="year">' . "\n");
                echo (htmlentities($position['year']) . "\n");
                echo ("\n</value></p>\n</div>\n");
                echo ('<p>Description: ');
                echo ('<value name="desc" rows="8" cols="80">' . "\n");
                echo (htmlentities($position['description']) . "\n");
                echo ("\n</value></p>\n</div>\n");
            }
            echo ("</div><p>\n");
            ?>

            <a href="index.php?name=" method="POST" name="done">Done</a>
            </p>
        </div>
</body>

</html>