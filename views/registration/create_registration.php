<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Plan Enrollee Registration Wizard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .wizard-card {
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }
        .step-indicator {
            @apply w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-sm shadow-lg transition-all duration-300 relative;
        }
        .step-indicator.active {
            @apply bg-gradient-to-r from-indigo-500 to-purple-600 text-white scale-110 shadow-2xl;
        }
        .step-indicator.complete {
            @apply bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-2xl;
        }
        .step-indicator.complete::after {
            content: '✔';
            @apply absolute -top-1 -right-1 bg-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold text-green-600 shadow-md;
        }
        .step-indicator.inactive {
            @apply bg-gray-100 text-gray-400 border-2 border-gray-200;
        }
        .progress-line {
            @apply flex-grow h-2 bg-gray-200 mx-4 rounded-full relative overflow-hidden;
        }
        .progress-fill {
            @apply absolute top-0 left-0 h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full transition-all duration-500 ease-out;
        }
        .form-section {
            @apply bg-white/80 backdrop-blur-sm p-8 rounded-2xl border border-white/50 shadow-xl mb-8;
        }
        .form-input, .form-select, .form-textarea {
            @apply w-full px-4 py-3 border border-gray-200 rounded-xl shadow-sm focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 bg-white/50 backdrop-blur-sm;
        }
        .form-label {
            @apply block text-sm font-semibold text-gray-800 mb-2 tracking-wide;
        }
        .file-upload-box {
            @apply border-2 border-dashed border-indigo-200 rounded-2xl p-8 text-center cursor-pointer bg-gradient-to-br from-indigo-50 to-purple-50 hover:border-indigo-400 hover:shadow-xl transition-all duration-300 relative overflow-hidden;
        }
        .file-upload-box:hover .upload-icon {
            @apply scale-110 text-indigo-600;
        }
        .error-input {
            @apply border-red-400 focus:ring-red-500/30 focus:border-red-500;
        }
        .error-message {
            @apply text-xs text-red-500 mt-1 font-medium;
        }
        .dependant-card {
            @apply bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-indigo-100 rounded-3xl p-8 shadow-2xl hover:shadow-3xl transition-all duration-300;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen py-12 px-4">
    <div class="container mx-auto max-w-5xl">
        <div class="wizard-card p-8 lg:p-12 rounded-3xl max-w-4xl mx-auto">
            
            <!-- Header -->
            <header class="text-center mb-12">
                <h1 class="text-4xl lg:text-5xl font-black bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-4">
                    Health Plan Enrollment
                </h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    Complete these 3 simple steps to register the principal enrollee and dependents.
                </p>
            </header>

            <!-- Enhanced Progress Bar -->
            <div class="mb-16">
                <div class="flex items-center justify-between max-w-2xl mx-auto mb-8">
                    <div class="flex flex-col items-center space-y-2">
                        <div id="step-1-indicator" class="step-indicator active">1</div>
                        <span class="text-sm font-medium text-gray-700">Principal</span>
                    </div>
                    <div class="progress-line">
                        <div id="progress-1" class="progress-fill" style="width: 100%"></div>
                    </div>
                    <div class="flex flex-col items-center space-y-2">
                        <div id="step-2-indicator" class="step-indicator inactive">2</div>
                        <span class="text-sm font-medium text-gray-700">Dependants</span>
                    </div>
                    <div class="progress-line">
                        <div id="progress-2" class="progress-fill" style="width: 0%"></div>
                    </div>
                    <div class="flex flex-col items-center space-y-2">
                        <div id="step-3-indicator" class="step-indicator inactive">3</div>
                        <span class="text-sm font-medium text-gray-700">Documents</span>
                    </div>
                </div>
            </div>

            <form id="registration-form" class="space-y-8" enctype="multipart/form-data">
                
                <!-- STEP 1: Principal & Plan Information -->
                <div id="step-1" class="form-step">
                    <div class="form-section">
                        <h2 class="text-3xl font-black text-indigo-900 mb-8 text-center">Step 1 of 3</h2>
                        
                        <!-- Organization & Principal -->
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

                        <!-- Contact Information -->
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

                        <!-- Address -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                            <div>
                                <label class="form-label">Residential Address</label>
                                <textarea name="address" rows="3" required class="form-textarea" placeholder="Full street address, city, state"></textarea>
                            </div>
                            <div>
                                <label class="form-label">Location/Region</label>
                                <input type="text" name="location" required class="form-input" placeholder="e.g., Abuja FCT, Lagos Island">
                            </div>
                        </div>

                        <!-- HCP & Family Count -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-8 border-t-4 border-indigo-100">
                            <div>
                                <label class="form-label">Search Healthcare Provider</label>
                                <input type="text" id="hcp-search" oninput="filterHcpOptions()" class="form-input" placeholder="Type to search...">
                            </div>
                            <div>
                                <label class="form-label">Select HCP</label>
                                <select id="hcp" name="hcp" required class="form-select">
                                    <option value="" disabled selected>Select Provider</option>
                                    <option value="city-general">City General Hospital</option>
                                    <option value="wellness-clinic">Wellness Clinic</option>
                                    <option value="st-judes">St. Jude's Medical Center</option>
                                    <option value="rural-post">Rural Health Post</option>
                                    <option value="capital-trauma">Capital City Trauma</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Number of Dependants</label>
                                <input type="number" id="dependants-count" name="dependants_count" min="0" max="10" value="0" class="form-input" onchange="updateFamilyForms()">
                                <p class="text-xs text-gray-500 mt-2">Family members registered in next step</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-between">
                        <button type="button" onclick="resetForm()" class="flex-1 py-4 px-8 bg-gradient-to-r from-red-400 to-red-500 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-200">
                            🔄 Reset Form
                        </button>
                        <button type="button" onclick="nextStep()" class="flex-1 py-4 px-8 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-200">
                            Next Step →
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Dependants -->
                <div id="step-2" class="form-step hidden">
                    <div class="form-section">
                        <h2 class="text-3xl font-black text-indigo-900 mb-8 text-center">Step 2 of 3</h2>
                        <div id="family-members-container" class="space-y-6">
                            <!-- Dynamic forms generated here -->
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 justify-between">
                        <button type="button" onclick="prevStep()" class="flex-1 py-4 px-8 bg-gradient-to-r from-gray-400 to-gray-500 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-200">
                            ← Previous Step
                        </button>
                        <button type="button" id="next-step3" onclick="nextStep()" class="flex-1 py-4 px-8 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-200">
                            Next: Documents →
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Documents -->
                <div id="step-3" class="form-step hidden">
                    <div class="form-section">
                        <h2 class="text-3xl font-black text-indigo-900 mb-8 text-center">Step 3 of 3</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                            <div>
                                <label class="form-label mb-4 block text-center text-lg font-bold">Principal Passport Photo</label>
                                <div class="file-upload-box">
                                    <svg class="upload-icon mx-auto h-16 w-16 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M28 28l4 4m-4-4l-4 4m-4-4l4 4m-4-4l-4 4m-4-4l4 4m-4-4l-4 4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div class="space-y-2">
                                        <label for="principal-photo" class="block w-full text-center py-3 px-6 bg-white rounded-xl font-bold text-indigo-600 hover:bg-indigo-50 cursor-pointer transition-colors mx-auto max-w-sm shadow-lg">
                                            📸 Click to Upload Photo
                                            <input id="principal-photo" name="principal_photo" type="file" accept="image/*" required class="sr-only">
                                        </label>
                                        <p class="text-xs text-gray-500">PNG, JPG • Max 2MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 justify-between">
                        <button type="button" onclick="prevStep()" class="flex-1 py-4 px-8 bg-gradient-to-r from-gray-400 to-gray-500 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-200">
                            ← Previous Step
                        </button>
                        <button type="submit" class="flex-1 py-5 px-8 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-black text-lg rounded-2xl shadow-2xl hover:shadow-3xl hover:scale-[1.02] transition-all duration-200">
                            ✅ Complete Registration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Core wizard state
        const steps = ['step-1', 'step-2', 'step-3'].map(id => document.getElementById(id));
        const indicators = [0,1,2].map(i => document.getElementById(`step-${i+1}-indicator`));
        const progressBars = [0,1].map(i => document.getElementById(`progress-${i+1}`));
        const form = document.getElementById('registration-form');
        const familyContainer = document.getElementById('family-members-container');
        const dependantsCount = document.getElementById('dependants-count');
        const hcpSelect = document.getElementById('hcp');
        let currentStepIndex = 0;
        let dependantData = [];

        // HCP options cache
        const hcpOptions = Array.from(hcpSelect.options);

        // Initialize wizard
        document.addEventListener('DOMContentLoaded', function() {
            updateProgress();
            window.addEventListener('scroll', () => window.scrollTo(0, 0));
            form.addEventListener('submit', handleSubmit);
            dependantsCount.addEventListener('input', updateFamilyForms);
        });

        function updateProgress() {
            indicators.forEach((indicator, i) => {
                indicator.className = i < currentStepIndex ? 'step-indicator complete' : 
                                   i === currentStepIndex ? 'step-indicator active' : 'step-indicator inactive';
            });
            
            progressBars.forEach((bar, i) => {
                bar.style.width = i < currentStepIndex ? '100%' : '0%';
            });
        }

        function showStep(index) {
            steps.forEach((step, i) => step.classList.toggle('hidden', i !== index));
            currentStepIndex = index;
            updateProgress();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        window.nextStep = () => {
            if (!validateCurrentStep()) return;
            
            const count = parseInt(dependantsCount.value);
            const nextIndex = currentStepIndex === 0 && count === 0 ? 2 : currentStepIndex + 1;
            
            if (nextIndex < steps.length) {
                showStep(nextIndex);
            }
        };

        window.prevStep = () => {
            const count = parseInt(dependantsCount.value);
            const prevIndex = currentStepIndex === 2 && count === 0 ? 0 : currentStepIndex - 1;
            if (prevIndex >= 0) showStep(prevIndex);
        };

        function validateCurrentStep() {
            const required = steps[currentStepIndex].querySelectorAll('[required]');
            let valid = true;
            
            required.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('error-input');
                    valid = false;
                } else {
                    input.classList.remove('error-input');
                }
            });
            return valid;
        }

        window.resetForm = () => {
            if (!confirm('Reset entire form? All data will be lost.')) return;
            form.reset();
            dependantsCount.value = 0;
            dependantData = [];
            updateFamilyForms();
            showStep(0);
        };

        // Dynamic family forms
        function generateDependantForm(index, data = {}) {
            return `
                <div class="dependant-card" data-index="${index}">
                    <div class="flex justify-between items-start mb-8 pb-4 border-b-2 border-indigo-200">
                        <h3 class="text-2xl font-black bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Dependant ${index + 1}
                        </h3>
                        <button type="button" onclick="deleteDependant(${index})" class="px-4 py-2 bg-red-100 text-red-700 font-bold rounded-xl hover:bg-red-200 transition-all duration-200">
                            🗑️ Remove
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="dependant[${index}][name]" required value="${data.name || ''}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Relationship *</label>
                            <select name="dependant[${index}][relationship]" required class="form-select">
                                <option value="">Select</option>
                                <option value="spouse" ${data.relationship === 'spouse' ? 'selected' : ''}>Spouse</option>
                                <option value="child" ${data.relationship === 'child' ? 'selected' : ''}>Child</option>
                                <option value="parent" ${data.relationship === 'parent' ? 'selected' : ''}>Parent</option>
                                <option value="other" ${data.relationship === 'other' ? 'selected' : ''}>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="form-label">Date of Birth *</label>
                            <input type="date" name="dependant[${index}][dob]" required value="${data.dob || ''}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Phone (Optional)</label>
                            <input type="tel" name="dependant[${index}][phone]" value="${data.phone || ''}" class="form-input">
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-8 border-t-2 border-indigo-100">
                        <label class="form-label">Passport Photo *</label>
                        <div class="file-upload-box mt-4">
                            <input type="file" name="dependant[${index}][photo]" accept="image/*" required class="sr-only">
                            <svg class="upload-icon mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" d="M12 5v14M5 12h14"/>
                            </svg>
                            <p class="text-indigo-600 font-bold text-lg mb-1">Click to Upload</p>
                            <p class="text-xs text-gray-500">PNG, JPG • Max 2MB</p>
                        </div>
                    </div>
                </div>
            `;
        }

        window.updateFamilyForms = () => {
            const count = Math.max(0, parseInt(dependantsCount.value));
            let html = '';
            
            for (let i = 0; i < count; i++) {
                html += generateDependantForm(i, dependantData[i] || {});
            }
            
            familyContainer.innerHTML = html;
            document.getElementById('next-step3').classList.toggle('hidden', count === 0);
        };

        window.deleteDependant = (index) => {
            dependantData.splice(index, 1);
            dependantsCount.value = dependantData.length;
            updateFamilyForms();
        };

        window.filterHcpOptions = () => {
            const search = document.getElementById('hcp-search').value.toLowerCase();
            hcpSelect.innerHTML = '<option value="" disabled selected>Select Provider</option>';
            
            hcpOptions.forEach(option => {
                if (option.disabled) return;
                if (option.text.toLowerCase().includes(search)) {
                    hcpSelect.add(new Option(option.text, option.value));
                }
            });
        };

        function handleSubmit(e) {
            e.preventDefault();
            if (!validateCurrentStep()) return;
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            console.log('🎉 FORM SUBMITTED:', JSON.stringify(data, null, 2));
            alert('✅ Registration completed successfully!\nCheck console for full data structure.');
        }

        // Auto-save progress
        window.addEventListener('beforeunload', () => {
            localStorage.setItem('health-form-progress', JSON.stringify({
                currentStepIndex,
                dependantsCount: dependantsCount.value,
                formData: new FormData(form)
            }));
        });
    </script>
</body>
</html>
