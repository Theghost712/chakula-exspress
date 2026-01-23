// Chakula Express - Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initMobileMenu();
    initUserDropdown();
    initCart();
    initNotifications();
    initLocation();
    initForms();
    initOwlCarousels();
});

// ===== MOBILE MENU =====
function initMobileMenu() {
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuBtn && navMenu) {
        menuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.innerHTML = navMenu.classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : 
                '<i class="fas fa-bars"></i>';
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.navbar')) {
                navMenu.classList.remove('active');
                menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    }
}

// ===== USER DROPDOWN =====
function initUserDropdown() {
    const userAvatar = document.querySelector('.user-avatar');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    if (userAvatar && dropdownMenu) {
        userAvatar.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            dropdownMenu.classList.remove('show');
        });
    }
}

// ===== CART FUNCTIONALITY =====
function initCart() {
    const cartBtn = document.querySelector('.cart-link');
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    const closeCartBtn = document.querySelector('.close-cart');
    
    if (cartBtn && cartSidebar && cartOverlay) {
        // Open cart
        cartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            cartSidebar.classList.add('open');
            cartOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        // Close cart
        if (closeCartBtn) {
            closeCartBtn.addEventListener('click', closeCart);
        }
        
        cartOverlay.addEventListener('click', closeCart);
        
        // Close cart with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCart();
            }
        });
    }
    
    // Update cart count
    updateCartCount();
    
    // Load cart items
    loadCartItems();
}

function closeCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    
    if (cartSidebar && cartOverlay) {
        cartSidebar.classList.remove('open');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function updateCartCount() {
    const cartCount = document.querySelector('.cart-count');
    const cart = getCart();
    
    if (cartCount) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
    }
}

function getCart() {
    return JSON.parse(localStorage.getItem('chakula_cart') || '[]');
}

function saveCart(cart) {
    localStorage.setItem('chakula_cart', JSON.stringify(cart));
    updateCartCount();
    loadCartItems();
}

function loadCartItems() {
    const cartBody = document.querySelector('.cart-body');
    if (!cartBody) return;
    
    const cart = getCart();
    
    if (cart.length === 0) {
        cartBody.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Hakuna bidhaa kwenye kagua lako</p>
                <a href="restaurants.php" class="btn btn-primary">Nenda Kwenye Mikahawa</a>
            </div>
        `;
        return;
    }
    
    let html = '';
    let subtotal = 0;
    
    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        html += `
            <div class="cart-item" data-index="${index}">
                <img src="${item.image || 'assets/images/food-placeholder.jpg'}" 
                     alt="${item.name}" class="cart-item-image">
                <div class="cart-item-details">
                    <h4 class="cart-item-name">${item.name}</h4>
                    <p class="cart-item-price">TSh ${formatPrice(item.price)} × ${item.quantity}</p>
                    <div class="cart-item-actions">
                        <button class="qty-btn decrease-qty" data-index="${index}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="qty-input">${item.quantity}</span>
                        <button class="qty-btn increase-qty" data-index="${index}">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="remove-item" data-index="${index}">
                            <i class="fas fa-trash"></i> Ondoa
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    cartBody.innerHTML = html;
    
    // Update cart total
    const cartTotal = document.querySelector('.cart-total');
    if (cartTotal) {
        const deliveryFee = 1500; // Example delivery fee
        const vat = subtotal * 0.18;
        const total = subtotal + deliveryFee + vat;
        
        cartTotal.innerHTML = `
            <div>
                <div>Jumla ya Bidhaa: TSh ${formatPrice(subtotal)}</div>
                <div>Ada ya Uwasilishaji: TSh ${formatPrice(deliveryFee)}</div>
                <div>VAT (18%): TSh ${formatPrice(vat)}</div>
                <div><strong>Jumla: TSh ${formatPrice(total)}</strong></div>
            </div>
        `;
    }
    
    // Add event listeners to cart buttons
    document.querySelectorAll('.decrease-qty').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            updateCartItemQuantity(index, -1);
        });
    });
    
    document.querySelectorAll('.increase-qty').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            updateCartItemQuantity(index, 1);
        });
    });
    
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            removeCartItem(index);
        });
    });
}

function updateCartItemQuantity(index, change) {
    const cart = getCart();
    if (cart[index]) {
        cart[index].quantity += change;
        
        if (cart[index].quantity < 1) {
            cart.splice(index, 1);
        }
        
        saveCart(cart);
    }
}

function removeCartItem(index) {
    const cart = getCart();
    cart.splice(index, 1);
    saveCart(cart);
}

function addToCart(item) {
    const cart = getCart();
    
    // Check if item already exists in cart
    const existingItemIndex = cart.findIndex(cartItem => 
        cartItem.id === item.id && cartItem.restaurant_id === item.restaurant_id
    );
    
    if (existingItemIndex > -1) {
        cart[existingItemIndex].quantity += item.quantity || 1;
    } else {
        cart.push({
            id: item.id,
            restaurant_id: item.restaurant_id,
            name: item.name,
            price: item.price,
            image: item.image,
            quantity: item.quantity || 1
        });
    }
    
    saveCart(cart);
    showNotification('Bidhaa imeongezwa kwenye kagua', 'success');
}

// ===== NOTIFICATIONS =====
function initNotifications() {
    // Check for stored messages
    const message = localStorage.getItem('chakula_message');
    if (message) {
        const { type, text } = JSON.parse(message);
        showNotification(text, type);
        localStorage.removeItem('chakula_message');
    }
}

