$(document).ready(function () {
  $("#cartBtn").on("click", () => {
    //when cart button is clicked, open modal
    $("#cartModal").modal("show");
    loadCart();
  });

  //add to cart button click handler
  $(".add-to-cart").on("click", function () {
    // get ticket info from button attributes
    const ticketId = $(this).data("ticket-id");
    const title = $(this).data("title");
    const price = $(this).data("price");
    //get quantity from dropdown
    const quantity = $("#qty-" + ticketId).val();

    //AJAX call to add to cart
    $.post(
      "cart.php",
      {
        action: "add",
        ticket_id: ticketId,
        quantity: quantity,
      },
      (response) => {
        if (response.success) {
          showAlert("Added to Cart!", "success");
          updateCartCount();
        } else {
          alert("Error:" + response.message);
        }
      }
    );
  });

  //update cart quanitity
  $(document).on("change", ".quantity-update", function () {
    const ticketId = $(this).data("ticket-id");
    const quantity = $(this).val();

    $.post(
      "cart.php",
      {
        action: "update",
        ticket_id: ticketId,
        quantity: quantity,
      },
      function (response) {
        if (response.success) {
          loadCart();
          updateCartCount();
        } else {
          alert("Error: " + response.message);
        }
      },
      "json"
    );
  });

  //remove item
  $(document).on("click", ".remove-item", function () {
    const ticketId = $(this).data("ticket-id");

    $.post(
      "cart.php",
      {
        action: "remove",
        ticket_id: ticketId,
      },
      function (response) {
        if (response.success) {
          loadCart();
          updateCartCount();
        } else {
          alert("Error: " + response.message);
        }
      },
      "json"
    );
  });

  //clear cart
  $(document).on("click", ".clear-cart", function () {
    //   $(".clear-cart").on("click", () => {
    //ajax call to empty card
    $.post(
      "cart.php",
      { action: "clear" },
      function (response) {
        if (response.success) {
          $("#cartCount").text(0);
          loadCart();
          showAlert("Cart Cleared", "success");
        } else {
          alert("Error:" + response.message);
        }
      },
      "json"
    );
  });

  function loadCart() {
    $.get(
      "cart.php?action=get",
      (response) => {
        if (response.success) {
          displayCartItems(response.cart);
        }
      },
      "json"
    );
  }

  function updateCartCount() {
    $.get("cart.php?action=get", (response) => {
      if (response.success) {
        $("#cartCount").text(response.total_items);
      }
    });
  }

  function showAlert(message, type = "success", duration = 3000) {
    const alertId = `alert-${Date.now()}`;
    const html = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert" style="min-width: 200px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $("#cart-alert-container").append(html);

    // Automatically remove after time
    setTimeout(() => {
      $(`#${alertId}`).alert("close");
    }, duration);
  }

  function displayCartItems(cart) {
    let html = "";
    if (Object.keys(cart).length === 0) {
      html = '<p class="text-muted">Your cart is empty</p>';
    } else {
      cart.forEach((item) => {
        html += `<div class="d-flex justify-content-between align-items-center mb-2" data-ticket-id="${item.id}">
            <div>
                <strong>${item.title}</strong><br>
                <small class="text-muted">$${item.price} each</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm quantity-update" style="max-width: 80px;" data-ticket-id="${item.id}">`;

        // Add quantity options
        for (let i = 1; i <= item.max_quantity; i++) {
          html += `<option value="${i}" ${
            i === item.quantity ? "selected" : ""
          }>${i}</option>`;
        }

        html += `</select>
                <span class="ms-2">$${(item.price * item.quantity).toFixed(
                  2
                )}</span>
                <button class="btn btn-sm btn-outline-danger remove-item" data-ticket-id="${
                  item.id
                }">×</button>
            </div>
        </div>`;
      });
    }
    $("#cart-items").html(html);
  }
});
