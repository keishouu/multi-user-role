<?php  
require_once 'core/models.php'; 
require_once 'core/handleForms.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindHire</title>
    <link rel="stylesheet" href="styles\login.css">
</head>
<body>
    <div class="container">
        <div class="divider">
            <img src="assets\logindesign.jpg" alt="">
            <div class="contentcontainer">
                <div class="formcontainer" style="margin-bottom:5vh">
                    <div class="header"> 
                        <img src="assets\findhire logo.png" alt="">
                        <h1 style="margin-top: 5vh;margin-left:30%">LOGIN</h1>   
                    </div>
                    <form action="core/handleForms.php" method="POST">
                            <p>
                                <label for="username">Username</label>
                                <input type="text" name="username">
                            </p>
                            <p>
                                <label for="username">Password</label>
                                <input type="password" name="password">
                            </p>
                            <input type="submit" name="loginUserBtn" value="Login">
                        </form>
                    <p class="register"><a href="register.php">Don't have an account? Register here!</a></p>
                    <?php  
                        if (isset($_SESSION['message']) && isset($_SESSION['status'])) {

                            if ($_SESSION['status'] == "200") {
                                echo "<p style='color: white; text-align: center; background-color: #58D360; padding: 1vh;'>{$_SESSION['message']}</p>";
                            }

                            else {
                                echo "<p style='color: white; text-align: center; background-color: #D3585A; padding: 1vh;'>{$_SESSION['message']}</p>";	
                            }

                        }
                        unset($_SESSION['message']);
                        unset($_SESSION['status']);
                        ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>