<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP System - Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>ERP System</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active">
                    <span>🏠</span>
                    Dashboard
                </a>
                <a href="students.php" class="nav-item">
                    <span>👨‍🎓</span>
                    Students
                </a>
                <a href="attendance.php" class="nav-item">
                    <span>📊</span>
                    Attendance
                </a>
                <a href="fees.php" class="nav-item">
                    <span>💰</span>
                    Fees
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <span>🚪</span>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-container">
                <div class="welcome-section">
                    <h2>Welcome back! Here's your overview</h2>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <h3>Total Students</h3>
                            <span class="download-icon">📥</span>
                        </div>
                        <div class="stat-content">
                            <div class="stat-main">
                                <span class="stat-number">1,247</span>
                                <span class="stat-change positive">+12 from last month</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <h3>Attendance Today</h3>
                        </div>
                        <div class="stat-content">
                            <div class="stat-main">
                                <span class="stat-number">92%</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <h3>Fee Collection</h3>
                        </div>
                        <div class="stat-content">
                            <div class="stat-main">
                                <span class="stat-number">74.2L</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <h3>Outstanding Dues</h3>
                        </div>
                        <div class="stat-content">
                            <div class="stat-main">
                                <span class="stat-number">785K</span>
                                <div class="due-notice">
                                    <span class="due-text">This month</span>
                                    <span class="due-count">18 students pending</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-grid">
                    <div class="content-card">
                        <h3>Recent Enrollments</h3>
                        <div class="enrollment-list">
                            <div class="enrollment-item">
                                <div class="avatar">RS</div>
                                <div class="enrollment-details">
                                    <div class="student-name">Rahul Sharma</div>
                                    <div class="class-info">Class 10-A</div>
                                </div>
                                <div class="enrollment-date">2024-01-15</div>
                            </div>
                            <div class="enrollment-item">
                                <div class="avatar">PP</div>
                                <div class="enrollment-details">
                                    <div class="student-name">Priya Patel</div>
                                    <div class="class-info">Class 9-B</div>
                                </div>
                                <div class="enrollment-date">2024-01-14</div>
                            </div>
                            <div class="enrollment-item">
                                <div class="avatar">AK</div>
                                <div class="enrollment-details">
                                    <div class="student-name">Amit Kumar</div>
                                    <div class="class-info">Class 10-A</div>
                                </div>
                                <div class="enrollment-date">2024-01-13</div>
                            </div>
                        </div>
                    </div>

                    <div class="content-card">
                        <h3>Recent Payments</h3>
                        <div class="payment-list">
                            <div class="payment-item">
                                <div class="avatar">SS</div>
                                <div class="payment-details">
                                    <div class="student-name">Sanjay Singh</div>
                                </div>
                                <div class="payment-date">2024-01-15</div>
                            </div>
                            <div class="payment-item">
                                <div class="avatar">NG</div>
                                <div class="payment-details">
                                    <div class="student-name">Neha Gupta</div>
                                </div>
                                <div class="payment-date">2024-01-15</div>
                            </div>
                            <div class="payment-item">
                                <div class="avatar">VR</div>
                                <div class="payment-details">
                                    <div class="student-name">Vikram Rao</div>
                                </div>
                                <div class="payment-date">2024-01-14</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>