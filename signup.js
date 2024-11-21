function handleSubmit(event) {
    event.preventDefault(); 
  
    const role = document.getElementById('role').value;
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    
    if (!role || !username || !email || !password) {
        alert("Please fill in all fields.");
        return;
    }

    
    document.querySelector('form').submit();
}
