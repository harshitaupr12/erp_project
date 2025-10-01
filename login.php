<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "erp_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // For demo - hardcoded credentials
    if ($email === 'admin@example.com' && $password === 'password') {
        $_SESSION['user'] = 'Admin User';
        $_SESSION['role'] = 'admin';
        $_SESSION['email'] = 'admin@example.com';
        header("Location: dashboard.php");
        exit();
    }

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user'] = $row['name'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $row['email'];
            header("Location: dashboard.php");
            exit();
        } else {
            $login_error = "Invalid Password!";
        }
    } else {
        $login_error = "No user found with this email!";
    }
}

// Handle registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    if ($password !== $confirm_password) {
        $register_error = "Passwords do not match!";
    } else {
        $check_sql = "SELECT * FROM users WHERE email='$email'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $register_error = "User with this email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', 'user')";
            
            if ($conn->query($insert_sql)) {
                $register_success = "Registration successful! You can now login.";
            } else {
                $register_error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern ERP System for Educational Institutions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }

        .logo span {
            color: #3498db;
        }

        .btn {
            display: inline-block;
            padding: 10px 25px;
            background-color: #3498db;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 60px 0;
        }

        .hero-content {
            flex: 1;
            padding-right: 40px;
        }

        .hero-content h1 {
            font-size: 42px;
            color: #2c3e50;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 18px;
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 50px;
        }

        .feature {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .feature:hover {
            transform: translateY(-5px);
        }

        .feature h3 {
            font-size: 22px;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .feature p {
            color: #7f8c8d;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #7f8c8d;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #3498db;
            outline: none;
        }

        .form-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 25px;
            text-align: center;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #2980b9;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
        }

        .form-footer a {
            color: #3498db;
            text-decoration: none;
            cursor: pointer;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(231, 76, 60, 0.1);
            border-radius: 5px;
            text-align: center;
        }

        .success-message {
            color: #27ae60;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(39, 174, 96, 0.1);
            border-radius: 5px;
            text-align: center;
        }

        .demo-credentials {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }

        .demo-credentials h4 {
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .demo-credentials p {
            margin: 4px 0;
            color: #7f8c8d;
        }

        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
            }
            
            .hero-content {
                padding-right: 0;
                margin-bottom: 40px;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">Edu<span>ERP</span></div>
            <button class="btn" id="signin-btn">Sign In</button>
        </header>

        <section class="hero">
            <div class="hero-content">
                <h1>Modern ERP System for Educational Institutions</h1>
                <p>Streamline student management, track attendance, and manage fees effortlessly with our comprehensive ERP solution.</p>
                <button class="btn" id="get-started-btn">Get Started</button>
            </div>
        </section>

        <section class="features">
            <div class="feature">
                <h3>Student Management</h3>
                <p>Efficiently manage student records, enrollment, and academic information</p>
            </div>
            <div class="feature">
                <h3>Attendance Tracking</h3>
                <p>Track and monitor student attendance with detailed reports and analytics</p>
            </div>
            <div class="feature">
                <h3>Fee Management</h3>
                <p>Streamline fee collection, track payments, and manage outstanding dues</p>
            </div>
            <div class="feature">
                <h3>Analytics Dashboard</h3>
                <p>Get insights with comprehensive dashboards and real-time reports</p>
            </div>
        </section>
    </div>

    <!-- Registration Modal -->
    <div id="register-modal" class="modal">
        <div class="modal-content">
            <span class="close" id="close-register">&times;</span>
            <h2 class="form-title">Create Your Account</h2>
            
            <?php if (isset($register_error)): ?>
                <div class="error-message"><?php echo $register_error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($register_success)): ?>
                <div class="success-message"><?php echo $register_success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="register" value="1">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>
                </div>
                <button type="submit" class="submit-btn">Register</button>
                <div class="form-footer">
                    Already have an account? <a id="login-link">Sign In</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="login-modal" class="modal">
        <div class="modal-content">
            <span class="close" id="close-login">&times;</span>
            <h2 class="form-title">Sign In to Your Account</h2>
            
            <div class="demo-credentials">
                <h4>Demo Credentials:</h4>
                <p><strong>Email:</strong> admin@example.com</p>
                <p><strong>Password:</strong> password</p>
            </div>
            
            <?php if (isset($login_error)): ?>
                <div class="error-message"><?php echo $login_error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="login-email">Email Address</label>
                    <input type="email" id="login-email" name="email" value="admin@example.com" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" value="password" required>
                </div>
                <button type="submit" class="submit-btn">Sign In</button>
                <div class="form-footer">
                    Don't have an account? <a id="register-link">Register Now</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Get modal elements
        const registerModal = document.getElementById('register-modal');
        const loginModal = document.getElementById('login-modal');
        const getStartedBtn = document.getElementById('get-started-btn');
        const signinBtn = document.getElementById('signin-btn');
        const closeRegister = document.getElementById('close-register');
        const closeLogin = document.getElementById('close-login');
        const loginLink = document.getElementById('login-link');
        const registerLink = document.getElementById('register-link');

        // Open registration modal when "Get Started" is clicked
        getStartedBtn.addEventListener('click', function(e) {
            e.preventDefault();
            registerModal.style.display = 'flex';
        });

        // Open login modal when "Sign In" is clicked
        signinBtn.addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.style.display = 'flex';
        });

        // Close modals when X is clicked
        closeRegister.addEventListener('click', function() {
            registerModal.style.display = 'none';
        });

        closeLogin.addEventListener('click', function() {
            loginModal.style.display = 'none';
        });

        // Switch between login and registration modals
        loginLink.addEventListener('click', function(e) {
            e.preventDefault();
            registerModal.style.display = 'none';
            loginModal.style.display = 'flex';
        });

        registerLink.addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.style.display = 'none';
            registerModal.style.display = 'flex';
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === registerModal) {
                registerModal.style.display = 'none';
            }
            if (e.target === loginModal) {
                loginModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>