<?php
/*
 Plugin Name: Stats for WP
 Plugin URI:   https://github.com/statsforwp/Stats-for-WP
 Description: When users view your site, we will log user ID, view pages, referrers URL, user IP, user agent, ... and so on, to help admin understand how users working on your site  
 Version: 1.0.3
 Author: statsforwp
 Author URI: https://github.com/statsforwp/
 Text Domain: stats-for-wp
 License: GPLv3 or later
 */
/*  Copyright 2018-2018 statsforwp
 This program comes with ABSOLUTELY NO WARRANTY;
 https://www.gnu.org/licenses/gpl-3.0.html
 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('ABSPATH'))
{
	exit;
}

$m_hadInstall = get_option('stats_for_wpinstalled');

if (empty($m_hadInstall))
{
	sfw_stats_for_wp_Install();
	update_option('stats_for_wpinstalled','1.0.3');
}

$stats_for_wp_current_version = get_option('stats_for_wp_current_version');
if (empty($stats_for_wp_current_version))
{
	update_option('stats_for_wp_current_version','1.0.3');
}


function sfw_stats_for_wp_log()
{
	global $wpdb,$table_prefix;
	$table_name = $table_prefix . "statsforwp";

	$m_checkHadThere = "SELECT * FROM $table_name";
	$m_checkResult = $wpdb->get_results($m_checkHadThere,ARRAY_A);
	?>

<div style='margin:10px 5px;'>
<div style='padding-top:5px; font-size:22px;'>Site View Logs:</div>
</div>
<div style='clear:both'></div>
<?php 
	
?>
		<div class="wrap">
			<div id="dashboard-widgets-wrap">
			    <div id="dashboard-widgets" class="metabox-holder">
					<div id="post-body">
						<div id="dashboard-widgets-main-content">
							<div class="postbox-container" style="width:90%;">
								<div class="postbox">
									<div class="inside" style='padding-left:10px;'>
									<table id="bpmotable" width="100%" style="table-layout: fixed;">
									<tr>
										<th scope="row"  width="20%" style="padding: 0px; text-align:left;">
										<?php 
											echo  __( 'URL', 'stats-for-wp' );
										?>
										</th>
									
										<th scope="row"  width="25%" style="padding: 0px; text-align:left;">
										<?php 
											echo  __( 'Referrers', 'stats-for-wp' );
										?>
										</th>

										<th scope="row"  width="10%" style="padding: 0px; text-align:left;">
										<?php 
											echo  __( 'IP', 'stats-for-wp' );
										?>
										</th>

										<th scope="row"  width="20%" style="padding: 0px; text-align:left;">
										<?php 
											echo  __( 'User Agent', 'stats-for-wp' );
										?>
										</th>

										<th scope="row"  width="15%" style="padding: 0px; text-align:left;">
										<?php 
											echo  __( 'Date', 'stats-for-wp' );
										?>
										</th>
										
										<th scope="row"  width="15%" style="padding: 0px; text-align:left;">
										<?php 
											echo  __( 'User ID', 'stats-for-wp' );
										?>
										</th>										
									</tr>										

<?php 
		if (empty($m_checkResult))
		{
			//return;
		}
		else 
		{
			foreach ($m_checkResult as $m_checkResult_single)
			{
?>								
										<tr valign="top">
										<td scope="row" style="padding: 0px; text-align:left; word-wrap: break-word;">
										<?php 
											echo  $m_checkResult_single['pageurl'];
										?>
										</td>
										
										<td style="padding: 0px; word-wrap: break-word;">
										<?php 
											echo  $m_checkResult_single['pagereferrers'];
										?>
										</td>
										
										<td style="padding: 0px; word-wrap: break-word;">
										<?php 
											echo  $m_checkResult_single['pagevistorip'];
										?>
										</td>

										<td style="padding: 0px; word-wrap: break-word;">
										<?php 
											echo  $m_checkResult_single['pagevistoruseragent'];
										?>
										</td>

										<td style="padding: 0px; word-wrap: break-word;">
										<?php 
											echo  $m_checkResult_single['pageviewdate'];
										?>
										</td>
										
										<td style="padding: 0px; word-wrap: break-word;">
										<?php 
											echo  $m_checkResult_single['pageviewuserid'];
										?>
										</td>
										</tr>
		<?php 
			}
		}
		?>
										</table>
										<br />
										
										<br />
									</div>
								</div>
							</div>
						</div>
					</div>
		    	</div>
			</div> <!--   dashboard-widgets-wrap -->
		</div> <!--  wrap -->

		<div style="clear:both"></div>
		<br />
<?php 
}

function swp_stats_for_wp_log_tracer()
{
	global $wpdb,$table_prefix;

	$avoid_log_bots = sfw_stats_check_bots();
	if ($avoid_log_bots)
	{
		return;
	}

	
	$current_user_id = get_current_user_id();
	if (empty($current_user_id))
	{
		$current_user_id = 0;
	}

	$pageurl = sanitize_text_field($_SERVER['REQUEST_URI']);
	$pagereferrers = sanitize_text_field($_SERVER['HTTP_REFERER']);
	$pagevistorip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
	$pagevistoruseragent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
	$pageviewdate = current_time('mysql');
	
	$table_name = $table_prefix . "statsforwp";
	$m_mysql = $wpdb->prepare("INSERT INTO $table_name (pageurl,pagereferrers,pagevistorip, pagevistoruseragent, pageviewdate, pageviewuserid)
			VALUES (%s, %s, %s, %s, %s,%s)",
			$pageurl,$pagereferrers,$pagevistorip,$pagevistoruseragent,$pageviewdate,$current_user_id);

		
	$wpdb->query($m_mysql);
}

function sfw_stats_check_bots()
{
	$bots = array();
	$bots[] = 'bot';
	$bots[] = 'spider';
	$bots[] = 'crawler';

	$user_bots_or_not_agent = $_SERVER['HTTP_USER_AGENT'];

	$user_bots_or_not_result = false;
	
	foreach ( $bots as $bot_single )
	{
			$bot_single = trim($bot_single);
			if ( !(empty($bot_single)))
			{
				$bot_single = strtolower($bot_single);
				$user_bots_or_not_agent = strtolower($user_bots_or_not_agent);
				
				if (strpos( $user_bots_or_not_agent, $bot_single ) === false)
				{
					$user_bots_or_not_result = false;
				}
				else 
				{
					$user_bots_or_not_result = true;
				}
			}
			else
			{
				$user_bots_or_not_result = false;
			}
	}
	return $user_bots_or_not_result;	
}

add_action( 'wp_footer', 'swp_stats_for_wp_log_tracer' );

function sfw_stats_for_wp_Install()
{
	global $table_prefix, $wpdb;
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	
	$charset_collate = '';

	if ($wpdb->has_cap('collation'))
	{
		if(!empty($wpdb->charset))
		{
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if(!empty($wpdb->collate))
		{
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}

	$table_name = $table_prefix . "statsforwp";
	$wpdb->query("DROP TABLE `".$table_name."`");
	if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name)
	{
		$createRrRatingSql = "CREATE TABLE `".$table_name."` (".
				"`record_id` INT(11) NOT NULL auto_increment,".
				"`pageurl` VARCHAR(255) NOT NULL default '',".
				"`pagereferrers` VARCHAR(255) NOT NULL default '',".
				"`statssystemtype` VARCHAR(255) NOT NULL default '',".
				"`pagevistorip` VARCHAR(255) NOT NULL default '',".
				"`vistorcountry` VARCHAR(255) NOT NULL default '',".
				"`pagevistoruseragent` VARCHAR(255) NOT NULL default '',".
				"`pageviewdate` DATETIME ,".
				"`pageviewyear` VARCHAR(12) NOT NULL default '',".
				"`pageviewmonth` VARCHAR(12) NOT NULL default '',".
				"`pageviewday` VARCHAR(12) NOT NULL default '',".
				"`pageviewhour` VARCHAR(12) NOT NULL default '',".
				"`pageviewminute` VARCHAR(12) NOT NULL default '',".
				"`pageviewuserid`  INT(11) NOT NULL  default 0,".
				"`pageviewuseragent` longtext NOT NULL,".
				"`pageviewserverenv` longtext NOT NULL,".
				"`statsextendtable` VARCHAR(255) NOT NULL  default '',".
				"`statsextendid` VARCHAR(255) NOT NULL  default '',".
				"PRIMARY KEY (record_id)) $charset_collate;";
	}
	dbDelta($createRrRatingSql);
}


function sfw_stats_for_wp_menu()
{
	add_menu_page(__('statsforwp', 'stats-for-wp'), __('Stats', 'stats-for-wp'), 'manage_options', 'stats', 'sfw_stats_for_wp_log');
	add_submenu_page('stats', __('statsforwp', 'stats-for-wp'), __('Stats','stats-for-wp'), 'manage_options', 'stats', 'sfw_stats_for_wp_log');
}

add_action( 'admin_menu', 'sfw_stats_for_wp_menu');


