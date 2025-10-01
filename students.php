<?php
session_start();
if(!isset($_SESSION['user'])) header("Location: login.php");

$host="localhost"; $user="root"; $pass=""; $db="erp_db";
$conn = new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Create table if not exists with correct structure
$check_table = $conn->query("SHOW TABLES LIKE 'students'");
if($check_table->num_rows == 0) {
    $create_table = "CREATE TABLE students (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        roll_no VARCHAR(50) NOT NULL,
        name VARCHAR(100) NOT NULL,
        class VARCHAR(50),
        contact VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($create_table);
    
    // Insert sample data
    $sample_data = [
        ['2024001', 'Rahul Sharma', 'Class 10-A', '+91 98765 43210'],
        ['2024002', 'Priya Patel', 'Class 10-A', '+91 98765 43211'],
        ['2024003', 'Amit Kumar', 'Class 9-B', '+91 98765 43212'],
        ['2024004', 'Sneha Singh', 'Class 9-B', '+91 98765 43213'],
        ['2024005', 'Vikram Rao', 'Class 10-C', '+91 98765 43214']
    ];
    
    $stmt = $conn->prepare("INSERT INTO students (roll_no, name, class, contact) VALUES (?, ?, ?, ?)");
    foreach($sample_data as $data) {
        $stmt->bind_param("ssss", $data[0], $data[1], $data[2], $data[3]);
        $stmt->execute();
    }
    $stmt->close();
}

// Add student
if(isset($_POST['add'])){
    $roll = $_POST['roll_no']; 
    $name = $_POST['name']; 
    $class = $_POST['class']; 
    $contact = $_POST['contact'];
    
    $stmt = $conn->prepare("INSERT INTO students (roll_no, name, class, contact) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $roll, $name, $class, $contact);
    
    if($stmt->execute()) {
        $success = "Student added successfully!";
    } else {
        $error = "Error adding student: " . $stmt->error;
    }
    $stmt->close();
}

// Update student
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $roll = $_POST['roll_no']; 
    $name = $_POST['name']; 
    $class = $_POST['class']; 
    $contact = $_POST['contact'];
    
    $stmt = $conn->prepare("UPDATE students SET roll_no=?, name=?, class=?, contact=? WHERE id=?");
    $stmt->bind_param("ssssi", $roll, $name, $class, $contact, $id);
    
    if($stmt->execute()) {
        $success = "Student updated successfully!";
    } else {
        $error = "Error updating student: " . $stmt->error;
    }
    $stmt->close();
}

// Delete student
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM students WHERE id=$id");
    $success = "Student deleted successfully!";
}

// Get student data for editing
$edit_data = null;
if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM students WHERE id=$id");
    $edit_data = $result->fetch_assoc();
}

function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Students - ERP System</title>
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
                <a href="dashboard.php" class="nav-item">
                    <span>🏠</span>
                    Dashboard
                </a>
                <a href="students.php" class="nav-item active">
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
            <div class="module-container">
                <div class="module-header">
                    <div>
                        <h1>ERP System</h1>
                        <div class="module-subtitle">Students - Manage student records and information</div>
                    </div>
                </div>

                <!-- Display Messages -->
                <?php if(isset($success)): ?>
                    <div class="alert success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Search Bar -->
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search students...">
                </div>

                <!-- Students Table -->
                <div class="content-card">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Roll Number</th>
                                <th>Class</th>
                                <th>Contact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM students ORDER BY id DESC");
                            if($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()){
                                    $initials = getInitials($row['name']);
                                    echo "<tr>
                                            <td>
                                                <div class='student-info'>
                                                    <div class='student-avatar'>$initials</div>
                                                    <div>
                                                        <div class='student-name'>{$row['name']}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{$row['roll_no']}</td>
                                            <td>{$row['class']}</td>
                                            <td>{$row['contact']}</td>
                                            <td>
                                                <div class='action-buttons'>
                                                    <a href='students.php?edit={$row['id']}' class='edit-btn'>Edit</a>
                                                    <a href='students.php?delete={$row['id']}' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this student?\")'>Delete</a>
                                                </div>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align: center; padding: 2rem; color: #64748b;'>No students found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add/Edit Student Form -->
                <div class="form-container">
                    <h3><?php echo $edit_data ? 'Edit Student' : 'Add Student'; ?></h3>
                    <form method="POST">
                        <?php if($edit_data): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>
                        <input type="text" name="roll_no" placeholder="Roll Number" 
                               value="<?php echo $edit_data ? $edit_data['roll_no'] : ''; ?>" required>
                        <input type="text" name="name" placeholder="Full Name" 
                               value="<?php echo $edit_data ? $edit_data['name'] : ''; ?>" required>
                        <input type="text" name="class" placeholder="Class (e.g., Class 10-A)" 
                               value="<?php echo $edit_data ? $edit_data['class'] : ''; ?>" required>
                        <input type="text" name="contact" placeholder="Contact Number" 
                               value="<?php echo $edit_data ? $edit_data['contact'] : ''; ?>" required>
                        <button type="submit" name="<?php echo $edit_data ? 'update' : 'add'; ?>">
                            <?php echo $edit_data ? 'Update Student' : 'Add Student'; ?>
                        </button>
                        <?php if($edit_data): ?>
                            <a href="students.php" class="cancel-btn">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>