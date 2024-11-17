// WebSocket connection setup
let socket = new WebSocket("ws://localhost:8080");

socket.onopen = function () {
    console.log("WebSocket connection established.");
};

socket.onmessage = function (event) {
    const data = JSON.parse(event.data);
    if (data.type === "message") {
        const chatBox = document.getElementById("chatBox");
        if (chatBox) {
            const newMessage = document.createElement("p");
            newMessage.innerHTML = `<strong>${data.sender === "audience" ? "Audience" : "You (Artist)"}:</strong> ${data.message}`;
            chatBox.appendChild(newMessage);
            chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll to the bottom
        }
    }
};

socket.onerror = function (error) {
    console.error("WebSocket error:", error);
    alert("An error occurred with the WebSocket connection. Please try again later.");
};

socket.onclose = function () {
    console.log("WebSocket connection closed.");
};

// Sidebar navigation to display content on the right side
function showSection(sectionId) {
    document.querySelectorAll(".content-section").forEach(section => section.classList.remove("active"));
    document.querySelectorAll(".sidebar a").forEach(link => link.classList.remove("active"));
    
    const selectedSection = document.getElementById(sectionId);
    const selectedLink = document.querySelector(`.sidebar a[onclick="showSection('${sectionId}')"]`);
    
    if (selectedSection && selectedLink) {
        selectedSection.classList.add("active");
        selectedLink.classList.add("active");
    }
}
window.showSection = showSection; // Make function globally accessible

showSection("profile"); // Set default section to display

// Profile picture preview
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
        } else {
            profilePicturePreview.src = ""; // Reset if no file is selected
        }
    });
}

// Handle message sending
const messageForm = document.getElementById("messageForm");
if (messageForm) {
    messageForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const messageInput = document.getElementById("message");
        const recipientId = document.getElementById("recipientId").value;
        const message = messageInput.value.trim();

        if (message === "") {
            alert("Message cannot be empty!");
            return;
        }

        const messageData = {
            type: "message",
            sender: "artist",
            recipient_id: recipientId,
            message: message
        };
        socket.send(JSON.stringify(messageData));

        // Append the sent message to the chat box
        const chatBox = document.getElementById("chatBox");
        if (chatBox) {
            const newMessage = document.createElement("p");
            newMessage.innerHTML = `<strong>You (Artist):</strong> ${message}`;
            chatBox.appendChild(newMessage);
            chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll to the bottom
        }

        messageInput.value = ""; // Clear input field
    });
}

// Start conversation with a specific follower
function startConversation(followerId, followerUsername) {
    const recipientIdInput = document.getElementById("recipientId");
    if (recipientIdInput) {
        recipientIdInput.value = followerId;
        showSection('messages');

        const chatHeader = document.getElementById("chatHeader");
        if (chatHeader) {
            chatHeader.innerHTML = `Chat with ${followerUsername}`;
        }
        
        // Clear previous chat messages
        const chatBox = document.getElementById("chatBox");
        if (chatBox) {
            chatBox.innerHTML = ""; // Clear previous chat messages if needed
        }
    }
}
window.startConversation = startConversation; // Make function globally accessible

// Render followers list
function renderFollowers(followers) {
    const followersList = document.querySelector(".followers-list");
    if (!followersList) return;
    followersList.innerHTML = "";

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
window.renderFollowers = renderFollowers; // Make function globally accessible

// Example followers data (replace with dynamic data from backend)
const followersData = [
    { id: 19, username: "believe" },
    { id: 20, username: "praise" }
];
renderFollowers(followersData); // Call to render followers; replace with dynamic data fetching as needed