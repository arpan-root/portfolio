<?php
session_start();

require 'connector.php';

if (!isset($_SESSION['email'])) {
    if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
        $email = $_COOKIE['email'];
        $password = $_COOKIE['password'];

        // Validate user credentials
        $sql = "SELECT * FROM portfolio WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['email'] = $user['email'];
        } else {
            setcookie('email', '', time() - 3600, "/");
            setcookie('password', '', time() - 3600, "/");
            header('Location: log.html');
            exit;
        }

        $stmt->close();
    } else {
        header('Location: log.html');
        exit;
    }
}

$email = $_SESSION['email'];

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $image_name = $_FILES['profile_image']['name'];
    $image_tmp_name = $_FILES['profile_image']['tmp_name'];
    $image_size = $_FILES['profile_image']['size'];
    $image_error = $_FILES['profile_image']['error'];

    $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
    $allowed_ext = array('jpg', 'png');

    if (in_array($image_ext, $allowed_ext)) {
        if ($image_error === 0) {
            if ($image_size < 5000000) { // 5MB limit
                $new_image_name = uniqid('', true) . '.' . $image_ext;
                $image_destination = 'images/' . $new_image_name;

                if (move_uploaded_file($image_tmp_name, $image_destination)) {
                    // Update image record in the database
                    $sql = "UPDATE portfolio SET image_path = ? WHERE email = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $new_image_name, $email);
                    $stmt->execute();

                    // Redirect to avoid resubmission
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error_message = "Failed to upload image.";
                }
            } else {
                $error_message = "Image size is too big.";
            }
        } else {
            $error_message = "Error uploading image.";
        }
    } else {
        $error_message = "Invalid image format.";
    }
}

// Fetch the user's details
$sql = "SELECT username, email, image_path FROM portfolio WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* General Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: #0d47a1;
}

/* Navbar Styles */
.navbar {
    background-color: #0d47a1;
    color: #fff;
    justify-content: center;
    align-items: center;
    padding: 12px 20px;
}

.user-info {
    opacity: 0; /* Initially hide element */
    animation: slideInFromRight 6s ease-in-out infinite; /* Apply animation */
}

/* Keyframes for fade-in animation */
@keyframes slideInFromRight {
    0% {
        opacity: 0;
        transform: translateX(100%);
    }
    100% {
        opacity: 1;
        transform: translateX(0%);
    }
}

/* Aside Navbar Styles */
.aside-navbar {
    background-color: whitesmoke;
    width: 35%;
    height: 60vh;
    position: fixed;
    top: 0;
    left: 0;
    padding: 50px;
    display: flex;
    flex-direction: column;
    margin-left: 25%;
    margin-top: 8%;
    border-radius: 5px;
}
.dark-mode {
    background-color: #000;
    color: #fff;
}
#dark-mode-toggle{
    display: flex;
    padding: 10px;
    justify-content: right;
    cursor: pointer;
    font-size: 35px;
}
.aside-navbar .profile-image {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 20px;
}

.aside-navbar .profile-image img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    margin-bottom: 10px;
}

.aside-navbar .upload-icon {
    font-size: 24px;
    margin-bottom: 10px;
    cursor: pointer;
    color: darkblue;
}

.aside-navbar .upload-icon:hover {
    text-decoration: underline;
}

.aside-navbar .profile-name {
    font-weight: bold;
    text-align: center;
    margin-bottom: 30px;
}

.aside-navbar button {
    background-color: blue;
    color: #fff;
    text-decoration: none;
    text-align: center;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 30px;
    cursor: pointer;
    display:flex;
    justify-content:center;
    margin-right:30%;
    margin-left:30%;
}

/* Media Queries for Responsive Design */
@media (max-width: 320px) {
    .aside-navbar {
        width: 80%; /* Adjust width for smaller screens */
        margin-right: auto;
        margin-left: 10%;
        padding-right:90%;
    }

    .aside-navbar .profile-name,
    .aside-navbar  button {
        margin-left: 5px;
        margin-right: 5px; /* Center the elements */
    }
}

    </style>
