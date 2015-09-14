<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

global $wpdb;

class Amber_List_Table extends WP_List_Table {
    
    function __construct(){
        global $status, $page, $wpdb;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'capture',     //singular name of the listed records
            'plural'    => 'captures',    //plural name of the listed records
            'ajax'      => false          //does this table support ajax?
        ) );
        
    }

    function get_report() {
        global $wpdb;
        $prefix = $wpdb->prefix;

        $statement = 
            "SELECT c.id, c.url, c.status, c.last_checked, c.message, ca.date, ca.size, ca.location, a.views, a.date as activity_date, substring_index(c.url,'://',-1) as url_sort " .
            "FROM ${prefix}amber_check c " .
            "LEFT JOIN ${prefix}amber_cache ca on ca.id = c.id " .
            "LEFT JOIN ${prefix}amber_activity a on ca.id = a.id ";

        if (!empty($_REQUEST['orderby'])) {
            if (in_array($_REQUEST['orderby'], array('c.url', 'c.last_checked', 'ca.date', 'c.status', 'ca.size', 'a.date', 'a.views', 'url_sort'), true)) {
                $statement .= " ORDER BY " . $_REQUEST['orderby'];
                if (!empty($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc','desc'))) {
                    $statement .=  " " . $_REQUEST['order'];
                } else {
                    $statement .= " DESC";
                }
            }
        }

        $rows = $wpdb->get_results($statement, ARRAY_A);
        return $rows;        
    }

    /** Column display functions **/
    function column_default($item, $column_name){
        return $item[$column_name];
    }

    function column_site($item) {
        $actions = array();
        if (!empty($item['location'])) {
            $url = join('/',array(get_site_url(),htmlspecialchars($item['location'])));
            $actions['view'] =  "<a href='${url}'>View</a>";     
        }
        if (!empty($item['id'])) {
            $url = join('/',array(get_site_url(),"wp-admin/tools.php?page=amber-dashboard")) . "&delete=" . $item['id'];
            $params = array('orderby', 'order');
            foreach ($params as $param) {
                if (isset($_REQUEST[$param])) {
                    $url .= "&${param}=" . $_REQUEST[$param];
                }
            }    
            $url = wp_nonce_url($url, 'delete_link_' . $item['id']);
            $actions['delete'] = "<a href='${url}'>Delete</a>";
        }

        return parse_url($item['url'],PHP_URL_HOST) . $this->row_actions($actions);
    }

    function column_status($item) {
        return $item['status'] ? 'Up' : 'Down';
    }

    function column_size($item) {
        return round($item['size'] / 1024, 2);
    }

    function column_last_checked($item) {
        return isset($item['last_checked']) ? date('r',$item['last_checked']) : "";
    }

    function column_date($item) {
        return isset($item['date']) ? date('r',$item['date']) : "";
    }

    function column_activity_date($item) {
        // return print_r($item,true);
        return isset($item['activity_date']) ? date('r',$item['activity_date']) : "";
    }

    /** Define the columns and sortable columns for the table **/
    function get_columns(){
        $columns = array(
            'site'           => 'Site',
            'url'            => 'URL',
            'status'         => 'Status',
            'last_checked'   => 'Last Checked',
            'date'           => 'Date preserved',
            'size'           => 'Size (kB)',
            'activity_date'  => 'Last viewed',
            'views'          => 'Total views',
            'message'        => 'Notes',
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'url'           => array('url_sort',false),     //true means it's already sorted
            'status'        => array('c.status',false),
            'last_checked'  => array('c.last_checked',false),
            'date'          => array('ca.date',false),
            'size'          => array('ca.size',false),
            'a.date'        => array('a.date',false),
            'views'         => array('a.views',false),
        );
        return $sortable_columns;
    }

    function prepare_items() {
        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        /** Load the data from the database **/
        $data = $this->get_report();                

        /** Currently handling pagination within the PHP code **/
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        /** Setup the data for the table **/
        $this->items = $data;
        
        /** Register our pagination options & calculations. **/
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                     
            'total_pages' => ceil($total_items/$per_page)   
        ) );
    }
}

class AmberDashboardPage
{
    /* Reference to the global $wpdb object */
    private $db;

