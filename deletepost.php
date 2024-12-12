<?php
require_once 'core/models.php';
require_once 'core/dbConfig.php';

// Ensure the user is logged in and is an employer
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'employer') {
    header("Location: login.php");
    exit();
}

// Check if job_id is provided
if (!isset($_GET['job_id']) || empty($_GET['job_id'])) {
    echo "Invalid job ID.";
    exit();
}

$job_id = $_GET['job_id'];

// Fetch the job details
$job = getJobById($pdo, $job_id);

if (!$job) {
    echo "Job not found.";
    exit();
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
                <button class="home" onclick="location.href='index.php';">
                    <i class="fa-solid fa-house"></i>
                </button>
                <form action="core/handleForms.php" method="POST">
                    <button type="submit" name="logout" class="logout"><i class="fa-solid fa-right-from-bracket"></i></button>
                </form>
            </div>
        </div>
        <div class="content">
    <h2>Delete Job Post</h2>
    <div class="form">
    <p>Are you sure you want to delete the following job post?</p>
    <p><strong>Title:</strong> <?php echo htmlspecialchars($job['title']); ?></p>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($job['description']); ?></p>
    <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
    <div class="btns">
        <form method="POST" action="core/handleforms.php">
            <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job_id); ?>">
            <button type="submit" name="deleteJobBtn" class="delBtn">Delete Job</button>
        </form>
        <button onclick="location.href='index.php';" class="cancelBtn">cancel</i></button>
    </div>
    </div>
</div>
</div>
</body>
</html>
