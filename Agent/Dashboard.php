<?php

session_start();

require_once "../config/database.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}


// Make sure the user is a Support Agent
if ($_SESSION["role_id"] != 2) {
    header("Location: ../dashboard.php");
    exit;
}


// Total tickets
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM tickets"
);

$total_tickets = $result->fetch_assoc()["total"];


// Open tickets
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM tickets
     WHERE status = 'Open'"
);

$open_tickets = $result->fetch_assoc()["total"];


// In Progress tickets
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM tickets
     WHERE status = 'In Progress'"
);

$in_progress = $result->fetch_assoc()["total"];


// Resolved tickets
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM tickets
     WHERE status = 'Resolved'"
);

$resolved_tickets = $result->fetch_assoc()["total"];
// Closed tickets
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM tickets
     WHERE status = 'Closed'"
);

$closed_tickets = $result->fetch_assoc()["total"];
// Get recent tickets
$tickets = $conn->query(
    "SELECT
        t.id,
        t.ticket_number,
        t.subject,
        t.priority,
        t.status,
        t.created_at,
        u.full_name AS requester,
        c.category_name

     FROM tickets t

     INNER JOIN users u
        ON t.user_id = u.id

     INNER JOIN ticket_categories c
        ON t.category_id = c.id

     ORDER BY t.created_at DESC

     LIMIT 10"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Agent Dashboard | Helpdesk System</title>

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

        <h2>
            Helpdesk
        </h2>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="tickets.php">
            All Tickets
        </a>

        <a href="my_tickets.php">
            My Assigned Tickets
        </a>

        </a>

        <a href="../auth/logout.php">
            Logout
        </a>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <div class="topbar">

            <h3>
                Support Dashboard
            </h3>

            <p class="text-muted mb-0">

                Welcome,
                <?php
                echo htmlspecialchars(
                    $_SESSION["full_name"]
                );
                ?>

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
                    Open
                </h3>

                <p>
                    <?php echo $open_tickets; ?>
                </p>

            </div>


            <div class="stat-card">

                <h3>
                    In Progress
                </h3>

                <p>
                    <?php echo $in_progress; ?>
                </p>

            </div>


            <div class="stat-card">

    <h3>
        Closed Tickets
    </h3>

    <p>
        <?php echo $closed_tickets; ?>
    </p>

</div>

</div>


        <!-- RECENT TICKETS -->

        <div class="topbar mt-4">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h4 class="mb-1">
                        Recent Tickets
                    </h4>

                    <p class="text-muted mb-0">
                        Latest support requests
                    </p>

                </div>

            </div>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>
                                Ticket #
                            </th>

                            <th>
                                Requester
                            </th>

                            <th>
                                Subject
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Priority
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Created
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($tickets->num_rows > 0): ?>

                        <?php while ($ticket = $tickets->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $ticket["ticket_number"]
                                        );
                                        ?>

                                    </strong>
                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $ticket["requester"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $ticket["subject"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $ticket["category_name"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php if ($ticket["priority"] === "Critical"): ?>

                                        <span class="badge bg-danger">
                                            Critical
                                        </span>

                                    <?php elseif ($ticket["priority"] === "High"): ?>

                                        <span class="badge bg-warning text-dark">
                                            High
                                        </span>

                                    <?php elseif ($ticket["priority"] === "Medium"): ?>

                                        <span class="badge bg-primary">
                                            Medium
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Low
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($ticket["status"] === "Open"): ?>

                                        <span class="badge bg-primary">
                                            Open
                                        </span>

                                    <?php elseif ($ticket["status"] === "In Progress"): ?>

                                        <span class="badge bg-warning text-dark">
                                            In Progress
                                        </span>

                                    <?php elseif ($ticket["status"] === "Resolved"): ?>

                                        <span class="badge bg-success">
                                            Resolved
                                        </span>

                                    <?php elseif ($ticket["status"] === "Closed"): ?>

                                        <span class="badge bg-secondary">
                                            Closed
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-dark">
                                            <?php
                                            echo htmlspecialchars(
                                                $ticket["status"]
                                            );
                                            ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $ticket["created_at"]
                                        )
                                    );
                                    ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-4"
                            >

                                No tickets found.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

</body>

</html>