document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const togglePasswordBtn = document.querySelector('.toggle-password');
    const passwordInput = document.getElementById('password');
    const toggleViewBtn = document.getElementById('toggleView');
    const loginHeader = document.querySelector('.login-header h1');
    const loginSubtext = document.querySelector('.login-header p');
    const submitBtnSpan = document.querySelector('.btn-primary span');
    
    let isLoginView = true;

    // Toggle Password Visibility
    togglePasswordBtn.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle icon
        const icon = togglePasswordBtn.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    // Toggle between Login and Sign Up (Visual only for now)
    toggleViewBtn.addEventListener('click', (e) => {
        e.preventDefault();
        isLoginView = !isLoginView;

        if (isLoginView) {
            loginHeader.textContent = 'Welcome Back!';
            loginSubtext.textContent = 'Taste the world, one dash at a time.';
            submitBtnSpan.textContent = 'Sign In';
            toggleViewBtn.textContent = 'Create an account';
            document.querySelector('.login-footer p').firstChild.textContent = 'New to CraveDash? ';
        } else {
            loginHeader.textContent = 'Join CraveDash';
            loginSubtext.textContent = 'Start your gourmet journey today.';
            submitBtnSpan.textContent = 'Sign Up';
            toggleViewBtn.textContent = 'Sign in here';
            document.querySelector('.login-footer p').firstChild.textContent = 'Already have an account? ';
        }

        // Add a small fade animation
        const card = document.querySelector('.login-card');
        card.style.animation = 'none';
        card.offsetHeight; // trigger reflow
        card.style.animation = 'fadeInScale 0.4s ease-out';
    });

    // Form Submission
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const submitBtn = loginForm.querySelector('.btn-primary');
        
        // Show loading state
        const originalContent = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';

        // Simulate API call
        setTimeout(() => {
            alert(`Welcome, ${email}! Redirection to dashboard...`);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalContent;
        }, 1500);
    });

    // Add subtle parallax effect on mouse move
    document.addEventListener('mousemove', (e) => {
        const amount = 20;
        const x = (e.clientX / window.innerWidth - 0.5) * amount;
        const y = (e.clientY / window.innerHeight - 0.5) * amount;
        
        const card = document.querySelector('.login-card');
        card.style.transform = `perspective(1000px) rotateY(${x}deg) rotateX(${-y}deg) translateY(${-y}px)`;
    });
});
