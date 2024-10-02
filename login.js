// Function to handle login submission
function handleLogin(event) {
    event.preventDefault(); // Prevent default form submission

    // Get form values
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    // Basic validation
    if (!username || !password) {
        alert("Please fill in both fields.");
        return;
    }

    // Example of a simple check (this is for demo purposes)
    // Replace this with your actual login logic (AJAX, Fetch API, etc.)
    if (username === "admin" && password === "password123") {
        alert("Login successful!");
        // Redirect to a dashboard or homepage
        window.location.href = "dashboard.php"; // Change to your actual dashboard page
    } else {
        alert("Invalid username or password. Please try again.");
    }

    // If using AJAX or Fetch API, you could do the following:
    /*
    fetch('process-login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username, password }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to dashboard
            window.location.href = "dashboard.php"; // Change to your actual dashboard page
        } else {
            alert(data.message);
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        alert("An error occurred. Please try again later.");
    });
    */
}