    public function __construct()
    {
        global $wpdb;
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add dashboard page to the menu system
     */
    public function add_plugin_page()
    {
        add_management_page( 
            'Amber Dashboard', 
            'Amber Dashboard', 
            'manage_options', 
            'amber-dashboard', 
            array( $this, 'render_dashboard_page' )
        );
    }

    /* Handle any actions that might be requested on a page load.
     * Other actions handled through ajax calls are not included in this function
     */
    public function page_init()
    {
        global $_REQUEST;

        if (isset($_REQUEST['delete_all'])) {
            $this->delete_all();
        } else if (isset($_REQUEST['delete'])) {
            $this->delete($_REQUEST['delete']);         
        } else if (isset($_REQUEST['export'])) {
            $this->export();            
        }
        /* Since at least one action ('delete') is passed as a GET request in the URL,
         * redirect to this same page, but without that action in the URL. (This prevents
         * problems on refresh, such as deleting the item again). Add back any page parameters
         * we want to keep before redirecting. (This would not be necessary if submitted 
         * delete requests through a post, with a combination of javascript and hidden fields)
         */
        if (isset($_REQUEST['delete_all']) || isset($_REQUEST['delete'])) {
            $redirect = join('/',array(get_site_url(),"wp-admin/tools.php?page=amber-dashboard"));
            $params = array('orderby', 'order');
            foreach ($params as $param) {
                if (isset($_REQUEST[$param])) {
                    $redirect .= "&${param}=" . $_REQUEST[$param];
                }
            }
            wp_redirect($redirect);
            die();
        }
    }

    private function cache_size() {
        global $wpdb;
        $prefix = $wpdb->prefix;
        return $wpdb->get_var( "SELECT COUNT(*) FROM ${prefix}amber_cache" );
    }

    private function queue_size() {
        global $wpdb;
        $prefix = $wpdb->prefix;
        return $wpdb->get_var( "SELECT COUNT(*) FROM ${prefix}amber_queue" );
    }

    private function last_check() {
        $result = get_option(AMBER_VAR_LAST_CHECK_RUN, "");
        return ($result) ? date("r", $result) : "Never";
    }

    private function disk_usage() {
        global $wpdb;
        $status = new AmberStatus(new AmberWPDB($wpdb), $wpdb->prefix);
        $result = round($status->get_cache_size() / (1024 * 1024), 2);        
        return $result ? $result : 0;
    }

    private function delete($id) {
        check_admin_referer( 'delete_link_' . $id );
        $storage = Amber::get_storage();
        $storage->clear_cache_item($id);
        $status = Amber::get_status();
        $status->delete($id);
    }
    
    private function delete_all() {
        global $wpdb;
        check_admin_referer('amber_dashboard');
        $storage = Amber::get_storage();
        $storage->clear_cache();
        $status = Amber::get_status();
        $status->delete_all();  
        $prefix = $wpdb->prefix;
        $wpdb->query("DELETE from ${prefix}amber_queue");
    }   

    private function export() {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $statement = 
            "SELECT c.id, c.url, c.status, c.last_checked, c.message, ca.date, ca.size, ca.location, a.views, a.date as activity_date " .
            "FROM ${prefix}amber_check c " .
            "LEFT JOIN ${prefix}amber_cache ca on ca.id = c.id " .
            "LEFT JOIN ${prefix}amber_activity a on ca.id = a.id ";

        $data = $wpdb->get_results($statement, ARRAY_A);

        $header = array(
          'Site',
          'URL',
          'Status',
          'Last Checked',
          'Date preserved',
          'Size (kB)',
          'Last viewed',
          'Total views',
          'Notes',
        );

        $rows = array();
        foreach ($data as $r) {
          $host = parse_url($r['url'],PHP_URL_HOST);
          $rows[] = array(
            'site' => $host,
            'url' => $r['url'],
            'status' => $r['status'] ? 'Up' : 'Down',
            'last_checked' => isset($r['last_checked']) ? date('c',$r['last_checked']) : "",
            'date' => isset($r['date']) ? date('c',$r['date']) : "",
            'size' => round($r['size'] / 1024,2),
            'a.date' => isset($r['a_date']) ? date('c',$r['a_date']) : "",
            'views' => isset($r['views']) ? $r['views'] : 0,
            'message' => isset($r['message']) ? $r['message'] : ""
          );
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=report.csv');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, $header);
        foreach($rows as $line){
          fputcsv($fp, $line);
        }
        fclose($fp);
        die();
    }


    function render_dashboard_page(){
        
        $this->list_table = new Amber_List_Table();
        $this->list_table->prepare_items();
        
        ?>
        <div class="wrap">
            <form id="amber_dashboard" method="get">
                <?php wp_nonce_field( 'amber_dashboard' ); ?>

                <div id="icon-users" class="icon32"><br/></div>
                <h2>Amber Dashboard</h2>           

                <div id="amber-stats">  
                    <h3>Global Statistics</h3>
                    <table>
                        <tbody>
                            <tr><td>Captures preserved</td><td><?php print($this->cache_size()); ?></td></tr>
                            <tr><td>Links to capture</td><td><?php print($this->queue_size()); ?></td></tr>
                            <tr><td>Last check</td><td><?php print($this->last_check()); ?></td></tr>
                            <tr><td>Disk space used</td><td><?php print($this->disk_usage() . " of " . Amber::get_option('amber_max_disk')); ?> MB</td></tr>
                        </tbody>
                    </table>

                    <?php submit_button("Delete all captures", "small", "delete_all"); ?>
                    <?php submit_button("Scan content for links to preserve", "small", "scan"); ?>
                    <?php submit_button("Preserve all new links", "small", "cache_now"); ?>
                    <?php submit_button("Export list of preserved content", "small", "export"); ?>
                    
                </div>            
            </form>
            <form  id="amber_dashboard-2">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                <?php $this->list_table->display() ?>

                <div id="amber-lightbox">
                    <div id="amber-lightbox-content">
                        <div id="batch_status"></div>
                        <input type="submit" name="stop" id="stop" class="button button-small" value="Stop">
                    </div>
                </div>
            </form>
        </div>

<style>
    div#amber-stats {
         float: left;
         background:#ECECEC;
         border:1px solid #CCC;
         padding:0 10px;
         margin-top:5px;
         border-radius:5px;
    }

    p.submit {
        float: left;
        padding-right: 20px;
    }

    th.views, td.views
    {
        text-align: center;
    }

    div#amber-lightbox {
        width: 100%;
        height: 75%;
        z-index: 200;
        position: absolute;
        background-color: rgba(0,0,0,.7);
        left: -10px;
        top: 0;
        padding-top: 25%;
        display: none;
    }
    div#amber-lightbox-content {
        float: left;
        width: 50%;
        margin-left: 25%;
        top: 25%;
        postion: absolute;
        background:#ECECEC;
        border:1px solid #CCC;
        padding:30px;
        margin-top:5px;
        border-radius:5px;
    }
    div#batch_status {
        width: 80%;
        overflow: hidden;
        float: left;
    }

    div#amber-lightbox input {
        float: right;
        position: relative;
    }

