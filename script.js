
    document.addEventListener('DOMContentLoaded', () => {
    // Get the form and message area elements
    const form = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const messageArea = document.getElementById('message');

    // Function to display an error message
    const displayError = (message) => {
        messageArea.textContent = message;
        messageArea.classList.remove('hidden');
    };

    // Function to clear the error message
    const clearError = () => {
        messageArea.textContent = '';
        messageArea.classList.add('hidden');
    };

    // Add event listener for form submission
    form.addEventListener('submit', (event) => {
        clearError(); // Clear previous errors

        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();

        // 1. Check if fields are empty (basic required check)
        if (username === '' || password === '') {
            displayError('Please fill in both the username and password fields.');
            event.preventDefault(); // Stop form submission
            return;
        }

        // 2. Check minimum length (Example)
        if (username.length < 4) {
            displayError('Username must be at least 4 characters long.');
            event.preventDefault(); 
            return;
        }

        if (password.length < 4) {
            displayError('Password must be at least 6 characters long.');
            event.preventDefault(); 
            return;
        }
        
        
       form.submit(); // Submit the form if all checks pass
    });
});
    