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
    const otpDisplay = document.getElementById('otpDisplay');
    const otpValueInput = document.getElementById('otpValue');

    // Simple Login & OTP Flow
    if (authForm) {
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

        if (otpDisplay) {
            otpDisplay.addEventListener('input', () => {
                otpValueInput.value = otpDisplay.value;
            });
        }

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

    // Sync Cart Count on load
    if (typeof getCart === 'function') {
        saveCart(getCart());
    }

    // Render Centralized UI Components
    renderNavbar();
    renderFooter();

    // Sticky Navbar Logic
    window.addEventListener('scroll', () => {
        const dashNav = document.querySelector('.dash-nav');
        if (dashNav) {
            if (window.scrollY > 50) {
                dashNav.classList.add('sticky-active');
                dashNav.style.padding = '0.8rem 4rem';
                dashNav.style.background = 'rgba(255, 255, 255, 0.85)';
                dashNav.style.boxShadow = '0 15px 35px rgba(0,0,0,0.1)';
            } else {
                dashNav.classList.remove('sticky-active');
                dashNav.style.padding = '1.2rem 4rem';
                dashNav.style.background = 'rgba(255, 255, 255, 0.95)';
                dashNav.style.boxShadow = '0 2px 20px rgba(0,0,0,0.03)';
            }
        }
    });
});

function renderNavbar() {
    const dashNav = document.querySelector('.dash-nav');
    if (!dashNav) return;

    const session = JSON.parse(localStorage.getItem('user_session')) || { name: 'User' };
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    
    const links = [
        { name: 'Home', href: 'index.html', icon: 'fa-home' },
        { name: 'Menu', href: 'menu.html', icon: 'fa-utensils' },
        { name: 'Offers', href: 'offers.html', icon: 'fa-tags' },
        { name: 'Cart', href: 'cart.html', icon: 'fa-shopping-cart', id: 'cartLink' },
        { name: 'Orders', href: 'orders.html', icon: 'fa-history' },
        { name: 'Profile', href: 'profile.html', icon: 'fa-user-circle' }
    ];

    dashNav.style.transition = 'all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1)';
    dashNav.style.backdropFilter = 'blur(20px)';
    dashNav.style.webkitBackdropFilter = 'blur(20px)';
    dashNav.style.borderBottom = '1px solid rgba(0,0,0,0.05)';

    dashNav.innerHTML = `
        <div class="nav-brand" style="cursor: pointer; font-size: 2.4rem; font-weight: 900; letter-spacing: -2px; transition: 0.4s; font-style: italic;" onclick="location.href='index.html'" onmouseover="this.style.transform='scale(1.05) rotate(-2deg)'" onmouseout="this.style.transform='scale(1) rotate(0deg)'">
            <span style="background: var(--gradient-main); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Crave</span>
        </div>
        
        <div class="nav-links" style="display: flex; gap: 8px; align-items: center; background: rgba(0,0,0,0.04); padding: 8px; border-radius: 24px; border: 1px solid rgba(0,0,0,0.02);">
            ${links.map(link => `
                <a href="${link.href}" class="${currentPath === link.href ? 'active' : ''}" style="
                    text-decoration: none; 
                    color: ${currentPath === link.href ? 'white' : 'var(--text-main)'};
                    padding: 12px 22px;
                    border-radius: 18px;
                    font-weight: 700;
                    font-size: 0.95rem;
                    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    background: ${currentPath === link.href ? 'var(--gradient-main)' : 'transparent'};
                    box-shadow: ${currentPath === link.href ? '0 8px 20px rgba(255, 71, 87, 0.35)' : 'none'};
                " onmouseover="if('${currentPath}' !== '${link.href}') { this.style.background='rgba(255,255,255,0.9)'; this.style.color='var(--primary-color)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.05)'; }" 
                   onmouseout="if('${currentPath}' !== '${link.href}') { this.style.background='transparent'; this.style.color='var(--text-main)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'; }">
                    <i class="fas ${link.icon}" style="font-size: 1.1rem; opacity: 0.9;"></i>
                    ${link.name}
                    ${link.id === 'cartLink' ? `<span id="cartCount" style="background: ${currentPath === link.href ? 'white' : 'var(--primary-color)'}; color: ${currentPath === link.href ? 'var(--primary-color)' : 'white'}; border-radius: 50%; padding: 3px 8px; font-size: 0.8rem; font-weight: 900; min-width: 22px; text-align: center; margin-left: 5px; box-shadow: 0 3px 8px rgba(0,0,0,0.15);">0</span>` : ''}
                </a>
            `).join('')}
        </div>
        
        <div class="user-profile" style="display: flex; align-items: center; gap: 15px; padding: 6px 6px 6px 20px; border-radius: 50px; background: white; box-shadow: var(--shadow-soft); border: 1px solid rgba(0,0,0,0.04);">
            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                <span style="font-weight: 800; font-size: 1rem; color: var(--text-main); line-height: 1;">${session.name}</span>
                <span style="font-size: 0.75rem; color: #2ed573; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Premium Elite</span>
            </div>
            <div class="user-avatar" style="width: 45px; height: 45px; border-radius: 50%; background: var(--gradient-main); color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; box-shadow: 0 5px 15px rgba(255,71,87,0.3); border: 2px solid white;">
                ${session.name.charAt(0).toUpperCase()}
            </div>
            <button id="logoutBtn" class="logout-btn" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 50%; transition: 0.3s; color: var(--text-muted);" onmouseover="this.style.background='#ff4757';this.style.color='white';this.style.transform='rotate(90deg)'" onmouseout="this.style.background='#f8f9fa';this.style.color='var(--text-muted)';this.style.transform='rotate(0deg)'"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    `;

    const logoutBtn = dashNav.querySelector('#logoutBtn');
    if (logoutBtn) {
        logoutBtn.onclick = () => {
            localStorage.removeItem('user_session');
            window.location.href = 'signin.html';
        };
    }
    
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    const cartCount = document.getElementById('cartCount');
    if (cartCount) cartCount.textContent = count;
}

