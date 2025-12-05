
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Package</title>
    <!-- Tailwind CSS link -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom styles for focus glow */
        .input-focus-ring:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.5); /* Indigo-500 equivalent */
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-xl w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
        
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900">
                📦 Create a New Package
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                Define the details for a new service or product offering.
            </p>
        </div>
        
        <form action="create_package.php" method="POST" class="mt-8 space-y-6">
            
            <!-- Package Name -->
            <div>
                <label for="package_name" class="block text-sm font-medium text-gray-700 mb-1">Package Name:</label>
                <input 
                    type="text" 
                    id="package_name" 
                    name="package_name" 
                    required 
                    placeholder="e.g., Enterprise Tier"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus-ring focus:border-indigo-500 transition duration-150"
                >
            </div>
            
            <!-- Package Description -->
            <div>
                <label for="package_description" class="block text-sm font-medium text-gray-700 mb-1">Package Description:</label>
                <textarea 
                    id="package_description" 
                    name="package_description" 
                    rows="4"
                    required
                    placeholder="A brief summary of what this package includes."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus-ring focus:border-indigo-500 transition duration-150 resize-y"
                ></textarea>
            </div>
            
            <!-- Package Price -->
            <div>
                <label for="package_price" class="block text-sm font-medium text-gray-700 mb-1">Package Price (USD):</label>
                <input 
                    type="number" 
                    id="package_price" 
                    name="package_price" 
                    required 
                    min="0.01"
                    step="0.01"
                    placeholder="99.99"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus-ring focus:border-indigo-500 transition duration-150"
                >
            </div>
            
            <!-- Package Plan (Select) -->
            <div>
                <label for="package_plan" class="block text-sm font-medium text-gray-700 mb-1">Package Plan Level:</label>
                <select 
                    id="package_plan" 
                    name="package_plan" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 bg-white rounded-lg input-focus-ring focus:border-indigo-500 transition duration-150 appearance-none"
                    style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20d%3D%22M9.293%2012.95l.707.707L15.657%208l-1.414-1.414L10%2010.828%205.757%206.586%204.343%208z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 0.65em 0.65em;"
                >
                    <option value="" disabled selected>Select a plan level</option>
                    <option value="basic">Basic</option>
                    <option value="standard">Standard</option>
                    <option value="premium">Premium</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            <!-- Package Code (Now Alphanumeric) -->
            <div>
                <label for="package_code" class="block text-sm font-medium text-gray-700 mb-1">Package Code (Alphanumeric ID):</label>
                <input 
                    type="text" 
                    id="package_code" 
                    name="package_code" 
                    required 
                    placeholder="Unique Code (e.g., PRO-2024-A)"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus-ring focus:border-indigo-500 transition duration-150"
                >
            </div>
            
            <!-- Submit Button -->
            <div>
                <button 
                    type="submit" 
                    name="create_package"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200"
                >
                    🚀 Create Package
                </button>
            </div>
        </form>
    </div>
    
</body>
</html>