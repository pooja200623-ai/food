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

    // Render Centralized UI Components
    renderNavbar();
    renderFooter();
});

function renderNavbar() {
    const dashNav = document.querySelector('.dash-nav');
    if (!dashNav) return;

    const session = JSON.parse(localStorage.getItem('user_session')) || { name: 'User' };
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    
    const links = [
        { name: 'Home', href: 'index.html', icon: 'fa-home' },
        { name: 'Our Menu', href: 'menu.html', icon: 'fa-utensils' },
        { name: 'Offers', href: 'offers.html', icon: 'fa-tags' },
        { name: 'Cart', href: 'cart.html', icon: 'fa-shopping-cart', id: 'cartLink' },
        { name: 'Orders', href: 'orders.html', icon: 'fa-history' },
        { name: 'Profile', href: 'profile.html', icon: 'fa-user-circle' },
        { name: 'About', href: 'about.html', icon: 'fa-info-circle' },
        { name: 'Contact', href: 'contact.html', icon: 'fa-envelope' }
    ];

    dashNav.innerHTML = `
        <div class="nav-brand" style="cursor: pointer;" onclick="location.href='index.html'">
            <i class="fas fa-utensils"></i> Crave
        </div>
        
        <div class="nav-links" style="display: flex; gap: 8px; align-items: center;">
            ${links.map(link => `
                <a href="${link.href}" class="${currentPath === link.href ? 'active' : ''}" style="
                    text-decoration: none; 
                    color: ${currentPath === link.href ? 'var(--primary-color)' : 'var(--text-main)'};
                    padding: 8px 16px;
                    border-radius: 12px;
                    font-weight: 600;
                    font-size: 0.95rem;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    background: ${currentPath === link.href ? 'rgba(255, 71, 87, 0.08)' : 'transparent'};
                " onmouseover="if('${currentPath}' !== '${link.href}') { this.style.background='rgba(0,0,0,0.03)'; this.style.color='var(--primary-color)'; }" 
                   onmouseout="if('${currentPath}' !== '${link.href}') { this.style.background='transparent'; this.style.color='var(--text-main)'; }">
                    <i class="fas ${link.icon}" style="font-size: 0.9rem; opacity: 0.8;"></i>
                    ${link.name}
                    ${link.id === 'cartLink' ? `<span id="cartCount" style="background: var(--primary-color); color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem; font-weight: 800; min-width: 18px; text-align: center;">0</span>` : ''}
                </a>
            `).join('')}
        </div>
        
        <div class="user-profile" style="display: flex; align-items: center; gap: 12px; padding: 4px 4px 4px 12px; border-radius: 50px; background: rgba(0,0,0,0.03);">
            <span style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);">${session.name}</span>
            <div class="user-avatar" style="width: 38px; height: 38px; border-radius: 50%; background: var(--gradient-main); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 10px rgba(255,71,87,0.2);">
                ${session.name.charAt(0).toUpperCase()}
            </div>
            <button id="logoutBtn" class="logout-btn" style="margin-left: 5px;"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    `;

    // Re-bind logout listener
    const logoutBtn = dashNav.querySelector('#logoutBtn');
    if (logoutBtn) {
        logoutBtn.onclick = () => {
            localStorage.removeItem('user_session');
            window.location.href = 'signin.html';
        };
    }
    
    // Initial cart sync
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    const cartCount = document.getElementById('cartCount');
    if (cartCount) cartCount.textContent = count;
}