function showNotification(message, type = 'info') {
    // Remove existing notification
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button class="close-notification"><i class="fas fa-times"></i></button>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-lg);
        z-index: 9999;
        animation: slideIn 0.3s ease;
        max-width: 400px;
    `;
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Add close button event
    notification.querySelector('.close-notification').addEventListener('click', function() {
        notification.remove();
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function getNotificationColor(type) {
    const colors = {
        'success': '#28a745',
        'error': '#dc3545',
        'warning': '#ffc107',
        'info': '#17a2b8'
    };
    return colors[type] || '#17a2b8';
}

// ===== LOCATION SERVICES =====
function initLocation() {
    const locateBtn = document.querySelector('.btn-locate');
    if (locateBtn) {
        locateBtn.addEventListener('click', getCurrentLocation);
    }
    
    // Load saved location
    const savedLocation = localStorage.getItem('chakula_location');
    if (savedLocation) {
        const locationInput = document.getElementById('locationSearch');
        if (locationInput) {
            locationInput.value = savedLocation;
        }
    }
}

function getCurrentLocation() {
    if (!navigator.geolocation) {
        showNotification('Huduma ya eneo haipatikani kwenye kivinjari chako', 'error');
        return;
    }
    
    showNotification('Inapata eneo lako...', 'info');
    
    navigator.geolocation.getCurrentPosition(
        async function(position) {
            const { latitude, longitude } = position.coords;
            
            try {
                // Use OpenStreetMap Nominatim API for reverse geocoding
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
                );
                const data = await response.json();
                
                const address = data.display_name || `${latitude}, ${longitude}`;
                const locationInput = document.getElementById('locationSearch');
                
                if (locationInput) {
                    locationInput.value = address;
                    localStorage.setItem('chakula_location', address);
                    showNotification('Eneo limepatikana', 'success');
                }
                
                // You could also save coordinates for restaurant filtering
                localStorage.setItem('chakula_coords', JSON.stringify({ lat: latitude, lng: longitude }));
                
            } catch (error) {
                console.error('Error getting address:', error);
                showNotification('Imeshindikana kupata anwani. Tumia eneo la karibu.', 'warning');
            }
        },
        function(error) {
            let message = 'Hukumuruhusu kutumia eneo lako';
            if (error.code === error.PERMISSION_DENIED) {
                message = 'Hukumuruhusu kutumia eneo lako. Tafadhali ruhusu katika mipangilio.';
            } else if (error.code === error.TIMEOUT) {
                message = 'Muda umekwisha. Jaribu tena.';
            }
            showNotification(message, 'error');
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// ===== FORM VALIDATION =====
function initForms() {
    // Add validation to all forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Phone number formatting
    document.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('input', formatPhoneNumber);
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        field.classList.remove('error');
        
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
            
            // Add error message
            let errorMsg = field.nextElementSibling;
            if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                errorMsg = document.createElement('small');
                errorMsg.className = 'error-message';
                errorMsg.style.cssText = 'color: #dc3545; display: block; margin-top: 0.25rem;';
                field.parentNode.appendChild(errorMsg);
            }
            errorMsg.textContent = 'Sehemu hii inahitajika';
        } else {
            // Remove error message if exists
            const errorMsg = field.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('error-message')) {
                errorMsg.remove();
            }
        }
        
        // Specific validations
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                field.classList.add('error');
                isValid = false;
                
                let errorMsg = field.nextElementSibling;
                if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                    errorMsg = document.createElement('small');
                    errorMsg.className = 'error-message';
                    errorMsg.style.cssText = 'color: #dc3545; display: block; margin-top: 0.25rem;';
                    field.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = 'Anwani ya barua pepe si sahihi';
            }
        }
        
        if (field.type === 'tel' && field.value) {
            const phoneRegex = /^[0-9]{9}$/;
            const value = field.value.replace(/\D/g, '');
            if (!phoneRegex.test(value)) {
                field.classList.add('error');
                isValid = false;
                
                let errorMsg = field.nextElementSibling;
                if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                    errorMsg = document.createElement('small');
                    errorMsg.className = 'error-message';
                    errorMsg.style.cssText = 'color: #dc3545; display: block; margin-top: 0.25rem;';
                    field.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = 'Nambari ya simu si sahihi (tarakimu 9)';
            }
        }
    });
    
    return isValid;
}

function formatPhoneNumber(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 9) {
        value = value.slice(0, 9);
    }
    e.target.value = value;
}

// ===== OWL CAROUSEL =====
function initOwlCarousels() {
    // Initialize Owl Carousel if available
    if (typeof $.fn.owlCarousel !== 'undefined') {
        $('.owl-carousel').each(function() {
            $(this).owlCarousel({
                loop: true,
                margin: 20,
                nav: true,
                dots: false,
                responsive: {
                    0: { items: 1 },
                    576: { items: 2 },
                    768: { items: 3 },
                    992: { items: 4 }
                }
            });
        });
    }
}

// ===== UTILITY FUNCTIONS =====
function formatPrice(price) {
    return parseFloat(price).toLocaleString('en-TZ', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('sw-TZ', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

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

// ===== API FUNCTIONS =====
async function fetchRestaurants(filters = {}) {
    try {
        const queryParams = new URLSearchParams(filters).toString();
        const response = await fetch(`api/restaurants.php?${queryParams}`);
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error fetching restaurants:', error);
        showNotification('Hitilafu katika kupata mikahawa', 'error');
        return [];
    }
}

async function fetchMenuItems(restaurantId, category = '') {
    try {
        const url = category ? 
            `api/menu.php?restaurant_id=${restaurantId}&category=${category}` :
            `api/menu.php?restaurant_id=${restaurantId}`;
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error fetching menu:', error);
        showNotification('Hitilafu katika kupata menu', 'error');
        return [];
    }
}

// Export functions for use in other files
window.addToCart = addToCart;
window.showNotification = showNotification;
window.getCart = getCart;
window.saveCart = saveCart;
window.formatPrice = formatPrice;