function renderFooter() {
    const footerContainer = document.getElementById('footerContainer');
    if (!footerContainer) return;

    footerContainer.innerHTML = `
        <footer style="background: #0d0d0d; color: white; padding: 8rem 2rem 4rem; margin-top: 10rem; font-family: 'Outfit', sans-serif; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: var(--gradient-main);"></div>
            <div style="max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 6rem;">
                <div class="reveal">
                    <h2 style="font-size: 3.5rem; margin-bottom: 2rem; font-weight: 900; font-style: italic; letter-spacing: -2px;"><span style="background: var(--gradient-main); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Crave</span></h2>
                    <p style="opacity: 0.7; line-height: 1.8; font-size: 1.2rem; margin-bottom: 3rem; font-weight: 300;">Defining the future of culinary delivery. We bring master-chef creations from global kitchens directly to your sanctuary.</p>
                    <div style="display: flex; gap: 20px;">
                        ${['facebook-f', 'instagram', 'twitter', 'youtube'].map(icon => `
                            <a href="#" style="width: 50px; height: 50px; border-radius: 15px; background: rgba(255,255,255,0.08); color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.4s; border: 1px solid rgba(255,255,255,0.05);" 
                               onmouseover="this.style.background='var(--primary-color)'; this.style.transform='translateY(-8px) rotate(10deg)'; this.style.boxShadow='0 10px 20px rgba(255,71,87,0.3)'" 
                               onmouseout="this.style.background='rgba(255,255,255,0.08)'; this.style.transform='translateY(0) rotate(0deg)'; this.style.boxShadow='none'">
                                <i class="fab fa-${icon}" style="font-size: 1.2rem;"></i>
                            </a>
                        `).join('')}
                    </div>
                </div>
                <div class="reveal" style="animation-delay: 0.1s;">
                    <h3 style="margin-bottom: 2.5rem; font-size: 1.3rem; font-weight: 800; text-transform: uppercase; letter-spacing: 3px; color: var(--primary-light);">Gastronomy</h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 1.5rem; padding: 0;">
                        ${['Home', 'Menu Explore', 'Exclusive Offers', 'Live Tracking'].map(item => `
                            <li><a href="#" style="color: white; text-decoration: none; opacity: 0.7; transition: 0.4s; display: flex; align-items: center; gap: 12px; font-weight: 500;" onmouseover="this.style.opacity=1; this.style.paddingLeft='12px'; this.style.color='var(--primary-color)'" onmouseout="this.style.opacity=0.7; this.style.paddingLeft='0'; this.style.color='white'"><i class="fas fa-arrow-right" style="font-size: 0.8rem; color: var(--primary-color);"></i> ${item}</a></li>
                        `).join('')}
                    </ul>
                </div>
                <div class="reveal" style="animation-delay: 0.2s;">
                    <h3 style="margin-bottom: 2.5rem; font-size: 1.3rem; font-weight: 800; text-transform: uppercase; letter-spacing: 3px; color: var(--primary-light);">The Brand</h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 1.5rem; padding: 0;">
                        ${['Our Story', 'Join the Kitchen', 'Privacy Concierge', 'Legacy Terms'].map(item => `
                            <li><a href="#" style="color: white; text-decoration: none; opacity: 0.7; transition: 0.4s; display: flex; align-items: center; gap: 12px; font-weight: 500;" onmouseover="this.style.opacity=1; this.style.paddingLeft='12px'; this.style.color='var(--primary-color)'" onmouseout="this.style.opacity=0.7; this.style.paddingLeft='0'; this.style.color='white'"><i class="fas fa-arrow-right" style="font-size: 0.8rem; color: var(--primary-color);"></i> ${item}</a></li>
                        `).join('')}
                    </ul>
                </div>
                <div class="reveal" style="animation-delay: 0.3s;">
                    <h3 style="margin-bottom: 2.5rem; font-size: 1.3rem; font-weight: 800; text-transform: uppercase; letter-spacing: 3px; color: var(--primary-light);">The Inner Circle</h3>
                    <p style="opacity: 0.7; margin-bottom: 2.5rem; font-size: 1.1rem; font-weight: 300;">Subscribe for early access to seasonal menus and elite member rewards.</p>
                    <div style="background: rgba(255,255,255,0.04); padding: 8px; border-radius: 20px; display: flex; gap: 12px; border: 1px solid rgba(255,255,255,0.08); box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);">
                        <input type="email" placeholder="Email Address" style="background: transparent; border: none; padding: 12px 20px; color: white; flex: 1; outline: none; font-family: inherit; font-size: 1.05rem;">
                        <button style="background: var(--gradient-main); color: white; border: none; padding: 12px 30px; border-radius: 15px; cursor: pointer; font-weight: 800; transition: 0.4s; box-shadow: 0 10px 20px rgba(255,71,87,0.3); text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 15px 30px rgba(255,71,87,0.4)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 10px 20px rgba(255,71,87,0.3)'">Join</button>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 8rem; padding-top: 4rem; border-top: 1px solid rgba(255,255,255,0.05); opacity: 0.5; font-size: 1rem; display: flex; flex-direction: column; gap: 12px; font-weight: 300;">
                <span style="letter-spacing: 1px;">&copy; 2026 CRAVE LUXE GASTRONOMY. ALL RIGHTS RESERVED.</span>
                <span style="font-size: 0.85rem; color: var(--primary-light); font-weight: 700; text-transform: uppercase; letter-spacing: 2px;">Excellence in Every Byte.</span>
            </div>
        </footer>
    `;
}

