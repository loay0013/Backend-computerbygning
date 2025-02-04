<?php

// Enqueue parent and child styles
add_action('wp_enqueue_scripts', 'my_stylesheet');
function my_stylesheet() {
    $parenthandle = 'parent-style';
    $theme = wp_get_theme();
    wp_enqueue_style($parenthandle, get_template_directory_uri() . '/style.css', array(), $theme->parent()->get('Version'));
    wp_enqueue_style('child-style', get_stylesheet_uri(), array($parenthandle), $theme->get('Version'));
}

// Customize Login Page with inline CSS
add_action('login_enqueue_scripts', 'custom_login_inline_styles');
function custom_login_inline_styles() {
    $logo_url = 'http://eksamen.nordicwebworks.dk/wp-content/uploads/2024/11/nw_logo1.png'; // Din logo URL
    ?>
    <style type="text/css">
        /* Body styling */
        body.login {
            background: #FCF3E4 !important;
        }

        /* Logo styling */
        #login h1 a {
            background-image: url('<?php echo esc_url($logo_url); ?>') !important;
            height: 87px !important;
            width: 300px !important;
            background-size: cover !important;
            background-repeat: no-repeat !important;
            padding-bottom: 10px !important;
        }

        #login h1 {
            display: flex !important;
        }

        /* Login form styling */
        .login form {
            background: #76825E !important;
            border-radius: 10px !important;
            padding: 20px !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2) !important;
        }

        /* Text color */
        .login p {
            color: #FCF3E4 !important;
        }

        /* Button styling */
        .wp-core-ui .button-primary {
            background-color: #DCA226 !important;
            border: none !important;
            color: #fff !important;
            border-radius: 5px !important;
            transition: background-color 0.3s ease !important;
        }

        .wp-core-ui .button-primary:hover {
            background-color: #e5b63b !important;
        }

        /* Message styling */
        #login-message {
            color: #0c0c0c !important;
        }
    </style>
    <?php
}

// Dynamisk logo link
add_filter('login_headerurl', 'custom_login_logo_url');
function custom_login_logo_url() {
    return home_url(); // Gør linket dynamisk til hjemmesiden
}

// Dynamisk header title
add_filter('login_headertext', 'custom_login_logo_url_title');
function custom_login_logo_url_title() {
    return get_bloginfo('name') . ' - ' . get_bloginfo('description');
}


// Register Custom Post Types
function create_pc_builder_post_types() {
    register_post_type("games", [
        "public" => true,
        "show_in_rest" => true,
        "labels" => [
            "name" => "Games",
            "singular_name" => "Game",
            "add_new" => "Add New Game",
            "add_new_item" => "Add New Game",
            "edit_item" => "Edit Game",
            "all_items" => "All Games",
            "featured_image" => "Spil Cover Image",
            "set_featured_image" => "Sæt spil cover",
            "remove_featured_image" => "Fjern cover",
            "use_featured_image" => "Brug som spil cover"
        ],
        'supports' => ['title', 'editor', 'thumbnail'],
        'menu_icon' => 'dashicons-games'
    ]);

    register_post_type('hardware', [
        'public' => true,
        'show_in_rest' => true,
        'labels' => [
            'name' => 'Hardware',
            'singular_name' => 'Hardware',
            'add_new' => 'Add New Hardware',
            'add_new_item' => 'Add New Hardware',
            'edit_item' => 'Edit Hardware',
            'all_items' => 'All Hardware',
            'featured_image' => 'Hardware Image',
            'set_featured_image' => 'Sæt hardware billede',
            'remove_featured_image' => 'Fjern billede',
            'use_featured_image' => 'Brug som hardware billede',
        ],
        'supports' => ['title', 'editor', 'thumbnail'],
        'menu_icon' => 'dashicons-desktop'
    ]);
}
add_action('init', 'create_pc_builder_post_types');

