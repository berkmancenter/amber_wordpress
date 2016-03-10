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
            'amber_backend',
            'Backend to use for storing snapshots',
            array( $this, 'amber_backend_callback' ),
            'amber-settings-admin',
            'amber_cache_section'
        );

        add_settings_field(
            'amber_alternate_backends',
            'Alternate backend(s) to use for storing snapshots',
            array( $this, 'amber_alternate_backends_callback' ),
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
            'amber_perma_api_key',
            'Perma API key',
            array( $this, 'amber_perma_api_key_callback' ),
            'amber-settings-admin',
            'amber_cache_section'
        );

        add_settings_field(
            'amber_perma_server_url',
            'Perma URL',
            array( $this, 'amber_perma_server_url_callback' ),
            'amber-settings-admin',
            'amber_cache_section'
        );

        add_settings_field(
            'amber_perma_api_server_url',
            'Perma API URL',
            array( $this, 'amber_perma_api_server_url_callback' ),
            'amber-settings-admin',
            'amber_cache_section'
        );

        add_settings_field(
            'amber_aws_access_key',
            'AWS Access Key',
            array( $this, 'amber_aws_access_key_callback' ),
            'amber-settings-admin',
            'amber_cache_section'
        );

        add_settings_field(
            'amber_aws_secret_key',
            'AWS Secret Access Key',
            array( $this, 'amber_aws_secret_key_callback' ),
            'amber-settings-admin',
            'amber_cache_section'
        );

        add_settings_field(
            'amber_aws_bucket',
            'S3 Bucket',
            array( $this, 'amber_aws_bucket_callback' ),
            'amber-settings-admin',
            'amber_cache_section'
        );

        add_settings_field(
            'amber_aws_region',
            'S3 Region',
            array( $this, 'amber_aws_region_callback' ),
            'amber-settings-admin',
            'amber_cache_section'
        );
        add_settings_field(
            'amber_post_types',
            'Included post types',
            array( $this, 'amber_post_types_callback' ),
            'amber-settings-admin',
            'amber_cache_section'
        );

        add_settings_field(
            'amber_update_strategy',
            'Update strategy for snapshots',
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

        add_settings_section(
            'amber_services_section',
            'Optional Functionality',
            array( $this, 'print_services_section_info' ),
            'amber-settings-admin' // Page
        );

        if (isset($this->options['amber_enable_netclerk']) && $this->options['amber_enable_netclerk']) {
            add_settings_field(
                'amber_external_availability',
                'Use a third-party database to check site availability',
                array( $this, 'amber_external_availability_callback' ),
                'amber-settings-admin',
                'amber_services_section'
            );

            add_settings_field(
                'amber_report_availability',
                'Inform a third-party database of site availability',
                array( $this, 'amber_report_availability_callback' ),
                'amber-settings-admin',
                'amber_services_section'
            );
        }

        add_settings_field(
            'amber_timegate',
            'Check a TimeGate server for additional snapshots',
            array( $this, 'amber_timegate_callback' ),
            'amber-settings-admin',
            'amber_services_section'
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
            'amber_backend',
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
            'amber_external_availability',
            'amber_report_availability',
            );
        foreach ($valid_integer_options as $opt) {
            if( isset( $input[$opt] ) )
                $new_input[$opt] = absint( $input[$opt] );
        }

        $valid_string_options = array(
            'amber_storage_location',
            'amber_excluded_formats',
            'amber_timegate',
            'amber_country_id',
            'amber_perma_api_key',
            'amber_perma_server_url',
            'amber_perma_api_server_url',
            'amber_aws_access_key',
            'amber_aws_secret_key',
            'amber_aws_bucket',
            'amber_aws_region',
            );
        foreach ($valid_string_options as $opt) {
            if( isset( $input[$opt] ) )
                $new_input[$opt] = sanitize_text_field( $input[$opt] );
        }

        if (isset($input['amber_alternate_backends'])) {
            foreach ($input['amber_alternate_backends'] as $key => $value) {
                $new_input['amber_alternate_backends'][$key] = sanitize_text_field($value);
            }
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

        /* Validate backend settings */
        if (($input['amber_backend'] == AMBER_BACKEND_PERMA) ||
                (isset($input['amber_alternate_backends']) && in_array(AMBER_BACKEND_PERMA, $input['amber_alternate_backends']))) {
            foreach (array('amber_perma_api_key', 'amber_perma_server_url', 'amber_perma_api_server_url') as $key)
            if (empty($input[$key]) ) {
                add_settings_error($key, $key, "API key is required for Perma storage");
                break;
            }
        }
        if (($input['amber_backend'] == AMBER_BACKEND_AMAZON_S3) ||
                (isset($input['amber_alternate_backends']) && in_array(AMBER_BACKEND_AMAZON_S3, $input['amber_alternate_backends']))) {
            foreach (array('amber_aws_access_key', 'amber_aws_secret_key', 'amber_aws_bucket', 'amber_aws_region') as $key)
            if (empty($input[$key]) ) {
                add_settings_error($key, $key, "AWS Access Key, Secret Key, Bucket, and Region are required for Amazon S3 storage");
                break;
            }
            /* Attempt to connect to AWS with provided credentials */
            try {
                require_once("vendor/aws/aws-autoloader.php");
                $storage = new AmazonS3Storage(array(
                      'access_key' => $input['amber_aws_access_key'],
                      'secret_key' => $input['amber_aws_secret_key'],
                      'bucket' => $input['amber_aws_bucket'],
                      'region' => $input['amber_aws_region'],
                    ));
            } catch (Exception $e) {
                add_settings_error('amber_backend', 'amber_backend', "There is a problem with the provided Amazon
                configuration. Check that the access key and secret key are correct,
                and that they provide write access to the selected bucket. Ensure that
                your bucket name is unique - it cannot have the same name as any other
                bucket in S3.");
            }
        }

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_cache_section_info()
    {
        print 'Control how Amber stores snapshots';
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
    $("#amber_backend").change(function(e) {
        var v = $("#amber_alternate_backends").val();
        if (v == null) { v = []; }
        $(".local").parent().parent().toggle($(this).val() == ' . AMBER_BACKEND_LOCAL . ');
        $(".perma").parent().parent().toggle(($("#amber_backend").val() == ' . AMBER_BACKEND_PERMA . ') || (v.indexOf("' . AMBER_BACKEND_PERMA . '") != -1));
        $(".perma-hidden").parent().parent().toggle($(this).val() == "Hidden Perma fields");
        $(".ia").parent().parent().toggle(($("#amber_backend").val() == ' . AMBER_BACKEND_INTERNET_ARCHIVE . ') || (v.indexOf("' . AMBER_BACKEND_INTERNET_ARCHIVE . '") != -1));
        $(".aws").parent().parent().toggle($(this).val() == ' . AMBER_BACKEND_AMAZON_S3 . ');
    });
    $("#amber_alternate_backends").change(function(e) {
        var v = $("#amber_alternate_backends").val();
        if (v == null) { v = []; }
        $(".perma").parent().parent().toggle(($("#amber_backend").val() == ' . AMBER_BACKEND_PERMA . ') || (v.indexOf("' . AMBER_BACKEND_PERMA . '") != -1));
        $(".ia").parent().parent().toggle(($("#amber_backend").val() == ' . AMBER_BACKEND_INTERNET_ARCHIVE . ') || (v.indexOf("' . AMBER_BACKEND_INTERNET_ARCHIVE . '") != -1));
    });
    $("#amber_country_id").change();
    $("#amber_available_action").change();
    $("#amber_unavailable_action").change();
    $("#amber_backend").change();

});</script>';

    }

    public function print_services_section_info()
    {
        print 'Connect to academic efforts to retrieve more accurate data and additional snapshots';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function amber_backend_callback()
    {
        $option = isset($this->options['amber_backend']) ? $this->options['amber_backend'] : 0;
        ?>
            <select id="amber_backend" name="amber_options[amber_backend]">
                <option value="<?php echo AMBER_BACKEND_LOCAL; ?>" <?php if ( $option == AMBER_BACKEND_LOCAL ) { echo 'selected="selected"'; } ?>>Local</option>
                <option value="<?php echo AMBER_BACKEND_PERMA; ?>" <?php if ( $option == AMBER_BACKEND_PERMA ) { echo 'selected="selected"'; } ?>>Perma.cc</option>
                <option value="<?php echo AMBER_BACKEND_INTERNET_ARCHIVE; ?>" <?php if ( $option == AMBER_BACKEND_INTERNET_ARCHIVE ) { echo 'selected="selected"'; } ?>>Internet Archive</option>
                <?php if (version_compare(PHP_VERSION, "5.3.3") >= 0) { ?>
                    <option value="<?php echo AMBER_BACKEND_AMAZON_S3; ?>" <?php if ( $option == AMBER_BACKEND_AMAZON_S3 ) { echo 'selected="selected"'; } ?>>Amazon Web Services S3</option>
                <?php } ?>
            </select>
            <p class="description">Amber can store snapshots locally, in your website's storage space. If you prefer, you can store snapshots in an alternative backend. At this time, Amber is compatible with the following services: <a href="https://perma.cc/" target="_blank">Perma.cc</a>, the <a href="https://archive.org" target="_blank">Internet Archive</a>, and <a href="https://aws.amazon.com/s3/">Amazon S3</a>.</p>
        <?php
    }

    public function amber_alternate_backends_callback()
    {
        $options = isset($this->options['amber_alternate_backends']) ? $this->options['amber_alternate_backends'] : 0;
        ?>
            <select id="amber_alternate_backends" name="amber_options[amber_alternate_backends][]" multiple>
                <option value="<?php echo AMBER_BACKEND_PERMA; ?>" <?php if ( is_array($options) && in_array(AMBER_BACKEND_PERMA, $options) ) { echo 'selected="selected"'; } ?>>Perma.cc</option>
                <option value="<?php echo AMBER_BACKEND_INTERNET_ARCHIVE; ?>" <?php if ( is_array($options) && in_array(AMBER_BACKEND_INTERNET_ARCHIVE, $options) ) { echo 'selected="selected"'; } ?>>Internet Archive</option>
            </select>
            <p class="description">Preserve snapshots in multiple storage locations by selecting one or more alternates above. Amber will show your visitors only the storage location selected in the dropdown menu.</p>
        <?php
    }

    public function amber_post_types_callback()
    {
        $options = isset($this->options['amber_post_types']) ? $this->options['amber_post_types'] : "post,page";

        $all_post_types = get_post_types( array(), 'objects', 'and' );
        $crawled_post_types = explode( ',', $options );
        $ignored_post_types = array( 'revision', 'attachment', 'nav_menu_item');
        printf('<select id="amber_post_types" name="amber_options[amber_post_types][]" multiple="1" size="5">' . PHP_EOL);
        foreach ( $all_post_types as $post_type ) {
            if ( in_array( $post_type->name, $ignored_post_types ) ) {
                continue;
            } elseif ( in_array( $post_type->name, $crawled_post_types ) ) {
                echo '<option value="'. $post_type->name .'" selected="1">' . $post_type->label . '</option>' . PHP_EOL;
            } else {
                echo '<option value="'. $post_type->name .'">' . $post_type->label . '</option>' . PHP_EOL;
            }
        }
        printf('</select><p class="description">Preserve snapshots from particular <a href="https://codex.wordpress.org/Post_Types" target="_blank">post types</a>, including custom post types.</p>');
    }

    public function amber_max_file_callback()
    {
        printf(
            '<input type="text" id="amber_max_file" name="amber_options[amber_max_file]" class="local" value="%s" /> ' .
            '<p class="description">Amber will store snapshots up to a specified size. Links to pages that exceed this size will not be preserved.</p>',
            isset( $this->options['amber_max_file'] ) ? esc_attr( $this->options['amber_max_file']) : ''
        );
    }

    public function amber_max_disk_callback()
    {
        printf(
            '<input type="text" id="amber_max_disk" name="amber_options[amber_max_disk]" class="local" value="%s" />' .
            '<p class="description">The maximum amount of disk space to be used for all preserved content. If this disk space usage is exceeded, old snapshots will be removed.</p>',
            isset( $this->options['amber_max_disk'] ) ? esc_attr( $this->options['amber_max_disk']) : ''
        );
    }

    public function amber_storage_location_callback()
    {
        printf(
            '<input type="text" id="amber_storage_location" name="amber_options[amber_storage_location]" class="local" value="%s" />' .
            '<p class="description">Path to the location where snapshots are stored on disk, relative to the uploads directory.</p>',
            isset( $this->options['amber_storage_location'] ) ? esc_attr( $this->options['amber_storage_location']) : ''
        );
    }

    public function amber_update_strategy_callback()
    {
        $option = $this->options['amber_update_strategy'];
        ?>
            <select id="amber_update_strategy" name="amber_options[amber_update_strategy]" class="local">
                <option value="0" <?php if ( $option == 0 ) { echo 'selected="selected"'; } ?>>Update snapshots periodically</option>
                <option value="1" <?php if ( $option == 1 ) { echo 'selected="selected"'; } ?>>Do not update snapshots</option>
            </select>
            <p class="description">Select "Do not update" if you want to preserve links at the time the content is published. Otherwise, link storage will be periodically updated.</p>
        <?php
    }

    public function amber_excluded_sites_callback()
    {
        printf(
            '<textarea rows="5" cols="40" id="amber_excluded_sites" name="amber_options[amber_excluded_sites]" class="local">%s</textarea>' .
            '<p class="description">A list of URL patterns, separated by commas. Amber will not preserve any link that matches one of these patterns. Regular expressions may be used.</p>',
            isset( $this->options['amber_excluded_sites'] ) ? esc_textarea( $this->options['amber_excluded_sites']) : ''
        );
    }

    public function amber_excluded_formats_callback()
    {
        printf(
            '<textarea rows="5" cols="40" id="amber_excluded_formats" name="amber_options[amber_excluded_formats]" class="local">%s</textarea>' .
            '<p class="description">A list of of MIME types, separated by commas. Amber will not preserve any link containing an excluded MIME type.</p>',
            isset( $this->options['amber_excluded_formats'] ) ? esc_textarea( $this->options['amber_excluded_formats']) : ''
        );
    }

    public function amber_perma_api_key_callback()
    {
        printf(
            '<input type="text" id="amber_perma_api_key" name="amber_options[amber_perma_api_key]" class="perma" value="%s" />' .
            '<p class="description">Generate an API key in your Perma.cc Dashboard under Settings &gt; Tools</p>',
            isset( $this->options['amber_perma_api_key'] ) ? esc_attr( $this->options['amber_perma_api_key']) : ''
        );
    }

    public function amber_perma_server_url_callback()
    {
        printf(
            '<input type="text" id="amber_perma_server_url" name="amber_options[amber_perma_server_url]" class="perma-hidden" value="%s" />' .
            '<p class="description">This should not need to be changed</p>',
            isset( $this->options['amber_perma_server_url'] ) ? esc_attr( $this->options['amber_perma_server_url']) : ''
        );
    }

    public function amber_perma_api_server_url_callback()
    {
        printf(
            '<input type="text" id="amber_perma_api_server_url" name="amber_options[amber_perma_api_server_url]" class="perma-hidden" value="%s" />' .
            '<p class="description">This should not need to be changed</p>',
            isset( $this->options['amber_perma_api_server_url'] ) ? esc_attr( $this->options['amber_perma_api_server_url']) : ''
        );
    }

    public function amber_aws_access_key_callback()
    {
        printf(
            '<input type="text" id="amber_aws_access_key" name="amber_options[amber_aws_access_key]" class="aws" value="%s" />' .
            '<p class="description">Visit <a href="http://docs.aws.amazon.com/general/latest/gr/managing-aws-access-keys.html" target="_blank">Managing Access Keys for your AWS Account</a> for instructions to generate an access key.</p>',
            isset( $this->options['amber_aws_access_key'] ) ? esc_attr( $this->options['amber_aws_access_key']) : ''
        );
    }

    public function amber_aws_secret_key_callback()
    {
        printf(
            '<input type="text" id="amber_aws_secret_key" name="amber_options[amber_aws_secret_key]" class="aws" value="%s" />' .
            '<p class="description">Visit <a href="http://docs.aws.amazon.com/general/latest/gr/managing-aws-access-keys.html" target="_blank">Managing Access Keys for your AWS Account</a> for instructions to generate a secret access key.</p>',
            isset( $this->options['amber_aws_secret_key'] ) ? esc_attr( $this->options['amber_aws_secret_key']) : ''
        );
    }

    public function amber_aws_bucket_callback()
    {
        printf(
            '<input type="text" id="amber_aws_bucket" name="amber_options[amber_aws_bucket]" class="aws" value="%s" />' .
            '<p class="description">Name of the Bucket where snapshots will be stored</p>',
            isset( $this->options['amber_aws_bucket'] ) ? esc_attr( $this->options['amber_aws_bucket']) : ''
        );
    }

    public function amber_aws_region_callback()
    {
        printf(
            '<input type="text" id="amber_aws_region" name="amber_options[amber_aws_region]" class="aws" value="%s" />' .
            '<p class="description">Your snapshots will be stored in this <a href="http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region" target="_blank">S3 region</a>. Unless you are an advanced user, do not modify this setting.</p>',
            isset( $this->options['amber_aws_region'] ) ? esc_attr( $this->options['amber_aws_region']) : ''
        );
    }


    public function amber_available_action_callback()
    {
        $option = isset($this->options['amber_available_action']) ? $this->options['amber_available_action'] : AMBER_ACTION_NONE;
        ?>
            <select id="amber_available_action" name="amber_options[amber_available_action]">
                <option value="<?php echo AMBER_ACTION_NONE; ?>" <?php if ( $option == AMBER_ACTION_NONE ) { echo 'selected="selected"'; } ?>>None</option>
                <option value="<?php echo AMBER_ACTION_HOVER; ?>" <?php if ( $option == AMBER_ACTION_HOVER ) { echo 'selected="selected"'; } ?>>Hover</option>
                <option value="<?php echo AMBER_ACTION_POPUP; ?>" <?php if ( $option == AMBER_ACTION_POPUP ) { echo 'selected="selected"'; } ?>>Link to Pop-up</option>
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
                <option value="<?php echo AMBER_ACTION_POPUP; ?>" <?php if ( $option == AMBER_ACTION_POPUP ) { echo 'selected="selected"'; } ?>>Link to Pop-up</option>
                <option value="<?php echo AMBER_ACTION_CACHE; ?>" <?php if ( $option == AMBER_ACTION_CACHE ) { echo 'selected="selected"'; } ?>>Link directly to Snapshot</option>
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

    public function amber_external_availability_callback()
    {
        $option = isset($this->options['amber_external_availability']) ? $this->options['amber_external_availability'] : AMBER_EXTERNAL_AVAILABILITY_NONE;
        ?>
            <select class="external_availability_field" id="amber_external_availability" name="amber_options[amber_external_availability]">
                <option value="<?php echo AMBER_EXTERNAL_AVAILABILITY_NONE; ?>" <?php if ( $option == AMBER_EXTERNAL_AVAILABILITY_NONE ) { echo 'selected="selected"'; } ?>>Do not use an external service</option>
                <option value="<?php echo AMBER_EXTERNAL_AVAILABILITY_NETCLERK; ?>" <?php if ( $option == AMBER_EXTERNAL_AVAILABILITY_NETCLERK ) { echo 'selected="selected"'; } ?>>Use NetClerk</option>
            </select>
            <p class="description">Optional: Use site accessibility data from the Berkman Center for Internet & Society at Harvard University</p>
        <?php
    }

    public function amber_report_availability_callback()
    {
        $option = isset($this->options['amber_report_availability']) ? $this->options['amber_report_availability'] : AMBER_REPORT_AVAILABILITY_NONE;
        ?>
            <select class="report_field" id="amber_report_availability" name="amber_options[amber_report_availability]">
                <option value="<?php echo AMBER_REPORT_AVAILABILITY_NONE; ?>" <?php if ( $option == AMBER_REPORT_AVAILABILITY_NONE ) { echo 'selected="selected"'; } ?>>Do not report site availability to an external service</option>
                <option value="<?php echo AMBER_REPORT_AVAILABILITY_NETCLERK; ?>" <?php if ( $option == AMBER_REPORT_AVAILABILITY_NETCLERK ) { echo 'selected="selected"'; } ?>>Use NetClerk</option>
            </select>
            <p class="description">Optional: Contribute site accessibility data for research as part of the Berkman Center for Internet & Society at Harvard University</p>
        <?php
    }

    public function amber_timegate_callback()
    {
        printf(
            '<input type="text" id="amber_timegate" name="amber_options[amber_timegate]" value="%s" />' .
            '<p class="description">Optional: Request additional snapshots from the Internet Archive, the Library of Congress web archive, archive.today, and more.</p>',
            isset( $this->options['amber_timegate'] ) ? esc_attr( $this->options['amber_timegate']) : ''
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
                <option value="<?php echo AMBER_ACTION_POPUP; ?>" <?php if ( $option == AMBER_ACTION_POPUP ) { echo 'selected="selected"'; } ?>>Link to Pop-up</option>
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
                <option value="<?php echo AMBER_ACTION_POPUP; ?>" <?php if ( $option == AMBER_ACTION_POPUP ) { echo 'selected="selected"'; } ?>>Link to Pop-up</option>
                <option value="<?php echo AMBER_ACTION_CACHE; ?>" <?php if ( $option == AMBER_ACTION_CACHE ) { echo 'selected="selected"'; } ?>>Link directly to Snapshot</option>
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
