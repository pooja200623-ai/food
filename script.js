document.addEventListener('DOMContentLoaded', () => {
    
    // Elements
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const verifyBtn = document.getElementById('verifyBtn');
    const backBtn = document.getElementById('backBtn');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const displayEmail = document.getElementById('displayEmail');
    const authForm = document.getElementById('authForm');
    const otpBoxes = document.querySelectorAll('.otp-box');
    const otpValueInput = document.getElementById('otpValue');

    // Simple Login
    if (authForm) {
        authForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const name = nameInput.value.trim();
            const email = emailInput.value.trim();

            if (!name || !email) {
                showToast('Please enter both name and email', 'error');
                return;
            }

            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showToast('Please enter a valid email address', 'error');
                return;
            }

            const loginBtn = document.getElementById('loginBtn');
            if (loginBtn) {
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            }

            // Simulate a brief network delay for UX
            setTimeout(() => {
                // 1-Time Verification Rule: Save session to localStorage
                localStorage.setItem('user_session', JSON.stringify({
                    name: name,
                    email: email
                }));
                
                showToast('Login successful! Redirecting...', 'success');
                
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 800);
            }, 600);
        });
    }

    // Dashboard Logout Logic
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem('user_session');
            window.location.href = 'signin.html';
        });
    }

    // Dashboard User Init
    const userNameDisplay = document.getElementById('userNameDisplay');
    const userAvatar = document.getElementById('userAvatar');
    if (userNameDisplay && userAvatar) {
        const session = JSON.parse(localStorage.getItem('user_session'));
        if (session) {
            userNameDisplay.textContent = session.name;
            userAvatar.textContent = session.name.charAt(0).toUpperCase();
        } else {
            // Not logged in, redirect to login
            window.location.replace('signin.html');
        }
    }

    // Toast Function
    window.showToast = function(message, type = 'success') {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        let icon = 'fa-info-circle';
        if (type === 'success') icon = 'fa-check-circle';
        if (type === 'error') icon = 'fa-exclamation-circle';

        toast.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 10);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Cart Management
    window.getCart = function() {
        return JSON.parse(localStorage.getItem('cart')) || [];
    }

    window.saveCart = function(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
        // Update cart count if exists in UI
        const cartCount = document.getElementById('cartCount');
        if (cartCount) {
            cartCount.textContent = cart.reduce((total, item) => total + item.quantity, 0);
        }
    }

    window.addToCart = function(food) {
        const cart = getCart();
        const existingItem = cart.find(item => item.name === food.name);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                ...food,
                quantity: 1
            });
        }
        
        saveCart(cart);
        showToast(`${food.name} added to cart!`, 'success');
    }

    window.updateQuantity = function(foodName, delta) {
        let cart = getCart();
        const item = cart.find(i => i.name === foodName);
        if (item) {
            item.quantity += delta;
            if (item.quantity <= 0) {
                cart = cart.filter(i => i.name !== foodName);
            }
            saveCart(cart);
            // If we are on cart page, re-render
            if (typeof renderCart === 'function') renderCart();
        }
    }

    window.removeFromCart = function(foodName) {
        let cart = getCart();
        cart = cart.filter(item => item.name !== foodName);
        saveCart(cart);
        showToast('Item removed from cart', 'info');
        if (typeof renderCart === 'function') renderCart();
    }
});
