<?php

session_start();

require_once "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Email and password are required.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, full_name, email, password, role_id
FROM users
WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                // Regenerate session ID after successful login
                session_regenerate_id(true);

               $_SESSION["user_id"] = $user["id"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["role_id"] = $user["role_id"];

if ($user["role_id"] == 1) {

    // Admin
    header("Location: ../admin/dashboard.php");

} elseif ($user["role_id"] == 2) {

    // Support Agent
    header("Location: ../agent/dashboard.php");

} elseif ($user["role_id"] == 3) {

    // Employee
    header("Location: ../dashboard.php");

} else {

    // Unknown role
    session_destroy();
    header("Location: login.php?error=invalid_role");
}

exit;

            } else {

                $message = "Invalid email or password.";
            }

        } else {

            $message = "Invalid email or password.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | Helpdesk System</title>
    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<div class="auth-container">

    <div class="auth-card">

        <h2 class="auth-title">
            Helpdesk System
        </h2>

        <p class="auth-subtitle">
            Sign in to your support account
        </p>

        <?php if (!empty($message)): ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

    <div class="mb-3">

        <label class="form-label">
            Email Address
        </label>

        <input
            type="email"
            name="email"
            class="form-control"
            placeholder="Enter your email"
            required
        >

    </div>

    <div class="mb-4">

        <label class="form-label">
            Password
        </label>

        <input
            type="password"
            name="password"
            class="form-control"
            placeholder="Enter your password"
            required
        >

    </div>

    <button
        type="submit"
        class="auth-button"
    >
        Sign In
    </button>

</form>


        <div class="auth-footer">

            <p>
                Don't have an account?
                <a href="register.php">
                    Create one
                </a>
            </p>

        </div>

    </div>

</div>

</body>

</html>