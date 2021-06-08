<?php // Do not put any HTML above this line
require_once "pdo.php";
require_once "util.php";
require_once "head.php";

session_start();

if (!isset($_SESSION['user_id'])) {    // Demand a GET parameter
    die('ACCESS DENIED');
}

if (!isset($_REQUEST['profile_id'])) {  // make sure the request parameter is present
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

if (isset($_POST['cancel'])) {
    header("Location: index.php");  // Redirect the browser to index.php
    return;
}

// load up the profile in question
$stmt = $pdo->prepare('SELECT * FROM Profile WHERE profile_id = :pid AND user_id = :uid');
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

//handle the incoming post data
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) && isset($_POST['save'])) {

    $msg = validateProfile();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST['profile_id']);
        return;
    }
    // validate position entries if present
    $msg = validatePos();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST['profile_id']);
        return;
    }
    // Begin to update the data
    $stmt = $pdo->prepare('UPDATE Profile SET first_name=:fn, last_name=:ln, email=:em, headline=:he, summary=:su WHERE profile_id=:pid AND user_id=:uid');
    $stmt->execute(array(
        ':pid' => $_REQUEST['profile_id'],
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary']
    ));

    // clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
    $stmt->execute(array(
        ':pid' => $_REQUEST['profile_id']
    ));

    /*   $stmt = $pdo->prepare('INSERT INTO Profile
      (user_id, first_name, last_name, email, headline, summary) VALUES ( :uid, :fn, :ln, :em, :he, :su)');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
     ':su' => $_POST['summary']
       ));  
    $profile_id = $pdo->prepare("SELECT * FROM Position where profile_id= :pid");
    $profile_id->execute(array(
        ":pid" => $_REQUEST['profile_id']
   ));
*/
    // TODO: Should validate education


    // insert the position entries
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['year' . $i])) continue;
        if (!isset($_POST['desc' . $i])) continue;

        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];

        $stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
        $stmt->execute(array(':pid' => $_REQUEST['profile_id'], ':rank' => $rank, ':year' => $year, ':desc' => $desc));
        $rank++;
    }

    $_SESSION['success'] = "Profile Updated";
    header("Location: index.php");
    return;


    // Clear out the old education entries
    $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
    $stmt->execute(array(
        ':pid' => $_REQUEST['profile_id']
    ));

    // insert the education entries 
    insertEducations($pdo, $_REQUEST['profile_id']);

    $_SESSION['success'] = "Profile Updated";
    header("Location: index.php");
    return;
}
// load up the position & education rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Zachary Koepsell 's Profile Edit</title>
    <?php require_once "head.php"; ?>
</head>
<h1>Confirm Editing Profile for: <?= htmlentities($_SESSION['name']) ?></h1>
<?php
flashMessages();
?>

<body>
    <div class="container">
        <form method="POST" action="edit.php">

            <p>First Name:
                <input type="text" name="first_name" value="<?= htmlentities($profile['first_name']); ?>" size="60">
            </p>
            <p>Last Name:
                <input type="text" name="last_name" value="<?= htmlentities($profile['last_name']); ?>" size="60">
            </p>
            <p>Email:
                <input type="text" name="email" value="<?= htmlentities($profile['email']); ?>" size="30">
            </p>
            <p>Headline:
                <input type="year" name="headline" value="<?= htmlentities($profile['headline']); ?>" size="80">
            </p>
            <p>Summary:
                <input name="summary" value="<?= htmlentities($profile['summary']); ?>" rows="8" cols="80"></input>
            </p>

            <div id="position_fields"></div>
            <?php
            $countEdu = 0;

            echo ('<p>Education: <input type="submit" id="addEdu" value="+">' . "\n");
            echo ('<div id="edu_fields">' . "\n");
            if (count($schools) > 0) {
                foreach ($schools as $school) {
                    $countEdu++;
                    echo ('<div id="edu' . $countEdu . '">');
                    echo '<p>Year: <input type="text" name="edu_year' . $countEdu . '" value="' . $school['year'] . '" /><input type="button" value="-" onclick="$(\'#edu' . $countEdu . '\').remove();return false;"></p>
                    <p>School: <input type="text" size="80" name="edu_school' . $countEdu . '" class="school" value="' . htmlentities($school['name']) . '" />';
                    echo "\n</div>\n";
                }
            }
            echo ("</div></p>");

            $pos = 0;
            echo ('<p>Position: <input type="submit" id="addPos" value="+">' . "\n");
            echo ('<div id="position_fields">' . "\n");
            foreach ($positions as $position) {
                $pos++;
                echo ('<div id="position' . $pos . '">' . "\n");
                echo ('<p>Year: <input type="text" name="Year' . $pos . '"');
                echo (' value="' . $position['year'] . '" />' . "\n");
                echo ('<input type="button" value="-" ');
                echo ('onclick="$(\'#position' . $pos . '" rows="8" cols="80">' . "\n");
                echo ("</p>\n");
                echo ('<textarea name="desc' . $pos . '" rows="8" cols="80">' . "\n");
                echo (htmlentities($position['description']) . "\n");
                echo ("\n</textarea>\n</div>\n");
            }
            echo ("</div><p>\n");
            ?>

            <input type="hidden" name="profile_id" value="<?= htmlentities($_REQUEST['profile_id']) ?>">
            <input type="submit" value="Save" name="save">
            <input type="submit" name="cancel" value="Cancel">

            <script>
                countPos = <?= $pos ?>;
                countEdu = <?= $countEdu ?>;
                // http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
                $(document).ready(function() {
                    window.console && console.log('Document ready called');
                    $('#addPos').click(function(event) {
                        // http://api.jquery.com/event.preventdefault/
                        event.preventDefault();
                        if (countPos >= 9) {
                            alert("Maximum of nine position entries exceeded");
                            return;
                        }
                        countPos++;
                        window.console && console.log("Adding position " + countPos);

                        $('#position_fields').append(
                            '<div id="position' + countPos + '"> \
            <p>Year: <input type="text" name="year' + countPos + '" value="" /> \
            <input type="button" value="-" \
            onclick="$(\'#position' + countPos + '\').remove(); return false;"></p> \
            <textarea name="desc' + countPos + '" rows="8" cols="80"></textarea>\
            </div>');
                    });

                    $('#addEdu').click(function(event) {
                        event.preventDefault();
                        if (countPos >= 9) {
                            alert("Maximum of nine education entries exceeded");
                            return;
                        }
                        countEdu++;
                        window.console && console.log("Adding education " + countEdu);

                        // grab some HTML with hot spots and insert into the DOM
                        var source = $("#edu-template").html();
                        $('#edu_fields').append(source.replace(/@COUNT@/, countEdu));

                        // Add the even handler to the new ones
                        $('.school').autocomplete({
                            source: "School.php"
                        });
                    })
                    $('.school').autocomplete({
                        source: "school.php"
                    });
                });
            </script>

        </form>
    </div>
</body>

</html>