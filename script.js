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

    // Simple Login & OTP Flow
    if (authForm) {
        // Step 1: Send OTP
        if (sendOtpBtn) {
            sendOtpBtn.addEventListener('click', async () => {
                const name = nameInput.value.trim();
                const email = emailInput.value.trim();

                if (!name || !email) {
                    showToast('Please enter both name and email', 'error');
                    return;
                }

                sendOtpBtn.disabled = true;
                sendOtpBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

                try {
                    const response = await fetch('api/auth.php?action=send_otp', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ name, email })
                    });
                    const data = await response.json();

                    if (data.success) {
                        displayEmail.textContent = email;
                        step1.classList.remove('active');
                        step1.style.display = 'none';
                        step2.classList.add('active');
                        step2.style.display = 'block';
                        showToast(data.message, 'success');
                    } else {
                        // If it failed but provided a dev_otp (fallback), show it
                        if (data.dev_otp) {
                            displayEmail.textContent = email;
                            step1.classList.remove('active');
                            step1.style.display = 'none';
                            step2.classList.add('active');
                            step2.style.display = 'block';
                            showToast(`${data.message} (Dev OTP: ${data.dev_otp})`, 'warning');
                        } else {
                            showToast(data.message, 'error');
                        }
                        sendOtpBtn.disabled = false;
                        sendOtpBtn.innerHTML = 'Get Verification Code <i class="fas fa-paper-plane"></i>';
                    }
                } catch (err) {
                    showToast('Network error', 'error');
                    sendOtpBtn.disabled = false;
                    sendOtpBtn.innerHTML = 'Get Verification Code <i class="fas fa-paper-plane"></i>';
                }
            });
        }

        // OTP Box Logic
        otpBoxes.forEach((box, index) => {
            box.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < otpBoxes.length - 1) {
                    otpBoxes[index + 1].focus();
                }
                combineOTP();
            });

            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpBoxes[index - 1].focus();
                }
            });
        });

        function combineOTP() {
            let otp = '';
            otpBoxes.forEach(box => otp += box.value);
            otpValueInput.value = otp;
        }

        // Step 2: Verify OTP
        authForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const otp = otpValueInput.value;
            const email = emailInput.value.trim();

            if (otp.length !== 4) {
                showToast('Please enter 4-digit OTP', 'error');
                return;
            }

            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

            try {
                const response = await fetch('api/auth.php?action=verify_otp', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, otp })
                });
                const data = await response.json();

                if (data.success) {
                    localStorage.setItem('user_session', JSON.stringify(data.user));
                    showToast('Login successful!', 'success');
                    setTimeout(() => window.location.href = 'index.html', 1000);
                } else {
                    showToast(data.message, 'error');
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = 'Verify & Login <i class="fas fa-check-double"></i>';
                }
            } catch (err) {
                showToast('Network error', 'error');
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = 'Verify & Login <i class="fas fa-check-double"></i>';
            }
        });

        // Back button
        if (backBtn) {
            backBtn.addEventListener('click', () => {
                step2.classList.remove('active');
                step2.style.display = 'none';
                step1.classList.add('active');
                step1.style.display = 'block';
                sendOtpBtn.disabled = false;
                sendOtpBtn.innerHTML = 'Get Verification Code <i class="fas fa-paper-plane"></i>';
            });
        }
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
        }
    }
});

// --- Global Utility Functions (Available for inline event handlers) ---

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
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    
    // Update all possible cart count indicators
    const cartCount = document.getElementById('cartCount');
    if (cartCount) cartCount.textContent = totalItems;
    
    const floatCartCount = document.getElementById('floatCartCount');
    if (floatCartCount) floatCartCount.textContent = totalItems;
}

window.addToCart = function(food, btn) {
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

    // Visual feedback on button
    if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Added!';
        btn.style.background = '#2ed573';
        btn.style.borderColor = '#2ed573';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.style.background = '';
            btn.style.borderColor = '';
            btn.disabled = false;
        }, 1500);
    }
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
