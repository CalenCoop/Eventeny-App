<?php 
require_once 'database/database.php';
$dbo = new Database();

session_start();
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

///sort by functionality
$sortField = $_GET['sort'] ?? 'created_at';
$sortOrder = $_GET['order'] ?? 'desc';

$allowedFields = ['event_start', 'created_at', 'price', 'quantity', 'updated_at'];
$allowedOrder = ['asc', 'desc'];

if (!in_array($sortField, $allowedFields)) $sortField = 'created_at';
if (!in_array($sortOrder, $allowedOrder)) $sortOrder = 'desc';


//Max amount of events per page setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage; 


//fetch tickets for the current page
$cmd = "SELECT * FROM tickets
WHERE is_deleted = 0
AND sale_start <= UTC_TIMESTAMP()
AND sale_end >= UTC_TIMESTAMP()
AND event_end >= UTC_TIMESTAMP()
AND visibility = 'public'
ORDER BY $sortField $sortOrder
LIMIT ? OFFSET ?";

$stmt = $dbo->conn->prepare($cmd);
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

//get total # of tickets for page calculation
$comm = "SELECT COUNT(*) FROM tickets
WHERE is_deleted = 0
AND sale_start <= UTC_TIMESTAMP()
AND sale_end >= UTC_TIMESTAMP()
AND event_end >= UTC_TIMESTAMP()
AND visibility = 'public'";

$countStmt= $dbo->conn->prepare($comm);
$countStmt->execute();
$totalTickets = $countStmt-> fetchColumn();
$totalPages = ceil($totalTickets / $perPage);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Index Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="bg-primary bg-gradient text-white py-5 mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold mb-3">Discover Amazing Events</h1>
                    <p class="lead">Browse and purchase tickets for upcoming events</p>
                    <a href="dashboard.php" class="text-white-50 small" style="text-decoration: underline;"> Organizer Dashboard →</a>
                </div>
                <div class="col-md-4 text-end">
                    <button id="cartBtn" class="btn btn-light btn-lg position-relative">
                        🛒 Cart
                        <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0 ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
        
    <!-- display tickets -->
    <div class="container my-5">
        <div id="cart-alert-container" class="position-fixed top-0 start-50 translate-middle-x w-50 text-center mt-3" style="z-index: 1050;">
        <!-- alerts go here -->
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
        <section class="tickets-section">
            <?php if(empty($tickets)):?> 
                <div class="text-center py-5">
                    <h3 class="text-muted">
                        No ticets available at the moment.
                    </h3>
                    <p>
                        Check back later for upcoming events!
                    </p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($tickets as $ticket): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm position-relative" style="transition: transform 0.2s">

                                <!-- Event img  -->
                                <?php if (!empty($ticket['image'])): ?>
                                    <img src="<?= htmlspecialchars($ticket['image']) ?>" class="card-img-top" style="height:150px; object-fit:cover;">
                                    <?php else: ?>
                                    <img src="assets/placeholder.jpeg" class="card-img-top" style="height:150px; object-fit:cover;">
                                <?php endif; ?>

                                <div class="card-body d-flex flex-column">
                                    <!-- title and price -->
                                    <div class="mb-3">
                                        <h5 class="card-title mb-2">
                                            <?=htmlspecialchars($ticket['title'])?>
                                        </h5>
                                        <p class="cart-text text-success fw-bold fs-4 mb-0">$<?=number_format($ticket['price'], 2) ?></p>
                                    </div>

                                    <!-- description -->
                                    <?php if ($ticket['description']):?>
                                    <p class="card-text text-muted small mb-3">
                                        <?= htmlspecialchars(substr($ticket['description'], 0, 100)) ?><?= strlen($ticket['description']) > 100 ? '...' : '' ?>
                                    </p>
                                    <?php endif; ?> 

                                    <!-- key details  -->
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-2 small text-muted">
                                            <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                            </svg>
                                            <span><?= htmlspecialchars($ticket['location']) ?></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2 small text-muted">
                                            <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                                        </svg>
                                        <span><?= date('M j, Y g:i A', strtotime($ticket['event_start'])) ?></span>
                                        </div>
                                    </div>

                                    <!-- add to cart section  -->

                                    <div class="mt-auto pt-3 border-top">
                                        <div class="d-flex gap-2 align-items-center">
                                            <select class="form-select form-select-sm" style="max-width: 80px;" id = "qty-<?= $ticket['id'] ?>">
                                            <?php for ($i = 1; $i <= min(10, $ticket['quantity']); $i++): ?>
                                                            <option value="<?= $i ?>"><?= $i ?></option>
                                                        <?php endfor; ?>
                                            </select>
                                            <button class="btn btn-primary flex-grow-1 add-to-cart" data-ticket-id="<?= $ticket['id'] ?>" data-title="<?= htmlspecialchars($ticket['title']) ?>" data-price ="<?= $ticket['price']?> ">
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

        </section>
    </div>

    <!-- pagination UI -->
    <?php if($totalPages > 1): ?>
        <nav aria-label="Ticket pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                    <a href="?page=<?= $page - 1 ?>&sort=<?= $sortField ?>&order=<?= $sortOrder ?>" class="page-link">Previous</a>
                </li>

                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&sort=<?= $sortField ?>&order=<?= $sortOrder ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
                    <a href="?page=<?= $page + 1 ?>&sort=<?= $sortField ?>&order=<?= $sortOrder ?>" class="page-link">Next</a>
                </li>
            </ul>
        </nav> 
        <?php endif ?>

  
        <!-- cart modal ui -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Shopping Cart
                    </h5>
                    <button type="button" class= "btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="cart-items">
                        <!-- cart items load here -->
                    </div>
                    <div id="review-section" style="display: none;">
                        <h6 class = "mb-3">Order Review</h6>
                        <div id="review-items">
                            <!-- review info loads here -->
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Total:</h5>
                            <h5 id="review-total">$0.00</h5>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- cart buttons -->
                     <div id="cart-buttons">
                         <button type="button" class= "btn btn-outline-primary clear-cart"> 
                            Empty Cart
                        </button>
                         <button type="button" class= "btn btn-secondary" data-bs-dismiss="modal">
                             Continue Shopping
                            </button>
                         <button type="button" class="btn btn-primary" id="proceedBtn">
                            Checkout
                        </button>
                    </div>
                    <!-- review buttons -->
                     <div id="review-buttons" style="display: none;">
                        <button type="button" class="btn btn-secondary" id="backToCart">
                            Back to Cart
                        </button>
                        <button type="button" class="btn btn-success" id="completeOrder">
                            Complete Order
                        </button>
                     </div>
                         
                </div>
            </div>
        </div>
    </div>
    


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/scripts.js"></script>
</body>
</html>