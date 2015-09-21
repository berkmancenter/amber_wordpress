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
            'amber_post_types', 
            'Included post types', 
            array( $this, 'amber_post_types_callback' ), 
            'amber-settings-admin', 
            'amber_cache_section'          
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

        add_settings_field(
            'amber_update_strategy', 
            'Update strategy for captures', 
            array( $this, 'amber_update_strategy_callback' ), 
            'amber-settings-admin', 
            'amber_cache_section'
        );      

        add_settings_field(
            'amber_excluded_sites', 
            'Excluded URL Patterns', 
            array( $this, 'amber_excluded_sites_callback' ), 
            'amber-settings-admin', 
            'amber_cache_section'
        );      

        add_settings_field(
            'amber_excluded_formats', 
            'Excluded file formats', 
            array( $this, 'amber_excluded_formats_callback' ), 
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

        add_settings_field(
            'amber_country_id', 
            'Country', 
            array( $this, 'amber_country_id_callback' ), 
            'amber-settings-admin', 
            'amber_delivery_section'
        );      

        add_settings_field(
            'amber_country_available_action', 
            'Available links', 
            array( $this, 'amber_country_available_action_callback' ), 
            'amber-settings-admin', 
            'amber_delivery_section'
        );      

        add_settings_field(
            'amber_country_available_action_hover', 
            'Hover delay (seconds)', 
            array( $this, 'amber_country_available_action_hover_callback' ), 
            'amber-settings-admin', 
            'amber_delivery_section'
        );      

        add_settings_field(
            'amber_country_unavailable_action', 
            'Unavailable links', 
            array( $this, 'amber_country_unavailable_action_callback' ), 
            'amber-settings-admin', 
            'amber_delivery_section'
        );    

        add_settings_field(
            'amber_country_unavailable_action_hover', 
            'Hover delay (seconds)', 
            array( $this, 'amber_country_unavailable_action_hover_callback' ), 
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
            'amber_country_available_action',
            'amber_country_unavailable_action',
            'amber_country_available_action_hover',
            'amber_country_unavailable_action_hover',
            'amber_update_strategy',
            );
        foreach ($valid_integer_options as $opt) {
            if( isset( $input[$opt] ) )
                $new_input[$opt] = absint( $input[$opt] );
        }

        $valid_string_options = array(
            'amber_storage_location',
            'amber_excluded_formats',
            'amber_country_id'
            );
        foreach ($valid_string_options as $opt) {
            if( isset( $input[$opt] ) )
                $new_input[$opt] = sanitize_text_field( $input[$opt] );
        }


        /* Process selected post types */
        $crawled_post_types = $input['amber_post_types'];
        $stringified_post_types = implode(',', $crawled_post_types);
        $new_input['amber_post_types'] = $stringified_post_types;

        /* Validate excluded sites regular expressions */
        $excluded_sites = explode( ',' , $input['amber_excluded_sites'] );
        $sanitized_excluded_sites = array();
        foreach ($excluded_sites as $site) {
            $blacklistitem = preg_replace("/https?:\\/\\//i", "", trim($site));
            if ($blacklistitem) {
                $blacklistitem = str_replace("@", "\@", $blacklistitem); 
                $blacklistitem = '@' . $blacklistitem . '@';              
                /* Hide warning messages from preg_match() that can be generated by
                   invalid user-entered regular expressions. */
                $default_error_logging_level = error_reporting();
                error_reporting(0);
                $match_result = preg_match($blacklistitem, "foobar");            
                error_reporting($default_error_logging_level);                  
                if ($match_result === FALSE) {
                    add_settings_error('amber_excluded_sites', 'amber_excluded_sites', 
                        "'${site}' is not a valid regular expression for Excluded URL Patterns");
                } else {
                    $sanitized_excluded_sites[] = $site;
                }
            }
        }
        $new_input['amber_excluded_sites'] = sanitize_text_field( implode( ",", $sanitized_excluded_sites ) );
        
        return $new_input;

    }

    /** 
     * Print the Section text
     */
    public function print_cache_section_info()
    {
        print 'Control how Amber stores captures';
    }

    /* As well as printing the section text for the delivery info, add some javscript
       to show and hide fields as appropriate.
     */
    public function print_delivery_section_info()
    {
        print '
Settings that control the user experience
<script type="text/javascript" >';
        print "var hover_state='" . AMBER_ACTION_HOVER . "';";
        print '
jQuery(document).ready(function($) {
    $("#amber_available_action").change(function(e) { $("#amber_available_action_hover").parent().parent().toggle($(this).val() == hover_state); });
    $("#amber_unavailable_action").change(function(e) { $("#amber_unavailable_action_hover").parent().parent().toggle($(this).val() == hover_state); });
    $("#amber_country_available_action").change(function(e) { $("#amber_country_available_action_hover").parent().parent().toggle($(this).val() == hover_state); });
    $("#amber_country_unavailable_action").change(function(e) { $("#amber_country_unavailable_action_hover").parent().parent().toggle($(this).val() == hover_state); });
    $("#amber_country_id").change(function(e) { 
        $(".country_field").parent().parent().toggle($(this).val() != ""); 
        if ($(this).val() != "") {
            $("#amber_country_available_action").change();   
            $("#amber_country_unavailable_action").change();           
        }
    });
    $("#amber_country_id").change();   
    $("#amber_available_action").change();   
    $("#amber_unavailable_action").change();   
});</script>';            

    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function amber_post_types_callback()
    {
        $amber_post_types_args = array(
           'public'   => true
        );        
        $output = 'objects';
        $operator = 'and';
        $post_types = get_post_types( $args, $output, $operator ); 

        $crawled_post_types = explode(',',$this->options['amber_post_types']);
        $ignored_post_types = array('revision');

        printf('<select id="amber_post_types" name="amber_options[amber_post_types][]" multiple="1" size="5">' . PHP_EOL);
        foreach ( $post_types  as $post_type ) {

            if(in_array( $post_type->name, $ignored_post_types )) {
                continue;
            } elseif (in_array($post_type->name, $crawled_post_types)) {
                echo '<option value="'. $post_type->name .'" selected="1">' . $post_type->label . '</option>' . PHP_EOL;
            } else {
                echo '<option value="'. $post_type->name .'">' . $post_type->label . '</option>' . PHP_EOL;
            }         
        }
        printf('</select>' . PHP_EOL);

    }

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

    public function amber_storage_location_callback()
    {
        printf(
            '<input type="text" id="amber_storage_location" name="amber_options[amber_storage_location]" value="%s" />' . 
            '<p class="description">Path to the location where captures are stored on disk, relative to the uploads directory.</p>',
            isset( $this->options['amber_storage_location'] ) ? esc_attr( $this->options['amber_storage_location']) : ''
        );
    }

    public function amber_update_strategy_callback()
    {
        $option = $this->options['amber_update_strategy'];
        ?>
            <select id="amber_update_strategy" name="amber_options[amber_update_strategy]">
                <option value="0" <?php if ( $option == 0 ) { echo 'selected="selected"'; } ?>>Update captures periodically</option>
                <option value="1" <?php if ( $option == 1 ) { echo 'selected="selected"'; } ?>>Do not update captures</option>
            </select> 
            <p class="description">Select "Do not update" if you want to preserve links at the time the content is published. Otherwise, link storage will be periodically updated.</p>
        <?php
    }

    public function amber_excluded_sites_callback()
    {
        printf(
            '<textarea rows="5" cols="40" id="amber_excluded_sites" name="amber_options[amber_excluded_sites]">%s</textarea>' .
            '<p class="description">A list of URL patterns, separated by commas. Amber will not preserve any link that matches one of these patterns. Regular expressions may be used.</p>',
            isset( $this->options['amber_excluded_sites'] ) ? esc_textarea( $this->options['amber_excluded_sites']) : ''
        );
    }

    public function amber_excluded_formats_callback()
    {
        printf(
            '<textarea rows="5" cols="40" id="amber_excluded_formats" name="amber_options[amber_excluded_formats]">%s</textarea>' .
            '<p class="description">A list of of MIME types, separated by commas. Amber will not preserve any link containing an excluded MIME type.</p>',
            isset( $this->options['amber_excluded_formats'] ) ? esc_textarea( $this->options['amber_excluded_formats']) : ''
        );
    }


    public function amber_available_action_callback()
    {
        $option = isset($this->options['amber_available_action']) ? $this->options['amber_available_action'] : AMBER_ACTION_NONE;
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
        $option = isset($this->options['amber_unavailable_action']) ? $this->options['amber_unavailable_action'] : AMBER_ACTION_NONE;
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

    public function amber_country_id_callback()
    {
        $option = isset($this->options['amber_country_id']) ? $this->options['amber_country_id'] : "";
        ?>
            <select id="amber_country_id" name="amber_options[amber_country_id]">
            <?php 
                $countries = $this->get_countries();
                print '<option value="" ' . ((!$option) ? 'selected="selected"' : '') . '></option>'; 
                foreach ($countries as $key => $value) {
                    print '<option value="' . $key . '" ' . (($option == $key) ? 'selected="selected"' : '') . '>' . $value . '</option>'; 
                }
            ?>
            </select> 
            <p class="description">Visitors to your website with browser IP addresses originating in this country will experience specified behavior.</p>
        <?php
    }

    public function amber_country_available_action_callback()
    {
        $option = isset($this->options['amber_country_available_action']) ? $this->options['amber_country_available_action'] : AMBER_ACTION_NONE;
        ?>
            <select class="country_field" id="amber_country_available_action" name="amber_options[amber_country_available_action]">
                <option value="<?php echo AMBER_ACTION_NONE; ?>" <?php if ( $option == AMBER_ACTION_NONE ) { echo 'selected="selected"'; } ?>>None</option>
                <option value="<?php echo AMBER_ACTION_HOVER; ?>" <?php if ( $option == AMBER_ACTION_HOVER ) { echo 'selected="selected"'; } ?>>Hover</option>
                <option value="<?php echo AMBER_ACTION_POPUP; ?>" <?php if ( $option == AMBER_ACTION_POPUP ) { echo 'selected="selected"'; } ?>>Link to Popup</option>
            </select> 
            <p class="description">How a visitor to your site will experience links to pages that are currently available.</p>
        <?php
    }

    public function amber_country_unavailable_action_callback()
    {
        $option = isset($this->options['amber_country_unavailable_action']) ? $this->options['amber_country_unavailable_action'] : AMBER_ACTION_NONE;
        ?>
            <select class="country_field" id="amber_country_unavailable_action" name="amber_options[amber_country_unavailable_action]">
                <option value="<?php echo AMBER_ACTION_NONE; ?>" <?php if ( $option == AMBER_ACTION_NONE ) { echo 'selected="selected"'; } ?>>None</option>
                <option value="<?php echo AMBER_ACTION_HOVER; ?>" <?php if ( $option == AMBER_ACTION_HOVER ) { echo 'selected="selected"'; } ?>>Hover</option>
                <option value="<?php echo AMBER_ACTION_POPUP; ?>" <?php if ( $option == AMBER_ACTION_POPUP ) { echo 'selected="selected"'; } ?>>Link to Popup</option>
                <option value="<?php echo AMBER_ACTION_CACHE; ?>" <?php if ( $option == AMBER_ACTION_CACHE ) { echo 'selected="selected"'; } ?>>Link to Cache</option>
            </select> 
            <p class="description">How a visitor to your site will experience links to pages that are currently unavailable.</p>
        <?php
    }

    public function amber_country_available_action_hover_callback()
    {
        printf(
            '<input class="country_field" type="text" id="amber_country_available_action_hover" name="amber_options[amber_country_available_action_hover]" value="%s" />' .
            '<p class="description">Delay before "Site Available" notification appears to a visitor on your site.</p>',
            isset( $this->options['amber_country_available_action_hover'] ) ? esc_attr( $this->options['amber_country_available_action_hover']) : ''
        );
    }

    public function amber_country_unavailable_action_hover_callback()
    {
        printf(
            '<input class="country_field" type="text" id="amber_country_unavailable_action_hover" name="amber_options[amber_country_unavailable_action_hover]" value="%s" />' .
            '<p class="description">Delay before "Site Unavailable" notification appears to a visitor on your site.</p>',
            isset( $this->options['amber_country_unavailable_action_hover'] ) ? esc_attr( $this->options['amber_country_unavailable_action_hover']) : ''
        );
    }

    private function get_countries()
    {
        $countries = array(
          'AD' => 'Andorra',
          'AE' => 'United Arab Emirates',
          'AF' => 'Afghanistan',
          'AG' => 'Antigua and Barbuda',
          'AI' => 'Anguilla',
          'AL' => 'Albania',
          'AM' => 'Armenia',
          'AN' => 'Netherlands Antilles',
          'AO' => 'Angola',
          'AQ' => 'Antarctica',
          'AR' => 'Argentina',
          'AS' => 'American Samoa',
          'AT' => 'Austria',
          'AU' => 'Australia',
          'AW' => 'Aruba',
          'AX' => 'Aland Islands',
          'AZ' => 'Azerbaijan',
          'BA' => 'Bosnia and Herzegovina',
          'BB' => 'Barbados',
          'BD' => 'Bangladesh',
          'BE' => 'Belgium',
          'BF' => 'Burkina Faso',
          'BG' => 'Bulgaria',
          'BH' => 'Bahrain',
          'BI' => 'Burundi',
          'BJ' => 'Benin',
          'BL' => 'Saint Barthélemy',
          'BM' => 'Bermuda',
          'BN' => 'Brunei',
          'BO' => 'Bolivia',
          'BR' => 'Brazil',
          'BS' => 'Bahamas',
          'BT' => 'Bhutan',
          'BV' => 'Bouvet Island',
          'BW' => 'Botswana',
          'BY' => 'Belarus',
          'BZ' => 'Belize',
          'CA' => 'Canada',
          'CC' => 'Cocos (Keeling) Islands',
          'CD' => 'Congo (Kinshasa)',
          'CF' => 'Central African Republic',
          'CG' => 'Congo (Brazzaville)',
          'CH' => 'Switzerland',
          'CI' => 'Ivory Coast',
          'CK' => 'Cook Islands',
          'CL' => 'Chile',
          'CM' => 'Cameroon',
          'CN' => 'China',
          'CO' => 'Colombia',
          'CR' => 'Costa Rica',
          'CU' => 'Cuba',
          'CW' => 'Curaçao',
          'CV' => 'Cape Verde',
          'CX' => 'Christmas Island',
          'CY' => 'Cyprus',
          'CZ' => 'Czech Republic',
          'DE' => 'Germany',
          'DJ' => 'Djibouti',
          'DK' => 'Denmark',
          'DM' => 'Dominica',
          'DO' => 'Dominican Republic',
          'DZ' => 'Algeria',
          'EC' => 'Ecuador',
          'EE' => 'Estonia',
          'EG' => 'Egypt',
          'EH' => 'Western Sahara',
          'ER' => 'Eritrea',
          'ES' => 'Spain',
          'ET' => 'Ethiopia',
          'FI' => 'Finland',
          'FJ' => 'Fiji',
          'FK' => 'Falkland Islands',
          'FM' => 'Micronesia',
          'FO' => 'Faroe Islands',
          'FR' => 'France',
          'GA' => 'Gabon',
          'GB' => 'United Kingdom',
          'GD' => 'Grenada',
          'GE' => 'Georgia',
          'GF' => 'French Guiana',
          'GG' => 'Guernsey',
          'GH' => 'Ghana',
          'GI' => 'Gibraltar',
          'GL' => 'Greenland',
          'GM' => 'Gambia',
          'GN' => 'Guinea',
          'GP' => 'Guadeloupe',
          'GQ' => 'Equatorial Guinea',
          'GR' => 'Greece',
          'GS' => 'South Georgia and the South Sandwich Islands',
          'GT' => 'Guatemala',
          'GU' => 'Guam',
          'GW' => 'Guinea-Bissau',
          'GY' => 'Guyana',
          'HK' => 'Hong Kong S.A.R., China',
          'HM' => 'Heard Island and McDonald Islands',
          'HN' => 'Honduras',
          'HR' => 'Croatia',
          'HT' => 'Haiti',
          'HU' => 'Hungary',
          'ID' => 'Indonesia',
          'IE' => 'Ireland',
          'IL' => 'Israel',
          'IM' => 'Isle of Man',
          'IN' => 'India',
          'IO' => 'British Indian Ocean Territory',
          'IQ' => 'Iraq',
          'IR' => 'Iran',
          'IS' => 'Iceland',
          'IT' => 'Italy',
          'JE' => 'Jersey',
          'JM' => 'Jamaica',
          'JO' => 'Jordan',
          'JP' => 'Japan',
          'KE' => 'Kenya',
          'KG' => 'Kyrgyzstan',
          'KH' => 'Cambodia',
          'KI' => 'Kiribati',
          'KM' => 'Comoros',
          'KN' => 'Saint Kitts and Nevis',
          'KP' => 'North Korea',
          'KR' => 'South Korea',
          'KW' => 'Kuwait',
          'KY' => 'Cayman Islands',
          'KZ' => 'Kazakhstan',
          'LA' => 'Laos',
          'LB' => 'Lebanon',
          'LC' => 'Saint Lucia',
          'LI' => 'Liechtenstein',
          'LK' => 'Sri Lanka',
          'LR' => 'Liberia',
          'LS' => 'Lesotho',
          'LT' => 'Lithuania',
          'LU' => 'Luxembourg',
          'LV' => 'Latvia',
          'LY' => 'Libya',
          'MA' => 'Morocco',
          'MC' => 'Monaco',
          'MD' => 'Moldova',
          'ME' => 'Montenegro',
          'MF' => 'Saint Martin (French part)',
          'MG' => 'Madagascar',
          'MH' => 'Marshall Islands',
          'MK' => 'Macedonia',
          'ML' => 'Mali',
          'MM' => 'Myanmar',
          'MN' => 'Mongolia',
          'MO' => 'Macao S.A.R., China',
          'MP' => 'Northern Mariana Islands',
          'MQ' => 'Martinique',
          'MR' => 'Mauritania',
          'MS' => 'Montserrat',
          'MT' => 'Malta',
          'MU' => 'Mauritius',
          'MV' => 'Maldives',
          'MW' => 'Malawi',
          'MX' => 'Mexico',
          'MY' => 'Malaysia',
          'MZ' => 'Mozambique',
          'NA' => 'Namibia',
          'NC' => 'New Caledonia',
          'NE' => 'Niger',
          'NF' => 'Norfolk Island',
          'NG' => 'Nigeria',
          'NI' => 'Nicaragua',
          'NL' => 'Netherlands',
          'NO' => 'Norway',
          'NP' => 'Nepal',
          'NR' => 'Nauru',
          'NU' => 'Niue',
          'NZ' => 'New Zealand',
          'OM' => 'Oman',
          'PA' => 'Panama',
          'PE' => 'Peru',
          'PF' => 'French Polynesia',
          'PG' => 'Papua New Guinea',
          'PH' => 'Philippines',
          'PK' => 'Pakistan',
          'PL' => 'Poland',
          'PM' => 'Saint Pierre and Miquelon',
          'PN' => 'Pitcairn',
          'PR' => 'Puerto Rico',
          'PS' => 'Palestinian Territory',
          'PT' => 'Portugal',
          'PW' => 'Palau',
          'PY' => 'Paraguay',
          'QA' => 'Qatar',
          'RE' => 'Reunion',
          'RO' => 'Romania',
          'RS' => 'Serbia',
          'RU' => 'Russia',
          'RW' => 'Rwanda',
          'SA' => 'Saudi Arabia',
          'SB' => 'Solomon Islands',
          'SC' => 'Seychelles',
          'SD' => 'Sudan',
          'SE' => 'Sweden',
          'SG' => 'Singapore',
          'SH' => 'Saint Helena',
          'SI' => 'Slovenia',
          'SJ' => 'Svalbard and Jan Mayen',
          'SK' => 'Slovakia',
          'SL' => 'Sierra Leone',
          'SM' => 'San Marino',
          'SN' => 'Senegal',
          'SO' => 'Somalia',
          'SR' => 'Suriname',
          'ST' => 'Sao Tome and Principe',
          'SV' => 'El Salvador',
          'SY' => 'Syria',
          'SZ' => 'Swaziland',
          'TC' => 'Turks and Caicos Islands',
          'TD' => 'Chad',
          'TF' => 'French Southern Territories',
          'TG' => 'Togo',
          'TH' => 'Thailand',
          'TJ' => 'Tajikistan',
          'TK' => 'Tokelau',
          'TL' => 'Timor-Leste',
          'TM' => 'Turkmenistan',
          'TN' => 'Tunisia',
          'TO' => 'Tonga',
          'TR' => 'Turkey',
          'TT' => 'Trinidad and Tobago',
          'TV' => 'Tuvalu',
          'TW' => 'Taiwan',
          'TZ' => 'Tanzania',
          'UA' => 'Ukraine',
          'UG' => 'Uganda',
          'UM' => 'United States Minor Outlying Islands',
          'US' => 'United States',
          'UY' => 'Uruguay',
          'UZ' => 'Uzbekistan',
          'VA' => 'Vatican',
          'VC' => 'Saint Vincent and the Grenadines',
          'VE' => 'Venezuela',
          'VG' => 'British Virgin Islands',
          'VI' => 'U.S. Virgin Islands',
          'VN' => 'Vietnam',
          'VU' => 'Vanuatu',
          'WF' => 'Wallis and Futuna',
          'WS' => 'Samoa',
          'YE' => 'Yemen',
          'YT' => 'Mayotte',
          'ZA' => 'South Africa',
          'ZM' => 'Zambia',
          'ZW' => 'Zimbabwe',
        );
        natcasesort($countries);
        return $countries;
    }
}

include_once dirname( __FILE__ ) . '/amber.php';

if( is_admin() )
    $my_settings_page = new AmberSettingsPage();
