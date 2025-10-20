<?php
require_once "database/database.php";
session_start();
$dbo = new Database();

// handeling form submissions
if($_POST && isset($_POST['action'])){
    if($_POST['action'] === 'create'){
        try{
            $cmd = "INSERT INTO tickets (title, description, location, instructions, price, quantity, sale_start, sale_end, event_start, event_end, visibility, image) VALUES (?, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt= $dbo-> conn-> prepare($cmd);
            $stmt->execute([
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
                $_POST['visibility'],
                $_POST['image']
            ]);
            $_SESSION['success']  = "Ticket created successfully!";
            header("Location: dashboard.php");

        //handle ticket/form updates
        if($_POST['action']=== 'update' && !empty($_POST['id'])){
            $cmd = "UPDATE tickets SET title = ?, description = ?, location = ?, instruction = ?, price = ?, quantity=?, sale_start = ?, sale_end = ?, event_start = ?, event_end = ?, visibility = ?, image = ? 
            WHERE id = ? AND is_deleted = 0";
            $stmt= $dbo->conn->prepare($cmd);
            $stmt->execute([
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
                $_POST['visibility'],
                $_POST['image']
            ]);
            $_SESSION['success'] = "Ticket updated successfully.";
            header("Location: dashboard.php"); 
            exit;
        }

        //soft-delete ticket
        if($_POST['action']==='delete' && !empty($_POST['id'])){
            $stmt = $dbo->conn->prepare("UPDATE tickets SET is_deleted = 1 WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['success'] = "Ticket deleted.";
            header("Location: dashboard.php");
            exit;
        }
        
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
    <div class="container mt-4">
        <h1 class="mb-4">Event Organizer Dashboard</h1>


        <!-- figure out error handeling & put here -->
        <?php if(!empty($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert">
                <?= $_SESSION['success']; ?>
                <!-- have to unset so it doesnt display on refresh -->
                <?php unset($_SESSION['success']);?>  
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            
         <?php elseif(!empty($_SESSION['error'])): ?> 
            <div class="alert alert-danger" role="alert">
                <?= $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?> 
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>


        <section class="mb-4">
            <h2 class="h4 mb-3">Create new Ticket</h2>
            <?php include 'partials/ticket-form.php'; ?>
        </section>

        <section class="tickets-list">
            <h2 class="h4 mb-3">Your Tickets (<?= count($tickets) ?>)</h2>
            <?php if (empty($tickets)): ?>
                <p class="text-muted">No tickets created yet. Create your first ticket above!</p>
            <?php else: ?>
                <div class="tickets-grid row g-3">
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="ticket-card card p-3 h-100 col-md-4">
                            <h3 class="card-title h5">
                                <?= htmlspecialchars($ticket['title']) ?>
                            </h3>
                            <p class="price card-text fw-semibold">
                                $<?= number_format($ticket['price'], 2) ?>
                            </p>
                            <p class="location card-text">
                                <?= htmlspecialchars($ticket['location']) ?>
                            </p>
                            <p class="quantity card-text">
                                Quantity: <?= $ticket['quantity'] ?>
                            </p>
                            <p class="quantity-left card-text">
                            Tickets left: <?= $ticket['quantity']?>
                            </p>
                            <p class="visibility card-text">
                                Visibility: <?= ucfirst($ticket['visibility']) ?>
                            </p>
                            <p class="event-date card-text">
                                Event: <?= date('M j, Y g:i A', strtotime($ticket['event_start'])) ?>
                            </p>
                            
                            <div class="ticket-actions d-flex gap-2 mt-2">
                                <button class="btn btn-warning btn-sm">Edit</button>
                                <button class="btn btn-danger btn-sm">Delete</button>
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