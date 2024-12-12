<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['user_role']; // Get the user role from session

if ($user_role === 'employer') {
    $employer_username = $_SESSION['username'];
    $jobs = getJobs($pdo, $employer_username); // Pass the employer's username to the function
} else {
    $jobs = getJobs($pdo); // Retrieve all job listings for employees
}

// Corrected to pass the PDO instance and user_id to getNotificationsByUserId only once
if (!isset($notifications)) {
    $notifications = getNotificationsByUserId($pdo, $_SESSION['user_id']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Hire</title>
    <link rel="stylesheet" href="styles\style.css">
    <script src="https://kit.fontawesome.com/cd3bff5ff2.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <img src="assets\findhirelogowhite.png" alt="">
            <div class="logoutbtn">
                <form action="core/handleForms.php" method="POST">
                    <button type="submit" name="logout"><i class="fa-solid fa-right-from-bracket"></i></button>
                </form>
            </div>
        </div>
        <div class="content">
            <?php if ($user_role === 'employee'): ?>
                <h3 style="font-size: 4vh; margin-left: 1vw;">Job Listings</h3>
                <div class="jobposts">
    <?php if (!empty($jobs)): ?>
        <ul>
            <?php foreach ($jobs as $job): ?>
                <li>
                    <div class="job-info">
                        <div class="titlendate">
                            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                            <p><?php echo date("F j, Y", strtotime($job['date_posted'])); ?></p>
                        </div>
                        <p style="font-size: 3vh;"><?php echo htmlspecialchars($job['description']); ?></p>

                        <div class="hrnbtn">
                            <div class="sidedetails">
                                <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                                <p><strong>HR:</strong> <?php echo htmlspecialchars($job['employer_username']); ?></p>
                            </div>
                            <form action="core/handleForms.php" method="POST">
                                <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                <input type="hidden" name="applicant_username" value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>">
                                <input type="hidden" name="applicant_name" value="<?php echo isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : ''; ?> <?php echo isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name']) : ''; ?>">
                                <input type="hidden" name="applicant_email" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>">
                                <button type="submit" name="applyJobBtn">Apply</button>
                            </form>
                        </div>

                        <?php if (isset($_SESSION['messages'][$job['job_id']])): ?>
                            <div class="job-message" style="margin-top: 10px; padding: 10px; border: 1px solid <?php echo $_SESSION['messages'][$job['job_id']]['status'] === '200' ? 'green' : 'red'; ?>; background-color: <?php echo $_SESSION['messages'][$job['job_id']]['status'] === '200' ? '#e6ffe6' : '#ffe6e6'; ?>;">
                                <?php echo htmlspecialchars($_SESSION['messages'][$job['job_id']]['message']); ?>
                            </div>
                            <?php unset($_SESSION['messages'][$job['job_id']]); ?>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p style="margin-left: 2vw;">No Available jobs at the moment.</p>
    <?php endif; ?>
</div>


                <div class="notifnmess">
                    <div class="messagecontainer">
                        <?php
                            $users = getAllUsers($pdo, $_SESSION['username']);
                            $recipient_username = isset($_GET['recipient']) ? $_GET['recipient'] : '';
                            $messages = getMessages($pdo, $_SESSION['username'], $recipient_username);

                            if (!empty($messages)) {
                                // Reverse the order of messages to show the latest at the bottom
                                $messages = array_reverse($messages);
                        }
                        ?>

                        <h2 class="messages-header">Messages with <?php echo htmlspecialchars($recipient_username); ?></h2>

                        <form action="index.php" method="GET">
                            <label for="recipient">Select User:</label>
                            <select name="recipient" id="recipient">
                                <option value="" disabled <?php echo empty($recipient_username) ? 'selected' : ''; ?>>Choose a user</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo htmlspecialchars($user); ?>" <?php echo $recipient_username === $user ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Start Chat</button>
                        </form>

                        <?php if (!empty($messages)): ?>
                            <div class="messagehistory">
                            <ul style="list-style-type: none; padding: 0;">
                                <?php foreach ($messages as $message): ?>
                                    <li class="<?php echo $message['sender_username'] === $_SESSION['username'] ? 'message-sender' : 'message-receiver'; ?>">
                                        <p style="margin: 0;"><?php echo htmlspecialchars($message['message_text']); ?></p>
                                        <span style="font-size: 1.5vh;"><?php echo htmlspecialchars($message['timestamp']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            </div>
                            <?php else: ?>
                                <p>No messages found.</p>
                            <?php endif; ?>
                            

                        <form class="inputmsg" action="core/handleForms.php" method="POST">
                            <input type="hidden" name="recipient_username" value="<?php echo htmlspecialchars($recipient_username); ?>">
                            <textarea name="message_text" id="autoResizeTextarea" placeholder="Type your message..."></textarea>
                            <button type="submit" name="sendMessageBtn"><i class="fa-solid fa-circle-arrow-right"></i></button>
                        </form>
                    </div>
                    <div class="notifications">
                        <h2 class="notification-header">Notifications</h2>
                        <div class="notifinside">
                            <?php if (!empty($notifications)): ?>
                                <ul>
                                <?php foreach ($notifications as $notification): ?>
                                    <li class="<?php echo htmlspecialchars($notification['type']); ?>">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No notifications.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            <?php elseif ($user_role === 'employer'): ?>
                <div class="employercontent">
                    <div class="listing">
                        <form action="core/handleForms.php" method="POST">
                            <h3 class="create-header" style="font-size: 4vh;">Create a Job Posting</h3>
                            <p><strong>Job Title:</strong>
                            <input type="text" name="title" placeholder="Job Title" required></p>
                            <p><strong>Job Description:</strong><textarea name="description" placeholder="Job Description" required></textarea></p>
                            <p><strong>Salary:</strong><input type="text" name="salary" placeholder="Salary Range" required></p>
                            <button type="submit" name="postJobBtn">Post Job</button>
                        </form>
                        <div class="joblists">
            <h3 class="post-header" style="font-size: 4vh;">Your Job Listings</h3>
            <ul style="list-style-type: none; padding: 0;">
                <?php foreach ($jobs as $job): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($job['title']); ?></strong><br>
                        <?php echo htmlspecialchars($job['description']); ?><br>
                        <?php echo htmlspecialchars($job['salary']); ?><br>
                        <div class="action-buttons">
                            <a href="viewapplicants.php?job_id=<?php echo $job['job_id']; ?>" class="button view">View Applicants</a>
                            <a style="margin-left:17vw;" href="editpost.php?job_id=<?php echo $job['job_id']; ?>" class="button">Edit</a>
                            <a href="deletepost.php?job_id=<?php echo $job['job_id']; ?>" class="button delete-btn">Delete</a>
                        </div>

                        <!-- Show Accepted Applicants -->
                        <div class="accepted-applicants">
    <h4>Accepted Applicants</h4>
    <ul>
        <?php
            // Fetch accepted applicants for the job
            $acceptedApplicants = getAcceptedApplicants($pdo, $job['job_id']);
            if (!empty($acceptedApplicants)) {
                foreach ($acceptedApplicants as $acceptedApplicant):
        ?>
            <li>
                <?php echo htmlspecialchars($acceptedApplicant['first_name']) . ' ' . htmlspecialchars($acceptedApplicant['last_name']); ?> (<?php echo htmlspecialchars($acceptedApplicant['username']); ?>)
            </li>
        <?php
                endforeach;
            } else {
                echo "<li>No accepted applicants yet.</li>";
            }
        ?>
    </ul>
</div>
                        <hr style="margin-top:2vh;">
                    </li>
                <?php endforeach; ?>
                <?php if (empty($jobs)): ?>
                    <p>You have no Jobs posted yet.</p>
                <?php endif; ?>
            </ul>
                </div>
                </div>
                    <div class="messagecontainer" style="margin: 2vh 0;">
                    <?php
                        $users = getAllUsers($pdo, $_SESSION['username']);
                        $recipient_username = isset($_GET['recipient']) ? $_GET['recipient'] : '';
                        $messages = getMessages($pdo, $_SESSION['username'], $recipient_username);

                        if (!empty($messages)) {
                            // Reverse the order of messages to show the latest at the bottom
                            $messages = array_reverse($messages);
                        }
                        ?>

                        <h2 class="messages-header">Messages with <?php echo htmlspecialchars($recipient_username); ?></h2>

                        <form action="index.php" method="GET">
                            <label for="recipient">Select User:</label>
                            <select name="recipient" id="recipient">
                                <option value="" disabled <?php echo empty($recipient_username) ? 'selected' : ''; ?>>Choose a user</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo htmlspecialchars($user); ?>" <?php echo $recipient_username === $user ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Start Chat</button>
                        </form>

                        <?php if (!empty($messages)): ?>
                            <div class="messagehistory">
                            <ul style="list-style-type: none; padding: 0;">
                                <?php foreach ($messages as $message): ?>
                                    <li class="<?php echo $message['sender_username'] === $_SESSION['username'] ? 'message-sender' : 'message-receiver'; ?>">
                                        <p style="margin: 0;"><?php echo htmlspecialchars($message['message_text']); ?></p>
                                        <span style="font-size: 1.5vh;"><?php echo htmlspecialchars($message['timestamp']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            </div>
                        <?php else: ?>
                            <p>No messages found.</p>
                        <?php endif; ?>
                        

                        <form class="inputmsg" action="core/handleForms.php" method="POST">
                            <input type="hidden" name="recipient_username" value="<?php echo htmlspecialchars($recipient_username); ?>">
                            <textarea name="message_text" id="autoResizeTextarea" placeholder="Type your message..."></textarea>
                            <button type="submit" name="sendMessageBtn"><i class="fa-solid fa-circle-arrow-right"></i></button>
                        </form>
                    </div>
                    </div>
            <?php else: ?>
                <h2>Guest View</h2>
                <p>Welcome! Please log in to access your dashboard.</p>
            <?php endif; ?>

        
        </div>   
    </div>

    <script>
    const textarea = document.getElementById('autoResizeTextarea');
    textarea.addEventListener('input', function () {
      this.style.height = 'auto';
      this.style.height = this.scrollHeight + 'px';
    });
  </script>
</body>
</html>
