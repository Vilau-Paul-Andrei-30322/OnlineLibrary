<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$query = "SELECT * FROM UserData";
$stmt = sqlsrv_query($conn, $query);

$users = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $users[] = $row;
    }
} else {
    echo "Error: Failed to fetch users.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Console - See Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            font-family: Arial, sans-serif;
            text-align: center;
            font-size: 24px;
            color: #333;
        }

        h2 {
            font-family: Lucida Handwriting, Cursive;
            font-size: 24px;
            color: #333;    
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
        }
        h2 a {
            text-decoration: none; 
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .admin-toggle span {
            cursor: pointer;
            font-weight: bold;
        }

        .admin-toggle .x {
            color: red;
        }

        .admin-toggle .mark {
            color: green;
        }
    </style>
</head>
<body>
    <h2> <a href="admin_main.php">PV Library Admin</a> - Users</h2>
    <table>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Student ID</th>
            <th>Has Seat Reservation</th>
            <th>Is Admin</th>
            <th>Has Book Reservations</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['user_id']; ?></td>
                <td><?php echo $user['name']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td><?php echo $user['student_id']; ?></td>
                <td><?php echo $user['has_seat_reservation'] ? 'Yes' : 'No'; ?></td>
                <td class="admin-toggle" data-userid="<?php echo $user['user_id']; ?>">
                    <?php
                        $symbolClass = $user['is_admin'] ? 'mark' : 'x';
                    ?>
                    <span class="<?php echo $symbolClass; ?>"><?php echo $user['is_admin'] ? '✔' : '✘'; ?></span>
                </td>
                <td><?php echo $user['has_book_reservations'] ? 'Yes' : 'No'; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <script>
    var adminToggleElements = document.querySelectorAll('.admin-toggle');
    adminToggleElements.forEach(function(element) {
        element.addEventListener('click', function() {
            var userId = element.getAttribute('data-userid');
            var isAdmin = element.querySelector('span').textContent === 'x' ? 0 : 1;
            
            fetch('toggle_admin.php?user_id=' + userId)
                .then(response => {
                    if (response.ok) {
                        return response.text();
                    } else {
                        throw new Error('Failed to toggle admin status.');
                    }
                })
                .then(data => {
                    if (data.trim() === '1') {
                        element.querySelector('span').textContent = '✔';
                        element.querySelector('span').className = 'mark';
                    } else {
                        element.querySelector('span').textContent = '✘';
                        element.querySelector('span').className = 'x';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An unexpected error occurred.');
                });
        });
    });
</script>

</body>
</html>
