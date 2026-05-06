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
    const otpDisplay = document.getElementById('otpDisplay');
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

        // OTP Input Logic (Simplified)
        if (otpDisplay) {
            otpDisplay.addEventListener('input', () => {
                otpValueInput.value = otpDisplay.value;
            });
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

    // Active Link Highlighting
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath) {
            link.style.color = 'var(--primary-color)';
            link.style.borderBottom = '2px solid var(--primary-color)';
            link.style.paddingBottom = '5px';
        } else {
            link.style.color = 'var(--text-main)';
            link.style.borderBottom = 'none';
        }
    });

    // Sync Cart Count on load
    if (typeof getCart === 'function') {
        saveCart(getCart());
    }

    // Render Centralized Footer
    renderFooter();
});

function renderFooter() {
    const footerContainer = document.getElementById('footerContainer');
    if (!footerContainer) return;

    footerContainer.innerHTML = `
        <footer style="background: #1c1c1c; color: white; padding: 5rem 2rem 2rem; margin-top: 5rem; font-family: 'Outfit', sans-serif;">
            <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 4rem;">
                <div>
                    <h2 style="color: var(--primary-color); font-size: 2.5rem; margin-bottom: 1.5rem; font-weight: 700;"><i class="fas fa-utensils"></i> Crave</h2>
                    <p style="opacity: 0.7; line-height: 1.8; font-size: 1.1rem;">Experience the finest global cuisines delivered to your doorstep. We bridge the gap between world-class chefs and food lovers.</p>
                    <div style="display: flex; gap: 15px; margin-top: 2rem;">
                        <a href="#" style="color: white; font-size: 1.5rem; opacity: 0.6; transition: 0.3s;"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color: white; font-size: 1.5rem; opacity: 0.6; transition: 0.3s;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: white; font-size: 1.5rem; opacity: 0.6; transition: 0.3s;"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div>
                    <h3 style="margin-bottom: 2rem; font-size: 1.3rem; border-left: 4px solid var(--primary-color); padding-left: 15px;">Discover</h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 1rem; padding: 0;">
                        <li><a href="index.html" style="color: white; text-decoration: none; opacity: 0.7; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">Home</a></li>
                        <li><a href="menu.html" style="color: white; text-decoration: none; opacity: 0.7; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">Our Menu</a></li>
                        <li><a href="offers.html" style="color: white; text-decoration: none; opacity: 0.7; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">Special Offers</a></li>
                        <li><a href="orders.html" style="color: white; text-decoration: none; opacity: 0.7; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">Order Tracking</a></li>
                    </ul>
                </div>
                <div>
                    <h3 style="margin-bottom: 2rem; font-size: 1.3rem; border-left: 4px solid var(--primary-color); padding-left: 15px;">Company</h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 1rem; padding: 0;">
                        <li><a href="about.html" style="color: white; text-decoration: none; opacity: 0.7; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">About Us</a></li>
                        <li><a href="contact.html" style="color: white; text-decoration: none; opacity: 0.7; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">Contact Us</a></li>
                        <li><a href="#" style="color: white; text-decoration: none; opacity: 0.7; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">Privacy Policy</a></li>
                        <li><a href="#" style="color: white; text-decoration: none; opacity: 0.7; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">Terms of Service</a></li>
                    </ul>
                </div>
                <div>
                    <h3 style="margin-bottom: 2rem; font-size: 1.3rem; border-left: 4px solid var(--primary-color); padding-left: 15px;">Newsletter</h3>
                    <p style="opacity: 0.7; margin-bottom: 1.5rem;">Get the latest updates and offers.</p>
                    <div style="display: flex; gap: 10px;">
                        <input type="email" placeholder="Email Address" style="background: #333; border: none; padding: 12px; border-radius: 8px; color: white; flex: 1;">
                        <button style="background: var(--primary-color); color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer;"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 5rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); opacity: 0.5; font-size: 0.9rem;">
                &copy; 2026 Crave Food Delivery. All rights reserved. Made with <i class="fas fa-heart" style="color: var(--primary-color);"></i> for Food Lovers.
            </div>
        </footer>
    `;
}

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
