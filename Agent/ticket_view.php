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



// Make sure a ticket ID was provided
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: tickets.php");
    exit;
}

$ticket_id = intval($_GET["id"]);
// Handle ticket actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Assign ticket to the logged-in agent
    if (isset($_POST["assign_ticket"])) {

        $agent_id = $_SESSION["user_id"];

        $stmt = $conn->prepare(
            "UPDATE tickets
             SET assigned_to = ?
             WHERE id = ?"
        );

        $stmt->bind_param("ii", $agent_id, $ticket_id);

        if ($stmt->execute()) {

            header(
                "Location: ticket_view.php?id=" . $ticket_id
            );

            exit;

        } else {

            die("Failed to assign ticket.");
        }

        $stmt->close();
    }


    // Update ticket status
    if (isset($_POST["update_status"])) {

        $new_status = $_POST["status"];

        // Only allow valid statuses
        $allowed_statuses = [
            "Open",
            "In Progress",
            "Resolved",
            "Closed"
        ];

        if (!in_array($new_status, $allowed_statuses, true)) {
            die("Invalid ticket status.");
        }

        $stmt = $conn->prepare(
            "UPDATE tickets
             SET status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $new_status,
            $ticket_id
        );

        if ($stmt->execute()) {

            header(
                "Location: ticket_view.php?id=" . $ticket_id
            );

            exit;

        } else {

            die("Failed to update ticket status.");
        }

        $stmt->close();
    }
    // Resolve ticket
if (isset($_POST["resolve_ticket"])) {

    $resolved_status = "Resolved";

    $stmt = $conn->prepare(
        "UPDATE tickets
         SET status = ?
         WHERE id = ?"
    );

    $stmt->bind_param(
        "si",
        $resolved_status,
        $ticket_id
    );

    if ($stmt->execute()) {

        header(
            "Location: ticket_view.php?id=" . $ticket_id
        );

        exit;

    } else {

        die("Failed to resolve ticket.");
    }

    $stmt->close();
}

// Close ticket
if (isset($_POST["close_ticket"])) {

    $closed_status = "Closed";

    $stmt = $conn->prepare(
        "UPDATE tickets
         SET status = ?
         WHERE id = ?"
    );

    $stmt->bind_param(
        "si",
        $closed_status,
        $ticket_id
    );

    if ($stmt->execute()) {

        header(
            "Location: ticket_view.php?id=" . $ticket_id
        );

        exit;

    } else {

        die("Failed to close ticket.");
    }

    $stmt->close();
}

    // Add a reply to the ticket
    if (isset($_POST["send_reply"])) {
        $status_stmt = $conn->prepare(
    "SELECT status
     FROM tickets
     WHERE id = ?"
);

$status_stmt->bind_param("i", $ticket_id);
$status_stmt->execute();

$status_result = $status_stmt->get_result();
$status_ticket = $status_result->fetch_assoc();

$status_stmt->close();

if (!$status_ticket || $status_ticket["status"] === "Closed") {
    die("This ticket is closed. Replies are not allowed.");
}

        $message = trim($_POST["message"] ?? "");

        if ($message === "") {
            die("Reply cannot be empty.");
        }

        $user_id = $_SESSION["user_id"];

       $stmt = $conn->prepare(
    "INSERT INTO ticket_comments
    (ticket_id, user_id, comment)
    VALUES (?, ?, ?)"
);

        $stmt->bind_param(
            "iis",
            $ticket_id,
            $user_id,
            $message
        );

        if ($stmt->execute()) {

            header(
                "Location: ticket_view.php?id=" . $ticket_id
            );

            exit;

        } else {

            die("Failed to send reply.");
        }

        $stmt->close();
    }

}



// Get ticket information
$stmt = $conn->prepare(
    "SELECT
        t.id,
        t.ticket_number,
        t.subject,
        t.description,
        t.priority,
        t.status,
        t.created_at,
        t.updated_at,

        c.category_name,

        u.full_name AS requester,

        a.full_name AS assigned_agent

     FROM tickets t

     INNER JOIN ticket_categories c
        ON t.category_id = c.id

     INNER JOIN users u
        ON t.user_id = u.id

     LEFT JOIN users a
        ON t.assigned_to = a.id

     WHERE t.id = ?"
);

$stmt->bind_param("i", $ticket_id);

$stmt->execute();

$result = $stmt->get_result();


// Ticket doesn't exist
if ($result->num_rows !== 1) {
    header("Location: tickets.php");
    exit;
}

$ticket = $result->fetch_assoc();

