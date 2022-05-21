<?php
require_once '../utils/user.php';
require_once '../utils/functions.php';
$topicId = getTopicId();

$deletedSuccessfully = false;
if (isset($_POST['delete'])) {
    $flashcardId = $_POST['id'];
    $checkQuery = $db->prepare('SELECT * FROM Flashcard INNER JOIN Topic ON Flashcard.Topic_id = Topic.id WHERE Flashcard.id=:id AND User_id=:user_id LIMIT 1;');
    $checkQuery->execute([
        ':id' => $flashcardId,
        ':user_id' => $_SESSION['user_id']
    ]);
    if ($checkQuery->rowCount() == 1) {
        $sql = "DELETE FROM Flashcard WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$flashcardId]);
        $deletedSuccessfully = true;
    }
}


$flashcardQuery = $db->prepare('SELECT * FROM Flashcard WHERE Topic_id=:id;');
$flashcardQuery->execute([
    ':id' => $topicId
]);

$flashcards = $flashcardQuery->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/inner.css">
</head>

<body>

<nav class="navbar navbar-expand-sm navbar-dark bg-primary ms-auto">
    <div class="navbar-brand max-50"><?= htmlspecialchars($_SESSION['user_email']) ?></div>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav ms-auto me-5">
            <a class="nav-item nav-link" href="../topics.php">Topics</a>
            <a class="nav-item nav-link" href="../archived.php">Archived topics</a>
            <a class="nav-item nav-link" href="../account.php">Account</a>
            <a class="nav-item nav-link" href="../logout.php">Log out</a>
        </div>
    </div>
</nav>

<main class="content">
    <div class="d-flex flex-row align-items-center justify-content-center">
        <div class="col-4 d-flex align-items-center justify-content-center ">
            <div class="row text-center h5 my-3"><?= htmlspecialchars($_GET['topic']) ?> - manage flashcards</div>
        </div>
        <div class="col-4 d-flex align-items-center justify-content-center ">
            <div class="row text-center text-success h5 my-3"><?php echo $deletedSuccessfully ? 'Flashcard deleted successfully!' : '&nbsp;' ?></div>
        </div>
        <div class="col-4 d-flex align-items-center justify-content-center ">
            <a class='btn btn-secondary btn-padded' href="./index.php?topic=<?= htmlspecialchars($_GET['topic']) ?>">Back</a>
        </div>
    </div>


    <?php
    if (empty($flashcards)) {
        echo "
<div class='row my-3 d-flex flex-row align-items-center justify-content-center'> 
<div class='col-12 my-auto'><p class='h2 text-center'>No flashcards found</p>
</div>
</div>";

    } else {
        foreach ($flashcards as $flashcard) {
            echo "
<form class='row my-3 d-flex' method='post' action='' >
    <input type='hidden' name='id' value='" . htmlspecialchars($flashcard['id']) . "' readonly>
    <div class='col my-auto text-fit' ><p class='h5 text-center text-wrap mw-40'>" . htmlspecialchars($flashcard['question']) . "</p></div>
    <div class='col my-auto text-fit'><p class='h5 text-center text-wrap'>" . htmlspecialchars($flashcard['answer']) . "</p></div>
    <div class='col-2'><a type='button' class='btn btn-info btn-padded' href='./adjust.php?id=" . htmlspecialchars($flashcard['id']) . "' >Adjust</a></div>
     <div class='col-2'><button type='submit' name='delete' class='btn btn-danger btn-padded'>Delete</button></div>
</form>    
";
        }
    }
    ?>
</main>
</body>

</html>