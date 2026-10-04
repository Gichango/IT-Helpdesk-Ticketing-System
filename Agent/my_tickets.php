<?php

session_start();

require_once "../config/database.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

// Only Support Agents can access this page
if ($_SESSION["role_id"] != 2) {
    header("Location: ../dashboard.php");
    exit;
}

$agent_id = $_SESSION["user_id"];
// Get tickets assigned to the logged-in agent$tickets = $conn->query(
   $stmt = $conn->prepare(
    "SELECT
        t.id,
        t.ticket_number,
        t.subject,
        t.priority,
        t.status,
        t.created_at,

        u.full_name AS requester,

        c.category_name,

        a.full_name AS assigned_agent

     FROM tickets t

     INNER JOIN users u
        ON t.user_id = u.id

     INNER JOIN ticket_categories c
        ON t.category_id = c.id

     LEFT JOIN users a
        ON t.assigned_to = a.id

     WHERE t.assigned_to = ?

     ORDER BY t.created_at DESC"
);

$stmt->bind_param("i", $agent_id);

$stmt->execute();

$tickets = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>All Tickets | Helpdesk System</title>

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
                All Tickets
            </h3>

            <p class="text-muted">
                Manage and respond to employee support requests.
            </p>

        </div>


        <div class="topbar">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>Ticket #</th>

                            <th>Requester</th>

                            <th>Subject</th>

                            <th>Category</th>

                            <th>Priority</th>

                            <th>Status</th>

                            <th>Assigned To</th>

                            <th>Created</th>

                            <th>Action</th>

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

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php

                                    if ($ticket["assigned_agent"]) {

                                        echo htmlspecialchars(
                                            $ticket["assigned_agent"]
                                        );

                                    } else {

                                        echo "Unassigned";

                                    }

                                    ?>

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


                                <td>

                                    <a
                                        href="ticket_view.php?id=<?php echo $ticket["id"]; ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        View
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="9"
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