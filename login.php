<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 65vh;
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
    <h1>Login</h1>

    <form method="POST" action="login.php">
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>


    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include 'db.php';

        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM Students WHERE email = ?";
        $params = array($email);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            echo "Login failed: " . print_r(sqlsrv_errors(), true);
        } else {
            $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['user_id'];
                header("Location: main.php?login=true");
                exit();
            } else {
                echo "Invalid email or password";
            }
        }
    }
    ?>
</body>
</html>
