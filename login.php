<?php
session_start();
include 'connector.php';

function refreshRecaptcha() {
    echo "<script>
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.ready(function() {
                    grecaptcha.reset();
                });
            } else {
                console.error('grecaptcha is not defined');
            }
          </script>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verify reCAPTCHA response
    if (isset($_POST['g-recaptcha-response'])) {
        $response = $_POST['g-recaptcha-response'];
        $secretKey = '6LdiuPIpAAAAAAuE-iRlUhoGJPW3rrZPUqiCM65T';
        $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $postData = http_build_query(array(
            'secret' => $secretKey,
            'response' => $response
        ));
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $postData
            )
        ));
        $result = file_get_contents($recaptchaUrl, false, $context);
        if ($result === FALSE) {
            echo 'Error while fetching reCAPTCHA response';
            exit;
        }
        $responseKeys = json_decode($result, true);
        if (!$responseKeys["success"]) {
            refreshRecaptcha();
            echo "<script>alert('reCAPTCHA verification failed');history.back();</script>";
            exit;
        }
    }

    // Initialize or increment the failed login attempt counter
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }

    // Check if the lockout period is active
    if (isset($_SESSION['lockout']) && $_SESSION['lockout'] > time()) {
        $remainingTime = $_SESSION['lockout'] - time();
        echo "<script>
                var remainingTime = $remainingTime;
                var countdownInterval = setInterval(function() {
                    if (remainingTime > 0) {
                        document.getElementById('countdown').innerText = 'Please wait ' + remainingTime + ' seconds before trying again.';
                        remainingTime--;
                    } else {
                        clearInterval(countdownInterval);
                        window.location.href='log.html';
                    }
                }, 1000);

                // Disable the login form
                document.getElementById('loginForm').style.display = 'none';

                // Prevent back button
                history.pushState(null, null, location.href);
                window.onpopstate = function() {
                    history.pushState(null, null, location.href);
                };
              </script>";
        echo "<div id='countdown' style='font-size: 20px; color: red;'></div>";
        exit;
    }

    // Validate user credentials
    $sql = "SELECT * FROM portfolio WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['email'] = $user['email'];
        unset($_SESSION['lockout']);
        unset($_SESSION['login_attempts']);

        // Handle Remember Me functionality
        if (isset($_POST['remember_me'])) {
            setcookie('email', $email, time() + (86400 * 30), "/");
            setcookie('password', $password, time() + (86400 * 30), "/");
        } else {
            setcookie('email', '', time() - 3600, "/");
            setcookie('password', '', time() - 3600, "/");
        }

        header('Location: dashboard.php');
        exit;
    } else {
        $_SESSION['login_attempts']++;

        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['lockout'] = time() + 20; // Lockout for 20 seconds
            $_SESSION['login_attempts'] = 0; // Reset login attempts after lockout
        }

        // refreshRecaptcha();
        echo "<script>alert('Invalid email or password');history.back();</script>";
    }

    $stmt->close();
}

$conn->close();
?>
