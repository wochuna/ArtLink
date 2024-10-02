// Function to handle form submission
function handleSubmit(event) {
    event.preventDefault(); // Prevent the default form submission

    // Get form values
    const role = document.getElementById('role').value;
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Basic validation (you can expand this as needed)
    if (!role || !username || !email || !password) {
        alert("Please fill in all fields.");
        return;
    }

    // You can also add more sophisticated validation here (e.g., email format)

    // If validation passes, submit the form
    document.querySelector('form').submit();
}
