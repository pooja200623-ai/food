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

    // OTP Box Navigation Logic
    if (otpBoxes.length > 0) {
        otpBoxes.forEach((box, index) => {
            box.addEventListener('input', (e) => {
                if (e.target.value.length === 1) {
                    if (index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                    }
                }
                updateOtpValue();
            });

            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '') {
                    if (index > 0) {
                        otpBoxes[index - 1].focus();
                    }
                }
            });
        });
    }

    function updateOtpValue() {
        if(otpValueInput) {
            otpValueInput.value = Array.from(otpBoxes).map(box => box.value).join('');
        }
    }

    // Send OTP
    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', () => {
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

            sendOtpBtn.disabled = true;
            sendOtpBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            fetch('api/auth.php?action=send_otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email })
            })
            .then(res => res.json())
            .then(data => {
                sendOtpBtn.disabled = false;
                sendOtpBtn.innerHTML = 'Send OTP <i class="fas fa-arrow-right"></i>';

                if (data.success) {
                    displayEmail.textContent = email;
                    step1.classList.remove('active');
                    step2.classList.add('active');
                    showToast('OTP sent successfully!', 'success');
                    
                    if (data.dev_otp) {
                        showToast(`[DEV] Your OTP is: ${data.dev_otp}`, 'info');
                    }
                    
                    // Focus first OTP box
                    setTimeout(() => otpBoxes[0].focus(), 100);
                } else {
                    showToast(data.message || 'Failed to send OTP', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                sendOtpBtn.disabled = false;
                sendOtpBtn.innerHTML = 'Send OTP <i class="fas fa-arrow-right"></i>';
                showToast('Server connection error.', 'error');
            });
        });
    }

    // Back button
    if (backBtn) {
        backBtn.addEventListener('click', (e) => {
            e.preventDefault();
            step2.classList.remove('active');
            step1.classList.add('active');
            otpBoxes.forEach(box => box.value = '');
            updateOtpValue();
        });
    }

    // Verify OTP
    if (authForm) {
        authForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Only trigger if we are on step 2
            if (step1.classList.contains('active')) {
                sendOtpBtn.click();
                return;
            }

            const email = emailInput.value.trim();
            const otp = otpValueInput.value.trim();

            if (otp.length !== 4) {
                showToast('Please enter a valid 4-digit OTP', 'error');
                return;
            }

            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

            fetch('api/auth.php?action=verify_otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, otp })
            })
            .then(res => res.json())
            .then(data => {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = 'Verify & Login <i class="fas fa-check-circle"></i>';

                if (data.success) {
                    // 1-Time Verification Rule: Save session to localStorage
                    localStorage.setItem('user_session', JSON.stringify({
                        name: data.user.name,
                        email: data.user.email
                    }));
                    
                    showToast('Login successful! Redirecting...', 'success');
                    
                    setTimeout(() => {
                        window.location.href = 'index.html';
                    }, 800);
                } else {
                    showToast(data.message || 'Invalid OTP', 'error');
                    // Clear OTP boxes
                    otpBoxes.forEach(box => box.value = '');
                    otpBoxes[0].focus();
                    updateOtpValue();
                }
            })
            .catch(err => {
                console.error(err);
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = 'Verify & Login <i class="fas fa-check-circle"></i>';
                showToast('Server connection error.', 'error');
            });
        });
    }

    // Dashboard Logout Logic
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem('user_session');
            window.location.href = 'login.html';
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
            window.location.replace('login.html');
        }
    }

    // Toast Function
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
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
