<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'employer') {
    header("Location: index.php");
    exit();
}

$job_id = isset($_GET['job_id']) ? $_GET['job_id'] : null;
$job = getJobById($pdo, $job_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editJobBtn'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $salary = trim($_POST['salary']);

    $updateSuccess = updateJob($pdo, $job_id, $title, $description, $salary);

    if ($updateSuccess) {
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Failed to update job posting. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Hire</title>
    <link rel="stylesheet" href="styles\jobdeets.css">
    <script src="https://kit.fontawesome.com/cd3bff5ff2.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <img src="assets\findhirelogowhite.png" alt="">
            <div class="logoutbtn">
                <button class="home" onclick="location.href='index.php';" class="icon-button">
                    <i class="fa-solid fa-house"></i>
                </button>
                <form action="core/handleForms.php" method="POST">
                    <button type="submit" name="logout" class="logout"><i class="fa-solid fa-right-from-bracket"></i></button>
                </form>
            </div>
        </div>
        <div class="content">
            <h2>Edit Job Posting</h2>
            <div class="form">
                <form action="editpost.php?job_id=<?php echo $job_id; ?>" method="POST">
                    <p><strong>Title:</strong> <input type="text" name="title" value="<?php echo htmlspecialchars($job['title']); ?>" required></p>
                    <p><strong>Description:</strong> <textarea name="description" required><?php echo htmlspecialchars($job['description']); ?></textarea></p>
                    <p><strong>Salary:</strong><input type="text" name="salary" value="<?php echo htmlspecialchars($job['salary']); ?>" required></p>
                    <button type="submit" name="editJobBtn" class="editJobBtn">Update Job</button>
                </form>
                </div>
                <?php if (isset($error_message)) echo "<p class='msg-alert'>$error_message</p>"; ?>
        </div>
</div>
</body>
</html>
