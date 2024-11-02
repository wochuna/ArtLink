// Function to handle sidebar link clicks
document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default anchor behavior

        // Get the target section ID from the href attribute
        const targetId = this.getAttribute('href');

        // Smooth scroll to the target section
        const targetElement = document.querySelector(targetId);
        const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;

        window.scrollTo({
            top: elementPosition,
            behavior: 'smooth'
        });

        // Hide all content sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });

        // Show the target content section
        targetElement.classList.add('active');

        // Update active link in sidebar
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.classList.remove('active');
        });
        this.classList.add('active');
    });
});

// Function to start a conversation (if needed)
function startConversation(followerId) {
    const messageSection = document.getElementById('collaboration');
    const messageInput = messageSection.querySelector('#message');
    messageInput.value = `Hello!`; // Pre-fill the message input

    // Optionally, switch to the collaboration section
    setActiveSection('collaboration');
}

// Function to set the active section (if needed)
function setActiveSection(sectionId) {
    // Remove active class from all sections
    var sections = document.getElementsByClassName('content-section');
    for (var i = 0; i < sections.length; i++) {
        sections[i].classList.remove('active');
    }

    // Set active class on the selected section
    document.getElementById(sectionId).classList.add('active');
}