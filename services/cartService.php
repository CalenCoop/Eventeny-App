<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function cart_ensure_initialized() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function cart_add_or_increment_item($ticket, $quantity) {
    cart_ensure_initialized();
    $ticketId = (int)$ticket['id'];
    if (isset($_SESSION['cart'][$ticketId])) {
        $_SESSION['cart'][$ticketId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$ticketId] = [
            'id' => $ticket['id'],
            'title' => $ticket['title'],
            'price' => $ticket['price'],
            'quantity' => $quantity,
            'max_quantity' => $ticket['quantity']
        ];
    }
    if ($_SESSION['cart'][$ticketId]['quantity'] > (int)$ticket['quantity']) {
        $_SESSION['cart'][$ticketId]['quantity'] = (int)$ticket['quantity'];
    }
}

function cart_set_item_quantity($ticketId, $quantity) {
    cart_ensure_initialized();
    if (!isset($_SESSION['cart'][$ticketId])) {
        return false;
    }
    $max = (int)($_SESSION['cart'][$ticketId]['max_quantity'] ?? PHP_INT_MAX);
    if ($quantity <= 0 || $quantity > $max) {
        return false;
    }
    $_SESSION['cart'][$ticketId]['quantity'] = $quantity;
    return true;
}

function cart_decrement_or_remove_item($ticketId) {
    cart_ensure_initialized();
    if (!isset($_SESSION['cart'][$ticketId])) {
        return false;
    }
    if ($_SESSION['cart'][$ticketId]['quantity'] > 1) {
        $_SESSION['cart'][$ticketId]['quantity'] -= 1;
    } else {
        unset($_SESSION['cart'][$ticketId]);
    }
    return true;
}

function cart_remove_item($ticketId) {
    cart_ensure_initialized();
    if (isset($_SESSION['cart'][$ticketId])) {
        unset($_SESSION['cart'][$ticketId]);
        return true;
    }
    return false;
}

function cart_clear() {
    $_SESSION['cart'] = [];
}

function cart_get_items() {
    cart_ensure_initialized();
    return array_values($_SESSION['cart']);
}

function cart_get_totals() {
    cart_ensure_initialized();
    $totalItems = array_sum(array_column($_SESSION['cart'], 'quantity'));
    $totalPrice = array_sum(array_map(function($item){
        return $item['price'] * $item['quantity'];
    }, $_SESSION['cart']));
    return [
        'total_items' => $totalItems,
        'total_price' => $totalPrice
    ];
}


