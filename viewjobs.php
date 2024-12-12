<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

$jobs = getJobs($pdo);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Jobs</title>
</head>
<body>
    <h1>Available Jobs</h1>

    <ul>
        <?php foreach ($jobs as $job): ?>
            <li>
                <strong><?php echo htmlspecialchars($job['title']); ?></strong><br>
                <?php echo htmlspecialchars($job['description']); ?><br>
                <a href="applyjob.php?job_id=<?php echo $job['job_id']; ?>">Apply</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <a href="index.php">Back to Dashboard</a>
</body>
</html>
