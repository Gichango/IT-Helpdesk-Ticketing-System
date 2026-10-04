<?php

session_start();

require_once "../config/database.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

// Make sure a ticket ID was provided
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: my_tickets.php");
    exit;
}

$ticket_id = intval($_GET["id"]);
$user_id = $_SESSION["user_id"];

// Get the ticket, but ONLY if it belongs to this user
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
        u.full_name AS requester_name,
        a.full_name AS assigned_agent

     FROM tickets t

     INNER JOIN ticket_categories c
        ON t.category_id = c.id

     INNER JOIN users u
        ON t.user_id = u.id

     LEFT JOIN users a
        ON t.assigned_to = a.id

     WHERE t.id = ?
     AND t.user_id = ?"
);

$stmt->bind_param("ii", $ticket_id, $user_id);

$stmt->execute();

$result = $stmt->get_result();


// Ticket doesn't exist or doesn't belong to the user
if ($result->num_rows !== 1) {
    header("Location: my_tickets.php");
    exit;
}

$ticket = $result->fetch_assoc();

$stmt->close();
// Add a new comment
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($ticket["status"] === "Closed") {
    die("This ticket is closed. Replies are not allowed.");
}

    $comment = trim($_POST["comment"]);

    if (!empty($comment)) {

        $stmt = $conn->prepare(
            "INSERT INTO ticket_comments
            (ticket_id, user_id, comment)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "iis",
            $ticket_id,
            $user_id,
            $comment
        );

        $stmt->execute();

        $stmt->close();

        // Refresh the page after submitting
        header(
            "Location: view.php?id=" . $ticket_id
        );

        exit;
    }
}


// Get comments for this ticket
$stmt = $conn->prepare(
    "SELECT
        tc.comment,
        tc.created_at,
        u.full_name

     FROM ticket_comments tc

     INNER JOIN users u
        ON tc.user_id = u.id

     WHERE tc.ticket_id = ?

     ORDER BY tc.created_at ASC"
);

$stmt->bind_param("i", $ticket_id);

$stmt->execute();

$comments = $stmt->get_result();

$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($ticket["ticket_number"]); ?>
        | Helpdesk System
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
                    href="my_tickets.php"
                    class="btn btn-outline-primary"
                >
                    ← Back to My Tickets
                </a>

            </div>

        </div>


        <!-- TICKET INFORMATION -->

        <div class="topbar">

            <div class="row">

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

                    <p style="white-space: pre-line;">

                        <?php
                        echo htmlspecialchars(
                            $ticket["description"]
                        );
                        ?>

                    </p>

                </div>


                <div class="col-md-4">

                    <h6>
                        Ticket Information
                    </h6>

                    <hr>

                    <p>
                        <strong>Category:</strong><br>

                        <?php
                        echo htmlspecialchars(
                            $ticket["category_name"]
                        );
                        ?>
                    </p>


                    <p>
                        <strong>Priority:</strong><br>

                        <?php
                        echo htmlspecialchars(
                            $ticket["priority"]
                        );
                        ?>
                    </p>


                    <p>
                        <strong>Status:</strong><br>

                        <?php
                        echo htmlspecialchars(
                            $ticket["status"]
                        );
                        ?>
                    </p>


                    <p>
                        <strong>Assigned Agent:</strong><br>

                        <?php

                        if ($ticket["assigned_agent"]) {

                            echo htmlspecialchars(
                                $ticket["assigned_agent"]
                            );

                        } else {

                            echo "Not assigned";

                        }

                        ?>

                    </p>


                    <p>
                        <strong>Created:</strong><br>

                        <?php
                        echo date(
                            "d M Y, H:i",
                            strtotime($ticket["created_at"])
                        );
                        ?>

                    </p>

                </div>

            </div>

        </div>


        <!-- COMMENTS WILL GO HERE -->

        <div class="topbar mt-4">

    <h4>
        Conversation
    </h4>

    <hr>


    <!-- EXISTING COMMENTS -->

    <?php if ($comments->num_rows > 0): ?>

        <?php while ($comment = $comments->fetch_assoc()): ?>

            <div class="mb-4">

                <div class="d-flex justify-content-between">

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $comment["full_name"]
                        );
                        ?>
                    </strong>

                    <small class="text-muted">

                        <?php
                        echo date(
                            "d M Y, H:i",
                            strtotime(
                                $comment["created_at"]
                            )
                        );
                        ?>

                    </small>

                </div>


                <div class="mt-2 p-3 bg-light rounded">

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $comment["comment"]
                        )
                    );
                    ?>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p class="text-muted">
            No comments yet.
        </p>

    <?php endif; ?>


    <hr>


    <!-- ADD COMMENT -->

    <h5>
        Add a Reply
        <?php if ($ticket["status"] !== "Closed"): ?>
    </h5>

    <form method="POST">

        <div class="mb-3">

            <textarea
                name="comment"
                class="form-control"
                rows="4"
                placeholder="Write your message..."
                required
            ></textarea>

        </div>

        <button
            type="submit"
            class="auth-button"
        >
            Send Reply
        </button>

    </form>
    <?php else: ?>

    <div class="alert alert-secondary">
        This ticket is closed. No further replies can be added.
    </div>

<?php endif; ?>

</div>
</body>

</html>