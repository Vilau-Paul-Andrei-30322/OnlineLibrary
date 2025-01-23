<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 75vh;
            flex-direction: column;
            background-color: #f4f4f4;
        }
        form {
            display: flex;
            flex-direction: column;
            width: 300px;
        }
        input, button {
            margin: 10px 0;
            padding: 10px;
            font-size: 16px;
        }
        a {
            margin-top: 10px;
            text-align: center;
            color: blue; 
            text-decoration: none;
        }
        a:hover {
            color: red; 
        }

        a.active {
            color: green;
        }
        h1 {
            font-family: Arial, sans-serif;
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-top: 20px;
        }
        h2 {
            font-family: Lucida Handwriting, Cursive;
            font-size: 48px;
            color: #333;
            text-align: center;
            margin-top: 0px;
        }
    </style>
</head>
<body>
    <h2>PV Library</h2>
    <h1>Register</h1>
    
    <form method="POST" action="register.php">
        <input type="text" name="name" placeholder="Name" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="text" name="student_id" placeholder="Student ID" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>


    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include 'db.php';

        $name = $_POST['name'];
        $email = $_POST['email'];
        $student_id = $_POST['student_id'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO Students (name, email, student_id, password) VALUES (?, ?, ?, ?)";
        $params = array($name, $email, $student_id, $password);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            echo "Registration failed: " . print_r(sqlsrv_errors(), true);
        } else {
            header("Location: login.php?registered=true");
            exit();        
        }
    }
    ?>
</body>
</html>
