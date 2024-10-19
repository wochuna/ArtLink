// JavaScript to toggle content based on link clicked
document.getElementById('profile-link').addEventListener('click', function() {
    setActiveSection('profile-section');
});

document.getElementById('collaboration-link').addEventListener('click', function() {
    setActiveSection('collaboration-section');
});

document.getElementById('partnership-link').addEventListener('click', function() {
    setActiveSection('partnership-section');
});

document.getElementById('messages-link').addEventListener('click', function() {
    setActiveSection('messages-section');
});

function setActiveSection(sectionId) {
    // Remove active class from all sections
    var sections = document.getElementsByClassName('content-section');
    for (var i = 0; i < sections.length; i++) {
        sections[i].classList.remove('active');
    }
    
    // Hide all sidebar links
    var links = document.querySelectorAll('.sidebar a');
    links.forEach(link => link.classList.remove('active'));

    // Set active class on the selected section and link
    document.getElementById(sectionId).classList.add('active');
    document.getElementById(sectionId.split('-')[0] + '-link').classList.add('active');
}
