document.addEventListener('DOMContentLoaded', function() {
    // Password complexity validation
    function validatePassword(password) {
        return password.length >= 8 && 
               /[A-Z]/.test(password) && 
               /[a-z]/.test(password) && 
               /[0-9]/.test(password) && 
               /[^A-Za-z0-9]/.test(password);
    }

    // Email validation
    function validateEmail(email) {
        const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return re.test(String(email).toLowerCase());
    }

    // Signup form validation
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const email = document.getElementById('email').value;
            const passwordConfirm = document.getElementById('confirm-password').value;

            let isValid = true;
            const errorMessages = [];

            if (!validateEmail(email)) {
                errorMessages.push('Invalid email format');
                isValid = false;
            }

            if (!validatePassword(password)) {
                errorMessages.push('Password must be at least 8 characters long, contain uppercase, lowercase, number, and special character');
                isValid = false;
            }

            if (password !== passwordConfirm) {
                errorMessages.push('Passwords do not match');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                const errorDiv = document.getElementById('error-messages');
                errorDiv.innerHTML = errorMessages.map(msg => `<div class="alert alert-danger">${msg}</div>`).join('');
            }
        });
    }
});