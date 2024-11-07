// Establish WebSocket connection
let socket = new WebSocket("ws://localhost:8080"); // Replace with your WebSocket server URL and port

// WebSocket events
socket.onopen = function () {
    console.log("WebSocket connection established.");
};

socket.onmessage = function (event) {
    const data = JSON.parse(event.data);

    // Check message type
    if (data.type === "message") {
        const chatBox = document.getElementById("chatBox");
        if (chatBox) {
            const newMessage = document.createElement("p");
            newMessage.innerHTML = `<strong>${data.sender === "audience" ? "Audience" : "You (Artist)"}:</strong> ${data.message}`;
            chatBox.appendChild(newMessage);
            chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll to the latest message
        }
    }
};

socket.onerror = function (error) {
    console.error("WebSocket error:", error);
};

socket.onclose = function () {
    console.log("WebSocket connection closed.");
};

// Sidebar navigation function to show selected section
function showSection(sectionId) {
    // Hide all sections and remove active class from sidebar links
    document.querySelectorAll(".content-section").forEach(section => section.classList.remove("active"));
    document.querySelectorAll(".sidebar a").forEach(link => link.classList.remove("active"));

    // Display selected section and highlight its link
    const selectedSection = document.getElementById(sectionId);
    const selectedLink = document.querySelector(`.sidebar a[onclick="showSection('${sectionId}')"]`);
    if (selectedSection && selectedLink) {
        selectedSection.classList.add("active");
        selectedLink.classList.add("active");
    } else {
        console.error(`Section or link with ID ${sectionId} not found`);
    }
}

// Set default section to display
showSection("profile");

// Profile picture preview function
const profilePictureInput = document.getElementById("profile_picture");
const profilePicturePreview = document.querySelector(".profile-container img");

if (profilePictureInput) {
    profilePictureInput.addEventListener("change", function () {
        const file = profilePictureInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                profilePicturePreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

// Handle message sending (Artist side)
const messageForm = document.getElementById("messageForm");
if (messageForm) {
    messageForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const messageInput = document.getElementById("messageInput");
        const recipientId = document.getElementById("recipientId").value;
        const message = messageInput.value.trim();

        if (message === "") {
            alert("Message cannot be empty!");
            return;
        }

        // Send message via WebSocket
        const messageData = {
            type: "message",
            sender: "artist",
            recipient_id: recipientId,
            message: message
        };
        socket.send(JSON.stringify(messageData));

        // Display message in chat box (artist's side)
        const chatBox = document.getElementById("chatBox");
        if (chatBox) {
            const newMessage = document.createElement("p");
            newMessage.innerHTML = `<strong>You (Artist):</strong> ${message}`;
            chatBox.appendChild(newMessage);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Clear input field
        messageInput.value = "";
    });
} else {
    console.error("Message form not found.");
}

// Handle sending a message to a specific follower
function startConversation(followerId, followerUsername) {
    const recipientIdInput = document.getElementById("recipientId");
    if (recipientIdInput) {
        recipientIdInput.value = followerId; // Set the recipient to the follower's ID
        showSection('messages'); // Switch to the 'messages' section
        
        // Optionally update the chat title or header
        const chatHeader = document.getElementById("chatHeader");
        if (chatHeader) {
            chatHeader.innerHTML = `Chat with ${followerUsername}`;
        }
    } else {
        console.error("Recipient ID input not found.");
    }
}

// Function to display followers with message button
function renderFollowers(followers) {
    const followersList = document.getElementById("followersList");
    followersList.innerHTML = ""; // Clear current list

    followers.forEach(follower => {
        const followerItem = document.createElement("div");
        followerItem.classList.add("follower-item");
        followerItem.innerHTML = `
            <span>${follower.username}</span>
            <button onclick="startConversation(${follower.id}, '${follower.username}')">Message</button>
        `;
        followersList.appendChild(followerItem);
    });
}

// Example of how to use renderFollowers function (to be replaced by actual dynamic data from your backend)
const followersData = [
    { id: 19, username: "believe" },
    { id: 20, username: "praise" }
];
renderFollowers(followersData);
