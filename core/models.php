<?php
function checkIfUserExists($pdo, $user_id) {
    $response = array();
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$user_id])) {
        $userInfoArray = $stmt->fetch();

        if ($stmt->rowCount() > 0) {
            $response = array(
                "result"=> true,
                "status" => "200",
                "userInfoArray" => $userInfoArray
            );
        } else {
            $response = array(
                "result"=> false,
                "status" => "400",
                "message"=> "User doesn't exist from the database"
            );
        }
    }

    return $response;
}

require_once 'dbConfig.php';

function insertNewUser($pdo, $first_name, $last_name, $email, $username, $user_role, $password) {
    try {
        $sql = "INSERT INTO users (first_name, last_name, email, username, user_role, password) VALUES (:first_name, :last_name, :email, :username, :user_role, :password)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':user_role', $user_role);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            return [
                'status' => '200',
                'message' => 'Registration successful!',
            ];
        } else {
            return [
                'status' => '500',
                'message' => 'Database insertion failed.',
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => '500',
            'message' => 'Error: ' . $e->getMessage(),
        ];
    }
}

function getUserRole($pdo, $username) {
    try {
        $sql = "SELECT user_role FROM users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            return $user['user_role'];
        } else {
            return null;
        }
    } catch (PDOException $e) {
        echo "Error fetching user role: " . $e->getMessage();
        return null;
    }
}

function getUserByUsername($pdo, $username) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

function sendMessage($pdo, $sender_username, $recipient_username, $message_text) {
    try {
        $sql = "INSERT INTO messages (sender_username, recipient_username, message_text) VALUES (:sender_username, :recipient_username, :message_text)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':sender_username', $sender_username);
        $stmt->bindParam(':recipient_username', $recipient_username);
        $stmt->bindParam(':message_text', $message_text);
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function getMessages($pdo, $sender_username, $recipient_username) {
    try {
        $sql = "SELECT sender_username, message_text, timestamp FROM messages 
                WHERE (sender_username = :sender_username AND recipient_username = :recipient_username)
                OR (sender_username = :recipient_username AND recipient_username = :sender_username)
                ORDER BY timestamp DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':sender_username', $sender_username);
        $stmt->bindParam(':recipient_username', $recipient_username);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getAllUsers($pdo, $current_user) {
    try {
        $sql = "SELECT username FROM users WHERE username != :current_user";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':current_user', $current_user);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}


function getJobs($pdo, $employer_username = null) {
    try {
        if ($employer_username) {
            $sql = "SELECT * FROM job_posts WHERE employer_username = :employer_username ORDER BY date_posted DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':employer_username', $employer_username);
        } else {
            $sql = "SELECT * FROM job_posts ORDER BY date_posted DESC";
            $stmt = $pdo->prepare($sql);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}


function postJob($pdo, $employer_username, $title, $description, $salary) {
    try {
        $sql = "INSERT INTO job_posts (employer_username, title, description, salary) VALUES (:employer_username, :title, :description, :salary)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':employer_username', $employer_username);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':salary', $salary);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function getJob($pdo, $job_id) {
    try {
        $sql = "SELECT * FROM job_posts WHERE job_id = :job_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':job_id', $job_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

function getApplicants($pdo, $job_id) {
    try {
        $sql = "
            SELECT ja.application_id, ja.status, u.id AS user_id, u.username, u.first_name, u.last_name, u.email
            FROM job_applications ja
            JOIN users u ON ja.user_id = u.id
            WHERE ja.job_id = :job_id
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':job_id' => $job_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}



function applyForJob($pdo, $job_id, $user_id) {
    try {
        $sql = "INSERT INTO job_applications (job_id, user_id) VALUES (:job_id, :user_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':job_id', $job_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function getApplicationById($pdo, $application_id) {
    try {
        $sql = "SELECT * FROM job_applications WHERE application_id = :application_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

function getJobById($pdo, $job_id) {
    try {
        $sql = "SELECT * FROM job_posts WHERE job_id = :job_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

function updateApplicationStatus($pdo, $application_id, $status) {
    try {
        $sql = "UPDATE job_applications SET status = :status WHERE application_id = :application_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        // Log or handle the error as necessary
        return false;
    }
}

function addNotification($pdo, $user_id, $message, $type) {
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $message);
        $stmt->bindParam(3, $type);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        // Log or handle the error as necessary
        error_log("Failed to add notification: " . $e->getMessage());
        return false;
    }
}


// models.php - getNotificationsByUserId function
function getNotificationsByUserId($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteApplication($pdo, $application_id) {
    try {
        $sql = "DELETE FROM job_applications WHERE application_id = :application_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        // Log or handle the error as necessary
        return false;
    }
}

function updateJob($pdo, $job_id, $title, $description, $salary) {
    try {
        $sql = "UPDATE job_posts SET title = :title, description = :description, salary = :salary WHERE job_id = :job_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':salary', $salary);
        $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;  // Return true if the update was successful
    } catch (PDOException $e) {
        // Log or handle the error as necessary
        error_log("Failed to update job: " . $e->getMessage());
        return false;
    }
}



?>
