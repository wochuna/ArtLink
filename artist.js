function showSection(sectionId) {
    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => section.classList.remove('active'));

    // Show the selected section
    const activeSection = document.getElementById(sectionId);
    if (activeSection) {
        activeSection.classList.add('active');
    }

    // Update active link styling
    const links = document.querySelectorAll('.sidebar a');
    links.forEach(link => link.classList.remove('active'));
    const activeLink = document.querySelector(`.sidebar a[onclick="showSection('${sectionId}')"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// Start conversation function (if needed)
function startConversation(followerId) {
    // Logic to initiate a conversation with the follower
    document.getElementById('artist_id').value = followerId; // Set artist ID in the form
    showSection('messages'); // Show messages section
}