<?php 
require_once 'database/database.php';
$dbo = new Database();

session_start();
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

$cmd = "SELECT * FROM tickets
WHERE is_deleted = 0
AND sale_start <= UTC_TIMESTAMP()
AND sale_end >= UTC_TIMESTAMP()
AND event_end >= UTC_TIMESTAMP()
AND visibility = 'public'
ORDER BY event_start ASC";

$stmt = $dbo->conn->prepare($cmd);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);



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
    <div class="container mt-4">
        
        <header class="d-flex justify-content-between mb-3">
        <div id="cart-alert-container" class="position-fixed top-0 start-50 translate-middle-x w-50 text-center mt-3" style="z-index: 1050;">
    <!-- alerts go here -->
        </div>
            <div>
                <h1>Available Event Tickets</h1>
                <p class="text-muted">
                    Browse and purchase tickets for upcoming events
                </p>
            </div>
            <button id = "cartBtn" class = "btn btn-outline-primary position-relative" >
            🛒 Cart
                <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0 ?></span>
            </button>
        </header>

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
                        <div class="card h-100">
                            <?php if ($ticket['image']): ?>
                                <img src="<?= htmlspecialchars($ticket['image']) ?>" class="card-img-top" style ="height:200px; object-fit:cover;">
                            <?php endif;?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <?=htmlspecialchars($ticket['title'])?>
                                </h5>
                                <p class="cart-text text-success fw-bold fs-4">$<?=number_format($ticket['price'], 2) ?></p>

                                <?php if ($ticket['description']):?>
                                <p class="card-text">
                                    <?= htmlspecialchars($ticket['description'])?>
                                </p>
                                <?php endif; ?> 
                                <div class="card-text small text-muted mb-3">
                                    <div class="">
                                    📍 <?= htmlspecialchars($ticket['location'])?> 
                                    </div> 
                                    <div>
                                        📅 <?= date('M j, Y g:i A', strtotime($ticket['event_start'])) ?>
                                    </div>
                                    <!-- <div class="">
                                        🎫<?= $ticket['quantity']?> tickets left  
                                    </div> -->
                                </div>
                                <div class="mt-auto">
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