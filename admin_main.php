<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Console</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .logo {
            font-family: Lucida Handwriting, Cursive;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 36px;
            margin-bottom: 40px;
        }

        .options {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .option {
            margin-bottom: 20px;
        }

        .option a {
            display: inline-block;
            padding: 10px 20px;
            font-size: 20px;
            color: blue;
            text-decoration: none;
            border: 2px solid blue;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .option a:hover {
            background-color: blue;
            color: white;
        }

        .option a:active {
            background-color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">PV Library</div>
        <div class="title">Admin Console</div>
        <div class="options">
            <div class="option">
                <a href="see_users.php">See All Users</a>
            </div>
            <div class="option">
                <a href="see_seat_reservations.php">See Active Seat Reservations</a>
            </div>
            <div class="option">
                <a href="see_book_reservations.php">See Active Book Reservations</a>
            </div>
            <div class="option">
                <a href="see_all_books.php">See All Books</a>
            </div>
            <div class="option">
                <a href="main.php">Redirect to Library</a>
            </div>
            <div class="option">
                <a href="admin_login.php">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
