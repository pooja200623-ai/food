document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // Authentication & Modal Logic
    // -------------------------------------------------------------
    const authModal = document.getElementById('authModal');
    const closeAuthModal = document.getElementById('closeAuthModal');
    const loginBtns = document.querySelectorAll('#loginBtn, #navLoginBtn');
    const signupBtns = document.querySelectorAll('#signupBtn, #navSignupBtn');
    
    const authModalTitle = document.getElementById('authModalTitle');
    const nameGroup = document.getElementById('nameGroup');
    const toggleAuthMode = document.getElementById('toggleAuthMode');
    const authToggleText = document.getElementById('authToggleText');
    const userNameInput = document.getElementById('userName');
    
    let isSignupMode = false;

    // Open Modal Handlers
    const openModal = (signup = false) => {
        isSignupMode = signup;
        updateModalUI();
        if(authModal) authModal.classList.add('active');
    };

    loginBtns.forEach(btn => btn?.addEventListener('click', () => openModal(false)));
    signupBtns.forEach(btn => btn?.addEventListener('click', () => openModal(true)));

    // Close Modal Handler
    if (closeAuthModal) {
        closeAuthModal.addEventListener('click', () => {
            authModal.classList.remove('active');
        });
    }

    // Toggle Login/Signup Mode
    if (toggleAuthMode) {
        toggleAuthMode.addEventListener('click', (e) => {
            e.preventDefault();
            isSignupMode = !isSignupMode;
            updateModalUI();
        });
    }

    function updateModalUI() {
        if (!authModalTitle) return;
        if (isSignupMode) {
            authModalTitle.textContent = 'Sign up';
            nameGroup.style.display = 'block';
            authToggleText.textContent = 'Already have an account?';
            toggleAuthMode.textContent = 'Log in';
        } else {
            authModalTitle.textContent = 'Login';
            nameGroup.style.display = 'none';
            authToggleText.textContent = 'New to Zomato?';
            toggleAuthMode.textContent = 'Create account';
        }
    }

    // Check Auth State
    const checkAuth = () => {
        const user = JSON.parse(localStorage.getItem('currentUser'));
        const navLoginBtn = document.getElementById('navLoginBtn');
        const navSignupBtn = document.getElementById('navSignupBtn');
        const userProfile = document.getElementById('userProfile');
        const userNameDisplay = document.getElementById('userNameDisplay');
        const userAvatar = document.getElementById('userAvatar');

        const indexLoginBtn = document.getElementById('loginBtn');
        const indexSignupBtn = document.getElementById('signupBtn');

        if (user) {
            // Update Dashboard/Index Header
            if(navLoginBtn) navLoginBtn.style.display = 'none';
            if(navSignupBtn) navSignupBtn.style.display = 'none';
            if(userProfile) {
                userProfile.style.display = 'flex';
                userNameDisplay.textContent = user.name;
                userAvatar.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=E23744&color=fff`;
            }

            if(indexLoginBtn) indexLoginBtn.style.display = 'none';
            if(indexSignupBtn) indexSignupBtn.textContent = user.name;
        }
    };

    checkAuth();

    // -------------------------------------------------------------
    // Login API Logic
    // -------------------------------------------------------------
    const authForm = document.getElementById('authForm');
    const verifyBtn = document.getElementById('verifyBtn');
    const initialStep = document.getElementById('initialStep');
    const otpStep = document.getElementById('otpStep');
    const backToEmail = document.getElementById('backToEmail');
    const emailInput = document.getElementById('email');
    const otpInput = document.getElementById('otp');

    if (verifyBtn) {
        verifyBtn.addEventListener('click', () => {
            const email = emailInput.value.trim();
            let name = 'Foodie';
            if (userNameInput && userNameInput.value.trim()) {
                name = userNameInput.value.trim();
            } else if (isSignupMode) {
                name = userNameInput ? userNameInput.value.trim() : 'Foodie';
            }

            if (!email) {
                showToast('Please enter a valid email', 'error');
                return;
            }

            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending OTP...';

            fetch('api/auth.php?action=send_otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, name: name })
            })
            .then(response => response.json())
            .then(data => {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = 'Send OTP';

                if (data.success) {
                    initialStep.style.display = 'none';
                    otpStep.style.display = 'block';
                    showToast(`OTP sent to ${email}`, 'success');
                    
                    if (data.dev_otp) {
                        showToast(`[DEV MODE] OTP: ${data.dev_otp}`, 'info');
                    }
                } else {
                    showToast(data.message || 'Failed to send OTP', 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = 'Send OTP';
                showToast('Server error', 'error');
            });
        });
    }

    if (backToEmail) {
        backToEmail.addEventListener('click', (e) => {
            e.preventDefault();
            otpStep.style.display = 'none';
            initialStep.style.display = 'block';
        });
    }

    if (authForm) {
        authForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (initialStep.style.display !== 'none') {
                verifyBtn.click();
                return;
            }

            const enteredOTP = otpInput.value.trim();
            const email = emailInput.value.trim();
            const submitBtn = authForm.querySelector('button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Verifying...';

            fetch('api/auth.php?action=verify_otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, otp: enteredOTP })
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Verify & Continue';

                if (data.success) {
                    localStorage.setItem('currentUser', JSON.stringify(data.user));
                    showToast(`Welcome, ${data.user.name}!`, 'success');
                    if (authModal) authModal.classList.remove('active');
                    checkAuth();
                    
                    // Redirect to dashboard if on index page
                    const path = window.location.pathname;
                    if (path.endsWith('index.html') || path.endsWith('/') || path.endsWith('zomato/')) {
                        window.location.href = 'dashboard.html';
                    }
                } else {
                    showToast(data.message || 'Invalid OTP', 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Verify & Continue';
                showToast('Server error', 'error');
            });
        });
    }

    // -------------------------------------------------------------
    // Dashboard Data Fetching
    // -------------------------------------------------------------
    const restaurantGrid = document.getElementById('restaurantGrid');
    const skeletonGrid = document.getElementById('skeletonGrid');
    const filterPills = document.querySelectorAll('.category-filter');

    function loadRestaurants(category = 'all') {
        if (!restaurantGrid || !skeletonGrid) return;
        
        restaurantGrid.style.display = 'none';
        skeletonGrid.style.display = 'grid';
        
        // Match Zomato styling
        const url = category === 'all' 
            ? 'api/restaurants.php?action=all' 
            : `api/restaurants.php?action=category&category=${encodeURIComponent(category)}`;
            
        fetch(url)
            .then(res => res.json())
            .then(data => {
                skeletonGrid.style.display = 'none';
                restaurantGrid.style.display = 'grid';
                
                if (data.success && data.data.length > 0) {
                    restaurantGrid.innerHTML = data.data.map(res => `
                        <div class="res-card" onclick="window.location.href='restaurant.html?id=${res.id}'">
                            <div class="res-image-container">
                                <img src="${res.image_url}" alt="${res.name}">
                                ${res.rating >= 4.5 ? '<span class="res-promoted">Promoted</span>' : ''}
                                <span class="res-discount">50% OFF up to 100</span>
                                <span class="res-time">${res.delivery_time}</span>
                            </div>
                            <div class="res-info-row">
                                <h4 class="res-name">${res.name}</h4>
                                <span class="res-rating">${res.rating} <i class="fas fa-star" style="font-size:0.7rem"></i></span>
                            </div>
                            <div class="res-meta-row">
                                <span class="res-cuisines">${res.category}</span>
                                <span>₹200 for one</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    restaurantGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 3rem; color:var(--text-muted)">No restaurants found for this filter.</div>';
                }
            })
            .catch(err => {
                console.error('API Error:', err);
                skeletonGrid.style.display = 'none';
                restaurantGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 3rem; color:var(--primary-red)">Failed to load data. Ensure MySQL is running and setup_database.php was executed.</div>';
                restaurantGrid.style.display = 'block';
            });
    }

    if (restaurantGrid) {
        loadRestaurants('all');

        filterPills.forEach(pill => {
            pill.addEventListener('click', () => {
                filterPills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                const cat = pill.getAttribute('data-category');
                const dbCat = cat === 'all' ? 'all' : cat.charAt(0).toUpperCase() + cat.slice(1);
                loadRestaurants(dbCat);
            });
        });
    }

    // -------------------------------------------------------------
    // Toast Notification System
    // -------------------------------------------------------------
    function showToast(message, type = 'success') {
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
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(100%)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
