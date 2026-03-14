function showForm(formID){
    document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
    document.getElementById(formID).classList.add("active");
}

function addAccount(){
    let message = "Account is successfully registered!";
    alert(message);
}