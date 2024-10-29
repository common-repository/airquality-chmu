<?php
/*
=== AirQuality CHMU ===

Plugin Name: AirQuality CHMU
Author: Jakub Macíček
Tags: weather, pollution, monitor, widget, health
Requires at least: 4.7
Tested up to: 4.9.4
Requires PHP: 5.5
Stable tag: 0.4
Version: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Tento plugin slouží k zobrazení environmentálních dat z Českého hydrometeorologického ústavu.

*/

/**
 * Create custom plugin settings menu
 */

add_action( 'admin_menu', 'chmu_create_menu' );
function chmu_create_menu() {
	add_menu_page(
		__( 'CHMU Nastavení', 'chmu' ),
		__( 'CHMU Nastavení', 'chmu' ),
		'administrator',
		__FILE__, 'chmu_settings_page',
		plugins_url( '/images/icon.png', __FILE__ )
	);

	//call register settings function
	add_action( 'admin_init', 'chmu_register_settings' );
}

add_action( 'admin_enqueue_scripts', 'chmu_admin_scripts' );
function chmu_admin_scripts() {
	wp_enqueue_script( 'select2', plugin_dir_url( __FILE__ ) . '/lib/select2.min.js', [ 'jquery' ] );
	wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . '/lib/select2.min.css' );
}

if (isset($_POST['option_page']) && $_POST['option_page'] === 'chmu-settings-group') {
	add_action( 'admin_notices', 'chmu_admin_notice_success' );
}

function chmu_admin_notice_success() { ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Settings saved sucessfully!', 'chmu' ); ?></p>
    </div>
<?php }

/**
 * Register settings
 */
function chmu_register_settings() {
	register_setting( 'chmu-settings-group', 'chmu_station' );
	foreach ( chmu_get_data()->Components as $component ) {
		register_setting( 'chmu-settings-group', 'chmu_show_' . $component->Code );

	}
	foreach ( chmu_get_data()->Legend as $legend ) {
		register_setting( 'chmu-settings-group', 'chmu_legend_' . $legend->Ix );
		}	}

/**
 * Render the settings page
 * This utilizes the WP Settings API https://codex.wordpress.org/Creating_Options_Pages
 */
function chmu_settings_page() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $(document).ready(function () {
                $('select').select2();
            });
        });

    </script>
    <div class="wrap">
        <h1>Administrativní nastavení</h1>
	    <?php  settings_errors(); ?>
        <form method="post" action="options.php">
			<?php settings_fields( 'chmu-settings-group' ); ?>
			<?php do_settings_sections( 'chmu-settings-group' ); ?>
            <table class="form-table">
                <tr valign="top">
<th scope="row"><?php _e( 'Zadej město', 'chmu' ); ?></th>
<td><select name="chmu_station">
<option value="">Select station</option>
<?php foreach ( chmu_get_stations() as $key => $station ) { ?>
<option value="<?php echo $key; ?>" <?php selected( $key, get_option( 'chmu_station' ) ); ?>>
<?php echo $station->Name; ?></option>
<?php } ?>
</select></td>
 </tr>
				
				<?php
				foreach ( chmu_get_data()->Components as $component ) { ?>
                    <tr valign="top">
                        <th scope="row"><?php echo $component->Code; ?></th>
<td><input type="checkbox" value="1" name="<?php echo 'chmu_show_' . $component->Code ?>" 
<?php checked( 1, get_option( 'chmu_show_' . $component->Code ) ); ?>/></td>
                    </tr>
<?php } ?>

<?php /* textové pole */ ?>
     <tr valign="top">
	 <th scope="row">
     <h3>Vložte popis pro kvalitu ovzduší:</h3>
				<?php
				foreach ( chmu_get_data()->Legend as $legend ) { ?>
                    <tr valign="top">
                        <th scope="row">
                        <?php echo $legend->Ix; ?></th>
 <td><input type="text" 
 			name="<?php echo 'chmu_legend_' . $legend->Ix ?>" 
 			value="<?php echo get_option( 'chmu_legend_' . $legend->Ix ) ?>"
 			/></td>
             </tr>
				<?php } ?>
            </table>
			<?php submit_button('Uložit'); ?>
        </form>
    </div>
<?php }

