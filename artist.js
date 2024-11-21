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
            chatBox.scrollTop = chatBox.scrollHeight;
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
window.showSection = showSection; 
showSection("profile"); 


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
            profilePicturePreview.src = ""; 
        }
    });
}


// Handle message form submission
const messageForm = document.getElementById("messageForm");
if (messageForm) {
    messageForm.addEventListener("submit", function (e) {
        e.preventDefault();
        
        const messageInput = document.getElementById("message");
        const recipientId = document.getElementById("recipientId").value;
        const message = messageInput.value.trim();

        if (!message) {
            alert("Message cannot be empty!");
            return;
        }

        const messageData = {
            type: "message",
            sender: "artist",
            recipient_id: recipientId,
            message: message
        };

        // Send the message via WebSocket
        socket.send(JSON.stringify(messageData));

        // Update the chat box with the new message
        const chatBox = document.getElementById("chatBox");
        if (chatBox) {
            const newMessage = document.createElement("div");
            newMessage.classList.add("message", "sent");
            newMessage.innerHTML = `
                <strong>You (Artist):</strong> ${message}
                <span class="timestamp">${new Date().toLocaleTimeString()}</span>
            `;
            chatBox.appendChild(newMessage);
            chatBox.scrollTop = chatBox.scrollHeight; // Scroll to the latest message
        }

        messageInput.value = ""; // Clear the input field
    });
}

// Start a new conversation with a follower
function startConversation(followerId, followerUsername) {
    const recipientIdInput = document.getElementById("recipientId");
    const chatHeader = document.getElementById("chatHeader");
    const chatBox = document.getElementById("chatBox");

    if (recipientIdInput && chatHeader && chatBox) {
        recipientIdInput.value = followerId;
        showSection('messages'); // Function to display the messages section

        chatHeader.innerHTML = `Chat with ${followerUsername}`;
        chatBox.innerHTML = ""; // Clear the chat box for a new conversation
    }
}
window.startConversation = startConversation; // Export the function globally

// Render followers dynamically in the followers list
function renderFollowers(followers) {
    const followersList = document.querySelector(".followers-list");
    if (!followersList) return;

    followersList.innerHTML = ""; // Clear existing followers

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
window.renderFollowers = renderFollowers; // Export the function globally

// Example data for testing
const followersData = [
    { id: 19, username: "believe" },
    { id: 20, username: "praise" }
];
renderFollowers(followersData);

// Add feedback to indicate WebSocket readiness
if (typeof socket !== "undefined" && socket.readyState === WebSocket.OPEN) {
    console.log("WebSocket connection is active.");
} else {
    console.error("WebSocket is not connected. Ensure the server is running.");
}
