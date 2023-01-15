var checkbox = document.getElementById("basic_auth_for_wp_admin_show_password");
var passwordInput = document.getElementById("basic_auth_for_wp_admin_password");

checkbox.addEventListener("change", function() {
    passwordInput.type = this.checked ? "text" : "password";
});
