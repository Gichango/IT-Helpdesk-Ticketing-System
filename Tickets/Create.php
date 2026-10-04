<?php

session_start();

require_once "../config/database.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$message_type = "";

// Handle ticket submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id = $_SESSION["user_id"];
    $category_id = intval($_POST["category_id"]);
    $subject = trim($_POST["subject"]);
    $description = trim($_POST["description"]);
    $priority = $_POST["priority"];

    // Basic validation
    if (
        empty($category_id) ||
        empty($subject) ||
        empty($description) ||
        empty($priority)
    ) {

        $message = "Please complete all required fields.";
        $message_type = "danger";

    } else {

        // Generate a unique ticket number
        $ticket_number = "HD-" . date("YmdHis") . "-" . rand(100, 999);

        $stmt = $conn->prepare(
            "INSERT INTO tickets
            (ticket_number, user_id, category_id, subject, description, priority)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "siisss",
            $ticket_number,
            $user_id,
            $category_id,
            $subject,
            $description,
            $priority
        );

        if ($stmt->execute()) {

            $message = "Ticket created successfully! Your ticket number is " . $ticket_number;
            $message_type = "success";

            // Clear form values
            $subject = "";
            $description = "";

        } else {

            $message = "Something went wrong while creating the ticket.";
            $message_type = "danger";
        }

        $stmt->close();
    }
}


// Get ticket categories
$categories = $conn->query(
    "SELECT id, category_name
     FROM ticket_categories
     ORDER BY category_name ASC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Ticket | Helpdesk System</title>

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

<div class="dashboard-container">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2>Helpdesk</h2>

        <a href="../dashboard.php">
            Dashboard
        </a>

        <a href="#">
            My Tickets
        </a>

        <a href="create.php">
            Create Ticket
        </a>

        <a href="#">
            Profile
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <div class="topbar">

            <h3>
                Create New Ticket
            </h3>

            <p class="text-muted mb-0">
                Submit a request to the IT support team.
            </p>

        </div>


        <?php if (!empty($message)): ?>

            <div class="alert alert-<?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <div class="topbar">

            <form method="POST">


                <!-- SUBJECT -->

                <div class="mb-4">

                    <label class="form-label">
                        Subject
                    </label>

                    <input
                        type="text"
                        name="subject"
                        class="form-control"
                        placeholder="Briefly describe the issue"
                        value="<?php echo htmlspecialchars($subject ?? ''); ?>"
                        required
                    >

                </div>


                <!-- CATEGORY -->

                <div class="mb-4">

                    <label class="form-label">
                        Category
                    </label>

                    <select
                        name="category_id"
                        class="form-control"
                        required
                    >

                        <option value="">
                            Select a category
                        </option>

                        <?php while ($category = $categories->fetch_assoc()): ?>

                            <option
                                value="<?php echo $category["id"]; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $category["category_name"]
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <!-- PRIORITY -->

                <div class="mb-4">

                    <label class="form-label">
                        Priority
                    </label>

                    <select
                        name="priority"
                        class="form-control"
                        required
                    >

                        <option value="">
                            Select priority
                        </option>

                        <option value="Low">
                            Low
                        </option>

                        <option value="Medium">
                            Medium
                        </option>

                        <option value="High">
                            High
                        </option>

                        <option value="Critical">
                            Critical
                        </option>

                    </select>

                </div>


                <!-- DESCRIPTION -->

                <div class="mb-4">

                    <label class="form-label">
                        Describe the problem
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="6"
                        placeholder="Explain what happened, when it started, and any error messages you received..."
                        required
                    ><?php echo htmlspecialchars($description ?? ''); ?></textarea>

                </div>


                <!-- BUTTONS -->

                <div>

                    <button
                        type="submit"
                        class="auth-button"
                    >
                        Submit Ticket
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>

</html>