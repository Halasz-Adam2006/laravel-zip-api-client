<meta name="csrf-token" content="{{ csrf_token() }}">

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">{{ $county->name }}</h1>
        <a href="/counties" style="color: #3b82f6; text-decoration: none;">&larr; Back to Counties</a>
    </div>

    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Select a letter:</h2>
        <div id="alphabet-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(50px, 1fr)); gap: 0.5rem; max-width: 800px;">
        </div>
    </div>

    <div id="cities-section" style="display: none;">
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 0;">
                Cities starting with <span id="selected-letter"></span>:
            </h2>
            <button id="export-pdf" style="padding: 0.5rem 1rem; background-color: #dc2626; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600;">Export PDF</button>
            <button id="export-csv" style="padding: 0.5rem 1rem; background-color: #16a34a; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600;">Export CSV</button>
        </div>
        <p id="cities-loading" style="display: none;">Loading cities...</p>
        <p id="cities-error" style="color: #b91c1c; display: none;"></p>
        <ul id="cities-list" style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.75rem;">
        </ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const countyName = '{{ $county->name }}';
        const apiTokenFromSession = @json(session('api_token', ''));
        const apiTokenFromStorage = (typeof localStorage !== 'undefined') ? (localStorage.getItem('api_token') || '') : '';
        const apiToken = apiTokenFromSession || apiTokenFromStorage;
        const apiBaseUrl = @json(config('services.api.base_url'));
        const apiBase = (apiBaseUrl || '').replace(/\/+$/, '');
        
        console.log('County:', countyName);
        console.log('API Token (session):', apiTokenFromSession);
        console.log('API Token (storage):', apiTokenFromStorage);
        console.log('API Token (effective):', apiToken);
        console.log('API Base URL:', apiBaseUrl);
        console.log('Token is empty:', apiToken === '');

        // Persist session token into storage to survive navigation
        if (apiTokenFromSession && typeof localStorage !== 'undefined') {
            try { localStorage.setItem('api_token', apiTokenFromSession); } catch (e) {}
        }
        
        const alphabetGrid = document.getElementById('alphabet-grid');
        const citiesSection = document.getElementById('cities-section');
        const selectedLetterEl = document.getElementById('selected-letter');
        const citiesLoadingEl = document.getElementById('cities-loading');
        const citiesErrorEl = document.getElementById('cities-error');
        const citiesListEl = document.getElementById('cities-list');
        const exportPdfBtn = document.getElementById('export-pdf');
        const exportCsvBtn = document.getElementById('export-csv');
        let currentLetter = null;

        const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split(''); 
        alphabet.forEach(letter => {
            const button = document.createElement('button');
            button.textContent = letter;
            button.style.padding = '1rem';
            button.style.fontSize = '1.25rem';
            button.style.fontWeight = '600';
            button.style.border = '2px solid #d1d5db';
            button.style.borderRadius = '0.5rem';
            button.style.backgroundColor = '#ffffff';
            button.style.cursor = 'pointer';
            button.style.transition = 'all 0.2s';

            button.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f3f4f6';
                this.style.borderColor = '#9ca3af';
            });

            button.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '#ffffff';
                this.style.borderColor = '#d1d5db';
            });

            button.addEventListener('click', function() {
                currentLetter = letter;
                loadCitiesByLetter(letter);
            });

            alphabetGrid.appendChild(button);
        });

        function loadCitiesByLetter(letter) {
            selectedLetterEl.textContent = letter;
            citiesSection.style.display = 'block';
            citiesLoadingEl.style.display = 'block';
            citiesErrorEl.style.display = 'none';
            citiesListEl.innerHTML = '';

            fetch(`${apiBase}/county/${encodeURIComponent(countyName)}/${letter}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + apiToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
                return response.json();
            })
            .then(cities => {
                citiesLoadingEl.style.display = 'none';

                if (cities.error) {
                    citiesErrorEl.textContent = cities.error;
                    citiesErrorEl.style.display = 'block';
                    return;
                }

                if (!Array.isArray(cities) || cities.length === 0) {
                    citiesErrorEl.textContent = 'No cities found starting with ' + letter;
                    citiesErrorEl.style.display = 'block';
                    return;
                }

                citiesListEl.innerHTML = '';
                cities.forEach(city => {
                    const li = document.createElement('li');
                    li.style.padding = '0.75rem 1rem';
                    li.style.backgroundColor = '#f9fafb';
                    li.style.border = '1px solid #e5e7eb';
                    li.style.borderRadius = '0.5rem';
                    
                    const nameEl = document.createElement('div');
                    nameEl.textContent = city.city;
                    nameEl.style.fontWeight = '600';
                    
                    const zipEl = document.createElement('div');
                    zipEl.textContent = 'Irányítószám: ' + city.zip;
                    zipEl.style.fontSize = '0.875rem';
                    zipEl.style.color = '#6b7280';
                    
                    li.appendChild(nameEl);
                    li.appendChild(zipEl);
                    citiesListEl.appendChild(li);
                });
            })
            .catch(err => {
                console.error('Error fetching cities:', err);
                citiesLoadingEl.style.display = 'none';
                citiesErrorEl.textContent = 'Error fetching cities. See console for details.';
                citiesErrorEl.style.display = 'block';
            });
        }

        async function downloadWithAuth(url, fallbackFilename) {
            try {
                const res = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + apiToken,
                        'Accept': '*/*'
                    }
                });
                if (!res.ok) throw new Error('Download failed: ' + res.status);

                const blob = await res.blob();
                let filename = fallbackFilename;
                const cd = res.headers.get('Content-Disposition');
                if (cd) {
                    const match = /filename\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i.exec(cd);
                    if (match) {
                        filename = decodeURIComponent(match[1] || match[2] || fallbackFilename);
                    }
                }

                const urlObj = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = urlObj;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(urlObj);
            } catch (e) {
                console.error('Export error:', e);
                alert('Export failed. See console for details.');
            }
        }

        exportPdfBtn.addEventListener('click', function() {
            if (!currentLetter) return;
            const url = `${apiBase}/county/${encodeURIComponent(countyName)}/${currentLetter}/export/pdf`;
            const name = `cities_${countyName}_${currentLetter}.pdf`;
            downloadWithAuth(url, name);
        });

        exportCsvBtn.addEventListener('click', function() {
            if (!currentLetter) return;
            const url = `${apiBase}/county/${encodeURIComponent(countyName)}/${currentLetter}/export/csv`;
            const name = `cities_${countyName}_${currentLetter}.csv`;
            downloadWithAuth(url, name);
        });
    });
</script>
