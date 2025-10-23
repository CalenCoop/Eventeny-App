<?php 
require_once 'database/database.php';
$dbo = new Database();

session_start();
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}


$cmd = "SELECT * FROM tickets 
WHERE is_deleted = 0
AND sale_start <= NOW()
AND sale_end >= NOW()
AND visibility = 1 
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
    <div class="containter mt-4">
        <header class="d-flex">
            <div>
                <h1>Available Event Tickets</h1>
                <p class="text-muted">
                    Browse and purchase tickets for upcoming events
                </p>
            </div>
            <button id = "cartBtn" class = "btn btn-outline-primary position-relative" >
            🛒 Cart
                <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
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
                        <?php if ($ticket['image']): ?>
                            <img src="<?= htmlspecialchars($ticket['image']) ?>" class="card-img-top" style ="height:200px; object-fit:cover;">
                        <?php endif;?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <?=htmlspecialchars($ticket['title'])?>
                            </h5>
                            <p class="cart-text text-success fw-bold fs-4">$<?=number_format($ticket['price'], 2) ?></p>

                            <?php if ($tickets['description']):?>
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
                                    <select class="form-select form-select-sm">
                                    <?php for ($i = 1; $i <= min(10, $ticket['quantity']); $i++): ?>
                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                <?php endfor; ?>
                                    </select>
                                    <button class="btn btn-primary flex-grow-1 add-to-cart" data-ticket-id="<?= $ticket['id']?> " data-title="<?= htmlspecialchars($ticket['title']) ?>" data-price ="<?= $ticket['price']?> ">
                                        Add to Cart
                                    </button>
                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>
            <?php endif; ?>

    </section>
    



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>