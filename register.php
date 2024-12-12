<?php  
require_once 'core/models.php'; 
require_once 'core/handleForms.php'; 


// Determine user type based on form submission or default to 'employee'
$user_role = isset($_POST['user_role']) ? $_POST['user_role'] : 'employee';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>JobExpress</title>
	<link rel="stylesheet" href="styles\login.css">
</head>
<body>
    <div class="container">
        <div class="divider">
        <img src="assets\registrationdesign.jpg" alt="">
            <div class="contentcontainer">
                <div class="formcontainer">
                    <div class="header">
                        <h1 style="margin-left:30%">SIGN UP</h1>
                    </div>
                    <form method="POST" action="">
                        <p>
                            <label for="user_role">Register As</label>
                            <select name="user_role" id="user_role" onchange="this.form.submit()">
                                <option value="employee" <?= $user_role === 'employee' ? 'selected' : '' ?>>Employee</option>
                                <option value="employer" <?= $user_role === 'employer' ? 'selected' : '' ?>>Employer</option>
                            </select>
                        </p>
                    </form>

                    <form action="core/handleForms.php" method="POST">
                        <input type="hidden" name="user_role" value="<?= htmlspecialchars($user_role) ?>">
                        <p>
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name">
                        </p>
                        <p>
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name">
                        </p>
                        <p>
                            <label for="email">Email</label>
                            <input type="text" name="email">
                        </p>
                        <p>
                            <label for="username">Username</label>
                            <input type="text" name="username">
                        </p>
                        <p>
                            <label for="password">Password</label>
                            <input type="password" name="password">
                        </p>
                        <p>
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" name="confirm_password" >
                        </p>
                        <input type="submit" name="registerBtn" value="Sign up">
                    </form>
                    <p class="register"><a href="login.php">Already have an Account? Login here!</a></p>
                </div>

            </div>
        </div>
        <div class="msgalert">
        <?php  
                        if (isset($_SESSION['message']) && isset($_SESSION['status'])) {
                            if ($_SESSION['status'] == "200") {
                                echo "<p style='color: white; text-align: center; background-color: #58D360; padding: 1vh;'>{$_SESSION['message']}</p>";
                            } else {
                                echo "<p style='color: white; text-align: center; background-color: #D3585A; padding: 1vh;'>{$_SESSION['message']}</p>";	
                            }
                        }
                        unset($_SESSION['message']);
                        unset($_SESSION['status']);
                        ?>
        </div>
    </div>
</body>
</html>
