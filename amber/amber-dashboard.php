<?php

class AmberDashboardPage
{

	/* Reference to the global $wpdb object */
	private $db;

    public function __construct()
    {
    	global $wpdb;
    	$this->db = $wpdb;
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        // add_action( 'admin_init', array( $this, 'page_init' ) );
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
            array( $this, 'create_admin_page' )
        );
    }

    private function cache_size() {
    	$prefix = $this->db->prefix;
    	return $this->db->get_var( "SELECT COUNT(*) FROM ${prefix}amber_cache" );
    }

    private function queue_size() {
    	return 3;
    }

    private function last_check() {
    	return 'now';
    }

    private function disk_usage() {
		$status = new AmberStatus(new AmberWPDB($this->db), $this->db->prefix);
		$result = $status->get_cache_size();		
		return $result ? $result : 0;
    }

	private function get_sort() {
		$result = "";
		if (isset($_GET['amber_sort'])) {
			switch ($_GET['amber_sort']) {
				case 'checked':
					$result = "ORDER BY c.last_checked";
					break;
				case 'cached':
					$result = "ORDER BY ca.date";
					break;
				case 'status':
					$result = "ORDER BY c.status";
					break;
				case 'size':
					$result = "ORDER BY ca.size";
					break;
				case 'viewdate':
					$result = "ORDER BY a.date";
					break;
				case 'views':
					$result = "ORDER BY a.views";
					break;
			}
			if (isset($_GET['amber_dir'])) {
				switch ($_GET['amber_dir']) {
					case "asc":
						$result .= " ASC";
						break;
					case "desc":
						$result .= " DESC";
						break;
				}
			}
		}
		return $result;
	}

    public function sort_link($column) {
    	join('/',array(get_site_url(),"wp-admin/tools.php?page=amber-dashboard"));
		$href = join('/',array(get_site_url(),"wp-admin/tools.php?page=amber-dashboard")) . "&amber_sort=${column}";
		if (isset($_GET['amber_sort']) && ($_GET['amber_sort'] == $column)) {
			if (isset($_GET['amber_dir']) && ($_GET['amber_dir'] == "desc")) {
				$href .= "&amber_dir=asc";
			} else {
				$href .= "&amber_dir=desc";
			}
		}
		return $href;
    }

    public function view_link($row) {
    	if (empty($row['location'])) {
    		return "";
    	}
    	$url = join('/',array(get_site_url(),htmlspecialchars($row['location'])));
    	return "<a href='${url}'>View</a>";    	
    }

    public function get_report() {
    	$prefix = $this->db->prefix;

		$statement = 
			"SELECT c.id, c.url, c.status, c.last_checked, c.message, ca.date, ca.size, ca.location, a.views, a.date as activity_date " .
			"FROM ${prefix}amber_check c " .
			"LEFT JOIN ${prefix}amber_cache ca on ca.id = c.id " .
			"LEFT JOIN ${prefix}amber_activity a on ca.id = a.id ";
		$statement .= $this->get_sort();

		$rows = $this->db->get_results($statement, ARRAY_A);
		return $rows;
    }


    /**
     * Dashboard page callback
     */
    public function create_admin_page()
    {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Amber Dashboard</h2>           
<form>
<table >
	<tr>
		<td>
			<h3>Global Statistics</h3>
		</td>
	</tr>
	<tr>
		<td>
			<table>
				<tbody>
					<tr><td>Captures preserved</td><td><?php print($this->cache_size()); ?></td></tr>
					<tr><td>Links to capture</td><td><?php print($this->queue_size()); ?></td></tr>
					<tr><td>Last check</td><td><?php print($this->last_check()); ?></td></tr>
					<tr><td>Disk space used</td><td><?php print($this->disk_usage() . " of " . Amber::get_option('amber_max_disk') * 1024 * 1024); ?></td></tr>
				</tbody>
			</table>

			<?php submit_button("Delete all captures"); ?>
			<?php submit_button("Scan content for links to preserve"); ?>
			<?php submit_button("Preserve all new links"); ?>
		</td>

	</tr>
</table>

<h3>Amber Data</h3>
<table>
<thead>
<tr>
<th>Site</th>
<th>URL</th>
<th><a href='<?php print $this->sort_link("status"); ?>'>Status</a></th>
<th><a href='<?php print $this->sort_link("checked"); ?>'>Last Checked</a></th>
<th><a href='<?php print $this->sort_link("cached"); ?>'>Date Preserved</a></th>
<th><a href='<?php print $this->sort_link("size"); ?>'>Size</a></th>
<th><a href='<?php print $this->sort_link("viewdate"); ?>'>Last Viewed</a></th>
<th><a href='<?php print $this->sort_link("views"); ?>'>Total Views</a></th>
<th> </th>
<th> </th>
</tr>
</thead>
<tbody>
<?php 

	foreach ($this->get_report() as $row) {
		print "<tr>";
		print("<td>" . htmlspecialchars(parse_url($row['url'], PHP_URL_HOST)) . "</td>");
		print("<td>" . "<a href='" . htmlspecialchars($row['url']) . "'>" . htmlspecialchars($row['url']) . "</a>" . "</td>");
		print("<td>" . ($row['status'] ? "Up" : "Down") . "</td>");
		print("<td>" . (isset($row['last_checked']) ? date("r", $row['last_checked']) : "") . "</td>");
		print("<td>" . (isset($row['date']) ? date("r", $row['date']) : "") . "</td>");
		print("<td>" . (isset($row['size']) ? $row['size'] : (isset($row['message']) ? htmlspecialchars($row['message']) : "")) . "</td>");
		print("<td>" . (isset($row['activity_date']) ? date("r", $row['activity_date']) : "") . "</td>");
		print("<td>" . $row['views'] . "</td>");
		print("<td>" . $this->view_link($row) . "</td>");
		print("<td>" . "<a href='" .$script_location . "?delete=" . $row['id'] . "'>Delete</a>" . "</td>");
		print "</tr>";
	}


?>
</tbody>
</table>


</form>
        </div>
        <?php
    }
}

include_once dirname( __FILE__ ) . '/amber.php';

if( is_admin() )
    $my_dashboard_page = new AmberDashboardPage();

?>