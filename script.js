window.applyGlobalTheme = function(color) {
    const root = document.documentElement;
    const themes = {
        '#d4af37': {
            '--primary-color': '#d4af37',
            '--primary-dark': '#b08e26',
            '--primary-light': '#e6c55c',
            '--primary-rgb': '212, 175, 55',
            '--accent-color': '#c5a880',
            '--bg-color': '#090806',
            '--card-bg': '#13110d',
            '--input-border': '#221e16',
            '--gradient-main': 'linear-gradient(135deg, #d4af37 0%, #aa882c 100%)',
            '--gradient-dark': 'linear-gradient(135deg, #aa882c 0%, #d4af37 100%)',
            '--shadow-glow': '0 0 35px rgba(212, 175, 55, 0.15)'
        },
        '#b76e79': {
            '--primary-color': '#b76e79',
            '--primary-dark': '#9e5963',
            '--primary-light': '#cf8d97',
            '--primary-rgb': '183, 110, 121',
            '--accent-color': '#cf8d97',
            '--bg-color': '#0e0a0a',
            '--card-bg': '#171112',
            '--input-border': '#291e20',
            '--gradient-main': 'linear-gradient(135deg, #b76e79 0%, #8a4f58 100%)',
            '--gradient-dark': 'linear-gradient(135deg, #8a4f58 0%, #b76e79 100%)',
            '--shadow-glow': '0 0 35px rgba(183, 110, 121, 0.15)'
        },
        '#dcdde1': {
            '--primary-color': '#dcdde1',
            '--primary-dark': '#b2b3b8',
            '--primary-light': '#f5f6fa',
            '--primary-rgb': '220, 221, 225',
            '--accent-color': '#a5a6ab',
            '--bg-color': '#0a0b0d',
            '--card-bg': '#121418',
            '--input-border': '#20232a',
            '--gradient-main': 'linear-gradient(135deg, #dcdde1 0%, #718093 100%)',
            '--gradient-dark': 'linear-gradient(135deg, #718093 0%, #dcdde1 100%)',
            '--shadow-glow': '0 0 35px rgba(220, 221, 225, 0.15)'
        },
        '#ff3838': {
            '--primary-color': '#ff3838',
            '--primary-dark': '#cf2323',
            '--primary-light': '#ff6b6b',
            '--primary-rgb': '255, 56, 56',
            '--accent-color': '#ff4757',
            '--bg-color': '#0c0606',
            '--card-bg': '#150d0d',
            '--input-border': '#261616',
            '--gradient-main': 'linear-gradient(135deg, #ff3838 0%, #cf2323 100%)',
            '--gradient-dark': 'linear-gradient(135deg, #cf2323 0%, #ff3838 100%)',
            '--shadow-glow': '0 0 35px rgba(255, 56, 56, 0.15)'
        },
        '#2e86de': {
            '--primary-color': '#2e86de',
            '--primary-dark': '#1b61ab',
            '--primary-light': '#54a0ff',
            '--primary-rgb': '46, 134, 222',
            '--accent-color': '#54a0ff',
            '--bg-color': '#05080c',
            '--card-bg': '#0d1218',
            '--input-border': '#16202b',
            '--gradient-main': 'linear-gradient(135deg, #2e86de 0%, #1b61ab 100%)',
            '--gradient-dark': 'linear-gradient(135deg, #1b61ab 0%, #2e86de 100%)',
            '--shadow-glow': '0 0 35px rgba(46, 134, 222, 0.15)'
        },
        '#10b981': {
            '--primary-color': '#10b981',
            '--primary-dark': '#068259',
            '--primary-light': '#34d399',
            '--primary-rgb': '16, 185, 129',
            '--accent-color': '#34d399',
            '--bg-color': '#050a08',
            '--card-bg': '#0d1410',
            '--input-border': '#16241c',
            '--gradient-main': 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
            '--gradient-dark': 'linear-gradient(135deg, #059669 0%, #10b981 100%)',
            '--shadow-glow': '0 0 35px rgba(16, 185, 129, 0.15)'
        }
    };
    
    const activeTheme = themes[color] || themes['#d4af37'];
    for (const [property, value] of Object.entries(activeTheme)) {
        root.style.setProperty(property, value);
    }
};

