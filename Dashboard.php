<?php

session_start();

require_once "config/database.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Get ticket statistics for the logged-in user

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tickets
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$total_tickets = $result->fetch_assoc()["total"];

$stmt->close();


// Open tickets

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tickets
     WHERE user_id = ?
     AND status NOT IN ('Resolved', 'Closed')"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$open_tickets = $result->fetch_assoc()["total"];

$stmt->close();


// Resolved tickets

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tickets
     WHERE user_id = ?
     AND status IN ('Resolved', 'Closed')"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$resolved_tickets = $result->fetch_assoc()["total"];

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Helpdesk System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

<div class="dashboard-container">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2>
            Helpdesk
        </h2>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="tickets/my_tickets.php">
            My Tickets
        </a>

        <a href="#">
            Create Ticket
        </a>

        <a href="#">
            Profile
        </a>

        <a href="auth/logout.php">
            Logout
        </a>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <div class="topbar">

            <h3>
                Welcome,
                <?php echo htmlspecialchars($_SESSION["full_name"]); ?>!
            </h3>

            <p class="text-muted mb-0">
                Here's an overview of your support requests.
            </p>

        </div>


        <!-- STATISTICS -->

        <div class="stats-container">

            <div class="stat-card">

                <h3>
                    Total Tickets
                </h3>

                <p>
                    <?php echo $total_tickets; ?>
                </p>

            </div>


            <div class="stat-card">

                <h3>
                    Open Tickets
                </h3>

                <p>
                    <?php echo $open_tickets; ?>
                </p>

            </div>


            <div class="stat-card">

                <h3>
                    Resolved Tickets
                </h3>

                <p>
                    <?php echo $resolved_tickets; ?>
                </p>

            </div>


            <div class="stat-card">

                <h3>
                    Account
                </h3>

                <p>
                    Employee
                </p>

            </div>

        </div>


        <!-- QUICK ACTION -->

        <div class="topbar mt-4">

            <h4>
                Need IT Support?
            </h4>

            <p class="text-muted">
                Submit a new support request and our IT team
                will get back to you.
            </p>

            <a
               <a
    href="tickets/create.php"
    class="btn btn-primary"
>
    Create New Ticket
            </a>

        </div>

    </main>

</div>

</body>

</html>