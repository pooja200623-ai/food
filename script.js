document.addEventListener('DOMContentLoaded', () => {
    // 1. Initial UI Injections
    injectCartDrawer();
    injectChatWidget();
    injectThemeToggle();
    injectToastContainer();

    // 2. Global Element Selectors
    const loginForm = document.getElementById('loginForm');
    const verifyBtn = document.getElementById('verifyBtn');
    const initialStep = document.getElementById('initialStep');
    const otpStep = document.getElementById('otpStep');
    const backToEmail = document.getElementById('backToEmail');
    const emailInput = document.getElementById('email');
    const nameInput = document.getElementById('userName');
    const otpInput = document.getElementById('otp');
    const userProfileImg = document.querySelector('.user-profile img');
    
    // 3. Authentication & OTP Logic
    const checkAuth = () => {
        const user = JSON.parse(localStorage.getItem('currentUser'));
        const isLoginPage = window.location.pathname.endsWith('index.html') || window.location.pathname === '/' || window.location.pathname.endsWith('zomato/') || window.location.pathname === '';
        
        if (user) {
            if (isLoginPage) {
                window.location.href = 'dashboard.html';
            } else {
                // Update UI with user data
                if (userProfileImg) {
                    userProfileImg.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=FF5F52&color=fff`;
                }
            }
        } else {
            if (!isLoginPage) {
                window.location.href = 'index.html';
            }
        }
    };

    checkAuth();

    if (loginForm) {
        let generatedOTP = null;

        // Step 1: Verify Email & Send OTP
        if (verifyBtn) {
            verifyBtn.addEventListener('click', () => {
                const email = emailInput.value.trim();
                const name = nameInput.value.trim();

                if (!email || !name) {
                    showToast('Please fill in both Name and Email', 'error');
                    return;
                }

                verifyBtn.disabled = true;
                verifyBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending OTP...';

                setTimeout(() => {
                    generatedOTP = "1234"; // Simulated OTP
                    initialStep.style.display = 'none';
                    otpStep.style.display = 'block';
                    otpStep.classList.add('fade-in');
                    
                    showToast(`OTP sent to ${email} (Use: 1234)`, 'success');
                    
                    // Reset button
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<span>Verify Email</span><i class="fas fa-paper-plane"></i>';
                }, 1500);
            });
        }

        // Step 2: Back to Email
        if (backToEmail) {
            backToEmail.addEventListener('click', () => {
                otpStep.style.display = 'none';
                initialStep.style.display = 'block';
                initialStep.classList.add('fade-in');
            });
        }

        // Step 3: Final Login (OTP Verification)
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const enteredOTP = otpInput.value.trim();

            if (enteredOTP === generatedOTP) {
                const submitBtn = loginForm.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Authenticating...';

                setTimeout(() => {
                    const userData = {
                        name: nameInput.value.trim(),
                        email: emailInput.value.trim(),
                        loginTime: new Date().getTime()
                    };
                    localStorage.setItem('currentUser', JSON.stringify(userData));
                    showToast(`Welcome back, ${userData.name}!`, 'success');
                    setTimeout(() => window.location.href = 'dashboard.html', 1000);
                }, 1500);
            } else {
                showToast('Invalid OTP. Please try again (Use 1234).', 'error');
            }
        });
    }

    // 4. Parallax Effect
    if (document.querySelector('.login-card')) {
        document.addEventListener('mousemove', (e) => {
            const amount = 15;
            const x = (e.clientX / window.innerWidth - 0.5) * amount;
            const y = (e.clientY / window.innerHeight - 0.5) * amount;
            const card = document.querySelector('.login-card');
            card.style.transform = `perspective(1000px) rotateY(${x}deg) rotateX(${-y}deg) translateY(${-y}px)`;
        });
    }

    // 5. Global Cart Drawer Logic
    const cartBtns = document.querySelectorAll('.cart-btn');
    const closeDrawerBtn = document.querySelector('.close-drawer');
    const cartOverlay = document.querySelector('.cart-overlay');
    const cartDrawer = document.querySelector('.cart-drawer');
    const openDrawer = () => {
        cartDrawer.classList.add('active');
        cartOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        syncCartUI();
    };
    const closeDrawer = () => {
        cartDrawer.classList.remove('active');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    };
    cartBtns.forEach(btn => btn.addEventListener('click', openDrawer));
    if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeDrawer);
    if (cartOverlay) cartOverlay.addEventListener('click', closeDrawer);

    // 6. Checkout / Placement Logic
    const placeOrderBtn = document.getElementById('placeOrder');
    const successOverlay = document.getElementById('successOverlay');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', () => {
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
            setTimeout(() => {
                showToast('Order placed successfully!', 'success');
                successOverlay.classList.add('visible');
                triggerConfetti();
                cartCount = 0;
                localStorage.setItem('cartCount', 0);
                updateCartBadge(0);
            }, 2000);
        });
    }

    // 7. Search Logic
    const searchInputs = document.querySelectorAll('.search-bar input');
    searchInputs.forEach(input => {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const query = input.value.trim();
                if (query) window.location.href = `search.html?q=${encodeURIComponent(query)}`;
            }
        });
    });

    // 8. Dashboard Loading & Filtering
    const skeletonGrid = document.getElementById('skeletonGrid');
    const restaurantGrid = document.getElementById('restaurantGrid');
    const categoryCards = document.querySelectorAll('.category-card');
    if (skeletonGrid) {
        setTimeout(() => {
            skeletonGrid.classList.add('hidden');
            restaurantGrid.classList.remove('hidden');
            restaurantGrid.classList.add('fade-in');
        }, 1500);
        categoryCards.forEach(card => {
            card.addEventListener('click', () => {
                const cat = card.getAttribute('data-category');
                categoryCards.forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                restaurantGrid.classList.add('hidden');
                skeletonGrid.classList.remove('hidden');
                setTimeout(() => {
                    skeletonGrid.classList.add('hidden');
                    restaurantGrid.classList.remove('hidden');
                    restaurantGrid.classList.add('fade-in');
                    const resCards = restaurantGrid.querySelectorAll('.res-card');
                    resCards.forEach(rc => {
                        const info = rc.querySelector('.res-info p').textContent.toLowerCase();
                        rc.style.display = (cat === 'all' || info.includes(cat)) ? 'block' : 'none';
                    });
                    showToast(`Showing ${cat} restaurants`, 'info');
                }, 800);
            });
        });
    }

    // 9. Menu Category Highlighting
    if (document.querySelector('.menu-sidebar')) {
        window.addEventListener('scroll', () => {
            const categories = document.querySelectorAll('.menu-category');
            const navLinks = document.querySelectorAll('.menu-sidebar a');
            let current = '';
            categories.forEach(category => {
                if (pageYOffset >= category.offsetTop - 150) current = category.getAttribute('id');
            });
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').substring(1) === current) link.classList.add('active');
            });
        });
    }

    // 10. Tab Switching Logic
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    if (tabBtns.length > 0) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetTab = btn.getAttribute('data-tab');
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === `${targetTab}-tab`) content.classList.add('active');
                });
            });
        });
    }

    // 11. Accordion Logic
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const item = header.parentElement;
            const isActive = item.classList.contains('active');
            document.querySelectorAll('.accordion-item').forEach(i => i.classList.remove('active'));
            if (!isActive) item.classList.add('active');
        });
    });

    // 12. Support Form & Chat Logic
    const supportForm = document.getElementById('supportForm');
    if (supportForm) {
        supportForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const submitBtn = supportForm.querySelector('button');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending...';
            setTimeout(() => {
                showToast('Support request sent! We will contact you soon.', 'success');
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Message Sent!';
                submitBtn.style.background = '#10B981';
                supportForm.reset();
            }, 1500);
        });
    }

    const chatFab = document.querySelector('.chat-fab');
    const chatWindow = document.querySelector('.chat-window');
    const chatInput = document.querySelector('.chat-input-area input');
    const chatSend = document.querySelector('.chat-send-btn');
    const chatMessages = document.querySelector('.chat-messages');
    if (chatFab) {
        chatFab.addEventListener('click', () => chatWindow.classList.toggle('active'));
        const sendMessage = () => {
            const text = chatInput.value.trim();
            if (!text) return;
            const userMsg = document.createElement('div');
            userMsg.className = 'message user';
            userMsg.textContent = text;
            chatMessages.appendChild(userMsg);
            chatInput.value = '';
            chatMessages.scrollTop = chatMessages.scrollHeight;
            setTimeout(() => {
                const agentMsg = document.createElement('div');
                agentMsg.className = 'message agent';
                agentMsg.textContent = getBotResponse(text);
                chatMessages.appendChild(agentMsg);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, 1000);
        };
        chatSend.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
    }

    // 13. Settings Logic
    const settingsNavItems = document.querySelectorAll('.settings-nav li');
    const settingsSections = document.querySelectorAll('.settings-section');
    if (settingsNavItems.length > 0) {
        settingsNavItems.forEach(item => {
            item.addEventListener('click', () => {
                const targetSection = item.getAttribute('data-section');
                settingsNavItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                settingsSections.forEach(section => {
                    section.classList.remove('active');
                    if (section.id === `${targetSection}-section`) section.classList.add('active');
                });
            });
        });
    }

    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = profileForm.querySelector('.btn-primary');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Saving...';
            setTimeout(() => {
                showToast('Profile updated successfully!', 'success');
                btn.disabled = false;
                btn.textContent = 'Save Changes';
            }, 1000);
        });
    }

    const settingsToggles = document.querySelectorAll('.settings-section input[type="checkbox"]');
    settingsToggles.forEach(toggle => {
        toggle.addEventListener('change', () => {
            const label = toggle.closest('.toggle-item').querySelector('h4').textContent;
            showToast(`${label} ${toggle.checked ? 'enabled' : 'disabled'}`, 'info');
        });
    });

    // 14. Scroll Reveal Animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const revealElements = document.querySelectorAll('.res-card, .category-card, .menu-item-card, .section-header, .membership-card');
    revealElements.forEach(el => {
        el.classList.add('reveal-item');
        revealObserver.observe(el);
    });
});

// --- Helper Functions ---

let cartCount = parseInt(localStorage.getItem('cartCount')) || 0;

function updateCart(amount) {
    cartCount += amount;
    localStorage.setItem('cartCount', cartCount);
    updateCartBadge(cartCount);
    const floatingCart = document.getElementById('floatingCart');
    if (floatingCart) {
        if (cartCount > 0) {
            floatingCart.classList.add('visible');
            document.getElementById('itemsAdded').textContent = `${cartCount} Item${cartCount > 1 ? 's' : ''} added`;
        } else {
            floatingCart.classList.remove('visible');
        }
    }
    const cartIcon = document.querySelector('.cart-btn i');
    if (cartIcon) {
        cartIcon.style.transform = 'scale(1.3)';
        setTimeout(() => cartIcon.style.transform = 'scale(1)', 200);
    }
    if (amount > 0) showToast('Item added to cart!', 'success');
    else if (amount < 0) showToast('Item removed from cart', 'info');
}

function updateCartBadge(count) {
    const badges = document.querySelectorAll('.badge');
    badges.forEach(badge => {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    });
}

function syncCartUI() {
    const drawerContent = document.querySelector('.drawer-content');
    const drawerTotal = document.getElementById('drawerTotal');
    if (!drawerContent) return;
    if (cartCount === 0) {
        drawerContent.innerHTML = `<div class="empty-cart-state"><i class="fas fa-shopping-basket"></i><p>Your cart is empty.<br>Add some gourmet treats!</p></div>`;
        drawerTotal.textContent = '$0.00';
    } else {
        drawerContent.innerHTML = `<div class="drawer-item"><img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=70&q=80" alt="Pizza"><div class="drawer-item-info"><h4>Classic Margherita Pizza</h4><span class="price">$12.99</span><div class="item-qty-controls"><button class="qty-btn" onclick="updateCart(-1); syncCartUI();"><i class="fas fa-minus"></i></button><span>${cartCount}</span><button class="qty-btn" onclick="updateCart(1); syncCartUI();"><i class="fas fa-plus"></i></button></div></div></div>`;
        drawerTotal.textContent = `$${(cartCount * 12.99).toFixed(2)}`;
    }
}

function injectCartDrawer() {
    if (document.querySelector('.cart-drawer')) return;
    const drawerHTML = `<div class="cart-overlay"></div><aside class="cart-drawer"><div class="drawer-header"><h3>Your Order</h3><button class="close-drawer"><i class="fas fa-times"></i></button></div><div class="drawer-content"></div><div class="drawer-footer"><div class="drawer-bill-row"><span>Subtotal</span><span id="drawerTotal">$0.00</span></div><button class="btn-primary btn-block" onclick="window.location.href='checkout.html'">PROCEED TO CHECKOUT</button></div></aside>`;
    document.body.insertAdjacentHTML('beforeend', drawerHTML);
    updateCartBadge(cartCount);
}

function injectChatWidget() {
    if (document.querySelector('.chat-widget-container')) return;
    const chatHTML = `<div class="chat-widget-container"><div class="chat-window"><div class="chat-header"><img src="https://ui-avatars.com/api/?name=Crave+Bot&background=FF5F52&color=fff" alt="Bot"><div><h4>CraveBot Support</h4><span>Online • Usually replies in seconds</span></div></div><div class="chat-messages"><div class="message agent">Hi there! 👋 How can I help you today? I can help with orders, payments, or membership info.</div></div><div class="chat-input-area"><input type="text" placeholder="Type a message..."><button class="chat-send-btn"><i class="fas fa-paper-plane"></i></button></div></div><div class="chat-fab"><i class="fas fa-comment-dots"></i><div class="chat-badge"></div></div></div>`;
    document.body.insertAdjacentHTML('beforeend', chatHTML);
}

function injectThemeToggle() {
    const header = document.querySelector('.nav-container');
    if (!header || document.querySelector('.theme-toggle')) return;
    const toggleHTML = `<button class="theme-toggle" style="background:none; border:none; color:var(--text-main); font-size:1.2rem; cursor:pointer; margin-right:1rem;"><i class="fas fa-moon"></i></button>`;
    const userActions = header.querySelector('.user-actions');
    userActions.insertAdjacentHTML('afterbegin', toggleHTML);
    const themeBtn = document.querySelector('.theme-toggle');
    const html = document.documentElement;
    const currentTheme = localStorage.getItem('theme') || 'dark';
    if (currentTheme === 'light') { html.classList.add('light-mode'); themeBtn.querySelector('i').className = 'fas fa-sun'; }
    themeBtn.addEventListener('click', () => {
        html.classList.toggle('light-mode');
        const isLight = html.classList.contains('light-mode');
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
        themeBtn.querySelector('i').className = isLight ? 'fas fa-sun' : 'fas fa-moon';
        showToast(`Theme switched to ${isLight ? 'Light' : 'Dark'} Mode`, 'info');
    });
}

function injectToastContainer() {
    if (document.querySelector('.toast-container')) return;
    const toastHTML = `<div class="toast-container"></div>`;
    document.body.insertAdjacentHTML('beforeend', toastHTML);
}

function showToast(message, type = 'success') {
    const container = document.querySelector('.toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    toast.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.classList.add('hide'); setTimeout(() => toast.remove(), 400); }, 3000);
}

function triggerConfetti() {
    const container = document.createElement('div');
    container.className = 'confetti-container';
    document.body.appendChild(container);
    const colors = ['#FF5F52', '#FFB800', '#10B981', '#FFFFFF', '#3B82F6'];
    for (let i = 0; i < 100; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDelay = Math.random() * 2 + 's';
        confetti.style.width = Math.random() * 8 + 5 + 'px';
        confetti.style.height = confetti.style.width;
        container.appendChild(confetti);
    }
    setTimeout(() => container.remove(), 5000);
}

function getBotResponse(input) {
    const text = input.toLowerCase();
    if (text.includes('order')) return "I can help with that! Please provide your order ID (e.g., CD-88291) so I can check the status for you.";
    if (text.includes('payment') || text.includes('refund')) return "Payment issues are usually resolved within 24 hours. Would you like me to raise a ticket for our billing team?";
    if (text.includes('gold')) return "CraveGold is our premium membership! You're currently seeing our standard trial. Want to upgrade for free delivery?";
    if (text.includes('hello') || text.includes('hi')) return "Hello! How's your gourmet journey going today? Any specific craving I can help with?";
    return "That's a great question! Let me connect you with a live agent for more details. One moment please...";
}
