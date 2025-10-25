$(document).ready(function () {
  $("#cartBtn").on("click", function () {
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

    // alert(`Adding ${quantity} of "${title}" ${price} to cart`);

    //AJAX call to add to cart
    $.post(
      "cart.php",
      {
        action: "add",
        ticket_id: ticketId,
        quantity: quantity,
      },
      function (response) {
        if (response.success) {
          alert("Added to Cart!");
          updateCartCount();
        } else {
          alert("Error:" + response.message);
        }
      }
    );
  });

  function loadCart() {
    $.get(
      "cart.php?action=get",
      function (response) {
        if (response.success) {
          displayCartItems(response.cart);
        }
      },
      "json"
    );
  }

  function displayCartItems(cart) {
    let html = "";
    if (Object.keys(cart).length === 0) {
      html = '<p class="text-muted">Your cart is empty</p>';
    } else {
      cart.forEach((item) => {
        html += ` <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>${item.title}</strong><br>
                            <small class="text-muted">$${
                              item.price
                            } each</small>
                        </div>
                        <div>
                            <span class="badge bg-primary">${
                              item.quantity
                            }</span>
                            <span class="ms-2">$${(
                              item.price * item.quantity
                            ).toFixed(2)}</span>
                        </div>
                    </div>
                `;
      });
    }
    $("#cart-items").html(html);
  }

  function updateCartCount() {
    $.get("cart.php?action=get", (response) => {
      if (response.success) {
        $("#cartCount").text(response.total_items);
      }
    });
  }
});
