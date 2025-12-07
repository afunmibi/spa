<?php
// search_package.php (Main UI Page)
require_once dirname(__DIR__, 2) . '/db.php'; 

$initial_search_term = htmlspecialchars(trim($_GET['search_term'] ?? ''));
$initial_plan_filter = htmlspecialchars(trim($_GET['plan_filter'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Search Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-4xl mx-auto space-y-8">
        
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900">🔍 Live Search Packages</h1>
        </div>

        <form id="search-form" onsubmit="event.preventDefault(); return false;" class="bg-white p-6 rounded-xl shadow-lg flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
            
            <div class="flex-grow">
                <label for="search_term" class="sr-only">Search Keyword</label>
                <input type="text" id="search-term-input" name="search_term" placeholder="Type to search Name, Description, or Code..." value="<?php echo $initial_search_term; ?>">
            </div>
            
            <div>
                <label for="plan_filter" class="sr-only">Filter by Plan</label>
                <select id="plan-filter-select" name="plan_filter">
                    <option value="">-- Loading Plans --</option>
                </select>
            </div>
            
            <button type="button" onclick="performLiveSearch();" 
                    class="w-full sm:w-auto flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                Search
            </button>
        </form>

        <div class="mt-8">
            <h2 id="results-header" class="text-2xl font-bold text-gray-800 mb-4">Packages List</h2>
            
            <div id="results-container" class="bg-white shadow-xl rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (₦)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th> 
                        </tr>
                    </thead>
                    <tbody id="live-results-body" class="bg-white divide-y divide-gray-200">
                        <tr><td colspan="6" class="text-center py-4 text-gray-500">Start typing or click Search to load data.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    
    <script>
        // ... (JavaScript remains the same as previously provided) ...
        const searchInput = document.getElementById('search-term-input');
        const resultsBody = document.getElementById('live-results-body');
        const planSelect = document.getElementById('plan-filter-select');
        const resultsHeader = document.getElementById('results-header');
        let searchTimer;

        // --- A. Fetch Plans from Database ---
        function fetchPlans() {
            fetch('fetch_plans.php')
                .then(response => response.json())
                .then(plans => {
                    planSelect.innerHTML = '<option value="">-- All Plans --</option>';
                    plans.forEach(plan => {
                        const option = document.createElement('option');
                        option.value = plan;
                        option.textContent = plan.charAt(0).toUpperCase() + plan.slice(1);
                        planSelect.appendChild(option);
                    });
                    if ('<?php echo $initial_plan_filter; ?>') {
                        planSelect.value = '<?php echo $initial_plan_filter; ?>';
                    }
                    performLiveSearch(); 
                })
                .catch(error => {
                    console.error('Error fetching plans:', error);
                    planSelect.innerHTML = '<option value="">-- Error Loading Plans --</option>';
                });
        }

        // --- B. Perform Live Search ---
        function performLiveSearch() {
            const searchTerm = searchInput.value;
            const planFilter = planSelect.value;
            
            // NOTE: Colspan is now 6
            resultsBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-indigo-500">Searching...</td></tr>';
            resultsHeader.textContent = 'Searching...';

            fetch(`live_search.php?q=${encodeURIComponent(searchTerm)}&plan=${encodeURIComponent(planFilter)}`)
                .then(response => response.text())
                .then(html => {
                    resultsBody.innerHTML = html;
                    const rowCount = resultsBody.querySelectorAll('tr').length;
                    
                    if (html.includes('No live results found') || html.includes('A system error occurred') || html.includes('Enter a keyword')) {
                         resultsHeader.textContent = `Results: 0 found`;
                    } else {
                        resultsHeader.textContent = `Results: ${rowCount} found`;
                    }
                })
                .catch(error => {
                    console.error('Live search failed:', error);
                    resultsBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-red-500">Failed to connect to server for search.</td></tr>';
                });
        }

        // --- C. Event Listeners (Debounced) ---
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(performLiveSearch, 300);
        });
        
        planSelect.addEventListener('change', performLiveSearch);

        // --- D. Initial Load ---
        document.addEventListener('DOMContentLoaded', fetchPlans);
    </script>
</body>
</html>