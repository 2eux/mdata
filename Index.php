<?php
session_start();
include 'koneksi.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $query = mysqli_query($koneksi, "
        SELECT users.*, role.nama_role, company.nama_company
        FROM users
        JOIN role ON users.role_id = role.id
        JOIN company ON users.company_id = company.id
        WHERE users.email='$email' AND users.password='$password'
    ");

    $data = mysqli_fetch_assoc($query);

    if ($data) {
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['username'] = $data['nama'];
        $_SESSION['company_id'] = $data['company_id'];
        $_SESSION['company'] = $data['nama_company'];
        $_SESSION['role'] = $data['nama_role'];

        header("Location: home.php");
        exit();
    } else {
        $message = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            height: 100vh;
            background: url('Gambar/gambar1.png') no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
        }

.login-box {
    position: relative;
    width: 900px;
    padding: 30px;
    background: rgba(255, 255, 255, 0.25); /* lebih transparan */
    backdrop-filter: blur(10px); /* efek blur kaca */
    -webkit-backdrop-filter: blur(10px); /* support Safari */
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.3); /* efek kaca */
    text-align: center;
    z-index: 1;
}

        .login-box h2 {
            margin-bottom: 20px;
        }

        .input-box {
            margin-bottom: 15px;
            text-align: left;
        }

        .input-box label {
            font-size: 14px;
        }

        .input-box input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #000000;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background: #138c74;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        .btn:hover {
            background: #0d5143;
        }

        .message {
            margin-top: 10px;
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="overlay"></div>

<div class="login-box"> 

    <form method="POST">
        <div class="input-box">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-box">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn">Sign Up</button>
    </form>

    <div class="message">
        <?php echo $message; ?>
    </div>
</div>

</body>
</html>