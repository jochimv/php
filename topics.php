<?php

require_once 'utils/user.php';

$topicAlreadyExists = false;
$topicAddedSuccessfully = false;
$topicDeletedSuccessfully = false;
$topicArchivedSuccessfully = false;
if (!empty($_POST)) {
    if (!empty($_POST['topic'])) {
        $packagesWithoutNoteQuery = $db->prepare('SELECT * FROM Topic WHERE name=:name AND User_id=:user_id LIMIT 1;');
        $packagesWithoutNoteQuery->execute([
            ':name' => $_POST['topic'],
            ':user_id' => $_SESSION['user_id']
        ]);
        if ($packagesWithoutNoteQuery->rowCount() > 0) {
            $topicAlreadyExists = true;
        } else {

            $saveTopicQuery = $db->prepare('INSERT INTO Topic (name,archived,User_id) VALUES (:name,FALSE,:user_id);');
            $saveTopicQuery->execute([
                ':name' => $_POST["topic"],
                ':user_id' => $_SESSION['user_id']
            ]);
            $topicAddedSuccessfully = true;
        }
    } elseif (isset($_POST['delete'])) {
        $postedId = $_POST['id'];
        $verifyTopicOwnershipQuery = $db->prepare('SELECT * FROM Topic WHERE id=:id AND User_id=:user_id LIMIT 1;');
        $verifyTopicOwnershipQuery->execute([
            ':id' => $postedId,
            ':user_id' => $_SESSION['user_id']
        ]);
        if ($verifyTopicOwnershipQuery->rowCount() == 1) {

            $deleteTopicQuery = "DELETE FROM Topic WHERE id=?";
            $deleteTopicStmt = $db->prepare($deleteTopicQuery);
            $deleteTopicStmt->execute([$postedId]);


            $db->exec("DELETE FROM Note WHERE id NOT IN (SELECT Note_id FROM Topic_Note)");

            $topicDeletedSuccessfully = true;
        }


    } elseif (isset($_POST['archive'])) {
        $postedId = $_POST['id'];
        $verifyTopicOwnershipQuery = $db->prepare('SELECT * FROM Topic WHERE id=:id AND User_id=:user_id LIMIT 1;');
        $verifyTopicOwnershipQuery->execute([
            ':id' => $postedId,
            ':user_id' => $_SESSION['user_id']
        ]);
        if ($verifyTopicOwnershipQuery->rowCount() == 1) {
            $archiveTopicQuery = "UPDATE Topic SET archived=TRUE WHERE id=?";
            $archiveTopicStmt = $db->prepare($archiveTopicQuery);
            $archiveTopicStmt->execute([$postedId]);
            $topicArchivedSuccessfully = true;
        }
    }
}

$getTopicsQuery = $db->prepare('SELECT * FROM Topic WHERE User_id=:user_id AND archived = FALSE;');
$getTopicsQuery->execute([
    ':user_id' => $_SESSION['user_id']
]);

$topics = $getTopicsQuery->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/inner.css">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">

</head>

<body>

<nav class="navbar navbar-expand-sm navbar-dark bg-primary ms-auto">
    <div class="navbar-brand max-50"><?= htmlspecialchars($_SESSION['user_email']) ?></div>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav ms-auto me-5">
            <a class="nav-item nav-link active" href="#">Topics</a>
            <a class="nav-item nav-link" href="./archived.php">Archived topics</a>
            <a class="nav-item nav-link" href="./account.php">Account</a>
            <a class="nav-item nav-link" href="./logout.php">Log out</a>
        </div>
    </div>
</nav>

<main class="content">
    <form method="post" class="my-3">
        <div class="row align-items-center justify-content-center">
            <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                <input type="text" id="text" name="topic" maxlength="255" minlength="1"
                       class="form-control form-control-lg "
                       placeholder="Add a new topic" <?php if ($topicAlreadyExists) {
                    echo 'value="' . $_POST['topic'] . '"';
                } ?>
                />
            </div>
            <div class="col-1">
                <button class="btn btn-primary btn-sm btn-padded" id="add">Add</button>
            </div>
        </div>
    </form>
    <?php
    if ($topicAlreadyExists) {
        echo '
                <div class="col-12 pb-3">
                        <p class="text-center text-danger h5 w-100 my-auto break-word" >Topic ' . htmlspecialchars($_POST['topic']) . ' already exists!</p>
                </div>';
    } elseif ($topicAddedSuccessfully) {
        echo '
                <div class="col-12 pb-3">
                        <p class="text-center text-success h5 w-100 my-auto break-word" >Topic ' . htmlspecialchars($_POST['topic']) . ' added successfully!</p>
                </div>';
    } elseif ($topicDeletedSuccessfully) {
        echo '
                <div class="col-12 pb-3">
                        <p class="text-center text-success h5 w-100 my-auto" >Topic deleted successfully!</p>
                </div>';
    } elseif ($topicArchivedSuccessfully) {
        echo '
                <div class="col-12 pb-3">
                        <p class="text-center text-success h5 w-100 my-auto" >Topic archived successfully!</p>
                </div>';
    } else {
        echo ' <div class="col-12 pb-3">
                        <p class="text-center h5 w-100 my-auto" >&nbsp;</p>
                </div>
                ';
    }
    ?>

    <?php
    if (empty($topics)) {
        echo "
<div class='row my-3 d-flex flex-row align-items-center justify-content-center'> 
<div class='col-12 my-auto'><p class='h2 text-center'>No topics there buddy</p>
</div>
</div>";

    } else {
        foreach ($topics as $topic) {
            echo "
<div class='d-flex flex-row align-items-center justify-content-center'><p class='h4 break-word'>" . htmlspecialchars($topic['name']) . "</p></div>
<form class='row my-3 d-flex'  method='post'>
    <input type='hidden' name='id' value='" . htmlspecialchars($topic['id']) . "'>
    <div class='col-3 d-flex flex-row align-items-center justify-content-center'><a class='btn btn-secondary btn-padded' href='./notes?topic=" . htmlspecialchars($topic['name']) . "'>Notes</a></div>
    <div class='col-3 d-flex flex-row align-items-center justify-content-center'><a href='./flashcards?topic=" . htmlspecialchars($topic['name']) . "' class='btn btn-success btn-padded'>Flashcards</a></div>
    <div class='col-3 d-flex flex-row align-items-center justify-content-center'><button type='submit' name='archive' class='btn btn-info btn-padded'>Archive</button></div>
    <div class='col-3 d-flex flex-row align-items-center justify-content-center'><button type='submit' name='delete' class='btn btn-danger btn-padded'>Delete</button></div>
</form>    
";
        }
    }
    ?>


</main>
</body>

</html>