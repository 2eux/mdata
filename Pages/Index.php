<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = $_POST["email"];
    $password = $_POST["password"];

    $query = mysqli_query($koneksi, "
        SELECT users.*, role.nama_role, company.nama_company
        FROM users
        JOIN role    ON users.role_id    = role.id
        JOIN company ON users.company_id = company.id
        WHERE users.email    = '$email'
          AND users.password = '$password'
    ");

    $data = mysqli_fetch_assoc($query);

    if ($data) {
        $_SESSION['user_id']    = $data['id'];
        $_SESSION['username']   = $data['nama'];
        $_SESSION['company_id'] = $data['company_id'];
        $_SESSION['company']    = $data['nama_company'];
        $_SESSION['role']       = $data['nama_role'];

        header("Location: /atri/Pages/home.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – MDM Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background-color: #f0f0f0;
        }

        /* ── Card ── */
        .login-box {
            width: 100%;
            max-width: 420px;
            padding: 42px 34px;
            border-radius: 24px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            color: #1a1a1a;
            text-align: center;
        }

        /* ── Header ── */
        .login-box h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .subtitle {
            font-size: 14px;
            color: #888888;
            margin-bottom: 30px;
        }

        /* ── Input ── */
        .input-box {
            margin-bottom: 18px;
            text-align: left;
        }

        .input-box label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #444444;
        }

        .input-box input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #d8d8d8;
            background: #f7f7f7;
            color: #1a1a1a;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .input-box input::placeholder {
            color: #aaaaaa;
        }

        .input-box input:focus {
            border-color: #aaaaaa;
            background: #ffffff;
        }

        /* ── Button ── */
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: #5c5c5c;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #707070, #3a3a3a);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* ── Message ── */
        .message {
            margin-top: 16px;
            font-size: 13px;
            color: #c0392b;
            min-height: 20px;
        }

        /* ── Mobile ── */
        @media (max-width: 480px) {
            .login-box {
                padding: 32px 24px;
                border-radius: 18px;
            }

            .login-box h2 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>

<div class="login-box">

    <h2>MDM Portal</h2>
    <div class="subtitle">Sign in to continue access</div>

    <form method="POST">

        <div class="input-box">
            <label for="email">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                placeholder="Enter your email"
                required
            >
        </div>

        <div class="input-box">
            <label for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >
        </div>

        <button type="submit" class="btn">Sign In</button>

    </form>

    <div class="message">
        <?php echo htmlspecialchars($message); ?>
    </div>

</div>

</body>
</html>