// place this file under the js directory of the active theme i.e. /themes/ggm/js

document.addEventListener('DOMContentLoaded', () => {
    const stateSelect = document.getElementById('state-select');
    if (!stateSelect) return; // Exit if the shortcode isn't on the page

    const searchBtn = document.getElementById('search-btn');
    const loader = document.getElementById('loader');
    const errorMessage = document.getElementById('error-message');
    const senatorsSection = document.getElementById('senators-section');
    const representativesSection = document.getElementById('representatives-section');
    
    // US States and Territories (remains client-side for fast UI)
    const states = [
        { name: 'Alabama', abbr: 'AL' }, { name: 'Alaska', abbr: 'AK' },
        { name: 'Arizona', abbr: 'AZ' }, { name: 'Arkansas', abbr: 'AR' },
        { name: 'California', abbr: 'CA' }, { name: 'Colorado', abbr: 'CO' },
        { name: 'Connecticut', abbr: 'CT' }, { name: 'Delaware', abbr: 'DE' },
        { name: 'Florida', abbr: 'FL' }, { name: 'Georgia', abbr: 'GA' },
        { name: 'Hawaii', abbr: 'HI' }, { name: 'Idaho', abbr: 'ID' },
        { name: 'Illinois', abbr: 'IL' }, { name: 'Indiana', abbr: 'IN' },
        { name: 'Iowa', abbr: 'IA' }, { name: 'Kansas', abbr: 'KS' },
        { name: 'Kentucky', abbr: 'KY' }, { name: 'Louisiana', abbr: 'LA' },
        { name: 'Maine', abbr: 'ME' }, { name: 'Maryland', abbr: 'MD' },
        { name: 'Massachusetts', abbr: 'MA' }, { name: 'Michigan', abbr: 'MI' },
        { name: 'Minnesota', abbr: 'MN' }, { name: 'Mississippi', abbr: 'MS' },
        { name: 'Missouri', abbr: 'MO' }, { name: 'Montana', abbr: 'MT' },
        { name: 'Nebraska', abbr: 'NE' }, { name: 'Nevada', abbr: 'NV' },
        { name: 'New Hampshire', abbr: 'NH' }, { name: 'New Jersey', abbr: 'NJ' },
        { name: 'New Mexico', abbr: 'NM' }, { name: 'New York', abbr: 'NY' },
        { name: 'North Carolina', abbr: 'NC' }, { name: 'North Dakota', abbr: 'ND' },
        { name: 'Ohio', abbr: 'OH' }, { name: 'Oklahoma', abbr: 'OK' },
        { name: 'Oregon', abbr: 'OR' }, { name: 'Pennsylvania', abbr: 'PA' },
        { name: 'Rhode Island', abbr: 'RI' }, { name: 'South Carolina', abbr: 'SC' },
        { name: 'South Dakota', abbr: 'SD' }, { name: 'Tennessee', abbr: 'TN' },
        { name: 'Texas', abbr: 'TX' }, { name: 'Utah', abbr: 'UT' },
        { name: 'Vermont', abbr: 'VT' }, { name: 'Virginia', abbr: 'VA' },
        { name: 'Washington', abbr: 'WA' }, { name: 'West Virginia', abbr: 'WV' },
        { name: 'Wisconsin', abbr: 'WI' }, { name: 'Wyoming', abbr: 'WY' },
        { name: 'American Samoa', abbr: 'AS' }, { name: 'District of Columbia', abbr: 'DC' },
        { name: 'Guam', abbr: 'GU' }, { name: 'Northern Mariana Islands', abbr: 'MP' },
        { name: 'Puerto Rico', abbr: 'PR' }, { name: 'U.S. Virgin Islands', abbr: 'VI' }
    ];

    states.forEach(state => {
        const option = document.createElement('option');
        option.value = state.abbr;
        option.textContent = state.name;
        stateSelect.appendChild(option);
    });
    
    searchBtn.addEventListener('click', fetchLegislators);

    async function fetchLegislators() {
        const selectedState = stateSelect.value;
        if (!selectedState) return;

        loader.classList.remove('hidden');
        errorMessage.classList.add('hidden');
        senatorsSection.classList.add('hidden');
        representativesSection.classList.add('hidden');
        senatorsSection.innerHTML = '';
        representativesSection.innerHTML = '';

        const formData = new URLSearchParams();
        formData.append('action', 'fetch_legislators');
        formData.append('nonce', congress_lookup_ajax.nonce);
        formData.append('state', selectedState);
        
        try {
            const response = await fetch(congress_lookup_ajax.ajax_url, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (!result.success) {
                 throw new Error(result.data || 'An unknown error occurred.');
            }

            const legislators = result.data.objects;
            const senators = legislators.filter(leg => leg.role_type === 'senator').sort((a, b) => a.person.lastname.localeCompare(b.person.lastname));
            const representatives = legislators.filter(leg => leg.role_type === 'representative').sort((a, b) => a.district - b.district);

            displayResults(senators, representatives);

        } catch (error) {
            console.error('Fetch error:', error);
            showError(`Could not fetch legislator data. ${error.message}`);
        } finally {
            loader.classList.add('hidden');
        }
    }
    
    function displayResults(senators, representatives) {
        if (senators.length > 0) {
            const senatorsList = document.createElement('div');
            senatorsList.className = 'grid grid-cols-1 md:grid-cols-2 gap-6';
            senators.forEach(senator => senatorsList.appendChild(createLegislatorCard(senator)));
            
            const heading = document.createElement('h2');
            heading.className = 'text-2xl font-bold border-b-2 border-gray-200 pb-2 mb-4';
            heading.textContent = 'Senators';

            senatorsSection.appendChild(heading);
            senatorsSection.appendChild(senatorsList);
            senatorsSection.classList.remove('hidden');
        }

        if (representatives.length > 0) {
            const representativesList = document.createElement('div');
            representativesList.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
            representatives.forEach(rep => representativesList.appendChild(createLegislatorCard(rep)));

            const heading = document.createElement('h2');
            heading.className = 'text-2xl font-bold border-b-2 border-gray-200 pb-2 mb-4';
            heading.textContent = 'Representatives';

            representativesSection.appendChild(heading);
            representativesSection.appendChild(representativesList);
            representativesSection.classList.remove('hidden');
        }

        if (senators.length === 0 && representatives.length === 0) {
             showError(`No current legislators found for ${stateSelect.options[stateSelect.selectedIndex].text}. This may be a territory with a non-voting delegate.`);
        }
    }

    function getPartyColor(party) {
        switch(party) {
            case 'Democrat': return 'bg-blue-100 text-blue-800';
            case 'Republican': return 'bg-red-100 text-red-800';
            case 'Independent': return 'bg-green-100 text-green-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    function createLegislatorCard(legislator) {
        const { party, person, website, role_type, district } = legislator;
        const placeholderSvg = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23cbd5e1"><path d="M50 4a46 46 0 100 92 46 46 0 000-92zM50 31a15 15 0 110 30 15 15 0 010-30zm0 36c-13.3 0-25 7-25 15v3h50v-3c0-8-11.7-15-25-15z"/></svg>';
        
        // --- FIX #1: Build elements programmatically to prevent HTML rendering errors ---
        const card = document.createElement('div');
        // --- FIX #2: Add "h-full" class to make all cards in a row the same height ---
        card.className = 'bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col h-full';

        const mainContent = document.createElement('div');
        mainContent.className = 'flex-grow flex items-center space-x-4';

        const img = document.createElement('img');
        let photoUrl = placeholderSvg;
        if (person.twitterid) {
            photoUrl = `https://unavatar.io/twitter/${person.twitterid}?fallback=${encodeURIComponent(placeholderSvg)}`;
        }
        img.src = photoUrl;
        img.alt = `Photo of ${person.firstname} ${person.lastname}`;
        img.className = 'h-20 w-20 rounded-full object-cover bg-gray-200 flex-shrink-0';
        img.onerror = () => { img.src = placeholderSvg; };
        mainContent.appendChild(img);

        const infoDiv = document.createElement('div');
        infoDiv.className = 'flex-grow';

        const namePartyDiv = document.createElement('div');
        namePartyDiv.className = 'flex justify-between items-start';

        const nameH3 = document.createElement('h3');
        nameH3.className = 'text-lg font-bold text-gray-900 leading-tight';
        nameH3.textContent = `${person.firstname} ${person.lastname}`;
        namePartyDiv.appendChild(nameH3);

        const partySpan = document.createElement('span');
        partySpan.className = `text-xs font-semibold px-2 py-0.5 rounded-full ${getPartyColor(party)} whitespace-nowrap`;
        partySpan.textContent = party;
        namePartyDiv.appendChild(partySpan);

        infoDiv.appendChild(namePartyDiv);

        if (role_type === 'representative') {
            const districtP = document.createElement('p');
            districtP.className = 'text-sm text-gray-500';
            districtP.textContent = district === 0 ? "At-Large" : `District ${district}`;
            infoDiv.appendChild(districtP);
        }
        mainContent.appendChild(infoDiv);

        const footerDiv = document.createElement('div');
        footerDiv.className = 'mt-4 pt-4 border-t border-gray-200 text-right';

        const websiteLink = document.createElement('a');
        websiteLink.href = website || 'https://www.congress.gov/';
        websiteLink.target = '_blank';
        websiteLink.rel = 'noopener noreferrer';
        websiteLink.className = 'text-sm text-blue-600 hover:underline font-medium';
        websiteLink.textContent = 'Official Website →';
        footerDiv.appendChild(websiteLink);

        card.appendChild(mainContent);
        card.appendChild(footerDiv);

        return card;
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.remove('hidden');
    }
});
