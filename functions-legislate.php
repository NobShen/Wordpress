// this file is the updated functions.php file under the theme directory.  It contains the functions to lookup legislators in the USA to display to the client's browser.

<?php
// 1. Create the Shortcode for the Legislator Lookup Tool
add_shortcode('congress_lookup', 'congress_lookup_shortcode_handler');

function congress_lookup_shortcode_handler() {
    // Enqueue the necessary scripts and styles
    wp_enqueue_script('congress-lookup-js', get_stylesheet_directory_uri() . '/js/congress-lookup.js', array('jquery'), null, true);
    wp_enqueue_style('congress-lookup-css', 'https://cdn.tailwindcss.com');

    // Pass data to JavaScript, including the AJAX URL for server-side communication
    wp_localize_script('congress-lookup-js', 'congress_lookup_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('congress_lookup_nonce') // Security nonce
    ));

    // The HTML structure for the lookup tool
    ob_start(); ?>

    <style>
        /* Simple spinner animation */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #09f;
            animation: spin 1s ease infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <div class="container mx-auto p-4 sm:p-6 md:p-8 max-w-4xl font-sans">
        <header class="text-center mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">U.S. Congress Member Lookup</h1>
            <p class="text-md text-gray-600 mt-2">Find current senators and representatives for any state.</p>
        </header>

        <main>
            <div class="bg-white p-6 rounded-xl shadow-md">
                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4">
                    <label for="state-select" class="font-semibold text-lg mb-2 sm:mb-0">Select a State:</label>
                    <select id="state-select" class="flex-grow p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </select>
                    <button id="search-btn" class="mt-4 sm:mt-0 w-full sm:w-auto bg-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors duration-300 shadow-sm">
                        Search
                    </button>
                </div>
            </div>

            <div id="loader" class="hidden flex justify-center items-center mt-8">
                <div class="spinner"></div>
            </div>

            <div id="error-message" class="hidden text-center mt-8 bg-red-100 text-red-700 p-4 rounded-lg"></div>

            <div id="results-container" class="mt-8 space-y-8">
                <section id="senators-section" class="hidden"></section>
                <section id="representatives-section" class="hidden"></section>
            </div>
        </main>
    </div>

    <?php
    return ob_get_clean();
}


// 2. Create the AJAX handler to fetch data on the server-side
add_action('wp_ajax_fetch_legislators', 'fetch_legislators_callback');
add_action('wp_ajax_nopriv_fetch_legislators', 'fetch_legislators_callback'); // For non-logged-in users

function fetch_legislators_callback() {
    // Security check
    check_ajax_referer('congress_lookup_nonce', 'nonce');

    // Get the state from the AJAX request and sanitize it
    $state = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '';

    if (empty($state)) {
        wp_send_json_error('State is required.');
        return;
    }

    $api_url = "https://www.govtrack.us/api/v2/role?current=true&state=" . $state;

    // Use WordPress's built-in function to make the HTTP request
    $response = wp_remote_get($api_url);

    // Check for errors
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        wp_send_json_error('Failed to retrieve data from the API.');
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    // Send the data back to the browser as a JSON response
    wp_send_json_success($data);
}
