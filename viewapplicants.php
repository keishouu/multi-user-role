<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$job_id = isset($_GET['job_id']) ? $_GET['job_id'] : null;

if (empty($job_id)) {
    header("Location: index.php");
    exit();
}

// Proceed with your logic for fetching job details based on $job_id
$job = getJobById($pdo, $job_id);

// Fetch all applicants for the specified job ID
$applicants = getApplicants($pdo, $job_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = isset($_POST['application_id']) ? $_POST['application_id'] : null;
    $action = isset($_POST['action']) ? $_POST['action'] : null;

    if ($application_id && $action) {
        $application = getApplicationById($pdo, $application_id);

        if ($application) {
            $job = getJobById($pdo, $application['job_id']);
            $user_id = $application['user_id'];

            if ($action === 'accept') {
                updateApplicationStatus($pdo, $application_id, 'accepted');
                addNotification($pdo, $user_id, "Your application for '{$job['title']}' has been accepted.", 'accepted');
            } elseif ($action === 'reject') {
                deleteApplication($pdo, $application_id);
                addNotification($pdo, $user_id, "Your application for '{$job['title']}' has been rejected.", 'rejected');
            }
            
        }
    }

    // Redirect to prevent form resubmission
    header("Location: viewapplicants.php?job_id=" . $application['job_id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applicants</title>
    <link rel="stylesheet" href="styles\applicant.css">
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
    <h1>Applicants for Job: "<?php echo htmlspecialchars($job['title']); ?>"</h1>
    <div class="applicants">
        <?php if (!empty($applicants) && is_array($applicants)): ?>
            <ul style="padding: 0 3vw">
                <?php foreach ($applicants as $applicant): ?>
                    <li class="applicant-item">
                        <div class="applicant-details">
                            <strong>Username:</strong> <?php echo htmlspecialchars($applicant['username']); ?><br>
                            <strong>Name:</strong> <?php echo htmlspecialchars($applicant['first_name']) . ' ' . htmlspecialchars($applicant['last_name']); ?><br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($applicant['email']); ?><br>
                            <strong>Status:</strong> <?php echo htmlspecialchars($applicant['status']); ?><br>
                        </div>
                        <div class="buttons">
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="application_id" value="<?php echo $applicant['application_id']; ?>">
                                <input type="hidden" name="action" value="accept">
                                <button class="acceptbtn" type="submit">Accept</button>
                            </form>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="application_id" value="<?php echo $applicant['application_id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button class="rejectbtn" type="submit">Reject</button>
                            </form>
                        </div>
                    </li>
                    <hr>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No applicants found for this job.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
