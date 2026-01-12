<meta name="csrf-token" content="{{ csrf_token() }}">

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">Counties</h1>
        <p style="color: #666; margin: 0;">{{ session('user_name') }} ({{ session('user_email') }})</p>
    </div>

    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Select a county:</h2>
        <p id="counties-loading" style="display: block;">Loading counties…</p>
        <p id="counties-error" style="color: #b91c1c; display: none;"></p>
        <ul id="county-list" style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.75rem;"></ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const apiBaseUrl = @json(config('services.api.base_url'));
        const apiBase = (apiBaseUrl || '').replace(/\/+$/, '');
        const apiUrl = apiBase + '/counties';
        const apiTokenFromSession = @json(session('api_token', ''));
        const apiTokenFromStorage = (typeof localStorage !== 'undefined') ? (localStorage.getItem('api_token') || '') : '';
        const apiToken = apiTokenFromSession || apiTokenFromStorage;

        const loadingEl = document.getElementById('counties-loading');
        const errorEl = document.getElementById('counties-error');
        const listEl = document.getElementById('county-list');

        if (apiTokenFromSession && typeof localStorage !== 'undefined') {
            try { localStorage.setItem('api_token', apiTokenFromSession); } catch (e) {}
        }

        function showError(message) {
            loadingEl.style.display = 'none';
            errorEl.textContent = message;
            errorEl.style.display = '';
        }

        if (!apiToken) {
            showError('Authentication token not found. Please log in again.');
            return;
        }

        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + apiToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
                return response.json();
            })
            .then(data => {
                loadingEl.style.display = 'none';

                if (!Array.isArray(data) || data.length === 0) {
                    showError('No counties returned from the API.');
                    return;
                }

                // Sort counties by name
                data.sort((a, b) => (a.name || '').localeCompare(b.name || '', undefined, { sensitivity: 'base' }));

                // Populate list view with clickable items
                listEl.innerHTML = '';
                data.forEach(county => {
                    const li = document.createElement('li');
                    li.textContent = county.name;
                    li.style.padding = '.5rem 1rem';
                    li.style.cursor = 'pointer';
                    li.style.borderBottom = '1px solid #e5e7eb';
                    li.style.transition = 'background-color 0.2s';
                    
                    li.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = '#f3f4f6';
                    });
                    
                    li.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = '';
                    });
                    
                    li.addEventListener('click', function() {
                        window.location.href = '/counties/' + county.name + '/alphabet';
                    });
                    
                    listEl.appendChild(li);
                });
            })
            .catch(err => {
                showError('Error fetching counties. See console for details.');
            });
    });
</script>