// Add Meta Boxes
function add_game_meta_boxes() {
    add_meta_box(
        'game_details',
        'Game Details',
        'render_game_meta_box',
        'games',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_game_meta_boxes');

function add_hardware_meta_boxes() {
    add_meta_box(
        'hardware_details',
        'Hardware Details',
        'render_hardware_meta_box',
        'hardware',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_hardware_meta_boxes');

// Render Game Meta Box
function render_game_meta_box($post) {
    $description = get_post_meta($post->ID, 'game_description', true);
    $game_img = get_post_meta($post->ID, 'game_img', true);
    $requirements = get_post_meta($post->ID, 'requirements', true);
    wp_nonce_field('game_details', 'game_details_nonce');
    ?>
    <div class="meta_box_items">
        <label for="game_description">Beskrivelse af spil</label>
        <input type="text" id="game_description" name="game_description" value="<?php echo esc_attr($description); ?>">
    </div>
    <div class="meta_box_items">
        <label for="game_img">Indsæt URL</label>
        <input type="text" id="game_img" name="game_img" value="<?php echo esc_attr($game_img); ?>">
        <button type="button" class="upload-image-button">Upload billede</button>
    </div>
    <div class="meta_box_items">
        <label for="requirements">Klassificering</label>
        <select id="requirements" name="requirements">
            <option value="low" <?php selected($requirements, "low"); ?>>Low</option>
            <option value="medium" <?php selected($requirements, "medium"); ?>>Medium</option>
            <option value="high" <?php selected($requirements, "high"); ?>>High</option>
        </select>
    </div>
<script>
    jQuery(document).ready(function($) {
        $('.upload-image-button').click(function(e) {
            e.preventDefault();
            const button = $(this);
            const custom_uploader = wp.media({
                title: 'Vælg billede',
                library: {
                    type: 'image'
                },
                button: {
                    text: 'Vælg dette billede'
                },
                multiple: false
            }).on('select', function() {
                const attachment = custom_uploader.state().get('selection').first().toJSON();
                button.prev('input').val(attachment.url);
            }).open();
        });
    });
</script>

    <?php
}

// Render Hardware Meta Box
function render_hardware_meta_box($post) {
    $type = get_post_meta($post->ID, 'hardware_type', true);
    $user_title = get_post_meta($post->ID, 'user_title', true);
    $description = get_post_meta($post->ID, 'hardware_description', true);
    $short_description = get_post_meta($post->ID, 'short_description', true);
    $hardware_img = get_post_meta($post->ID, 'hardware_img', true);
    $hardware_img1 = get_post_meta($post->ID, 'hardware_img1', true);
    $requirements = get_post_meta($post->ID, 'requirements', true);
    $price_link = get_post_meta($post->ID, 'price_link', true);
    $class_requirements = get_post_meta($post->ID, 'class_requirements', true);

    wp_nonce_field('hardware_details', 'hardware_details_nonce');
    ?>
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px; margin-top: 20px;">
        <div style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
            <label for="hardware_type" style="font-weight: bold;">Hardware type</label>
            <select id="hardware_type" name="hardware_type" style="padding: 8px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="processor" <?php selected($type, "processor"); ?>>Processor</option>
                <option value="grafik" <?php selected($type, "grafik"); ?>>Grafikkort</option>
                <option value="ram" <?php selected($type, "ram"); ?>>Ram</option>
                <option value="lagring" <?php selected($type, "lagring"); ?>>Lagring</option>
                <option value="kabinet" <?php selected($type, "kabinet"); ?>>Kabinet</option>
                <option value="køling" <?php selected($type, "køling"); ?>>Køling</option>
                <option value="strømforsyning" <?php selected($type, "strømforsyning"); ?>>Strømforsyning</option>
                <option value="bundkort" <?php selected($type, "bundkort"); ?>>Bundkort</option>
            </select>
        </div>
      <div style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
            <label for="user_title" style="font-weight: bold;">Indsæt brugerrejse titel</label>
            <textarea id="user_title" name="user_title" style="padding: 8px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px; height: 100px; resize: vertical;"><?php echo esc_textarea($user_title); ?></textarea>
      </div>
        <div style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
            <label for="hardware_description" style="font-weight: bold;">Beskrivelse af komponent</label>
            <textarea id="hardware_description" name="hardware_description" style="padding: 8px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px; height: 100px; resize: vertical;"><?php echo esc_textarea($description); ?></textarea>
        </div>
      	<div style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
            <label for="short_description" style="font-weight: bold;">Kort beskrivelse af komponent</label>
            <textarea id="short_description" name="short_description" style="padding: 8px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px; height: 100px; resize: vertical;"><?php echo esc_textarea($short_description); ?></textarea>
        </div>
        <div style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
            <label for="hardware_img" style="font-weight: bold;">Indsæt komponent URL</label>
            <input type="url" id="hardware_img" name="hardware_img" value="<?php echo esc_url($hardware_img); ?>" style="padding: 8px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px;">
            <button type="button" class="upload-image-button" style="background-color: #007cba; color: white; border: none; cursor: pointer; padding: 8px 15px; text-align: center; border-radius: 4px; margin-top: 10px;">Upload billede</button>
        </div>
      	<div style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
            <label for="hardware_img1" style="font-weight: bold;">Indsæt brugerrejse URL</label>
            <input type="url" id="hardware_img1" name="hardware_img1" value="<?php echo esc_url($hardware_img1); ?>" style="padding: 8px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px;">
            <button type="button" class="upload-image-button" style="background-color: #007cba; color: white; border: none; cursor: pointer; padding: 8px 15px; text-align: center; border-radius: 4px; margin-top: 10px;">Upload billede</button>
        </div>
        <div style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
            <label for="requirements" style="font-weight: bold;">Klassificering</label>
            <select id="requirements" name="requirements" style="padding: 8px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="low" <?php selected($requirements, "low"); ?>>Low</option>
                <option value="medium" <?php selected($requirements, "medium"); ?>>Medium</option>
                <option value="high" <?php selected($requirements, "high"); ?>>High</option>
            </select>
        </div>
      <div style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
            <label for="class_requirements" style="font-weight: bold;">Klassificerings krav</label>
            <select id="class_requirements" name="class_requirements" style="padding: 8px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="1" <?php selected($class_requirements, "1"); ?>>1</option>
                <option value="2" <?php selected($class_requirements, "2"); ?>>2</option>
                <option value="3" <?php selected($class_requirements, "3"); ?>>3</option>
                <option value="4" <?php selected($class_requirements, "4"); ?>>4</option>
            </select>
        </div>
        <div style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
            <label for="price_link" style="font-weight: bold;">Link til pricerunner</label>
            <input type="url" id="price_link" name="price_link" value="<?php echo esc_url($price_link); ?>" style="padding: 8px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
    </div>


<script>
    jQuery(document).ready(function($) {
        $('.upload-image-button').click(function(e) {
            e.preventDefault();
            const button = $(this);
            const custom_uploader = wp.media({
                title: 'Vælg billede',
                library: {	
                    type: 'image'
                },
                button: {
                    text: 'Vælg dette billede'
                },
                multiple: false
            }).on('select', function() {
                const attachment = custom_uploader.state().get('selection').first().toJSON();
                button.prev('input').val(attachment.url);
            }).open();
        });
    });
</script>

    <?php
}

//add_action('admin_enqueue_scripts', 'enqueue_child_theme_admin_styles');
//function enqueue_child_theme_admin_styles() {
    //wp_enqueue_style('child-theme-admin-styles', get_stylesheet_directory_uri() . '/style.css');
//}


add_action('wp_enqueue_scripts', 'enqueue_jquery');
function enqueue_jquery() {
    wp_enqueue_script('jquery'); // Sikrer at jQuery er tilgængelig
}


// Save Meta Box Data
function save_game_meta($post_id) {
    if (!isset($_POST['game_details_nonce']) || !wp_verify_nonce($_POST['game_details_nonce'], 'game_details')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    $fields = ['game_description', 'game_img', 'requirements'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post_games', 'save_game_meta');

function save_hardware_meta($post_id) {
    if (!isset($_POST['hardware_details_nonce']) || !wp_verify_nonce($_POST['hardware_details_nonce'], 'hardware_details')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    $fields = ['user_title','hardware_description','short_description', 'hardware_img', 'hardware_img1', 'requirements', 'hardware_type', 'price_link', 'class_requirements'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post_hardware', 'save_hardware_meta');

// Register REST API Routes
function register_pc_builder_rest_routes() {
    register_rest_route('pc-builder/v1', '/games', [
    'methods' => 'GET',
    'callback' => 'get_games_data',
    'permission_callback' => '__return_true'
	]);

    register_rest_route('pc-builder/v1', '/hardware', [
    'methods' => 'GET',
    'callback' => 'get_hardware_data',
    'permission_callback' => '__return_true',
    'args' => [
        'type' => [
            'required' => false,
            'sanitize_callback' => 'sanitize_text_field',
        ]
    ]
]);


}
add_action('rest_api_init', 'register_pc_builder_rest_routes');

// REST API Callbacks
function get_games_data() {
    $args = [
        'post_type' => 'games',
        'posts_per_page' => -1
    ];
    $query = new WP_Query($args);
    $data = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // Tilføj kerneinformationer
            $post_data = [
                'id' => $post_id,
                'title' => get_the_title(),
                'content' => get_the_content(),
                'date' => get_the_date(),
                'modified' => get_the_modified_date(),
            ];

            // Tilføj metadata
            $post_data['meta'] = [
                'description' => get_post_meta($post_id, 'game_description', true),
                'game_img' => get_post_meta($post_id, 'game_img', true),
                'requirements' => get_post_meta($post_id, 'requirements', true),
            ];

            $data[] = $post_data;
        }
        wp_reset_postdata();
    }

    return $data;
}


function get_hardware_data($request) {
    $type = $request->get_param('type'); // Hent type-parameter fra forespørgslen

    $args = [
        'post_type' => 'hardware',
        'posts_per_page' => -1,
    ];

    // Tilføj en meta query, hvis 'type' er angivet
    if ($type) {
        $args['meta_query'] = [
            [
                'key' => 'hardware_type',
                'value' => $type,
                'compare' => '='
            ]
        ];
    }

    $query = new WP_Query($args);
    $data = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $post_data = [
                'id' => $post_id,
                'title' => get_the_title(),
                'content' => get_the_content(),
                'date' => get_the_date(),
                'modified' => get_the_modified_date(),
                'meta' => [
                    'user_title' => get_post_meta($post_id, 'user_title', true),
                    'description' => get_post_meta($post_id, 'hardware_description', true),
                 	'short_description' => get_post_meta($post_id, 'short_description', true),
                    'hardware_img' => get_post_meta($post_id, 'hardware_img', true),
                    'hardware_img1' => get_post_meta($post_id, 'hardware_img1', true),
                    'requirements' => get_post_meta($post_id, 'requirements', true),
                    'type' => get_post_meta($post_id, 'hardware_type', true),
                    'price_link' => get_post_meta($post_id, 'price_link', true),
                  	'class_requirements' => get_post_meta($post_id, 'class_requirements', true),
                ]
            ];

            $data[] = $post_data;
        }
        wp_reset_postdata();
    }

    return $data;
}


add_filter('manage_hardware_posts_columns', 'custom_hardware_columns');
function custom_hardware_columns($columns) {
    // Fjern standardkolonner, hvis nødvendigt
    unset($columns['date']); // Fjerner "Dato" kolonnen (valgfrit)

    // Tilføj brugerdefinerede kolonner
    $columns['hardware_type'] = 'Type';
    $columns['hardware_img'] = 'Billede';
    $columns['requirements'] = 'Krav';
    $columns['class_requirements'] = 'Klasse krav';

    // Genindsæt datoen, hvis fjernet
    $columns['date'] = 'Dato';
    return $columns;
}

add_action('manage_hardware_posts_custom_column', 'custom_hardware_columns_content', 10, 2);
function custom_hardware_columns_content($column, $post_id) {
    switch ($column) {
        case 'hardware_type':
            $type = get_post_meta($post_id, 'hardware_type', true);
            echo !empty($type) ? esc_html($type) : 'Ikke angivet';
            break;

        case 'hardware_img':
            $image = get_post_meta($post_id, 'hardware_img', true);
            if ($image) {
                echo '<img src="' . esc_url($image) . '" alt="Hardware Image" style="max-width: 50px; height: auto;">';
            } else {
                echo 'Intet billede';
            }
            break;

        case 'requirements':
            $requirements = get_post_meta($post_id, 'requirements', true);
            echo !empty($requirements) ? esc_html($requirements) : 'Ikke angivet';
            break;
        
        case 'class_requirements':
            $class_requirements = get_post_meta($post_id, 'class_requirements', true);
            echo !empty($class_requirements) ? esc_html($class_requirements) : 'Ikke angivet';
            break;
    }
}

add_filter('manage_games_posts_columns', 'custom_games_columns');
function custom_games_columns($columns) {
    // Fjern standardkolonner, hvis nødvendigt
    unset($columns['date']); // Fjerner "Dato" kolonnen (valgfrit)

    // Tilføj brugerdefinerede kolonner
  	$columns['game_img'] = 'Billede';
    $columns['requirements'] = 'Krav';

    // Genindsæt datoen, hvis fjernet
    $columns['date'] = 'Dato';
    return $columns;
}

add_action('manage_games_posts_custom_column', 'custom_games_columns_content', 10, 2);
function custom_games_columns_content($column, $post_id) {
    switch ($column) {
       
        case 'game_img':
            $image = get_post_meta($post_id, 'game_img', true);
            if ($image) {
                echo '<img src="' . esc_url($image) . '" alt="Game Image" style="max-width: 50px; height: auto;">';
            } else {
                echo 'Intet billede';
            }
            break;

        case 'requirements':
            $requirements = get_post_meta($post_id, 'requirements', true);
            echo !empty($requirements) ? esc_html($requirements) : 'Ikke angivet';
            break;
    }
}

function add_hardware_filters() {
    global $typenow;

    if ($typenow === 'hardware') { // Kun til hardware-posttypen
        // Filter for hardware_type
        $selected_type = isset($_GET['hardware_type']) ? $_GET['hardware_type'] : '';
        $types = array(
            'processor' => 'Processor',
            'grafik' => 'Grafik',
            'ram' => 'RAM',
            'lagring' => 'Lagring',
            'kabinet' => 'Kabinet',
            'køling' => 'Køling',
            'strømforsyning' => 'Strømforsyning',
            'bundkort' => 'Bundkort',
        );
        ?>
        <select name="hardware_type">
            <option value=""><?php _e('Alle typer', 'textdomain'); ?></option>
            <?php foreach ($types as $key => $label) : ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_type, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php

        // Filter for requirements
        $selected_req = isset($_GET['requirements']) ? $_GET['requirements'] : '';
        $requirements = array(
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        );
        ?>
        <select name="requirements">
            <option value=""><?php _e('Alle krav', 'textdomain'); ?></option>
            <?php foreach ($requirements as $key => $label) : ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_req, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
}
add_action('restrict_manage_posts', 'add_hardware_filters');

function filter_hardware_by_filters($query) {
    global $pagenow, $typenow;

    if ($pagenow === 'edit.php' && $typenow === 'hardware') {
        $meta_query = array();

        // Filter for hardware_type
        if (isset($_GET['hardware_type']) && !empty($_GET['hardware_type'])) {
            $meta_query[] = array(
                'key' => 'hardware_type',
                'value' => sanitize_text_field($_GET['hardware_type']),
                'compare' => '='
            );
        }

        // Filter for requirements
        if (isset($_GET['requirements']) && !empty($_GET['requirements'])) {
            $meta_query[] = array(
                'key' => 'requirements',
                'value' => sanitize_text_field($_GET['requirements']),
                'compare' => '='
            );
        }

        // Hvis der er filters, tilføj dem til query
        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'filter_hardware_by_filters');