function renderFooter() {
    const footerContainer = document.getElementById('footerContainer');
    if (!footerContainer) return;

    footerContainer.innerHTML = `
        <footer style="background: #111; color: white; padding: 6rem 2rem 3rem; margin-top: 8rem; font-family: 'Outfit', sans-serif; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: var(--gradient-main);"></div>
            <div style="max-width: 1300px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 5rem;">
                <div class="reveal">
                    <h2 style="color: var(--primary-color); font-size: 2.8rem; margin-bottom: 1.5rem; font-weight: 800; font-style: italic;"><i class="fas fa-utensils"></i> Crave</h2>
                    <p style="opacity: 0.6; line-height: 1.8; font-size: 1.1rem; margin-bottom: 2.5rem;">Elevating your dining experience with flavors from every corner of the globe. Authentic, fast, and remarkably delicious.</p>
                    <div style="display: flex; gap: 15px;">
                        ${['facebook-f', 'instagram', 'twitter', 'youtube'].map(icon => `
                            <a href="#" style="width: 45px; height: 45px; border-radius: 50%; background: rgba(255,255,255,0.05); color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s;" 
                               onmouseover="this.style.background='var(--primary-color)'; this.style.transform='translateY(-5px)'" 
                               onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.transform='translateY(0)'">
                                <i class="fab fa-${icon}"></i>
                            </a>
                        `).join('')}
                    </div>
                </div>
                <div class="reveal" style="animation-delay: 0.1s;">
                    <h3 style="margin-bottom: 2rem; font-size: 1.2rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: var(--primary-light);">Explore</h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 1.2rem; padding: 0;">
                        ${['Home', 'Our Menu', 'Special Offers', 'Track Orders'].map(item => `
                            <li><a href="${item === 'Home' ? 'index.html' : item.toLowerCase().replace(' ', '') + '.html'}" style="color: white; text-decoration: none; opacity: 0.6; transition: 0.3s; display: flex; align-items: center; gap: 10px;" onmouseover="this.style.opacity=1; this.style.paddingLeft='8px'" onmouseout="this.style.opacity=0.6; this.style.paddingLeft='0'"><i class="fas fa-chevron-right" style="font-size: 0.7rem; color: var(--primary-color);"></i> ${item}</a></li>
                        `).join('')}
                    </ul>
                </div>
                <div class="reveal" style="animation-delay: 0.2s;">
                    <h3 style="margin-bottom: 2rem; font-size: 1.2rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: var(--primary-light);">Company</h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 1.2rem; padding: 0;">
                        ${['About Us', 'Contact Us', 'Privacy Policy', 'Terms of Use'].map(item => `
                            <li><a href="${item.toLowerCase().split(' ')[0]}.html" style="color: white; text-decoration: none; opacity: 0.6; transition: 0.3s; display: flex; align-items: center; gap: 10px;" onmouseover="this.style.opacity=1; this.style.paddingLeft='8px'" onmouseout="this.style.opacity=0.6; this.style.paddingLeft='0'"><i class="fas fa-chevron-right" style="font-size: 0.7rem; color: var(--primary-color);"></i> ${item}</a></li>
                        `).join('')}
                    </ul>
                </div>
                <div class="reveal" style="animation-delay: 0.3s;">
                    <h3 style="margin-bottom: 2rem; font-size: 1.2rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: var(--primary-light);">Newsletter</h3>
                    <p style="opacity: 0.6; margin-bottom: 2rem;">Join 10k+ foodies for exclusive weekly deals.</p>
                    <div style="background: rgba(255,255,255,0.05); padding: 6px; border-radius: 16px; display: flex; gap: 10px; border: 1px solid rgba(255,255,255,0.1);">
                        <input type="email" placeholder="Your Email" style="background: transparent; border: none; padding: 10px 15px; color: white; flex: 1; outline: none; font-family: inherit;">
                        <button style="background: var(--gradient-main); color: white; border: none; padding: 12px 25px; border-radius: 12px; cursor: pointer; font-weight: 700; transition: 0.3s; box-shadow: 0 4px 15px rgba(255,71,87,0.3);" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">Join</button>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 6rem; padding-top: 3rem; border-top: 1px solid rgba(255,255,255,0.05); opacity: 0.4; font-size: 0.95rem; display: flex; flex-direction: column; gap: 10px;">
                <span>&copy; 2026 Crave Food Delivery. Premium Quality Guaranteed.</span>
                <span style="font-size: 0.8rem;">Designed for the ultimate food experience.</span>
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
