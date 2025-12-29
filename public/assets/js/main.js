// Main JavaScript for Asa Electronics

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarMenu = document.querySelector('.navbar-menu');
    
    if (navbarToggle) {
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Image preview for file uploads
    const imageInputs = document.querySelectorAll('input[type="file"][data-preview]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    });
});

// Add to cart function
function addToCart(productId, quantity = 1) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/public/cart.php';
    
    const productInput = document.createElement('input');
    productInput.type = 'hidden';
    productInput.name = 'product_id';
    productInput.value = productId;
    
    const quantityInput = document.createElement('input');
    quantityInput.type = 'hidden';
    quantityInput.name = 'quantity';
    quantityInput.value = quantity;
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'add';
    
    form.appendChild(productInput);
    form.appendChild(quantityInput);
    form.appendChild(actionInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Update cart quantity
function updateCartQuantity(productId, quantity) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/public/cart.php';
    
    const productInput = document.createElement('input');
    productInput.type = 'hidden';
    productInput.name = 'product_id';
    productInput.value = productId;
    
    const quantityInput = document.createElement('input');
    quantityInput.type = 'hidden';
    quantityInput.name = 'quantity';
    quantityInput.value = quantity;
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'update';
    
    form.appendChild(productInput);
    form.appendChild(quantityInput);
    form.appendChild(actionInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Format currency
function formatCurrency(amount) {
    return '₦' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
