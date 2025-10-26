<?php
require_once "database/database.php";
session_start();
$dbo = new Database();

//time conversion
$toDtUtc = function($v) {
    if (!$v) return null;
    $dt = new DateTime($v); // browser local
    $dt->setTimezone(new DateTimeZone('UTC')); // convert to UTC
    return $dt->format('Y-m-d H:i:s');
};

if($_POST && isset($_POST['action'])){
    try{
        if($_POST['action'] === 'create'){
            $cmd = "INSERT INTO tickets (title, description, location, instructions, price, quantity, sale_start, sale_end, event_start, event_end, visibility, image) VALUES (?, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt= $dbo-> conn-> prepare($cmd);
            $stmt->execute([
                $_POST['title'], 
                $_POST['description'] ?? null,
                $_POST['location'],
                $_POST['instructions'] ?? null,
                $_POST['price'],
                $_POST['quantity'],
                $toDtUtc($_POST['sale_start']),
                $toDtUtc($_POST['sale_end']),
                $toDtUtc($_POST['event_start']),
                $toDtUtc($_POST['event_end']),
                $_POST['visibility'],
                $_POST['image'] ?? null
            ]);
            $_SESSION['success']  = "Ticket created successfully!";
            header("Location: dashboard.php");
            exit;
        }
        
        //handle ticket updates
    if($_POST['action']=== 'update' && !empty($_POST['id'])){
        $cmd = "UPDATE tickets 
            SET title = ?, description = ?, location = ?, instructions = ?, price = ?, quantity= ?, sale_start = ?, sale_end = ?, event_start = ?, event_end = ?, visibility = ?, image = ? 
            WHERE id = ? AND is_deleted = 0";
        $stmt= $dbo->conn->prepare($cmd);
        $stmt->execute([
            $_POST['title'], 
            $_POST['description'] ?? null,
            $_POST['location'],
            $_POST['instructions'] ?? null,
            $_POST['price'],
            $_POST['quantity'],
            $toDtUtc($_POST['sale_start']),
            $toDtUtc($_POST['sale_end']),
            $toDtUtc($_POST['event_start']),
            $toDtUtc($_POST['event_end']),
            $_POST['visibility'],
            $_POST['image'] ?? null, 
            $_POST['id'] 
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
    

    $edit = null; 
    if(!empty($_GET['edit'])){
        $stmt= $dbo->conn->prepare("SELECT * FROM tickets WHERE id = ? AND is_deleted = 0");
        $stmt->execute([$_GET['edit']]);
        $edit = $stmt-> fetch(PDO::FETCH_ASSOC);
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
<script>
console.log("🔥 If you see this, browser is reading <script> tags above.");
</script>
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
        <h2 class="h4 mb-3"><?= !empty($edit) ? 'Edit Ticket' : 'Create New Ticket' ?></h2>
            <?php include 'partials/ticket-form.php'; ?>
        </section>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Your Tickets (<?= count($tickets) ?>)</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ticketModal">
                <i class="bi bi-plus-circle"></i> Create New Event
            </button>
        </div>


        <section class="tickets-list">
            <!-- <h2 class="h4 mb-3">Your Tickets (<?= count($tickets) ?>)</h2> -->
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
                               <a href="dashboard.php?edit=<?=(int)$ticket['id'] ?>" class = "btn btn-outline-secondary btn-sm">Edit</a> 
                               <form method="POST" onsubmit="return confirm('Delete this ticket?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value ="<?=(int)$ticket['id'] ?>">
                                    <button type= "submit" class= "btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <script>
console.log("🔥 If you see this, browser is reading <script> tags above.");
</script>

    <script>
console.log("🔥 If you see this, browser is reading <script> tags above.");
</script>

    <!-- Scripts at the bottom of body -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/scripts.js"></script>
</body>
</html>