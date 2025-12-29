// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const modals = document.querySelectorAll('.modal');
    
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
        }
    };
    
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
        }
    };
    
    // Close modal on background click
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
    
    // Image preview
    const imageInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.add('show');
                };
                reader.readAsDataURL(file);
            }
        });
    });
});
