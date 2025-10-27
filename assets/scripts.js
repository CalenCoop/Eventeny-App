$(document).ready(function () {
  $("#cartBtn").on("click", () => {
    //when cart button is clicked, open & reset modal
    $("#review-section, #review-buttons").hide();
    $("#cart-items, #cart-buttons").show();
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
          showAlert("Error: " + response.message, "danger");
        }
      },
      "json"
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
          showAlert("Error: " + response.message, "danger");
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
          showAlert("Error: " + response.message, "danger");
        }
      },
      "json"
    );
  });

  //clear cart
  $(document).on("click", ".clear-cart", function () {
    $.post(
      "cart.php",
      { action: "clear" },
      function (response) {
        if (response.success) {
          $("#cartCount").text(0);
          loadCart();
          showAlert("Cart Cleared", "success");
        } else {
          showAlert("Error: " + response.message, "danger");
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

  //cart items HTML
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
        for (let i = 1; i <= Math.min(10, item.max_quantity); i++) {
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

  //handles carts checkout button
  function showReviewSection() {
    $("#review-section, #review-buttons").show();
    $("#cart-items, #cart-buttons").hide();

    loadReviewData();
  }

  $(document).on("click", "#proceedBtn", function () {
    showReviewSection();
  });

  //handles carts back button
  function showCartSection() {
    $("#review-section, #review-buttons").hide();
    $("#cart-items, #cart-buttons").show();
  }

  $(document).on("click", "#backToCart", function () {
    showCartSection();
  });

  //display items in checkout page
  function displayReviewItems(cart) {
    let html = "";
    let total = 0;

    cart.forEach(function (item) {
      const price = parseFloat(item.price);
      const subtotal = price * item.quantity;
      total += subtotal;

      html += `
         <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>${item.title}</strong><br>
                    <small class="text-muted">${
                      item.quantity
                    } × $${price.toFixed(2)}</small>
                </div>
                <div>$${subtotal.toFixed(2)}</div>
            </div>
        `;
    });
    $("#review-items").html(html);
    $("#review-total").text(`$${total.toFixed(2)}`);
  }
  //load review data
  function loadReviewData() {
    $.get(
      "cart.php?action=get",
      function (response) {
        if (response.success) {
          displayReviewItems(response.cart);
        }
      },
      "json"
    );
  }

  //'complete' order
  function completeOrder() {
    $.post(
      "cart.php",
      {
        action: "complete",
      },
      function (response) {
        if (response.success) {
          showAlert(
            "Order completed successfully. Thank you for your purchase.",
            "success"
          );
          $("#cartModal").modal("hide");
          updateCartCount();
          showCartSection();
        } else {
          showAlert("Error: " + response.message, "danger");
        }
      },
      "json"
    );
  }

  $(document).on("click", "#completeOrder", function () {
    completeOrder();
  });

  // //dashboard collapsible form
  $(document).ready(function () {
    // Toggle form visibility
    $("#toggleFormBtn").on("click", function () {
      const container = $("#ticket-form-container");
      container.slideToggle();
    });

    // Auto-show form if in edit mode
    if (window.isEditMode) {
      $("#ticket-form-container").slideDown();
    }
  });
});
