<?php
require_once 'dbConfig.php';
require_once 'models.php';

// Register form handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registerBtn'])) {
    // Sanitize and trim inputs
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_role = trim($_POST['user_role']);

    // Check for empty fields
    if (!empty($username) && !empty($first_name) && !empty($last_name) && 
        !empty($email) && !empty($password) && !empty($confirm_password) && 
        !empty($user_role)) {

        // Check if passwords match
        if ($password === $confirm_password) {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into the database
            $insertQuery = insertNewUser($pdo, $first_name, $last_name, $email, 
                $username, $user_role, $hashed_password);

            // Check the result of the query
            if ($insertQuery['status'] == '200') {
                $_SESSION['message'] = $insertQuery['message'];
                $_SESSION['status'] = $insertQuery['status'];
                header("Location: ../login.php");
                exit();
            } else {
                $_SESSION['message'] = $insertQuery['message'];
                $_SESSION['status'] = $insertQuery['status'];
                header("Location: ../register.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Passwords do not match!";
            $_SESSION['status'] = "400";
            header("Location: ../register.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Please make sure there are no empty fields!";
        $_SESSION['status'] = "400";
        header("Location: ../register.php");
        exit();
    }
}

if (isset($_POST['loginUserBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (!empty($username) && !empty($password)) {
        // Fetch user details from the database based on username
        $user = getUserByUsername($pdo, $username);

        if ($user && password_verify($password, $user['password'])) {
            // Correct password, store user details in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['user_role']; // Store role (employee or employer)

            // Redirect to index.php after successful login
            header("Location: ../index.php"); // Same index for both roles
            exit();
        } else {
            // Invalid credentials
            $_SESSION['message'] = 'Invalid username or password';
            $_SESSION['status'] = '400';
            header("Location: ../login.php");
            exit();
        }
    } else {
        $_SESSION['message'] = 'Please fill in all fields';
        $_SESSION['status'] = '400';
        header("Location: ../login.php");
        exit();
    }
}

// Logout function
function logout() {
    // Destroy all session variables
    session_unset(); 

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: ../login.php");
    exit(); // Ensure no further code is executed after the redirect
}

// Handle logout request (this will be triggered when the form is submitted)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    logout(); // Call the logout function
}

// Messaging form handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendMessageBtn'])) {
    $sender_username = $_SESSION['username'];
    $recipient_username = trim($_POST['recipient_username']);
    $message_text = trim($_POST['message_text']);

    if (!empty($recipient_username) && !empty($message_text)) {
        $sendMessageResponse = sendMessage($pdo, $sender_username, $recipient_username, $message_text);

        if ($sendMessageResponse) {
            echo json_encode(array("status" => "200", "message" => "Message sent successfully."));
        } else {
            echo json_encode(array("status" => "500", "message" => "Failed to send message, please try again."));
        }
    } else {
        echo json_encode(array("status" => "400", "message" => "Recipient and message fields are required."));
    }
    header("Location: ../index.php");
    exit();
}



// Handle job application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['applyJobBtn'])) {
    session_start();

    // Assuming 'user_id' is the session variable storing the current user's ID
    $user_id = $_SESSION['user_id'];
    $job_id = $_POST['job_id'];

    // Attempt to apply for the job
    $applyResult = applyForJob($pdo, $job_id, $user_id);

    // Use job-specific messages
    if (!isset($_SESSION['messages'])) {
        $_SESSION['messages'] = []; // Initialize the messages array if not set
    }

    if ($applyResult) {
        $_SESSION['messages'][$job_id] = [
            "message" => "You have successfully applied for the job.",
            "status" => "200"
        ];
    } else {
        $_SESSION['messages'][$job_id] = [
            "message" => "Failed to apply for the job. Please try again.",
            "status" => "400"
        ];
    }

    header("Location: ../index.php");
    exit();
}


// Update application status form handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateApplicationStatusBtn'])) {
    $application_id = trim($_POST['application_id']);
    $status = trim($_POST['status']);

    if (!empty($application_id) && !empty($status)) {
        // Update application status
        $updateResponse = updateApplicationStatus($pdo, $application_id, $status);

        if ($updateResponse) {
            // Fetch job details to use in notification
            $job = getJob($pdo, $_POST['job_id']);

            // Create notification
            $notificationMessage = "Your application for " . $job['title'] . " has been " . $status . ".";
            addNotification($pdo, $_SESSION['user_id'], $notificationMessage);

            echo json_encode(array("status" => "200", "message" => "Application status updated successfully."));
        } else {
            echo json_encode(array("status" => "500", "message" => "Failed to update application status, please try again."));
        }
    } else {
        echo json_encode(array("status" => "400", "message" => "All fields are required."));
    }
}

// Handle job posting form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['postJobBtn'])) {
        $employer_username = $_SESSION['username'];
        $salary = trim($_POST['salary']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);

        if (!empty($title) && !empty($description)) {
            // Attempt to post the job
            $result = postJob($pdo, $employer_username, $title, $description, $salary);

            if ($result) {
                $_SESSION['message'] = "Job posted successfully!";
                $_SESSION['status'] = "200";
            } else {
                $_SESSION['message'] = "Failed to post the job, please try again.";
                $_SESSION['status'] = "400";
            }

            header("Location: ../index.php");
            exit();
        } else {
            $_SESSION['message'] = "Title and description fields are required.";
            $_SESSION['status'] = "400";
            header("Location: ../postJob.php");
            exit();
        }
    }
}

// Handle job update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateJobBtn'])) {
    $job_id = $_POST['job_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $salary = trim($_POST['salary']);

    $updateResult = updateJob($pdo, $job_id, $title, $description, $salary);

    if ($updateResult) {
        $_SESSION['message'] = "Job updated successfully!";
        $_SESSION['status'] = "200";
    } else {
        $_SESSION['message'] = "Failed to update the job. Please try again.";
        $_SESSION['status'] = "400";
    }

    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteJobBtn'])) {
    $job_id = $_POST['job_id']; // Fetch job_id from the POST data
    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Delete related rows in job_applications
        $sql = "DELETE FROM job_applications WHERE job_id = :job_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt->execute();

        // Delete related rows in applications
        $sql = "DELETE FROM applications WHERE job_id = :job_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt->execute();

        // Delete the job post
        $sql = "DELETE FROM job_posts WHERE job_id = :job_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt->execute();

        // Commit the transaction
        $pdo->commit();

        // Redirect to index.php after successful deletion
        header("Location: ../index.php");
        exit();
    } catch (PDOException $e) {
        // Rollback the transaction on error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}




?>
