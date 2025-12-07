<?php
// === 1. PHP DATABASE LOGIC SIMULATION ===

require_once dirname(__DIR__, 2) . '/db.php';
require_once dirname(__DIR__, 2) . '/csrf.php';

// Require staff login to access registration form
if (empty($_SESSION['staff_id']) && empty($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$hmo_code = '051';
$hmo_name = 'NONSUCH';
$rand = rand(1000, 9999);
$current_year = date('Y');
$current_month = date('m');

// plan_type should come from the registration flow (GET/POST). Default to 'INDV' if not provided.
$plan_type = $_GET['plan_type'] ?? $_POST['plan_type'] ?? 'INDV';

// --- Fetch last numeric id for this plan type from the DB (safe default 0) ---
$last_id_from_db = 0;
if (isset($conn) && $conn instanceof mysqli) {
    $sql = "SELECT MAX(id) AS max_id FROM enrolment WHERE plan_type = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $plan_type);
        $stmt->execute();
        $stmt->bind_result($max_id);
        $stmt->fetch();
        $last_id_from_db = $max_id ? (int)$max_id : 0;
        $stmt->close();
    }
}

$new_id = $last_id_from_db + 1; // Increment the ID for the current enrolment
// -----------------------

// Generated Policy Number: 051/NONSUCH/XXXX/YYYY/MM/INDV/12346
$policy_no = $hmo_code . '/' . strtoupper($hmo_name) . '/' . $rand . '/' . $current_year . '/' . $current_month . '/' . $plan_type . '/' . $new_id;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Plan Registration Wizard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php if (file_exists(dirname(__DIR__,2) . '/csrf.php')) { echo csrf_meta_tag(); } ?>
    <script src="/spa/assets/js/csrf_fetch.js"></script>
    <style>
        /* Custom Styles for Wizard (Omitted for brevity, assumed to be same as before) */
        .wizard-card {
            background-color: #ffffff;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .form-label {
            @apply block text-sm font-medium text-gray-700 mb-1;
        }
        .form-input, .form-select, .form-textarea {
            @apply w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200;
        }
        .form-select {
            @apply appearance-none bg-white bg-no-repeat bg-[right_0.75rem_center] bg-[length:1.5rem_1.5rem] pr-10; /* Adds custom arrow for select */
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%236366f1'%3e%3cpath d='M7 7l3-3 3 3m0 6l-3 3-3-3' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3e%3c/svg%3e");
        }
        .step-indicator {
            @apply w-10 h-10 flex items-center justify-center rounded-full font-extrabold text-white transition-all duration-300;
        }
        .step-indicator.active {
            @apply bg-indigo-600 ring-4 ring-indigo-300;
        }
        .step-indicator.inactive {
            @apply bg-gray-300 text-gray-600;
        }
        .progress-line {
            @apply flex-1 h-1 bg-gray-200 mx-4 rounded-full overflow-hidden;
        }
        .progress-fill {
            @apply h-full bg-indigo-500 transition-all duration-500 ease-in-out;
        }
        .file-upload-box {
            @apply mt-2 border-2 border-dashed border-indigo-300 rounded-xl p-8 text-center cursor-pointer hover:border-indigo-500 transition-colors duration-200;
        }
        .file-upload-box.has-file {
            @apply border-solid border-green-500 bg-green-50;
        }
        .file-upload-box:hover .upload-icon {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen py-12 px-4">
    <div class="container mx-auto max-w-5xl">
        <div class="wizard-card p-8 lg:p-12 rounded-3xl max-w-4xl mx-auto">
            
            <header class="text-center mb-16">
                <div class="inline-flex items-center space-x-3 text-indigo-600 mb-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944c2.812.392 5.485 1.747 7.564 3.72M16 19.414V17a2 2 0 00-2-2H4a2 2 0 00-2 2v2.414l3.125-3.125A2 2 0 017 15.172v-1.14c0-.79-.5-1.53-.98-2.22l-2.074-3.085A1.002 1.002 0 014.2 7.74l1.83-2.317c.507-.64.87-1.42.92-2.26.04-.68.4-1.3.93-1.74l.9-.76a1.002 1.002 0 011.39.29l.9.8c.47.42.75.98.8 1.57.05.58.4 1.13.9 1.48l1.37 1.02a2 2 0 01.8.84V17a2 2 0 002 2z"></path></svg>
                    <span class="text-xl font-extrabold tracking-widest uppercase">HMO ENROLL</span>
                </div>
                <h1 class="text-5xl font-extrabold text-gray-900 mb-3 leading-tight">
                    Health Plan Registration Wizard
                </h1>
                <p class="text-lg text-gray-500 max-w-2xl mx-auto leading-relaxed">
                    Securely enroll the principal member and any dependants in just 3 quick steps.
                </p>
            </header>

            <div class="mb-16">
                <div class="flex items-center justify-between max-w-xl mx-auto mb-8">
                    <div class="flex flex-col items-center space-y-2">
                        <div id="step-1-indicator" class="step-indicator active">1</div>
                        <span class="text-sm font-semibold text-gray-700">Principal</span>
                    </div>
                    <div class="progress-line">
                        <div id="progress-1" class="progress-fill" style="width: 0%"></div>
                    </div>
                    <div class="flex flex-col items-center space-y-2">
                        <div id="step-2-indicator" class="step-indicator inactive">2</div>
                        <span class="text-sm font-semibold text-gray-700">Dependants</span>
                    </div>
                    <div class="progress-line">
                        <div id="progress-2" class="progress-fill" style="width: 0%"></div>
                    </div>
                    <div class="flex flex-col items-center space-y-2">
                        <div id="step-3-indicator" class="step-indicator inactive">3</div>
                        <span class="text-sm font-semibold text-gray-700">Documents</span>
                    </div>
                </div>
            </div>

            <form id="registration-form" class="space-y-12" enctype="multipart/form-data" action="submit_enrolment.php" method="POST">
                
                <div id="step-1" class="form-step">
                    <div class="form-section">
                        <h2 class="text-3xl font-extrabold text-indigo-700 mb-8 pb-4 border-b border-indigo-100">
                            1. Principal Enrollee Details
                        </h2>

                        <input type="hidden" name="policy_no" value="<?php echo htmlspecialchars($policy_no); ?>">
                        <input type="hidden" name="plan_type" value="<?php echo htmlspecialchars($plan_type); ?>">
                        <?php if (isset($_SESSION['staff_id'])): ?>
                            <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($_SESSION['staff_id']); ?>">
                        <?php endif; ?>
                        <?php echo csrf_input(); ?>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                            <div>
                                <label class="form-label">Organization Name</label>
                                <input type="text" name="organization_name" required class="form-input" placeholder="e.g., Acme Corporation">
                            </div>
                            <div>
                                <label class="form-label">Principal Enrollee Name</label>
                                <input type="text" name="principal_name" required class="form-input" placeholder="Full Legal Name">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                            <div>
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" required class="form-input" placeholder="+234 800 123 4567">
                            </div>
                            <div>
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" required class="form-input" placeholder="name@company.com">
                            </div>
                            <div>
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" required class="form-input">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                            <div>
                                <label class="form-label">Residential Address</label>
                                <textarea name="address" rows="3" required class="form-textarea" placeholder="Full street address, city, state"></textarea>
                            </div>
                            <div>
                                <label class="form-label">Location/Region</label>
                                <input type="text" name="location" required class="form-input" placeholder="e.g., Abuja FCT, Lagos Island">
                                <label class="form-label mt-4">Number of Dependants</label>
                                <input type="number" id="dependants-count" name="dependants_count" min="0" max="10" value="0" class="form-input" onchange="updateFamilyForms()">
                                <p class="text-xs text-gray-500 mt-2">Family members details in next step</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-8 border-t border-indigo-200">
                            <div>
                                <label class="form-label">Plan Type</label>
                                <select name="plan_type" required class="form-select">
                                    <option value="BASIC" selected>BASIC</option>
                                    <option value="GOLD">GOLD</option>
                                    <option value="PLATINUM">PLATINUM</option>
                                    <option value="SILVER">SILVER</option>
                                    <option value="GROUP">Group (GROUP)</option>
                                    <option value="CUSTOMIZED">CUSTOMIZED</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Search Healthcare Provider (HCP)</label>
                                <input type="text" id="hcp-search" oninput="filterHcpOptions()" class="form-input" placeholder="Type to search e.g., 'City'...">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 pt-4">
                            <div>
                                <label class="form-label">Select Primary HCP</label>
                                <select id="hcp" name="hcp" required class="form-select">
                                    <option value="" disabled selected>Select Provider</option>
                                    <option value="city-general">City General Hospital</option>
                                    <option value="wellness-clinic">Wellness Clinic & Family Care</option>
                                    <option value="st-judes">St. Jude's Medical Center</option>
                                    <option value="rural-post">Rural Health Post</option>
                                    <option value="capital-trauma">Capital City Trauma & Surgery</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-between mt-12">
                        <button type="button" onclick="resetForm()" class="flex-1 py-4 px-8 bg-gray-100 text-gray-700 font-bold rounded-xl shadow-md hover:bg-gray-200 transition-all duration-200">
                            <span class="inline-flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Reset Form
                            </span>
                        </button>
                        <button type="button" onclick="nextStep()" class="flex-1 py-4 px-8 bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-extrabold text-lg rounded-xl shadow-xl hover:shadow-2xl hover:scale-[1.01] transition-all duration-200">
                            <span class="inline-flex items-center">
                                Continue to Step 2/3 
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                        </button>
                    </div>
                    <a href="admin_dashboard.php" class="p-4">Go Back</a>
                </div>

                <div id="step-2" class="form-step hidden">
                    <div class="form-section">
                        <h2 class="text-3xl font-extrabold text-indigo-700 mb-8 pb-4 border-b border-indigo-100">
                            2. Dependants Information
                        </h2>
                        <p id="dependant-count-summary" class="text-lg font-semibold text-gray-600 mb-6">No dependants selected.</p>
                        <div id="family-members-container" class="space-y-8">
                            </div>
                        <div id="no-dependants-message" class="text-center p-10 hidden">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M12 20V16m-4-6h8m-4-6h.01M12 2a10 10 0 100 20 10 10 0 000-20z"></path></svg>
                            <h3 class="mt-2 text-xl font-medium text-gray-900">No Dependants</h3>
                            <p class="mt-1 text-base text-gray-500">You indicated zero dependants in Step 1. Click 'Next' to proceed to the final step.</p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 justify-between mt-12">
                        <button type="button" onclick="prevStep()" class="flex-1 py-4 px-8 bg-gray-100 text-gray-700 font-bold rounded-xl shadow-md hover:bg-gray-200 transition-all duration-200">
                            <span class="inline-flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Back to Step 1
                            </span>
                        </button>
                        <button type="button" id="next-step3" onclick="nextStep()" class="flex-1 py-4 px-8 bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-extrabold text-lg rounded-xl shadow-xl hover:shadow-2xl hover:scale-[1.01] transition-all duration-200">
                            <span class="inline-flex items-center">
                                Next: Documents 
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                        </button>
                    </div>
                </div>

                <div id="step-3" class="form-step hidden">
                    <div class="form-section">
                        <h2 class="text-3xl font-extrabold text-indigo-700 mb-8 pb-4 border-b border-indigo-100">
                            3. Document Upload
                        </h2>
                        <p class="text-gray-500 mb-8">Please upload the required documents for verification. All files must be clear and legible.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                            <div>
                                <label class="form-label block text-center text-lg font-bold">Principal Passport Photo</label>
                                <div class="file-upload-box" id="principal-photo-box">
                                    <svg class="upload-icon mx-auto h-16 w-16 text-indigo-500 mb-4 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                    <div class="space-y-2">
                                        <label for="principal-photo" class="block w-full text-center py-3 px-6 bg-white rounded-lg font-bold text-indigo-600 hover:bg-indigo-50 cursor-pointer transition-colors mx-auto max-w-sm shadow-md">
                                            📸 Choose File
                                            <input id="principal-photo" name="principal_photo" type="file" accept="image/*" required class="sr-only">
                                        </label>
                                        <p class="text-sm text-gray-500" id="principal-photo-status">PNG, JPG, HEIC accepted. Max 2MB.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="form-label block text-center text-lg font-bold">Principal Identity Document</label>
                                <div class="file-upload-box" id="principal-id-box">
                                    <svg class="upload-icon mx-auto h-16 w-16 text-indigo-500 mb-4 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                                        <path d="M17 7V5c0-1.1-.9-2-2-2H9c-1.1 0-2 .9-2 2v2"></path>
                                        <line x1="12" y1="12" x2="12" y2="15"></line>
                                        <line x1="12" y1="18" x2="12" y2="18"></line>
                                    </svg>
                                    <div class="space-y-2">
                                        <label for="principal-id" class="block w-full text-center py-3 px-6 bg-white rounded-lg font-bold text-indigo-600 hover:bg-indigo-50 cursor-pointer transition-colors mx-auto max-w-sm shadow-md">
                                            📄 Choose File
                                            <input id="principal-id" name="principal_id" type="file" accept=".pdf,image/*" required class="sr-only">
                                        </label>
                                        <p class="text-sm text-gray-500" id="principal-id-status">Passport, National ID, or Driver's License.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 justify-between mt-4 ">
                        <button type="button" onclick="prevStep()" class="flex-1 py-4 px-8 bg-gray-100 text-gray-700 font-bold rounded-xl shadow-md hover:bg-gray-200 transition-all duration-200">
                            <span class="inline-flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Back to Step 2
                            </span>
                        </button>
                        <button type="submit" class="flex-1 py-5 px-8 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-black text-xl rounded-xl shadow-2xl shadow-green-400/50 hover:shadow-3xl hover:scale-[1.01] transition-all duration-200">
                            <span class="inline-flex items-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Complete Registration
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ... (All JS functions: updateStepDisplay, updateProgress, updateFamilyForms, validateStep, nextStep, prevStep, filterHcpOptions, file upload handlers, resetForm)
        
        let currentStep = 1;
        const totalSteps = 3;
        const form = document.getElementById('registration-form');

        function updateStepDisplay() {
            for (let i = 1; i <= totalSteps; i++) {
                const stepElement = document.getElementById(`step-${i}`);
                const indicator = document.getElementById(`step-${i}-indicator`);
                if (i === currentStep) {
                    stepElement.classList.remove('hidden');
                    indicator.classList.add('active');
                    indicator.classList.remove('inactive');
                } else {
                    stepElement.classList.add('hidden');
                    indicator.classList.remove('active');
                }
            }
        }

        function updateProgress() {
            for (let i = 1; i < totalSteps; i++) {
                const progressFill = document.getElementById(`progress-${i}`);
                if (i < currentStep) {
                    progressFill.style.width = '100%';
                    document.getElementById(`step-${i}-indicator`).classList.remove('inactive');
                    document.getElementById(`step-${i}-indicator`).classList.add('active');
                } else {
                    progressFill.style.width = '0%';
                    document.getElementById(`step-${i}-indicator`).classList.remove('active');
                    document.getElementById(`step-${i}-indicator`).classList.add('inactive');
                }
            }
        }
        
        function updateFamilyForms() {
            const count = parseInt(document.getElementById('dependants-count').value);
            const container = document.getElementById('family-members-container');
            const summary = document.getElementById('dependant-count-summary');
            const noDependantsMessage = document.getElementById('no-dependants-message');
            container.innerHTML = ''; 

            if (count > 0) {
                summary.textContent = `You are registering details for ${count} dependant(s).`;
                noDependantsMessage.classList.add('hidden');

                for (let i = 1; i <= count; i++) {
                    const html = `
                        <div class="p-6 border border-gray-200 rounded-xl bg-white shadow-sm">
                            <h3 class="text-xl font-bold text-indigo-700 mb-6 pb-2 border-b">Dependant #${i}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Name</label>
                                    <input type="text" name="dependant_${i}_name" required class="form-input" placeholder="Full Name">
                                </div>
                                <div>
                                    <label class="form-label">Relationship to Principal</label>
                                    <select name="dependant_${i}_relationship" required class="form-select">
                                        <option value="" disabled selected>Select Relationship</option>
                                        <option value="spouse">Spouse</option>
                                        <option value="child">Child</option>
                                        <option value="parent">Parent</option>
                                        <option value="sibling">Sibling</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="dependant_${i}_dob" required class="form-input">
                                </div>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                }
            } else {
                summary.textContent = 'No dependants selected.';
                noDependantsMessage.classList.remove('hidden');
            }
        }

        function validateStep(step) {
            let isValid = true;
            const stepElement = document.getElementById(`step-${step}`);
            const requiredFields = stepElement.querySelectorAll('[required]');

            requiredFields.forEach(field => {
                if (!field.value) {
                    field.classList.add('border-red-500', 'ring-red-500');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500', 'ring-red-500');
                }
            });

            if (!isValid) {
                alert('Please fill out all required fields before continuing.');
            }
            return isValid;
        }

        function nextStep() {
            if (validateStep(currentStep) && currentStep < totalSteps) {
                currentStep++;
                updateStepDisplay();
                updateProgress();
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepDisplay();
                updateProgress();
            }
        }
        
        function filterHcpOptions() {
            const input = document.getElementById('hcp-search').value.toLowerCase();
            const select = document.getElementById('hcp');
            const options = select.getElementsByTagName('option');

            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                const text = option.text.toLowerCase();
                if (text.includes(input)) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const fileInputs = [
                { id: 'principal-photo', boxId: 'principal-photo-box', statusId: 'principal-photo-status' },
                { id: 'principal-id', boxId: 'principal-id-box', statusId: 'principal-id-status' }
            ];

            fileInputs.forEach(item => {
                const input = document.getElementById(item.id);
                const box = document.getElementById(item.boxId);
                const status = document.getElementById(item.statusId);

                input.addEventListener('change', (e) => {
                    if (e.target.files.length > 0) {
                        const fileName = e.target.files[0].name;
                        status.textContent = `✅ File selected: ${fileName}`;
                        box.classList.add('has-file');
                    } else {
                        status.textContent = status.getAttribute('data-default-text') || 'PNG, JPG, HEIC accepted. Max 2MB.';
                        box.classList.remove('has-file');
                    }
                });
                status.setAttribute('data-default-text', status.textContent);
            });
            updateFamilyForms();
            updateStepDisplay();
            updateProgress();
        });

        function resetForm() {
            form.reset();
            currentStep = 1;
            updateStepDisplay();
            updateProgress();
            updateFamilyForms();
            document.querySelectorAll('.file-upload-box').forEach(box => {
                box.classList.remove('has-file');
            });
            document.getElementById('principal-photo-status').textContent = 'PNG, JPG, HEIC accepted. Max 2MB.';
            document.getElementById('principal-id-status').textContent = 'Passport, National ID, or Driver\'s License.';
        }
        
        // Disable default HTML form submission behavior to allow for custom JS/AJAX processing if desired, 
        // but the final server-side logic is delegated to the action attribute.
        form.addEventListener('submit', function(e) {
            if (!validateStep(3)) {
                e.preventDefault(); 
            }
            // If validation passes, the form will submit to submit_enrolment.php
        });
    </script>
</body>
</html>