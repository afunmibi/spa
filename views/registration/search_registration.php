<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollee Search and Management (PHP/MySQLi)</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar for table for better aesthetics */
        #policyTableBody::-webkit-scrollbar {
            width: 8px;
        }
        #policyTableBody::-webkit-scrollbar-thumb {
            background-color: #9ca3af;
            border-radius: 10px;
        }
        #policyTableBody::-webkit-scrollbar-track {
            background: #f3f4f6;
        }
        /* Custom font */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }
        /* Style for the loading/error message box */
        .message-box {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>
<body class="min-h-screen p-4 sm:p-8">

    <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow-2xl border border-gray-100">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Enrollee Search Dashboard</h1>
        <p class="text-sm text-gray-500 mb-6">Searchable by Registration ID, Enrollee Name, Phone, and DOB.</p>

        <!-- Search Input -->
        <div class="mb-6">
            <input type="text" id="searchQuery" oninput="filterEnrollees()" placeholder="Search by Registration ID, Name, Phone No, or Date of Birth..."
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-inner focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-800 transition duration-150 ease-in-out">
        </div>

        <!-- Utility Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 space-y-3 sm:space-y-0">
            <div id="authStatus" class="text-xs text-gray-600 font-medium">Using PHP/MySQL Backend</div>
            <button id="addEnrolleeBtn" class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-indigo-700 transition duration-150 ease-in-out text-sm font-semibold disabled:opacity-50" onclick="showModal('add')">
                + Register New Enrollee
            </button>
        </div>

        <!-- Enrollee Data Table -->
        <div class="overflow-x-auto rounded-lg shadow-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollee Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Phone No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Date of Birth</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="policyTableBody" class="bg-white divide-y divide-gray-200 max-h-96 overflow-y-auto block">
                    <!-- Enrollee rows will be injected here by JavaScript -->
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500 italic">Fetching enrollee data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Error/Success Message Box -->
        <div id="messageBox" class="message-box fixed bottom-4 right-4 p-4 rounded-lg shadow-lg text-white font-semibold hidden opacity-0 z-50"></div>
    </div>

    <!-- Enrollee Add/Edit Modal -->
    <div id="enrolleeModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 hidden z-40">
        <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-2xl transform transition-all duration-300">
            <h2 id="modalTitle" class="text-2xl font-bold mb-4 text-gray-900">Add New Enrollee (Principal)</h2>
            <form id="enrolleeForm">
                <input type="hidden" id="enrolleeId">
                
                <div class="mb-4">
                    <label for="name_of_enrollee" class="block text-sm font-medium text-gray-700">Enrollee Name (Searchable)</label>
                    <input type="text" id="name_of_enrollee" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="mb-4">
                    <label for="phone_no" class="block text-sm font-medium text-gray-700">Phone No (Searchable)</label>
                    <input type="tel" id="phone_no" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="mb-6">
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth (Searchable)</label>
                    <input type="date" id="date_of_birth" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <p class="text-xs text-gray-500 mb-4">Note: Editing other fields (like Organization or Dependants) requires a dedicated form, only key search fields are editable here.</p>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-150">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md transition duration-150">Save Enrollee</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 hidden z-40">
        <div class="bg-white rounded-xl w-full max-w-sm p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4 text-red-600">Confirm Deletion</h2>
            <p id="confirmMessage" class="mb-6 text-gray-700">Are you sure you want to delete this enrollee?</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideConfirmModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-150">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-md transition duration-150">Delete</button>
            </div>
        </div>
    </div>

    <!-- Application Logic (PHP/MySQLi Ready) -->
    <script>
        let enrollees = []; // Array to hold all enrollee data from the backend

        // DOM elements
        const enrolleeForm = document.getElementById('enrolleeForm');
        const enrolleeModal = document.getElementById('enrolleeModal');
        const policyTableBody = document.getElementById('policyTableBody');
        const searchInput = document.getElementById('searchQuery');
        const confirmModal = document.getElementById('confirmModal');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const modalTitle = document.getElementById('modalTitle');

        // --- API Endpoints (You must create these PHP files) ---
        const API_BASE = '/api/';
        const ENDPOINTS = {
            FETCH_ALL: API_BASE + 'enrollees.php', // GET: Returns array of all enrollees
            ADD: API_BASE + 'add_enrollee.php',     // POST: Data in JSON format
            UPDATE: API_BASE + 'update_enrollee.php', // POST: Data in JSON format (including ID)
            DELETE: API_BASE + 'delete_enrollee.php' // POST: { id: "..." }
        };

        // --- Utility Functions ---

        /**
         * Shows a temporary message box for success or error feedback.
         */
        function showMessage(message, isError) {
            const box = document.getElementById('messageBox');
            box.textContent = message;
            box.className = 'message-box fixed bottom-4 right-4 p-4 rounded-lg shadow-lg text-white font-semibold opacity-0 z-50';
            box.classList.remove('hidden');

            if (isError) {
                box.classList.add('bg-red-600');
            } else {
                box.classList.add('bg-green-600');
            }

            setTimeout(() => {
                box.classList.add('opacity-100');
            }, 10);

            setTimeout(() => {
                box.classList.remove('opacity-100');
                setTimeout(() => {
                    box.classList.add('hidden');
                }, 300);
            }, 3000);
        }

        /**
         * Format date string YYYY-MM-DD to DD-MM-YYYY
         */
        function formatDate(dateString) {
            if (!dateString) return '';
            try {
                const parts = dateString.split('-');
                if (parts.length === 3) {
                    return `${parts[2]}-${parts[1]}-${parts[0]}`;
                }
                return dateString;
            } catch (e) {
                return dateString;
            }
        }

        /**
         * Opens the Add/Edit Enrollee modal.
         */
        window.showModal = (mode, enrolleeData = {}) => {
            enrolleeForm.reset();
            document.getElementById('enrolleeId').value = '';

            // Map fields to DOM IDs
            const nameInput = document.getElementById('name_of_enrollee');
            const phoneInput = document.getElementById('phone_no');
            const dobInput = document.getElementById('date_of_birth');

            if (mode === 'edit' && enrolleeData.id) {
                modalTitle.textContent = `Edit Enrollee: ID ${enrolleeData.id}`;
                document.getElementById('enrolleeId').value = enrolleeData.id;
                nameInput.value = enrolleeData.name_of_enrollee || '';
                phoneInput.value = enrolleeData.phone_no || '';
                dobInput.value = enrolleeData.date_of_birth || ''; 
            } else {
                modalTitle.textContent = 'Register New Enrollee';
            }
            enrolleeModal.classList.remove('hidden');
        };

        window.hideModal = () => {
            enrolleeModal.classList.add('hidden');
        };

        window.showConfirmModal = (id) => {
            document.getElementById('confirmMessage').textContent = `Are you sure you want to delete enrollee ID: ${id}?`;
            confirmDeleteBtn.onclick = () => deleteEnrollee(id);
            confirmModal.classList.remove('hidden');
        };

        window.hideConfirmModal = () => {
            confirmModal.classList.add('hidden');
        };


        // --- Backend Communication Functions (PHP/MySQLi) ---

        /**
         * Fetches all enrollee data from the PHP backend.
         */
        async function fetchEnrolleesFromBackend() {
            policyTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-indigo-500 italic">Fetching enrollee data...</td></tr>`;
            try {
                const response = await fetch(ENDPOINTS.FETCH_ALL);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Expects an array of objects from the PHP script
                const data = await response.json(); 
                
                // Assuming your PHP returns fields: id (Registration ID), name_of_enrollee, phone_no, date_of_birth
                enrollees = data.map(item => ({
                    id: String(item.id), // Ensure ID is treated as a string for searching
                    name_of_enrollee: item.name_of_enrollee,
                    phone_no: item.phone_no,
                    date_of_birth: item.date_of_birth,
                }));

                // Initial render
                filterEnrollees(); 
                
            } catch (error) {
                console.error("Error fetching enrollees: ", error);
                showMessage(`Failed to load data from ${ENDPOINTS.FETCH_ALL}. Check PHP script and network.`, true);
                policyTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-red-500">Error: Could not load data.</td></tr>`;
            }
        }

        /**
         * Sends data to the PHP backend for adding or updating an enrollee.
         */
        enrolleeForm.onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById('enrolleeId').value;
            const isEditing = !!id;
            const endpoint = isEditing ? ENDPOINTS.UPDATE : ENDPOINTS.ADD;

            const enrolleeData = {
                // PHP/MySQL uses an auto-incrementing integer ID, but we include it for updates
                ...(isEditing && { id: id }), 
                name_of_enrollee: document.getElementById('name_of_enrollee').value.trim(),
                phone_no: document.getElementById('phone_no').value.trim(),
                date_of_birth: document.getElementById('date_of_birth').value,
            };

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(enrolleeData)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Assuming PHP script returns success confirmation
                const result = await response.json(); 

                if (result.success) {
                    showMessage(`Enrollee ${isEditing ? 'updated' : 'added'} successfully!`, false);
                    hideModal();
                    // Re-fetch data to update the table
                    await fetchEnrolleesFromBackend(); 
                } else {
                    showMessage(`Operation failed: ${result.message || 'Unknown error'}`, true);
                }

            } catch (error) {
                console.error("Error saving enrollee: ", error);
                showMessage(`Failed to save enrollee: ${error.message}`, true);
            }
        };

        /**
         * Sends a delete request to the PHP backend.
         */
        async function deleteEnrollee(id) {
            hideConfirmModal();
            try {
                const response = await fetch(ENDPOINTS.DELETE, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    showMessage("Enrollee deleted successfully!", false);
                    await fetchEnrolleesFromBackend(); // Re-fetch data
                } else {
                    showMessage(`Deletion failed: ${result.message || 'Unknown error'}`, true);
                }
            } catch (error) {
                console.error("Error deleting enrollee: ", error);
                showMessage(`Failed to delete enrollee: ${error.message}`, true);
            }
        }


        // --- Client-Side Rendering and Search ---

        /**
         * Renders the enrollee list based on the currently filtered data.
         */
        function renderEnrollees(data) {
            policyTableBody.innerHTML = ''; 

            if (data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="5" class="text-center py-6 text-gray-500 italic">${enrollees.length === 0 ? 'No enrollees found in database.' : 'No matching enrollees found.'}</td>`;
                policyTableBody.appendChild(row);
                return;
            }

            data.forEach(enrollee => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition duration-100 ease-in-out';
                // JSON.stringify needs to be escaped for the inline onclick handler
                const enrolleeJson = JSON.stringify(enrollee).replace(/"/g, '&quot;'); 
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${enrollee.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${enrollee.name_of_enrollee}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 hidden sm:table-cell">${enrollee.phone_no}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 hidden md:table-cell">${formatDate(enrollee.date_of_birth)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="showModal('edit', ${enrolleeJson})" 
                            class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                        <button onclick="showConfirmModal('${enrollee.id}')" 
                            class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                `;
                policyTableBody.appendChild(row);
            });
        }

        /**
         * Client-side search and filtering logic.
         */
        window.filterEnrollees = () => {
            const query = searchInput.value.toLowerCase().trim();
            if (!query) {
                renderEnrollees(enrollees);
                return;
            }

            const filtered = enrollees.filter(enrollee => {
                // Search fields: id, name_of_enrollee, phone_no, date_of_birth
                return (
                    String(enrollee.id).toLowerCase().includes(query) ||
                    enrollee.name_of_enrollee.toLowerCase().includes(query) ||
                    enrollee.phone_no.toLowerCase().includes(query) ||
                    // Search in both stored format (YYYY-MM-DD) and display format (DD-MM-YYYY)
                    enrollee.date_of_birth.toLowerCase().includes(query) ||
                    formatDate(enrollee.date_of_birth).toLowerCase().includes(query)
                );
            });

            renderEnrollees(filtered);
        };


        // --- Initialization ---

        // Start the application by fetching data from the backend
        window.onload = fetchEnrolleesFromBackend;

    </script>
</body>
</html>