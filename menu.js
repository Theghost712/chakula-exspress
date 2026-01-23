// Menu Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initMenuFilters();
    initQuantityControls();
    initCart();
    updateItemCount();
});

// Initialize menu filters
function initMenuFilters() {
    const searchInput = document.getElementById('menuSearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterMenu);
    }
    
    const priceRange = document.getElementById('priceRange');
    if (priceRange) {
        priceRange.addEventListener('input', function() {
            const maxPrice = parseInt(this.value);
            document.getElementById('maxPrice').textContent = formatPrice(maxPrice);
            filterMenu();
        });
    }
}

// Initialize quantity controls
function initQuantityControls() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.increase-qty')) {
            const input = e.target.closest('.quantity-controls').querySelector('.qty-input');
            input.value = parseInt(input.value) + 1;
        }
        
        if (e.target.closest('.decrease-qty')) {
            const input = e.target.closest('.quantity-controls').querySelector('.qty-input');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
    });
}

// Filter menu items
function filterMenu() {
    const searchTerm = document.getElementById('menuSearch')?.value.toLowerCase() || '';
    const filterVeg = document.getElementById('filterVeg')?.checked || false;
    const filterSpicy = document.getElementById('filterSpicy')?.checked || false;
    const filterAvailable = document.getElementById('filterAvailable')?.checked || false;
    const maxPrice = parseInt(document.getElementById('priceRange')?.value) || 50000;
    
    const menuItems = document.querySelectorAll('.menu-item');
    let visibleCount = 0;
    
    menuItems.forEach(item => {
        const itemName = item.querySelector('.item-name')?.textContent.toLowerCase() || '';
        const itemDescription = item.querySelector('.item-description')?.textContent.toLowerCase() || '';
        const isVeg = item.dataset.veg === 'true';
        const isSpicy = item.dataset.spicy === 'true';
        const price = parseFloat(item.dataset.price) || 0;
        const isAvailable = item.dataset.available === 'true';
        
        let isVisible = true;
        
        // Search filter
        if (searchTerm && !itemName.includes(searchTerm) && !itemDescription.includes(searchTerm)) {
            isVisible = false;
        }
        
        // Veg filter
        if (filterVeg && !isVeg) {
            isVisible = false;
        }
        
        // Spicy filter
        if (filterSpicy && !isSpicy) {
            isVisible = false;
        }
        
        // Availability filter
        if (filterAvailable && !isAvailable) {
            isVisible = false;
        }
        
        // Price filter
        if (price > maxPrice) {
            isVisible = false;
        }
        
        // Show/hide item
        item.style.display = isVisible ? 'block' : 'none';
        
        if (isVisible) {
            visibleCount++;
            
            // Add animation
            item.style.animation = 'fadeIn 0.3s ease';
            setTimeout(() => {
                item.style.animation = '';
            }, 300);
        }
    });
    
    // Update item count
    updateItemCount(visibleCount);
    
    // Show/hide menu sections
    document.querySelectorAll('.menu-section').forEach(section => {
        const visibleItems = section.querySelectorAll('.menu-item[style*="display: block"]');
        if (visibleItems.length === 0) {
            section.style.display = 'none';
        } else {
            section.style.display = 'block';
            section.querySelector('.section-count').textContent = `(${visibleItems.length})`;
        }
    });
}

// Clear search
function clearSearch() {
    const searchInput = document.getElementById('menuSearch');
    if (searchInput)