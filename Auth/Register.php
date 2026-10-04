<?php

require_once "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = strtolower(trim($_POST["email"]));
    $password = $_POST["password"];

    // Basic validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {

        // Check whether email already exists
        $stmt = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $message = "An account with that email already exists.";

        } else {

            // Hash the password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Employees get role_id = 3
            $role_id = 3;

            $stmt = $conn->prepare(
                "INSERT INTO users 
                (role_id, full_name, email, password)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "isss",
                $role_id,
                $full_name,
                $email,
                $hashed_password
            );

            if ($stmt->execute()) {

                $message = "Registration successful!";

            } else {

                $message = "Registration failed. Please try again.";
            }
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

    <title>Register | Helpdesk System</title>

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
            Create Account
        </h2>

        <p class="auth-subtitle">
            Create your Helpdesk System account
        </p>


        <?php if (!empty($message)): ?>

            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="mb-3">

                <label class="form-label">
                    Full Name
                </label>

                <input
                    type="text"
                    name="full_name"
                    class="form-control"
                    placeholder="Enter your full name"
                    required
                >

            </div>


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
                    placeholder="Create a password"
                    required
                >

                <small class="text-muted">
                    Password must be at least 8 characters.
                </small>

            </div>


            <button
                type="submit"
                class="auth-button"
            >
                Create Account
            </button>

        </form>


        <div class="auth-footer">

            <p>
                Already have an account?
                <a href="login.php">
                    Sign in
                </a>
            </p>

        </div>

    </div>

</div>

</body>

</html>