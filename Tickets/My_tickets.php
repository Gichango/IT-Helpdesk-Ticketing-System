<?php

session_start();

require_once "../config/database.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Get all tickets belonging to the logged-in user
$stmt = $conn->prepare(
    "SELECT 
        t.id,
        t.ticket_number,
        t.subject,
        t.priority,
        t.status,
        t.created_at,
        c.category_name
     
     FROM tickets t

     INNER JOIN ticket_categories c
        ON t.category_id = c.id

     WHERE t.user_id = ?

     ORDER BY t.created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$tickets = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Tickets | Helpdesk System</title>

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

        <a href="my_tickets.php">
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
                My Tickets
            </h3>

            <p class="text-muted mb-0">
                View and track your support requests.
            </p>

        </div>


        <div class="topbar">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h4 class="mb-0">
                    Support Requests
                </h4>

                <a
                    href="create.php"
                    class="btn btn-primary"
                >
                    + New Ticket
                </a>

            </div>


            <?php if ($tickets->num_rows > 0): ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>

                                <th>
                                    Ticket #
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

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

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
                                        strtotime($ticket["created_at"])
                                    );
                                    ?>
                                </td>

                                <td>

                                    <a
                                        href="view.php?id=<?php echo $ticket["id"]; ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        View
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="text-center py-5">

                    <h5>
                        No tickets yet
                    </h5>

                    <p class="text-muted">
                        You haven't submitted any support requests.
                    </p>

                    <a
                        href="create.php"
                        class="btn btn-primary"
                    >
                        Create Your First Ticket
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>

</html>

<?php

$stmt->close();

?>