<?php
require_once "database/database.php";
session_start();
$dbo = new Database();

//handeling form submissions
if($_POST && isset($_POST['action'])){
    if($_POST['action'] === 'create'){
        try{
            $cmd = "INSERT INTO tickets (title, description, location, instructions, price, quantity, sale_start, sale_end, event_start, event_end, visibility, image) VALUES (?, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $statement= $dbo-> conn-> prepare($cmd);
            $statement->execute([
                $_POST['title'], 
                $_POST['description'],
                $_POST['location'],
                $_POST['instructions'],
                $_POST['price'],
                $_POST['quantity'],
                $_POST['sale_start'],
                $_POST['sale_end'],
                $_POST['event_start'],
                $_POST['event_end'],
                $_POST['image']
            ]);
            $_SESSION['success']  = "Ticket created successfully!";
            header("Location: dashboard.php");

        }catch(PDOException $e){
            $_SESSION['error'] = "Error creating ticket:" . $e->getMessage();
        }
    }
}

//Tickets for display
$cmd = "SELECT * FROM tickets WHERE is_deleted = 0 ORDER BY created_at DESC";
$statement = $dbo->conn->prepare($cmd);
$statement-> execute();

$tickets = $statement->fetchAll(PDO::FETCH_ASSOC);

?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Organizer Dashboard</title>
    <!-- <link rel="stylesheet" href="assets/style.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body> 
    <div class="container">
        <h1>Event Organizer Dashboard</h1>


        <!-- figure out error handeling & put here -->


        <section>
            <h2>Create new Ticket</h2>
            <?php include 'partials/ticket-form.php'; ?>
        </section>

        <section class="tickets-list">
            <h2>Your Tickets (<?= count($tickets) ?>)</h2>
            <?php if (empty($tickets)): ?>
                <p>No tickets created yet. Create your first ticket above!</p>
            <?php else: ?>
                <div class="tickets-grid">
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="ticket-card">
                            <h3>
                                <?= htmlspecialchars($ticket['title']) ?>
                            </h3>
                            <p class="price">
                                $<?= number_format($ticket['price'], 2) ?>
                            </p>
                            <p class="location">
                                <?= htmlspecialchars($ticket['location']) ?>
                            </p>
                            <p class="quantity">
                                Quantity: <?= $ticket['quantity'] ?>
                            </p>
                            <p class="quantity-left">
                            Tickets left: <?= $ticket['quantity']?>
                            </p>
                            <p class="visibility">
                                Visibility: <?= ucfirst($ticket['visibility']) ?>
                            </p>
                            <p class="event-date">
                                Event: <?= date('M j, Y g:i A', strtotime($ticket['event_start'])) ?>
                            </p>
                            
                            <div class="ticket-actions">
                                <button class="btn btn-edit">Edit</button>
                                <button class="btn btn-delete">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
    


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>