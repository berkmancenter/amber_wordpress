<?php
class AmberSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Amber Settings', 
            'Amber Settings', 
            'manage_options', 
            'amber-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'amber_options' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Amber Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'amber_option_group' );   
                do_settings_sections( 'amber-settings-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        add_settings_section(
            'amber_cache_section', 
            'Storage Settings', 
            array( $this, 'print_cache_section_info' ), 
            'amber-settings-admin' // Page
        );  

        add_settings_field(
            'amber_max_file', 
            'Maximum file size (kB)', 
            array( $this, 'amber_max_file_callback' ), 
            'amber-settings-admin', 
            'amber_cache_section'          
        );      

        add_settings_field(
            'amber_max_disk', 
            'Maximum disk usage (MB)', 
            array( $this, 'amber_max_disk_callback' ), 
            'amber-settings-admin', 
            'amber_cache_section'
        );      

        add_settings_field(
            'amber_storage_location', 
            'Storage location', 
            array( $this, 'amber_storage_location_callback' ), 
            'amber-settings-admin', 
            'amber_cache_section'
        );      

        add_settings_section(
            'amber_delivery_section',
            'Amber Delivery', 
            array( $this, 'print_delivery_section_info' ), 
            'amber-settings-admin' 
        );  

        add_settings_field(
            'amber_available_action', 
            'Available links', 
            array( $this, 'amber_available_action_callback' ), 
            'amber-settings-admin', 
            'amber_delivery_section'
        );      

        add_settings_field(
            'amber_available_action_hover', 
            'Hover delay (seconds)', 
            array( $this, 'amber_available_action_hover_callback' ), 
            'amber-settings-admin', 
            'amber_delivery_section'
        );      

        add_settings_field(
            'amber_unavailable_action', 
            'Unavailable links', 
            array( $this, 'amber_unavailable_action_callback' ), 
            'amber-settings-admin', 
            'amber_delivery_section'
        );    

        add_settings_field(
            'amber_unavailable_action_hover', 
            'Hover delay (seconds)', 
            array( $this, 'amber_unavailable_action_hover_callback' ), 
            'amber-settings-admin', 
            'amber_delivery_section'
        );      

        register_setting(
            'amber_option_group',       // Option group
            'amber_options',            // Option name
            array( $this, 'sanitize' )  // Sanitize
        );

        Amber::disk_space_purge();
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        $valid_integer_options = array(
            'amber_max_file',
            'amber_max_disk',
            'amber_available_action',
            'amber_unavailable_action',
            'amber_available_action_hover',
            'amber_unavailable_action_hover',
            );
        foreach ($valid_integer_options as $opt) {
            if( isset( $input[$opt] ) )
                $new_input[$opt] = absint( $input[$opt] );
        }

        if( isset( $input['amber_storage_location'] ) )
            $new_input['amber_storage_location'] = sanitize_text_field( $input['amber_storage_location'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_cache_section_info()
    {
        print 'Control how Amber stores captures';
    }

    public function print_delivery_section_info()
    {
        print 'Settings that control the user experience';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function amber_max_file_callback()
    {
        printf(
            '<input type="text" id="amber_max_file" name="amber_options[amber_max_file]" value="%s" /> ' .
            '<p class="description">Amber will store captures up to a specified size. Links to pages that exceed this size will not be preserved.</p>',
            isset( $this->options['amber_max_file'] ) ? esc_attr( $this->options['amber_max_file']) : ''
        );
    }

    public function amber_max_disk_callback()
    {
        printf(
            '<input type="text" id="amber_max_disk" name="amber_options[amber_max_disk]" value="%s" />' .
            '<p class="description">The maximum amount of disk space to be used for all cached items. If this disk space usage is exceeded, old items will be removed from the cache.</p>',
            isset( $this->options['amber_max_disk'] ) ? esc_attr( $this->options['amber_max_disk']) : ''
        );
    }

    public function amber_available_action_callback()
    {
        $option = $this->options['amber_available_action'];
        ?>
            <select id="amber_available_action" name="amber_options[amber_available_action]">
                <option value="<?php echo AMBER_ACTION_NONE; ?>" <?php if ( $option == AMBER_ACTION_NONE ) { echo 'selected="selected"'; } ?>>None</option>
                <option value="<?php echo AMBER_ACTION_HOVER; ?>" <?php if ( $option == AMBER_ACTION_HOVER ) { echo 'selected="selected"'; } ?>>Hover</option>
                <option value="<?php echo AMBER_ACTION_POPUP; ?>" <?php if ( $option == AMBER_ACTION_POPUP ) { echo 'selected="selected"'; } ?>>Link to Popup</option>
            </select> 
            <p class="description">How a visitor to your site will experience links to pages that are currently available.</p>
        <?php
    }

    public function amber_unavailable_action_callback()
    {
        $option = $this->options['amber_unavailable_action'];
        ?>
            <select id="amber_unavailable_action" name="amber_options[amber_unavailable_action]">
                <option value="<?php echo AMBER_ACTION_NONE; ?>" <?php if ( $option == AMBER_ACTION_NONE ) { echo 'selected="selected"'; } ?>>None</option>
                <option value="<?php echo AMBER_ACTION_HOVER; ?>" <?php if ( $option == AMBER_ACTION_HOVER ) { echo 'selected="selected"'; } ?>>Hover</option>
                <option value="<?php echo AMBER_ACTION_POPUP; ?>" <?php if ( $option == AMBER_ACTION_POPUP ) { echo 'selected="selected"'; } ?>>Link to Popup</option>
                <option value="<?php echo AMBER_ACTION_CACHE; ?>" <?php if ( $option == AMBER_ACTION_CACHE ) { echo 'selected="selected"'; } ?>>Link to Cache</option>
            </select> 
            <p class="description">How a visitor to your site will experience links to pages that are currently unavailable.</p>
        <?php
    }

    public function amber_available_action_hover_callback()
    {
        printf(
            '<input type="text" id="amber_available_action_hover" name="amber_options[amber_available_action_hover]" value="%s" />' .
            '<p class="description">Delay before "Site Available" notification appears to a visitor on your site.</p>',
            isset( $this->options['amber_available_action_hover'] ) ? esc_attr( $this->options['amber_available_action_hover']) : ''
        );
    }

    public function amber_unavailable_action_hover_callback()
    {
        printf(
            '<input type="text" id="amber_unavailable_action_hover" name="amber_options[amber_unavailable_action_hover]" value="%s" />' .
            '<p class="description">Delay before "Site Unavailable" notification appears to a visitor on your site.</p>',
            isset( $this->options['amber_unavailable_action_hover'] ) ? esc_attr( $this->options['amber_unavailable_action_hover']) : ''
        );
    }


    /** 
     * Get the settings option array and print one of its values
     */
    public function amber_storage_location_callback()
    {
        printf(
            '<input type="text" id="amber_storage_location" name="amber_options[amber_storage_location]" value="%s" />' . 
            '<p class="description">Path to the location where captures are stored on disk, relative to the uploads directory.</p>',
            isset( $this->options['amber_storage_location'] ) ? esc_attr( $this->options['amber_storage_location']) : ''
        );
    }
}

include_once dirname( __FILE__ ) . '/amber.php';

if( is_admin() )
    $my_settings_page = new AmberSettingsPage();
