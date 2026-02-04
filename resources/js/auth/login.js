/**
 * Login Form Enhancement
 * Handles client-side validation and UI interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            // Simple Client-side validation before submission
            let isValid = true;
            
            if (!validateEmail(emailInput.value)) {
                isValid = false;
                highlightError(emailInput);
            } else {
                removeError(emailInput);
            }

            if (passwordInput.value.length < 1) {
                isValid = false;
                highlightError(passwordInput);
            } else {
                removeError(passwordInput);
            }

            if (!isValid) {
                e.preventDefault();
            } else {
                // Add loading state
                const submitBtn = loginForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerText;
                submitBtn.disabled = true;
                submitBtn.innerText = 'Verificando...';
                
                // Allow form submission to proceed
            }
        });
    }

    function validateEmail(email) {
        return String(email)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    }

    function highlightError(element) {
        element.classList.add('border-red-500');
        element.classList.add('ring-red-500');
        
        // Add shake animation
        element.parentElement.classList.add('animate-shake');
        setTimeout(() => {
            element.parentElement.classList.remove('animate-shake');
        }, 500);
    }

    function removeError(element) {
        element.classList.remove('border-red-500');
        element.classList.remove('ring-red-500');
    }
});
