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
AND event_end >= UTC_TIMESTAMP()");
$activeTicketsStmt->execute();
$activeTicketsCount = $activeTicketsStmt->fetchColumn();


//expired events count
$expiredStmt = $dbo->conn->prepare("SELECT COUNT(*) FROM tickets WHERE is_deleted = 0 AND event_end <= UTC_TIMESTAMP()");
$expiredStmt->execute();
$expiredEvents = $expiredStmt->fetchColumn();


// Get expired events 
$expiredCmd = "SELECT * FROM tickets WHERE is_deleted = 0 AND event_end <= UTC_TIMESTAMP() ORDER BY event_end DESC LIMIT 5";
$expiredStatement = $dbo->conn->prepare($expiredCmd);
$expiredStatement->execute();
$expiredTickets = $expiredStatement->fetchAll(PDO::FETCH_ASSOC);

// fake total sales 
$totalSales = rand(500, 5000);


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
if (!in_array($sortOrder, $allowedOrder)) $sortOrder = 'desc';


 
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
// $totalPages = ceil($activeTicketsCount / $perPage);
$totalPages = ceil($totalTickets / $perPage);
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
                <!-- Navigation -->
        <div class="mb-3">
            <a href="index.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-eye"></i> View Public Tickets
            </a>
        </div>


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
     


            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0">Your Tickets (<?= $activeTicketsCount ?>)</h2>



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

        <!-- sort by bs fields -->
        <div class="d-flex gap-2 mb-3">
            <strong class="align-self-center">Sort by:</strong>
            <a href="?sort=created_at&order=desc" 
            class="btn btn-outline-secondary btn-sm <?= ($sortField=='created_at' && $sortOrder=='desc')?'active':'' ?>">Newest</a>
            <a href="?sort=created_at&order=asc" 
            class="btn btn-outline-secondary btn-sm <?= ($sortField=='created_at' && $sortOrder=='asc')?'active':'' ?>">Oldest</a>
            <a href="?sort=event_start&order=asc" 
            class="btn btn-outline-secondary btn-sm <?= ($sortField=='event_start' && $sortOrder=='asc')?'active':'' ?>">Upcoming Soonest</a>
            <a href="?sort=price&order=asc" class="btn btn-outline-secondary btn-sm <?= ($sortField=='price' && $sortOrder=='asc')?'active':'' ?>">Lowest Price</a>
            <a href="?sort=price&order=desc" class="btn btn-outline-secondary btn-sm <?= ($sortField=='price' && $sortOrder=='desc')?'active':'' ?>">Highest Price</a>
        </div>
<!-- tickets list -->
        <section class="tickets-list">
            <?php if (empty($tickets)): ?>
                <p class="text-muted">No tickets created yet. Create your first ticket above!</p>
            <?php else: ?>
                <div class="tickets-grid row g-3">
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="ticket-card card p-0 h-100 col-md-4 position-relative shadow-sm">
                            <!-- event image -->
                            <?php if (!empty($ticket['image'])): ?>
                                <img src="<?= htmlspecialchars($ticket['image']) ?>" class="card-img-top" style="height:150px; object-fit:cover;">
                            <?php else: ?>
                                <img src="assets/placeholder.jpeg" class="card-img-top" style="height:150px; object-fit:cover;">
                            <?php endif; ?>

                        <!-- status badge -->
                        <span class="position-absolute top-0 end-0 m-2">
                            <?php 
                            $eventEnd = new DateTime($ticket['event_end'], new DateTimeZone('UTC'));
                            $now = new DateTime('now', new DateTimeZone('UTC'));
                            if ($eventEnd > $now): ?>
                                <span class="badge bg-success rounded-pill">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill">Ended</span>
                            <?php endif; ?>
                        </span>
                    <div class="card-body d-flex flex-column">
                        <!-- title and price -->
                        <div class="mb-3">
                            <h5 class="card-title mb-2">
                            <?= htmlspecialchars($ticket['title'])?> 
                            </h5>
                            <p class="text-primary fw-bold fs-4 mb-0">$<?= number_format($ticket['price'], 2) ?></p>
                        </div>
                    <!-- event details -->
                     <div class="mb-3">
                        <div class="d-flex align-items-center mb-2 small text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt-fill me-2" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                            </svg>
                            <span><?= htmlspecialchars($ticket['location']) ?></span>
                        </div>
                        <div class="d-flex align-items-center mb-2 small text muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar me-2" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                            </svg>
                            <span><?= date('M j, Y h:i a', strtotime($ticket['event_start'])) ?></span>
                        </div>
                        <?php if ($ticket['description']): ?>
                            <div class="d-flex align-items-start mb-2 small text-muted">
                                <svg width="16" height="16" fill="currentColor" class="me-2 mt-1" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                                </svg>
                                <span class="text-truncate"><?= htmlspecialchars(substr($ticket['description'], 0, 50)) ?>...</span>
                            </div>
                            <?php endif; ?>
                     </div>
                     
                     <!-- badges -->
                     <div class="d-flex gap-2 mb-3">
                        <span class="badge <?= $ticket['visibility'] === 'private' ? 'bg-dark' : 'bg-info' ?>">
                            <?= $ticket['visibility'] === 'private' ? '🔒 Private' : '🌍 Public' ?>
                        </span>
                        <span class="badge bg-light text-dark"><?= $ticket['quantity'] ?> tickets left</span>
                    </div>

                    <!-- actions -->
                    <div class="ticket-actions mt-auto pt-3 border-top">
                        <div class="d-grid gap-2">
                            <a href="dashboard.php?edit=<?=(int)$ticket['id'] ?>" class = "btn btn-outline-secondary btn-sm">Edit</a> 


                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value ="<?=(int)$ticket['id'] ?>">
                                <button type= "submit" class= "btn btn-outline-danger btn-sm w-100">Delete</button>
                            </form>
                            </div>
                        </div>
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
                        <a href="?page=<?= $page - 1 ?>&sort=<?= $sortField ?>&order=<?= $sortOrder ?>" class="page-link">Previous</a>
                    </li>

                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&sort=<?= $sortField ?>&order=<?= $sortOrder ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
                        <a href="?page=<?= $page + 1 ?>&sort=<?= $sortField ?>&order=<?= $sortOrder ?>" class="page-link">Next</a>
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