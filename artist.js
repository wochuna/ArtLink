    document.querySelector('form').addEventListener('submit', function(e) {
        const artistId = document.getElementById('artist_id').value.trim();
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const artworkImage = document.getElementById('artwork_image').files[0];

        if (!artistId || !title || !description || !artworkImage) {
            alert('Please fill in all required fields.');
            e.preventDefault(); // Prevent form submission
        }
    });

    document.getElementById('artwork_image').addEventListener('change', function() {
        const file = this.files[0];
        const preview = document.createElement('img');
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.maxWidth = '200px'; // Set max width for preview
            document.querySelector('.upload-container').appendChild(preview);
        };

        if (file) {
            reader.readAsDataURL(file);
        }
    });