</style>
<script type="text/javascript" >

jQuery(document).ready(function($) {

    $("input#cache_now").click(function() { cache_all(); return false;});
    $("input#scan").click(function() {  scan_all(); return false;});

    function show_status(s) {
        $("div#amber-lightbox").show();
        $("#batch_status").html(s);
    }

    function show_status_done(s) {
        $("#batch_status").html(s);
        $("#amber-lightbox input").val("Close");
    }

    function cache_one() {
        var data = { 'action': 'amber_cache', '_wpnonce': $("#_wpnonce").val() };
        $.post(ajaxurl, data, function(response) {
            if (response) {
                // Cached a page, check to see if there's another
                show_status("Preserving..." + response);
                setTimeout(cache_one, 100);     
            } else {
                show_status_done("Done preserving links");
            }
        });
    }

    function cache_all () {
        show_status("Preserving links...");
        cache_one();
    }

    function scan_one() {
        var data = { 'action': 'amber_scan', '_wpnonce': $("#_wpnonce").val()};
        $.post(ajaxurl, data, function(response) {
            if (response && response != 0) {
                show_status("Scanning content. " + response + " items remaining.");
                setTimeout(scan_one, 100);      
            } else {
                show_status_done("Done scanning content");
            }
        });
    }

    function scan_all () {
        show_status("Scanning content for links...");
        var data = { 'action': 'amber_scan_start', '_wpnonce': $("#_wpnonce").val() };
        $.post(ajaxurl, data, function(response) {
            if (response) {
                show_status(response + " items left to scan");
                setTimeout(scan_one, 100);
            } else {
                show_status_done("No items to scan");
            }
        });     
    }
});
</script>

        <?php
    }

}

include_once dirname( __FILE__ ) . '/amber.php';

if( is_admin() )
    $my_dashboard_page = new AmberDashboardPage();
