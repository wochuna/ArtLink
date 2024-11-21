function handleLogin(event) {
    event.preventDefault(); 
  
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

 
    if (!username || !password) {
        alert("Please fill in both fields.");
        return;
    }

   
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);

    
    fetch('process-login.php', {
        method: 'POST',
        body: formData,  
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
           
            window.location.href = "dashboard.php"; 
        } else {
            alert(data.message); 
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        alert("An error occurred. Please try again later.");
    });
}
