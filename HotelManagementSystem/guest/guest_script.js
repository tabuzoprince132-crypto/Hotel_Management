// ── Open Add-to-Cart Form ────────────────────────────────────────────────────
console.log("JS IS WORKING");
function setDatePairMin(checkinEl, checkoutEl) {
    if (!checkinEl || !checkoutEl) return;

    let today    = new Date().toISOString().split("T")[0];
    let tomorrow = new Date(Date.now() + 86400000).toISOString().split("T")[0];

    checkinEl.min  = today;
    checkoutEl.min = tomorrow;

    if (checkinEl.value) {
        let next = new Date(checkinEl.value);
        next.setDate(next.getDate() + 1);
        checkoutEl.min = next.toISOString().split("T")[0];
        if (checkoutEl.value && checkoutEl.value <= checkinEl.value) {
            checkoutEl.value = "";
        }
    }
}

function addDatePairListeners(checkinEl, checkoutEl) {
    if (!checkinEl || !checkoutEl) return;

    checkinEl.addEventListener('change', function () {
        setDatePairMin(checkinEl, checkoutEl);
    });

    checkoutEl.addEventListener('change', function () {
        if (this.value && checkinEl.value && checkinEl.value >= this.value) {
            checkinEl.value = "";
        }
    });
}

function openForm(roomId) {
    document.getElementById("cartForm").style.display = "flex";
    document.getElementById("cartModal").style.display = "none";
    document.getElementById("editForm").style.display = "none";
    document.getElementById("room_id").value = roomId;

    let modalCheckin  = document.getElementById("modal_checkin");
    let modalCheckout = document.getElementById("modal_checkout");
    let searchCheckin = document.getElementById("checkin");
    let searchCheckout = document.getElementById("checkout");
    let searchGuests = document.getElementById("guests");

    modalCheckin.value  = searchCheckin ? searchCheckin.value : "";
    modalCheckout.value = searchCheckout ? searchCheckout.value : "";
    document.getElementById("modal_guests").value   = searchGuests ? searchGuests.value : "";
    document.getElementById("modal_notes").value = "";

    setDatePairMin(modalCheckin, modalCheckout);
}

function closeForm() {
    document.getElementById("cartForm").style.display = "none";
}

// ── Submit Add-to-Cart ───────────────────────────────────────────────────────
function submitCart() {
    let roomId   = document.getElementById("room_id").value;
    let checkin  = document.getElementById("modal_checkin").value;
    let checkout = document.getElementById("modal_checkout").value;
    let guests   = document.getElementById("modal_guests").value;
    let notes    = document.getElementById("modal_notes").value;

    if (!checkin || !checkout || !guests) {
        alert("Please fill in all required fields.");
        return;
    }

    let today = new Date().toISOString().split("T")[0];
    if (checkin < today) {
        alert("Check-in date cannot be in the past.");
        return;
    }
    if (checkout <= checkin) {
        alert("Check-out date must be after check-in date.");
        return;
    }

    let data = new FormData();
    data.append("room_id",  roomId);
    data.append("checkin",  checkin);
    data.append("checkout", checkout);
    data.append("guests",   guests);
    data.append("notes",    notes);

    fetch("add_to_cart.php", { method: "POST", body: data })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            if (res.status === "success") {
                closeForm();
                loadCart(); // refresh cart if open
            }
        })
        .catch(() => alert("Something went wrong. Please try again."));
}

// ── Open / Close Cart Modal ──────────────────────────────────────────────────
function openCart() {
    document.getElementById("cartModal").style.display = "flex";
    document.getElementById("cartForm").style.display = "none";
    document.getElementById("editForm").style.display = "none";
    loadCart();
}

function closeCart() {
    document.getElementById("cartModal").style.display = "none";
}

// ── Load Cart via AJAX ───────────────────────────────────────────────────────
function loadCart() {
    fetch("cart.php")
        .then(res => res.text())
        .then(html => {
            document.getElementById("cartContent").innerHTML = html;
        })
        .catch(() => {
            document.getElementById("cartContent").innerHTML = "<p>Could not load cart.</p>";
        });
}

// ── Remove Item ──────────────────────────────────────────────────────────────
function removeItem(index) {
    if (!confirm("Remove this room from your cart?")) return;

    let data = new FormData();
    data.append("action", "remove");
    data.append("index",  index);

    fetch("cart_action.php", { method: "POST", body: data })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            loadCart();
        })
        .catch(() => alert("Could not remove item."));
}

// ── Open Edit Form ───────────────────────────────────────────────────────────
function openEdit(index, checkin, checkout, guests) {
    document.getElementById("editForm").style.display = "flex";
    document.getElementById("cartModal").style.display = "none";
    document.getElementById("cartForm").style.display = "none";
    document.getElementById("edit_index").value    = index;
    document.getElementById("edit_checkin").value  = checkin;
    document.getElementById("edit_checkout").value = checkout;
    document.getElementById("edit_guests").value   = guests;

    let editCheckin  = document.getElementById("edit_checkin");
    let editCheckout = document.getElementById("edit_checkout");
    setDatePairMin(editCheckin, editCheckout);
}

function closeEdit() {
    document.getElementById("editForm").style.display = "none";
    document.getElementById("cartModal").style.display = "flex";
    loadCart();
}

let modalCheckin   = document.getElementById("modal_checkin");
let modalCheckout  = document.getElementById("modal_checkout");
let editCheckin    = document.getElementById("edit_checkin");
let editCheckout   = document.getElementById("edit_checkout");
addDatePairListeners(modalCheckin, modalCheckout);
addDatePairListeners(editCheckin, editCheckout);

// ── Submit Edit ──────────────────────────────────────────────────────────────
function submitEdit() {
    let index    = document.getElementById("edit_index").value;
    let checkin  = document.getElementById("edit_checkin").value;
    let checkout = document.getElementById("edit_checkout").value;
    let guests   = document.getElementById("edit_guests").value;

    if (!checkin || !checkout || !guests) {
        alert("Please fill in all fields.");
        return;
    }

    let today = new Date().toISOString().split("T")[0];
    if (checkin < today) {
        alert("Check-in date cannot be in the past.");
        return;
    }
    if (checkout <= checkin) {
        alert("Check-out must be after check-in.");
        return;
    }

    let data = new FormData();
    data.append("action",   "edit");
    data.append("index",    index);
    data.append("checkin",  checkin);
    data.append("checkout", checkout);
    data.append("guests",   guests);

    fetch("cart_action.php", { method: "POST", body: data })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            if (res.status === "success") {
                closeEdit();
                loadCart();
            }
        })
        .catch(() => alert("Could not update item."));
}

// ── Checkout — redirect to checkout.php review page ─────────────────────────
function goCheckout() {
    window.location.href = "checkout.php";
}

// ── Event Delegation ─────────────────────────────────────────────────────────
document.addEventListener("click", function (e) {
    // Add-to-cart button on room cards
    if (e.target.classList.contains("add-cart-btn")) {
        openForm(e.target.dataset.room);
    }

    // Remove button inside cart modal
    if (e.target.classList.contains("removeBtn")) {
        removeItem(e.target.dataset.index);
    }

    // Edit button inside cart modal
    if (e.target.classList.contains("editBtn")) {
        openEdit(
            e.target.dataset.index,
            e.target.dataset.checkin,
            e.target.dataset.checkout,
            e.target.dataset.guests
        );
    }
});



function noCart(){
    alert("No rooms is in the cart");
}