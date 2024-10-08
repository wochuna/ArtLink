// Profile Picture Preview
document.getElementById('profile_picture').addEventListener('change', function() {
    const file = this.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        const profilePreview = document.getElementById('profile_preview');
        profilePreview.src = e.target.result;
        profilePreview.style.display = 'block';
    };

    if (file) {
        reader.readAsDataURL(file);
    }
});

// Artwork Image Preview
document.getElementById('artwork_image').addEventListener('change', function() {
    const file = this.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        const artworkPreview = document.getElementById('artwork_preview');
        artworkPreview.src = e.target.result;
        artworkPreview.style.display = 'block';
    };

    if (file) {
        reader.readAsDataURL(file);
    }
});



