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
    


// Get stats

//total tickets created (dashboard stat)
$totalTicketsStmt = $dbo->conn->prepare("SELECT COUNT(*) FROM tickets WHERE is_deleted = 0");
$totalTicketsStmt->execute();
$totalTicketsCreated = $totalTicketsStmt->fetchColumn();

//active events for display & pages
$activeTicketsStmt = $dbo->conn->prepare("SELECT COUNT(*) FROM tickets
WHERE is_deleted = 0
AND sale_start <= UTC_TIMESTAMP()
AND sale_end >= UTC_TIMESTAMP()
AND event_end >= UTC_TIMESTAMP()");
$activeTicketsStmt->execute();
$activeTicketsCount = $activeTicketsStmt->fetchColumn();

//expired events
$expiredStmt = $dbo->conn->prepare("SELECT COUNT(*) FROM tickets WHERE is_deleted = 0 AND event_end <= UTC_TIMESTAMP()");
$expiredStmt->execute();
$expiredEvents = $expiredStmt->fetchColumn();

// fake total sales 
$totalSales = rand(500, 5000);

// Get expired events 
$expiredCmd = "SELECT * FROM tickets WHERE is_deleted = 0 AND event_end <= UTC_TIMESTAMP() ORDER BY event_end DESC LIMIT 5";
$expiredStatement = $dbo->conn->prepare($expiredCmd);
$expiredStatement->execute();
$expiredTickets = $expiredStatement->fetchAll(PDO::FETCH_ASSOC);




 //pagination set up
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;


//sort by functionality
$sortField = $_GET['sort'] ?? 'created_at';
$sortOrder = $_GET['order'] ?? 'desc';

$allowedFields = ['event_start', 'created_at', 'price', 'quantity', 'updated_at'];
$allowedOrder = ['asc', 'desc'];

if (!in_array($sortField, $allowedFields)) $sortField = 'created_at';
if (!in_array($order, $allowedOrder)) $order = 'asc';


 
//Tickets for display
$cmd = "SELECT * FROM tickets
WHERE is_deleted = 0
AND sale_start <= UTC_TIMESTAMP()
AND sale_end >= UTC_TIMESTAMP()
AND event_end >= UTC_TIMESTAMP()
ORDER BY $sortField $sortOrder
LIMIT ? OFFSET ?";

$stmt = $dbo->conn->prepare($cmd);
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);



//get total count of tickets
// $comm = "SELECT COUNT(*) FROM tickets
// WHERE is_deleted = 0";
$comm = "SELECT COUNT(*) FROM tickets
WHERE is_deleted = 0
AND sale_start <= UTC_TIMESTAMP()
AND sale_end >= UTC_TIMESTAMP()
AND event_end >= UTC_TIMESTAMP()";

$countStmt= $dbo->conn->prepare($comm);
$countStmt->execute();
$totalTickets = $countStmt-> fetchColumn();
$totalPages = ceil($activeTicketsCount / $perPage);

?> 





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Organizer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body> 
    <div class="container mt-4">
        <h1 class="mb-4">Event Organizer Dashboard</h1>


        <!-- error handeling  -->
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


        <!-- Event Planner stats -->

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary"><?= $totalTicketsCreated ?></h3>
                            <p class="text-muted mb-0">Total Events</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success"><?= $activeTicketsCount ?></h3>
                            <p class="text-muted mb-0">Active</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning"><?= $expiredEvents ?></h3>
                            <p class="text-muted mb-0">Expired</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info"><?= $totalSales ?></h3>
                            <p class="text-muted mb-0">Total Sales</p>
                        </div>
                    </div>
                </div>
            </div>
     


            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">Your Tickets (<?= $totalTickets ?>)</h2>
                <button type="button" class="btn btn-primary" id="toggleFormBtn">
                    <i class="bi bi-plus-circle"></i> Create New Event
                </button>
            </div>

    <!-- collapsable ticket form  -->
        <div id="ticket-form-container" style="display: none; ">
            <section class="mb-4">
                <h2 class="h4 mb-3"><?= !empty($edit) ? 'Edit Ticket' : 'Create New Ticket' ?></h2>
                <?php include 'partials/ticket-form.php'; ?>
            </section>
        </div>



        <section class="tickets-list">
            <?php if (empty($tickets)): ?>
                <p class="text-muted">No tickets created yet. Create your first ticket above!</p>
            <?php else: ?>
                <div class="tickets-grid row g-3">
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="ticket-card card p-3 h-100 col-md-4">
                        <?php if (!empty($ticket['image'])): ?>
                            <img src="<?= htmlspecialchars($ticket['image']) ?>" class="card-img-top mb-2" style="height:150px; object-fit:cover;">
                        <?php else: ?>
                            <img src="assets/placeholder.jpeg" class="card-img-top mb-2" style="height:150px; object-fit:cover;">
                        <?php endif; ?>
                            
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

         <!-- pagination UI -->
         <?php if ($totalPages > 1): ?>
            <nav aria-label="Ticket pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                        <a href="?page=<?= $page - 1 ?>" class="page-link">Previous</a>
                    </li>

                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>






<!-- expired events section -->
<div class="mt-5">
    <div class="card">
        <div class="card-header">
            <h3 class="h5 mb-0"> Past Events (<?= $expiredEvents ?>)</h3>
        </div>
        <div class="card-body">
            <?php if(empty($expiredTickets)): ?>
                <p class="text-muted"> No past events</p>
            <?php else: ?> 
                <div class="row g-3">
                    <?php foreach($expiredTickets as $ticket): ?> 
                        <div class="col-md-4">
                            <div class="card p-3 opacity-75">
                                <h5><?=htmlspecialchars($ticket['title']) ?> 
                            </h5>
                            <p class="text-muted">
                                Ended <?= date('M j, Y', strtotime($ticket['event_end'])) ?>
                            </p>

                            </div>
                        </div>
                        <?php endforeach ?>
                </div>
                <?php endif; ?>
        </div>
    </div>
</div>
        
    </div>
    

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    window.isEditMode = <?= !empty($edit) ? 'true' : 'false'; ?>;
    </script>
    <script src="assets/scripts.js"></script>
</body>
</html>