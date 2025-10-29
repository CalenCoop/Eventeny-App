<?php 
require_once 'database/database.php';
require_once 'services/cartService.php';
session_start();

 
//setting responses to JSON 
header('Content-Type: application/json');

//initalize cart if it doesnt exist
    if(!isset($_SESSION['cart'])){
        $_SESSION['cart'] = [];
    }

    $dbo = new Database();
    $action = $_POST['action'] ?? $_GET['action'] ?? "";

    // function-based handlers
    function getCart(){
        $items = cart_get_items();
        $totals = cart_get_totals();
        return [
            'success' => true,
            'cart' => $items,
            'total_items' => $totals['total_items'],
            'total_price' => $totals['total_price']
        ];
    }

    function addToCart($dbo){
        $ticketId = (int)$_POST['ticket_id'];
        $quantity = (int)$_POST['quantity'];

        if ($ticketId <= 0 || $quantity <= 0) {
            return ['success' => false, 'message' => 'Invalid input'];
        }

        $cmd = "SELECT * FROM tickets WHERE id = ? AND visibility = 'public' AND is_deleted = 0";
        $stmt = $dbo-> conn-> prepare($cmd);
        $stmt-> execute([$ticketId]);
        $ticket = $stmt-> fetch();

        if($ticket){
            $now = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
            if($ticket['sale_start'] <= $now && $ticket['sale_end'] >= $now){
                cart_add_or_increment_item($ticket, $quantity);
                return ['success' => true, 'message' => 'Ticket added to cart'];
            }else{
                return ['success' => false, 'message' => 'Ticket not found'];
            }
        }else{
            return ['success' => false, 'message' => 'Ticket does not exist'];
        }
    }

    function updateCart(){
        $ticketId = (int)$_POST['ticket_id'];
        $quantity = (int)$_POST['quantity'];

        if (!isset($_SESSION['cart'][$ticketId])) {
            return ['success' => false, 'message' => 'Ticket not found in cart'];
        }
        $ok = cart_set_item_quantity($ticketId, $quantity);
        if ($ok) {
            return ['success' => true, 'message' => 'Cart updated'];
        }
        return ['success' => false, 'message' => 'Invalid quantity'];
    }

    function removeFromCart(){
        $ticketId = (int)$_POST['ticket_id'];
        $ok = cart_remove_item($ticketId);
        if ($ok) {
            return ['success' => true, 'message' => 'Ticket removed from cart'];
        }
        return ['success' => false, 'message' => 'Ticket not found in cart'];
    }

    function clearCart(){
        cart_clear();
        return ['success' => true, 'message' => ' Cart cleared'];
    }

    function completeOrder(){
        $orderTotal= 0;
        $orderItems = [];

        if (empty($_SESSION['cart'])) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }

        foreach($_SESSION['cart'] as $item){
            $price = (float) $item['price'];
            $subTotal = $price * $item['quantity'];
            $orderTotal += $subTotal;
            $orderItems[] = $item;
        }

        cart_clear();

        return [
            'success' => true,
            'message' => 'Order completed successfully!',
            'order_total' => $orderTotal,
            'items' => $orderItems
        ];
    }

    // routing map
    $handlers = [
        'get' => function(){ return getCart(); },
        'add' => function() use ($dbo){ return addToCart($dbo); },
        'update' => function(){ return updateCart(); },
        'remove' => function(){ return removeFromCart(); },
        'clear' => function(){ return clearCart(); },
        'complete' => function(){ return completeOrder(); }
    ];

    if(isset($handlers[$action])){
        $result = $handlers[$action]();
        echo json_encode($result);
        exit;
    } else if ($action !== ""){
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

?> 