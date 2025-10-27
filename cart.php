<?php 
require_once 'database/database.php';
session_start();

 
//setting responses to JSON 
header('Content-Type: application/json');

//initalize cart if it doesnt exist
    if(!isset($_SESSION['cart'])){
        $_SESSION['cart'] = [];
    }

    $dbo = new Database();
    $action = $_POST['action'] ?? $_GET['action'] ?? "";

    switch($action){
        case 'get':
            //return current cart items
            echo json_encode([
                'success' => true, 
                'cart' => array_values($_SESSION['cart']), 
                'total_items' => array_sum(array_column($_SESSION['cart'], 'quantity')), 
                'total_price' => array_sum(array_map(function($item){
                    return $item['price'] * $item['quantity'];
                }, $_SESSION['cart']))
            ]);
            break;
        case 'add':
            $ticketId = (int)$_POST['ticket_id'];
            $quantity = (int)$_POST['quantity'];

            
            if ($ticketId <= 0 || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }    
            //fetch ticket details from db
            $cmd = "SELECT * FROM tickets WHERE id = ? AND visibility = 'public' AND is_deleted = 0";
            $stmt = $dbo-> conn-> prepare($cmd);
            $stmt-> execute([$ticketId]);
            $ticket = $stmt-> fetch();
            

            if($ticket){
                //check if still in sale window
                $now = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
                if($ticket['sale_start'] <= $now && $ticket['sale_end'] >= $now){
                    //add/update cart item
                    if(isset($_SESSION['cart'][$ticketId])){
                        //ticket already in cart, increase amount
                        $_SESSION['cart'][$ticketId]['quantity'] += $quantity;
                    }else{
                        //Not in session, so add to cart
                        $_SESSION['cart'][$ticketId] = [
                            'id' => $ticket['id'], 
                            'title' => $ticket['title'],
                            'price' => $ticket['price'],
                            'quantity' => $quantity, 
                            'max_quantity' => $ticket['quantity']
                        ]; 
                    }
                    //dont go over available ticket amount
                    if($_SESSION['cart'][$ticketId]['quantity'] > $ticket['quantity']){
                        $_SESSION['cart'][$ticketId]['quantity'] = $ticket['quantity'];
                    }
                    echo json_encode(['success' => true, 'message' => 'Ticket added to cart']);
                }else{
                    echo json_encode(['success' => false, 'message' => 'Ticket not found']);
                    }
            }else{
                echo json_encode([
                    'success' => false, 
                    'message' => 'Ticket does not exist'
                ]);
            }
            break;
        
        case 'update':
            $ticketId = (int)$_POST['ticket_id'];
            $quantity = (int)$_POST['quantity'];
            
            if (isset($_SESSION['cart'][$ticketId])) {
                if ($quantity > 0 && $quantity <= $_SESSION['cart'][$ticketId]['max_quantity']) {
                    $_SESSION['cart'][$ticketId]['quantity'] = $quantity;
                    echo json_encode(['success' => true, 'message' => 'Cart updated']);
                }else{
                    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
                }
            }else{
                echo json_encode(['success' => false, 'message' => 'Ticket not found in cart']);
            }
            break;

        case 'remove':
            $ticketId = (int)$_POST['ticket_id'];
                
            if (isset($_SESSION['cart'][$ticketId])) {
                unset($_SESSION['cart'][$ticketId]);
                echo json_encode(['success' => true, 'message' => 'Ticket removed from cart']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ticket not found in cart']);
            }
            break;
        
        case 'clear':
            $_SESSION['cart'] = [];
            echo json_encode(['success' => true, 'message' => ' Cart cleared']); 
            exit;

        break;
        
        case 'complete':
            $orderTotal= 0;
            $orderItems = []; 

              if (empty($_SESSION['cart'])) {
                    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
                    exit;
                }
            

            foreach($_SESSION['cart'] as $item){
                $price = (float) $item['price'];
                $subTotal = $price * $item['quantity'];
                $orderTotal += $subTotal;
                $orderItems[] = $item; 
            }

            //clear cart after purchase
            $_SESSION['cart']=[];

            echo json_encode([
                'success' => true,
                'message' => 'Order completed successfully!',
                'order_total' => $orderTotal,
                'items' => $orderItems
            ]);
        break; 
        
    };



?> 