// Immediate global theme check to prevent flash of wrong colors
(function() {
    const session = JSON.parse(localStorage.getItem('user_session'));
    if (session && session.avatar_color) {
        window.applyGlobalTheme(session.avatar_color);
    } else {
        window.applyGlobalTheme('#d4af37');
    }
})();

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

    // Login logic has been moved directly to signin.html to prevent caching issues

    // Dashboard Logout Logic
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem('user_session');
            window.location.href = 'signin.html';
        });
    }

    // Sync Cart Count on load
    if (typeof getCart === 'function') {
        saveCart(getCart());
    }
    if (typeof getWishlist === 'function') {
        saveWishlist(getWishlist());
    }

    // Render Centralized UI Components
    renderNavbar();
    renderFooter();
    renderScrollToTop();

    // Sticky Navbar Logic
    window.addEventListener('scroll', () => {
        const dashNav = document.querySelector('.dash-nav');
        if (dashNav) {
            if (window.scrollY > 100) {
                dashNav.style.top = '15px';
                dashNav.style.padding = '0.6rem 2rem';
                dashNav.style.background = 'rgba(10, 10, 12, 0.9)';
                dashNav.style.width = '85%';
                dashNav.style.boxShadow = '0 20px 40px rgba(0,0,0,0.4)';
                dashNav.style.borderColor = 'rgba(124, 58, 237, 0.2)';
            } else {
                dashNav.style.top = '30px';
                dashNav.style.padding = '1rem 3rem';
                dashNav.style.background = 'rgba(255, 255, 255, 0.03)';
                dashNav.style.width = '95%';
                dashNav.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
                dashNav.style.borderColor = 'rgba(255, 255, 255, 0.08)';
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
        { name: 'Wishlist', href: 'wishlist.html', icon: 'fa-heart', id: 'wishlistLink' },
        { name: 'Cart', href: 'cart.html', icon: 'fa-shopping-cart', id: 'cartLink' },
        { name: 'Orders', href: 'orders.html', icon: 'fa-history' },
        { name: 'Profile', href: 'profile.html', icon: 'fa-user-circle' }
    ];

    dashNav.style.transition = 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
    dashNav.style.backdropFilter = 'blur(25px)';
    dashNav.style.webkitBackdropFilter = 'blur(25px)';
    dashNav.style.border = '1px solid rgba(255,255,255,0.08)';
    dashNav.style.position = 'fixed';
    dashNav.style.left = '50%';
    dashNav.style.transform = 'translateX(-50%)';
    dashNav.style.zIndex = '2000';
    dashNav.style.borderRadius = '50px';

    dashNav.innerHTML = `
        <div class="nav-brand" style="cursor: pointer; font-size: 2.4rem; font-weight: 400; letter-spacing: -2px; transition: 0.4s; font-family: 'DM Serif Display', serif;" onclick="location.href='index.html'" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            <span style="background: var(--gradient-main); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Crave</span>
        </div>
        
        <div class="nav-links" style="display: flex; gap: 5px; align-items: center; background: rgba(255,255,255,0.03); padding: 6px; border-radius: 40px; border: 1px solid rgba(255,255,255,0.05);">
            ${links.map(link => `
                <a href="${link.href}" class="${currentPath === link.href ? 'active' : ''}" style="
                    text-decoration: none; 
                    color: ${currentPath === link.href ? 'white' : 'var(--text-muted)'};
                    padding: 10px 22px;
                    border-radius: 30px;
                    font-weight: 700;
                    font-size: 0.9rem;
                    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    background: ${currentPath === link.href ? 'var(--gradient-main)' : 'transparent'};
                    box-shadow: ${currentPath === link.href ? '0 10px 20px rgba(124, 58, 237, 0.35)' : 'none'};
                    text-transform: uppercase;
                    letter-spacing: 1.5px;
                " onmouseover="if('${currentPath}' !== '${link.href}') { this.style.color='white'; this.style.background='rgba(255,255,255,0.05)'; }" 
                   onmouseout="if('${currentPath}' !== '${link.href}') { this.style.color='var(--text-muted)'; this.style.background='transparent'; }">
                    <i class="fas ${link.icon}" style="font-size: 1rem; opacity: 0.9;"></i>
                    ${link.name}
                    ${link.id === 'cartLink' ? `<span id="cartCount" style="background: white; color: var(--primary-color); border-radius: 50%; padding: 2px 7px; font-size: 0.75rem; font-weight: 900; min-width: 20px; text-align: center; margin-left: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">0</span>` : ''}
                    ${link.id === 'wishlistLink' ? `<span id="wishlistCount" style="background: white; color: #ff4757; border-radius: 50%; padding: 2px 7px; font-size: 0.75rem; font-weight: 900; min-width: 20px; text-align: center; margin-left: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">0</span>` : ''}
                </a>
            `).join('')}
        </div>
        
        <div class="user-profile" style="display: flex; align-items: center; gap: 15px; padding: 4px 4px 4px 20px; border-radius: 50px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                <span style="font-weight: 800; font-size: 0.95rem; color: white; line-height: 1;">${session.name}</span>
                <span style="font-size: 0.7rem; color: var(--accent-color); font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-top: 2px;">Luxe Member</span>
            </div>
            <div class="user-avatar" style="width: 45px; height: 45px; border-radius: 50%; background: ${session.avatar_color || 'var(--primary-color)'}; color: black; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; box-shadow: 0 5px 15px rgba(212,175,55,0.35); border: 2px solid #000;">
                ${session.name.charAt(0).toUpperCase()}
            </div>
            <button id="logoutBtn" class="logout-btn" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); border-radius: 50%; transition: 0.4s; color: var(--text-muted); border: none; cursor: pointer;" onmouseover="this.style.background='var(--primary-color)';this.style.color='white';this.style.transform='rotate(90deg)'" onmouseout="this.style.background='rgba(255,255,255,0.05)';this.style.color='var(--text-muted)';this.style.transform='rotate(0deg)'"><i class="fas fa-sign-out-alt"></i></button>
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

    const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
    const wishlistCount = document.getElementById('wishlistCount');
    if (wishlistCount) wishlistCount.textContent = wishlist.length;
}

function renderFooter() {
    const footerContainer = document.getElementById('footerContainer');
    if (!footerContainer) return;

    footerContainer.innerHTML = `
        <footer style="background: #000; color: white; padding: 10rem 2rem 5rem; margin-top: 15rem; font-family: 'Outfit', sans-serif; position: relative; overflow: hidden; border-top: 1px solid rgba(255,255,255,0.03);">
            <!-- Glow Effect -->
            <div style="position: absolute; top: -200px; left: 50%; transform: translateX(-50%); width: 800px; height: 400px; background: radial-gradient(circle, rgba(124, 58, 237, 0.07) 0%, transparent 70%); z-index: 1;"></div>
            
            <div style="max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 8rem; position: relative; z-index: 2;">
                <div class="reveal">
                    <h2 style="font-size: 4rem; margin-bottom: 2.5rem; font-weight: 400; font-family: 'DM Serif Display', serif; letter-spacing: -2px;"><span style="background: var(--gradient-main); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Crave Luxe</span></h2>
                    <p style="opacity: 0.6; line-height: 2; font-size: 1.25rem; margin-bottom: 3.5rem; font-weight: 300;">Crafting culinary masterpieces for the modern palate. We redefine delivery as a curated gourmet performance.</p>
                    <div style="display: flex; gap: 25px;">
                        ${['facebook-f', 'instagram', 'twitter', 'youtube'].map(icon => `
                            <a href="#" style="width: 55px; height: 55px; border-radius: 18px; background: rgba(255,255,255,0.03); color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.5s; border: 1px solid rgba(255,255,255,0.05);" 
                               onmouseover="this.style.background='var(--gradient-main)'; this.style.transform='translateY(-10px) rotate(10deg)'; this.style.boxShadow='0 15px 30px rgba(124, 58, 237,0.3)'; this.style.borderColor='transparent'" 
                               onmouseout="this.style.background='rgba(255,255,255,0.03)'; this.style.transform='translateY(0) rotate(0deg)'; this.style.boxShadow='none'; this.style.borderColor='rgba(255,255,255,0.05)'">
                                <i class="fab fa-${icon}" style="font-size: 1.3rem;"></i>
                            </a>
                        `).join('')}
                    </div>
                </div>
                
                <div class="reveal" style="animation-delay: 0.1s;">
                    <h3 style="margin-bottom: 3rem; font-size: 1.1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 4px; color: var(--primary-color);">Discover</h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 1.8rem; padding: 0;">
                        ${['The Collection', 'Signature Menu', 'Elite Offers', 'Private Tracking'].map(item => `
                            <li><a href="#" style="color: white; text-decoration: none; opacity: 0.5; transition: 0.5s; display: flex; align-items: center; gap: 15px; font-weight: 500; font-size: 1.1rem;" onmouseover="this.style.opacity=1; this.style.paddingLeft='15px'; this.style.color='var(--accent-color)'" onmouseout="this.style.opacity=0.5; this.style.paddingLeft='0'; this.style.color='white'"><i class="fas fa-chevron-right" style="font-size: 0.8rem; color: var(--primary-color);"></i> ${item}</a></li>
                        `).join('')}
                    </ul>
                </div>

                <div class="reveal" style="animation-delay: 0.2s;">
                    <h3 style="margin-bottom: 3rem; font-size: 1.1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 4px; color: var(--primary-color);">Luxe Club</h3>
                    <p style="opacity: 0.6; margin-bottom: 3rem; font-size: 1.2rem; font-weight: 300; line-height: 1.8;">Join our private circle for early access to michelin-star seasonal drops.</p>
                    <div style="background: rgba(255,255,255,0.02); padding: 10px; border-radius: 25px; display: flex; gap: 12px; border: 1px solid rgba(255,255,255,0.06); box-shadow: inset 0 2px 15px rgba(0,0,0,0.4);">
                        <input type="email" placeholder="Email Concierge" style="background: transparent; border: none; padding: 15px 25px; color: white; flex: 1; outline: none; font-family: inherit; font-size: 1.1rem; font-weight: 400;">
                        <button style="background: var(--gradient-main); color: #fff; border: none; padding: 15px 35px; border-radius: 18px; cursor: pointer; font-weight: 900; transition: 0.5s; box-shadow: 0 10px 25px rgba(124,58,237,0.35); text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 20px 40px rgba(124,58,237,0.5)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 10px 25px rgba(124,58,237,0.35)'">Join</button>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 10rem; padding-top: 5rem; border-top: 1px solid rgba(255,255,255,0.02); opacity: 0.4; font-size: 1rem; display: flex; flex-direction: column; gap: 15px; font-weight: 300; letter-spacing: 2px;">
                <span>&copy; 2026 CRAVE LUXE GASTRONOMY. DEFINING THE SUBLIME.</span>
                <span style="font-size: 0.85rem; color: var(--accent-color); font-weight: 800; text-transform: uppercase; letter-spacing: 4px;">Excellence as Standard.</span>
            </div>
        </footer>
    `;
}

function renderScrollToTop() {
    if (document.getElementById('scrollToTopBtn')) return; // Prevent duplicates

    const btn = document.createElement('button');
    btn.id = 'scrollToTopBtn';
    btn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    btn.style.position = 'fixed';
    btn.style.bottom = '40px';
    btn.style.right = '40px';
    btn.style.width = '55px';
    btn.style.height = '55px';
    btn.style.borderRadius = '50%';
    btn.style.background = 'var(--gradient-main)';
    btn.style.color = '#000';
    btn.style.border = 'none';
    btn.style.fontSize = '1.2rem';
    btn.style.cursor = 'pointer';
    btn.style.boxShadow = '0 10px 25px rgba(124,58,237,0.45)';
    btn.style.zIndex = '3000';
    btn.style.opacity = '0';
    btn.style.visibility = 'hidden';
    btn.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
    btn.style.display = 'flex';
    btn.style.alignItems = 'center';
    btn.style.justifyContent = 'center';
    
    // Hover effects
    btn.onmouseover = () => {
        btn.style.transform = 'translateY(-5px) scale(1.1)';
        btn.style.boxShadow = '0 15px 35px rgba(124,58,237,0.65)';
    };
    btn.onmouseout = () => {
        btn.style.transform = 'translateY(0) scale(1)';
        btn.style.boxShadow = '0 10px 25px rgba(124,58,237,0.45)';
    };

    btn.onclick = () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    document.body.appendChild(btn);

    // Scroll listener
    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) {
            btn.style.opacity = '1';
            btn.style.visibility = 'visible';
            // Avoid overriding hover transform if hovered
            if(!btn.matches(':hover')) {
                btn.style.transform = 'translateY(0) scale(1)';
            }
        } else {
            btn.style.opacity = '0';
            btn.style.visibility = 'hidden';
            btn.style.transform = 'translateY(20px) scale(0.9)';
        }
    });
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
    toast.style.borderRadius = '20px';
    toast.style.border = '1px solid rgba(255,255,255,0.1)';
    toast.style.background = 'rgba(10, 10, 12, 0.95)';
    toast.style.color = 'white';
    toast.style.backdropFilter = 'blur(20px)';
    toast.style.padding = '1.4rem 3rem';
    toast.style.boxShadow = '0 30px 60px rgba(0,0,0,0.6)';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '20px';
    toast.style.transition = 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(30px) scale(0.9)';
    
    let icon = 'fa-info-circle';
    let iconColor = 'var(--accent-color)';
    if (type === 'success') { icon = 'fa-check-circle'; iconColor = '#2ed573'; }
    if (type === 'error') { icon = 'fa-exclamation-circle'; iconColor = '#ff4757'; }

    toast.innerHTML = `<i class="fas ${icon}" style="font-size: 1.8rem; color: ${iconColor}; text-shadow: 0 0 15px ${iconColor}44;"></i><span style="font-weight: 700; font-size: 1.1rem; color: white; letter-spacing: 0.5px;">${message}</span>`;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0) scale(1)';
    }, 50);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%) scale(0.95)';
        setTimeout(() => toast.remove(), 600);
    }, 4500);
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
    showToast(`${food.name} Added to Collection`, 'success');

    if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Added';
        btn.style.background = '#2ed573';
        btn.style.color = '#000';
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
    showToast('Removed from Collection', 'info');
    if (typeof renderCart === 'function') renderCart();
}

window.getWishlist = function() {
    return JSON.parse(localStorage.getItem('wishlist')) || [];
}

window.saveWishlist = function(wishlist) {
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    const count = wishlist.length;
    const wishlistCount = document.getElementById('wishlistCount');
    if (wishlistCount) wishlistCount.textContent = count;
}

window.toggleWishlist = function(food, btn) {
    let wishlist = getWishlist();
    const index = wishlist.findIndex(item => item.name === food.name);
    const icon = btn.querySelector('i');
    
    if (index === -1) {
        wishlist.push(food);
        saveWishlist(wishlist);
        if (icon) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            icon.style.color = '#ff4757';
        }
        showToast(`${food.name} Added to Wishlist`, 'success');
    } else {
        wishlist = wishlist.filter(item => item.name !== food.name);
        saveWishlist(wishlist);
        if (icon) {
            icon.classList.remove('fas');
            icon.classList.add('far');
            icon.style.color = '';
        }
        showToast(`${food.name} Removed from Wishlist`, 'info');
        if (typeof renderWishlist === 'function') {
            renderWishlist();
        }
    }
}
