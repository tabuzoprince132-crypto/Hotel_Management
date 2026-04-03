console.log("JS WORKING");

// OPEN ADD FORM
function openForm(id){
    document.getElementById("cartForm").style.display = "block";
    document.getElementById("room_id").value = id;
}

function closeForm(){
    document.getElementById("cartForm").style.display = "none";
}

// ADD TO CART
function submitCart(){
    let id = document.getElementById("room_id").value;
    let name = document.getElementById("guest_name").value;
    let inDate = document.getElementById("checkin").value;
    let outDate = document.getElementById("checkout").value;
    let guests = document.getElementById("guests").value;

    if(!name || !inDate || !outDate || !guests){
        alert("Fill all");
        return;
    }

    let xhr = new XMLHttpRequest();
    xhr.open("POST","add_to_cart.php",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");

    xhr.onload = function(){
        let res = JSON.parse(this.responseText);
        alert(res.message);
        if(res.status==="success"){
            loadCart();
            closeForm();
        }
    };

    xhr.send("room_id="+id+"&guest_name="+name+"&checkin="+inDate+"&checkout="+outDate+"&guests="+guests);
}

// LOAD CART
function loadCart(){
    let xhr = new XMLHttpRequest();
    xhr.open("GET","cart.php",true);
    xhr.onload = function(){
        document.getElementById("cartContent").innerHTML = this.responseText;
    };
    xhr.send();
}

// OPEN CART
function openCart(){
    document.getElementById("cartModal").style.display="block";
    loadCart();
}

function closeCart(){
    document.getElementById("cartModal").style.display="none";
}

// REMOVE
function removeItem(index){
    let xhr = new XMLHttpRequest();
    xhr.open("POST","cart_action.php",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");

    xhr.onload = function(){
        let res = JSON.parse(this.responseText);
        alert(res.message);
        loadCart();
    };

    xhr.send("action=remove&index="+index);
}

// OPEN EDIT
function openEdit(index,name,checkin,checkout,guests){
    document.getElementById("edit_index").value=index;
    document.getElementById("edit_guest_name").value=name;
    document.getElementById("edit_checkin").value=checkin;
    document.getElementById("edit_checkout").value=checkout;
    document.getElementById("edit_guests").value=guests;

    document.getElementById("editForm").style.display="block";
}

function closeEdit(){
    document.getElementById("editForm").style.display="none";
}

// EDIT
function submitEdit(){
    let index = document.getElementById("edit_index").value;
    let name = document.getElementById("edit_guest_name").value;
    let inDate = document.getElementById("edit_checkin").value;
    let outDate = document.getElementById("edit_checkout").value;
    let guests = document.getElementById("edit_guests").value;

    let xhr = new XMLHttpRequest();
    xhr.open("POST","cart_action.php",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");

    xhr.onload = function(){
        let res = JSON.parse(this.responseText);
        alert(res.message);
        loadCart();
        closeEdit();
    };

    xhr.send("action=edit&index="+index+"&guest_name="+name+"&checkin="+inDate+"&checkout="+outDate+"&guests="+guests);
}

// EVENT HANDLING
document.addEventListener("click", function(e){

    if(e.target.classList.contains("add-cart-btn")){
        openForm(e.target.dataset.room);
    }

    if(e.target.classList.contains("removeBtn")){
        removeItem(e.target.dataset.index);
    }

    if(e.target.classList.contains("editBtn")){
        openEdit(
            e.target.dataset.index,
            e.target.dataset.name,
            e.target.dataset.checkin,
            e.target.dataset.checkout,
            e.target.dataset.guests
        );
    }
});