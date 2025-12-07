<?php
// Live search UI: policy_no AND package benefits
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../api/_auth.php';
require_once __DIR__ . '/../../csrf.php';

// Ensure user is logged in (staff)
require_staff_auth();

// Compute API base path dynamically so the page works if mounted at a different URI
$apiBase = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Policy & Package Live Search</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <?php echo csrf_meta_tag(); ?>
    <style>
        .result-card { transition: box-shadow .15s; }
        .result-card:hover { box-shadow: 0 6px 18px rgba(16,24,40,.08); }
    </style>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Live Policy Search & Package Benefits</h1>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search policy number, name or phone</label>
            <div class="flex gap-2">
                <input id="searchInput" type="search" placeholder="Start typing policy number, name or phone..." class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" autofocus>
                <button id="searchBtn" class="px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Search</button>
                <label class="inline-flex items-center space-x-2 text-sm text-gray-600 px-2">
                    <input id="liveToggle" type="checkbox" checked class="form-checkbox h-4 w-4 text-indigo-600">
                    <span>Live</span>
                </label>
            </div>
        </div>

        <div id="results" class="space-y-3"></div>
    </div>

    <script>
        const input = document.getElementById('searchInput');
        const results = document.getElementById('results');
        let timer = null;

        function escapeHtml(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        async function query(q) {
            if (!q || q.trim().length < 1) {
                results.innerHTML = '<div class="text-sm text-gray-500">Start typing to search.</div>';
                return;
            }
            try {
                // Use server-computed API base so this works regardless of mount point
                const url = '<?php echo $apiBase; ?>' + '/api/policy_search.php?q=' + encodeURIComponent(q);
                const res = await fetch(url, { credentials: 'same-origin' });
                if (!res.ok) throw new Error('Network');

                const contentType = res.headers.get('content-type') || '';
                if (contentType.indexOf('application/json') === -1) {
                    // Unexpected response (maybe redirected to login). Show helpful message.
                    const text = await res.text();
                    console.error('Unexpected response:', text);
                    results.innerHTML = '<div class="text-sm text-red-600">Search failed or you are not authenticated.</div>';
                    return;
                }

                const data = await res.json();
                renderResults(data);
            } catch (e) {
                console.error(e);
                results.innerHTML = '<div class="text-sm text-red-600">Search failed.</div>';
            }
        }

        function renderResults(items) {
            if (!items || items.length === 0) {
                results.innerHTML = '<div class="text-sm text-gray-500">No matching policies found.</div>';
                return;
            }
            results.innerHTML = items.map(it => {
                const pkgs = (it.packages && it.packages.length) ? it.packages.map(p => `
                    <div class="mt-2 p-3 bg-gray-50 rounded">\n                        <div class="font-semibold text-gray-800">${escapeHtml(p.package_name)} <span class="text-xs text-gray-500">(${escapeHtml(p.package_plan)})</span></div>\n                        <div class="text-xs text-gray-600 mt-1">${escapeHtml(p.package_description)}</div>\n                        <div class="text-sm text-indigo-600 mt-2 font-medium">Price: $${escapeHtml(p.package_price)}</div>\n                    </div>
                `).join('') : '<div class="text-sm text-gray-500 mt-2">No package benefits found for this plan.</div>';

                return `
                    <div class="result-card p-4 bg-white rounded-lg border">\n                        <div class="flex justify-between items-start">\n                            <div>\n                                <div class="text-sm text-gray-500">Policy</div>\n                                <div class="text-lg font-semibold text-gray-900">${escapeHtml(it.policy_no)}</div>\n                                <div class="text-sm text-gray-700 mt-1">${escapeHtml(it.principal_name)} &middot; ${escapeHtml(it.phone || '')}</div>\n                                <div class="text-xs text-gray-500 mt-1">Plan: ${escapeHtml(it.plan_type || '')}</div>\n                            </div>\n                        </div>\n                        ${pkgs}\n+                    </div>
                `;
            }).join('');
        }

        // Live search behavior (debounced) — only runs when Live checkbox is checked
        input.addEventListener('input', (e) => {
            const liveOn = document.getElementById('liveToggle').checked;
            if (!liveOn) return;
            clearTimeout(timer);
            timer = setTimeout(() => query(e.target.value), 220);
        });

        // Enter key triggers immediate search
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(timer);
                query(input.value);
            }
        });

        // Manual Search button
        document.getElementById('searchBtn').addEventListener('click', (e) => {
            e.preventDefault();
            clearTimeout(timer);
            query(input.value);
        });

        // Kick off with blank message
        results.innerHTML = '<div class="text-sm text-gray-500">Start typing to search.</div>';
    </script>
</body>
</html>