$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Ticket Details | Helpdesk
    </title>

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


        <!-- HEADER -->

        <div class="topbar">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h3>
                        Ticket Details
                    </h3>

                    <p class="text-muted mb-0">

                        <?php
                        echo htmlspecialchars(
                            $ticket["ticket_number"]
                        );
                        ?>

                    </p>

                </div>


                <a
                    href="tickets.php"
                    class="btn btn-outline-primary"
                >
                    ← Back to Tickets
                </a>

            </div>

        </div>


        <!-- TICKET DETAILS -->

        <div class="topbar mt-4">

            <div class="row">


                <!-- LEFT SIDE -->

                <div class="col-md-8">

                    <h4>

                        <?php
                        echo htmlspecialchars(
                            $ticket["subject"]
                        );
                        ?>

                    </h4>

                    <hr>


                    <h6>
                        Description
                    </h6>

                    <div class="p-3 bg-light rounded">

                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $ticket["description"]
                            )
                        );

                        ?>

                    </div>

                </div>


                <!-- RIGHT SIDE -->

                <div class="col-md-4">

                    <h6>
                        Ticket Information
                    </h6>

                    <hr>


                    <p>

                        <strong>
                            Requester:
                        </strong>

                        <br>

                        <?php
                        echo htmlspecialchars(
                            $ticket["requester"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Category:
                        </strong>

                        <br>

                        <?php
                        echo htmlspecialchars(
                            $ticket["category_name"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Priority:
                        </strong>

                        <br>

                        <?php
                        echo htmlspecialchars(
                            $ticket["priority"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Status:
                        </strong>

                        <br>

                        <span class="badge bg-primary">

                            <?php
                            echo htmlspecialchars(
                                $ticket["status"]
                            );
                            ?>

                        </span>

                    </p>


                    <p>

                        <strong>
                            Assigned To:
                        </strong>

                        <br>

                        <?php

                        if ($ticket["assigned_agent"]) {

                            echo htmlspecialchars(
                                $ticket["assigned_agent"]
                            );

                        } else {

                            echo "Unassigned";

                        }

                        ?>

                    </p>


                    <p>

                        <strong>
                            Created:
                        </strong>

                        <br>

                        <?php
                        echo date(
                            "d M Y, H:i",
                            strtotime(
                                $ticket["created_at"]
                            )
                        );
                        ?>

                    </p>

                </div>

            </div>

        </div>


        <!-- ACTIONS -->

        <div class="topbar mt-4">

            <h5>
                Ticket Actions
            </h5>

            <hr>


           <form method="POST" style="display: inline;">

    <button
        type="submit"
        name="assign_ticket"
        class="btn btn-primary"
    >
        Assign to Me
    </button>

</form>
<form method="POST" class="mt-3">

    <label for="status" class="form-label">
        Change Status
    </label>

    <select
        name="status"
        id="status"
        class="form-select"
        style="max-width: 300px;"
    >

        <option value="Open"
            <?php if ($ticket["status"] === "Open") echo "selected"; ?>>
            Open
        </option>

        <option value="In Progress"
            <?php if ($ticket["status"] === "In Progress") echo "selected"; ?>>
            In Progress
        </option>

        <option value="Resolved"
            <?php if ($ticket["status"] === "Resolved") echo "selected"; ?>>
            Resolved
        </option>

        <option value="Closed"
            <?php if ($ticket["status"] === "Closed") echo "selected"; ?>>
            Closed
        </option>

    </select>

    <button
        type="submit"
        name="update_status"
        class="btn btn-dark mt-2"
    >
        Update Status
    </button>

</form>


            <form method="POST" class="d-inline">

    <button
        type="submit"
        name="resolve_ticket"
        class="btn btn-success"
    >
        Resolve Ticket
    </button>

</form>
<form method="POST" class="d-inline">

    <button
        type="submit"
        name="close_ticket"
        class="btn btn-secondary"
    >
        Close Ticket
    </button>

</form>
        </div>


        <!-- CONVERSATION -->

        <div class="topbar mt-4">

            <h4>
                Conversation
            </h4>

            <hr>

           <?php

$reply_stmt = $conn->prepare(
    "SELECT
        ticket_comments.comment,
        ticket_comments.created_at,
        users.full_name
     FROM ticket_comments
     INNER JOIN users
        ON ticket_comments.user_id = users.id
     WHERE ticket_comments.ticket_id = ?
     ORDER BY ticket_comments.created_at ASC"
);
$reply_stmt->bind_param("i", $ticket_id);

$reply_stmt->execute();

$replies = $reply_stmt->get_result();

?>

<?php if ($replies->num_rows > 0): ?>

    <?php while ($reply = $replies->fetch_assoc()): ?>

        <div class="card mb-3">

            <div class="card-body">

                <h6 class="mb-1">
                    <?php echo htmlspecialchars($reply["full_name"]); ?>
                </h6>

                <small class="text-muted">
                    <?php echo htmlspecialchars($reply["created_at"]); ?>
                </small>

                <p class="mt-3 mb-0">
                    <?php echo nl2br(htmlspecialchars($reply["comment"])); ?>
                </p>

            </div>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <p class="text-muted">
        No replies yet.
    </p>

<?php endif; ?>

<?php $reply_stmt->close(); ?>

        </div>
        <?php if ($ticket["status"] !== "Closed"): ?>
        <form method="POST" class="mt-4">

    <label for="message" class="form-label">
        Add a Reply
    </label>

    <textarea
        name="message"
        id="message"
        class="form-control"
        rows="4"
        placeholder="Type your reply..."
        required
    ></textarea>

    <button
        type="submit"
        name="send_reply"
        class="btn btn-primary mt-3"
    >
        Send Reply
    </button>

</form>
<?php else: ?>

    <div class="alert alert-secondary mt-4">
        This ticket is closed. No further replies can be added.
    </div>

<?php endif; ?>


    </main>

</div>

</body>

</html>