/**
 * Save the data to options table
 *
 * @param $data
 */
function chmu_save_data( $data ) {
	update_option( 'chmu_data', $data );
	update_option( 'chmu_last_update', date( 'Y-m-d H:i:s' ) );
}

/**
 * Load data from the API
 * We are using wp_remote_get, which internally uses whatewver is available on the host system
 * @return array|mixed|object|null
 */
function chmu_load_data() {
$result = wp_remote_get( 'http://portal.chmi.cz/files/portal/docs/uoco/web_generator/aqindex_cze.json' );
return json_decode( wp_remote_retrieve_body( $result ) );
}
/**
 * Helper to get the data
 * @return mixed
 */
function chmu_get_data() {
	return get_option( 'chmu_data' );
}

/**
 * Scrub through the data and get all the stations in array
 * @return array
 */
function chmu_get_stations() {
	$stations = [];
	foreach ( chmu_get_data()->States as $state ) {
		foreach ( $state->Regions as $region ) {
			foreach ( $region->Stations as $station ) {
				$stations[ $station->Code ] = $station;
			}
		}
	}

	return $stations;
}

/**
 * Get the legend
 * @return mixed
 */
function chmu_get_legend() {
	return chmu_get_data()->Legend;
}

/**
 * get a single station by code
 *
 * @param $code
 *
 * @return mixed
 */
function chmu_get_station_by_code( $code ) {
	$stations = chmu_get_stations();

	return $stations[ $code ];
}

/**
 * Get last update date
 * @return mixed
 */
function chmu_get_last_update() {
	return get_option( 'chmu_last_update', '' );
}

/**
 * Add the shortcode
 */
add_shortcode( 'chmu_widget', 'chmu_render_widget' );
function chmu_render_widget() {
	ob_start();
	$station = chmu_get_station_by_code( get_option( 'chmu_station' ) );
	$legend  = chmu_get_legend();

	$rating = false;
	foreach ( $legend as $item ) {
		if ( $item->Ix === $station->Ix ) {
			$rating = $item;
			break;
		}
	}
	$style = '';
	if ( $rating ) {
		$style = "background-color: #$rating->Color; color: #$rating->ColorText";
	}

	?>
    <style type="text/css">
        .chmu table {
            width: 100%;
        }
        .chmu hr {
            border-top: 1px dashed black;
        }
    </style>
    <div class="chmu" style="<?php echo $style; ?>; padding: 20px;">
<h3><?php printf( __( 'Město: %s', 'chmu' ), $station->Name ); ?></h3>
<h4><strong>Kvalita ovzduší</strong>: <?php echo $item->Ix;?>,
		<?php echo $rating->Description?></h4>
<p><?php printf( __( 'Last update: %s', 'chmu' ), 
date( 'd.m.Y H:i:s', strtotime( chmu_get_last_update() ) ) ); ?></p>
        <hr>
        <p><?php echo get_option( 'chmu_legend_' . $item->Ix ) ?></p>
        <table>
            <tr>
				<?php
				$i = 0;
				foreach ( $station->Components as $component ) {
				if ( get_option( 'chmu_show_' . $component->Code ) != 1 ) {
					continue;
				}
				if ( $i % 2 == 0 ) { ?>
            </tr>
            <tr>
				<?php } ?>
<td><strong><?php echo $component->Code; ?>
:</strong> <?php echo isset( $component->Val ) ? $component->Val : '-'; ?></td>
				<?php
				$i ++;
				}
				?>
            </tr>
        </table>
        <p>Data jsou čerpána z ČHMÚ</p>
    </div>

	<?php
	return ob_get_clean();
}

/**
 * Register cron to periodically refresh the data
 */
register_activation_hook( __FILE__, 'chmu_activation' );

function chmu_activation() {
	if ( ! wp_next_scheduled( 'chmu_refresh_data' ) ) {
		wp_schedule_event( time(), 'hourly', 'chmu_refresh_data' );
	}

	chmu_run_refresh_data();
}

/**
 * Hook into the cron that we registered before
 */
add_action( 'chmu_refresh_data', 'chmu_run_refresh_data' );
function chmu_run_refresh_data() {
	$data = chmu_load_data();
	chmu_save_data( $data );
}