</head>
<body>
<div class="navbar">
        <div class="user-info"><h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1></div>
    </div>

    <!-- Aside Navbar -->
    <div class="aside-navbar">
        <div id="dark-mode-toggle"><i class="fa-solid fa-eye"></i></div>
        <div class="profile-image">
            <?php if (!empty($user['image_path'])): ?>
                <img src="images/<?php echo htmlspecialchars($user['image_path']); ?>" alt="Profile Image">
            <?php else: ?>
                <img src="profile.jpg" alt="Profile Image">
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <div class="upload-icon" onclick="document.getElementById('profile_image').click()"><i class="fa-solid fa-upload"></i></div>
            <form id="uploadForm" action="dashboard.php" method="post" enctype="multipart/form-data">
                <input type="file" name="profile_image" id="profile_image" style="display: none;">
                <input type="submit" value="Upload" style="display: none;">
            </form>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($user['username']); ?></div>
        <div class="profile-name"><?php echo htmlspecialchars($user['email']); ?></div>
        <button id="logout-btn">Logout</button>
    </div>


    <script>
        document.getElementById('profile_image').addEventListener('change', function() {
            document.getElementById('uploadForm').submit();
        });

        // Add an event listener to the logout button
        document.getElementById('logout-btn').addEventListener('click', function() {
            // Make an AJAX request to the logout script
            fetch('logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'logged_out') {
                        // Broadcast logout event to other tabs
                        localStorage.setItem('logout', Date.now());
                        // Redirect to portfolio page
                        window.location.href = 'portfolio.html';
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        // Listen for the logout event from other tabs
        window.addEventListener('storage', function(event) {
            if (event.key === 'logout') {
                // Redirect to the login page if logout event is detected
                window.location.href = 'portfolio.html';
            }
        });

        // Prevent back navigation after logout
        if (window.history && window.history.pushState) {
            window.history.pushState(null, null, window.location.href);
            window.addEventListener('popstate', function() {
                window.history.pushState(null, null, window.location.href);
            });
        }

        document.addEventListener('keydown', function(event) {
            const SCROLL_AMOUNT = 50; // Change this value to adjust the scroll amount

            switch (event.key) {
                case "ArrowUp":
                    window.scrollBy(0, -SCROLL_AMOUNT);
                    break;
                case "ArrowDown":
                    window.scrollBy(0, SCROLL_AMOUNT);
                    break;
                case "ArrowLeft":
                    window.scrollBy(-SCROLL_AMOUNT, 0);
                    break;
                case "ArrowRight":
                    window.scrollBy(SCROLL_AMOUNT, 0);
                    break;
                default:
                    break;
            }
        });

        // Disable right click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        // Auto-logout after 30 seconds of inactivity
        let logoutTimer;

        function resetLogoutTimer() {
            clearTimeout(logoutTimer);
            logoutTimer = setTimeout(function() {
                // Auto logout the user
                fetch('logout.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'logged_out') {
                            // Broadcast logout event to other tabs
                            localStorage.setItem('logout', Date.now());
                            // Redirect to portfolio page
                            window.location.href = 'portfolio.html';
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }, 30000); // 30 seconds
        }

        // Listen for any user activity to reset the timer
        document.addEventListener('mousemove', resetLogoutTimer);
        document.addEventListener('keydown', resetLogoutTimer);

        // Start the logout timer when the page loads
        resetLogoutTimer();
         // Function to toggle dark mode
         function toggleDarkMode() {
    const asideNavbar = document.querySelector('.aside-navbar');
    const profileNames = asideNavbar.querySelectorAll('.profile-name');

    // Toggle dark mode classes only for elements within the aside-navbar
    asideNavbar.classList.toggle('dark-mode');
    profileNames.forEach(name => name.classList.toggle('dark-mode'));

    // Save the user's dark mode preference in localStorage
    const darkModeEnabled = asideNavbar.classList.contains('dark-mode');
    localStorage.setItem('darkMode', darkModeEnabled);
}

// Add click event listener to the dark mode toggle button
document.getElementById('dark-mode-toggle').addEventListener('click', toggleDarkMode);

// Check if dark mode preference is stored in localStorage
const darkMode = localStorage.getItem('darkMode');
if (darkMode === 'true') {
    // Enable dark mode if preference is stored
    toggleDarkMode();
}

    </script>
</body>
</html>
