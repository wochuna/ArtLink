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

    // Create form data
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);

    // Send login request to the server using Fetch API
    fetch('process-login.php', {
        method: 'POST',
        body: formData,  // Sending form data
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to dashboard
            window.location.href = "dashboard.php"; // Change to your actual dashboard page
        } else {
            alert(data.message); // Show error message from the server
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        alert("An error occurred. Please try again later.");
    });
}