// --- Global Utility Functions ---

window.showToast = function(message, type = 'success') {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast ${type} glass-card`;
    toast.style.borderRadius = '18px';
    toast.style.border = '1px solid rgba(255,255,255,0.4)';
    toast.style.background = type === 'success' ? 'rgba(46, 213, 115, 0.95)' : (type === 'error' ? 'rgba(255, 71, 87, 0.95)' : 'rgba(30, 144, 255, 0.95)');
    toast.style.color = 'white';
    toast.style.backdropFilter = 'blur(15px)';
    toast.style.padding = '1.2rem 2.5rem';
    toast.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '15px';
    toast.style.transition = 'all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(20px) scale(0.9)';
    
    let icon = 'fa-info-circle';
    if (type === 'success') icon = 'fa-check-circle';
    if (type === 'error') icon = 'fa-exclamation-circle';

    toast.innerHTML = `<i class="fas ${icon}" style="font-size: 1.6rem;"></i><span style="font-weight: 800; font-size: 1.05rem; color: white;">${message}</span>`;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0) scale(1)';
    }, 50);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%) scale(0.9)';
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

window.getCart = function() {
    return JSON.parse(localStorage.getItem('cart')) || [];
}

window.saveCart = function(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    const cartCount = document.getElementById('cartCount');
    if (cartCount) cartCount.textContent = totalItems;
}

window.addToCart = function(food, btn) {
    const cart = getCart();
    const existingItem = cart.find(item => item.name === food.name);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ ...food, quantity: 1 });
    }
    
    saveCart(cart);
    showToast(`${food.name} added to cart!`, 'success');

    if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Added';
        btn.style.background = '#2ed573';
        btn.style.color = 'white';
        btn.style.borderColor = '#2ed573';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.style.background = '';
            btn.style.color = '';
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
