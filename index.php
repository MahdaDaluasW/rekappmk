<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selection = $_POST['selection']; 

    if ($selection == 'pmk') {
        header("Location: pmk/display-pmk.php");
    } elseif ($selection == 'pg') {
        header("Location: pg/display-pg.php");  
    }
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Data</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 400px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        .login-container h1 {
            margin-bottom: 20px;
            color: #007bff;
            font-size: 24px;
            font-weight: bold;
        }

        .login-container img {
            max-width: 80%;
            margin-bottom: 20px;
        }

        .login-container select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            outline: none;
            transition: border 0.3s ease;
        }

        .login-container select:focus {
            border-color: #007bff;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .login-container button:hover {
            background-color: #0056b3;
            transform: scale(1.02);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 600px) {
            .login-container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="images/logo-bkn.png" alt="Logo BKN" width="100px">
        <!-- <img src="images/kanreg1-yogyakarta.png" alt="Kanreg 1 Yogyakarta" width="100px"> -->
        <!-- <h1>Pilih Data</h1> -->
        <form method="POST" action="">
            <select name="selection" required>
                <option value="pmk">PMK</option>
                <option value="pg">PG</option>
            </select>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
