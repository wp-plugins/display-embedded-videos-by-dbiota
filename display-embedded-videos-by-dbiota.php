<?php
/**
 *Plugin Name: Display Embedded Videos by D.Biota
 *Description: You can display a gallery of the embedded Youtube and Vimeo videos within your site.
 *Version: 2.0
 *Author: Diego Biota
 *License: GPL2
 */

/*Date: April 2015*/


// no direct links allowed
defined('ABSPATH') or die("No script kiddies please!");

include_once "display-embedded-videos-by-dbiota-functions.php";
include_once "display-embedded-videos-by-dbiota-ajax-functions.php";


///////////////////////////////////////////ACTIVATION AND UPDATE RELATED//////////////////////////

	
global $displayembeddedvideosbydb_db_version;
$displayembeddedvideosbydb_db_version = "1.0";    //CAMBIAR ESTA VERSION SI MODIFICAMOS LA BASE DE DATOS, PARA QUE SE ACTUALICE

//Creates table and saves version as option.
function displayembeddedvideosbydb_install() {
   global $wpdb;
   global $displayembeddedvideosbydb_db_version;

   $table_name = $wpdb->prefix . "displayembeddedvideosbydb";
      
 	$installed_ver = get_option( "displayembeddedvideosbydb_db_version" );
	
	$charset_collate = $wpdb->get_charset_collate();  //Compatible a partir de WP 3.5

	if( $installed_ver != $displayembeddedvideosbydb_db_version ) {

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			vid_url tinytext NOT NULL,
			post_url tinytext NOT NULL,
			post_title tinytext NOT NULL,
			post_type varchar(20) NOT NULL,
			post_parent bigint(20) NOT NULL,
			post_id bigint(20) NOT NULL,
			post_cat longtext NOT NULL,
			post_tag longtext NOT NULL,
			post_time datetime NOT NULL,
			vid_provider tinytext NOT NULL,
			UNIQUE KEY id (id)
		)$charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );  //dbDelta actualizará la tabla en caso de que exista, añadiendo campos o modificando sus caracteristicas, pero atención, no borrara campos que ya no pongamos. Ni datos (como es lógico)

	update_option( "displayembeddedvideosbydb_db_version", $displayembeddedvideosbydb_db_version );
	update_option( "displayembeddedvideosbydb_tb_name", $table_name );
	
	//por ultimo incializamos la opcion de las recreaciones
		$data_flag_recreacion = array(
			"timestamp_start" => null,
			"state" => null,
			"timestamp_end" => null,
			"last_post_insp" => null,
			"timestamp_last_post_insp" => null
		);
	update_option( "displayembeddedvideosbydb_flag_recreation", $data_flag_recreacion );
	
 
	}
}
register_activation_hook( __FILE__, 'displayembeddedvideosbydb_install' );   //Se ejecuta cuando se activa el Plugin (y segun he probado, también al desactivarse)


//Como el 'register_activation_hook' no se dispara 
//cuando se actualiza un plugin, cada vez que se 
//inicializan los plugins revisamos si hay que 
//actualizar la tabla de la base de datos, y
//por otro lado revisamos si el plugin está en un proceso de recarga interrumpido, y lo volvemos a relanzar.
function displayembeddedvideosbydb_update_db_check() {
    //¿Hay que actualizar tabla de base de datos debido a actualización del  plugin?
	global $displayembeddedvideosbydb_db_version;
    if (get_site_option( 'displayembeddedvideosbydb_db_version' ) != $displayembeddedvideosbydb_db_version) {
        displayembeddedvideosbydb_install();
    }

	
}
add_action( 'plugins_loaded', 'displayembeddedvideosbydb_update_db_check' );


//Desactivacion, vaciamos la tabla de videos y opciones para que al reactivar le pida recrearla.
function display_embedded_videos_by_db_deactivate () {
	$flag_recreacion = get_option( "displayembeddedvideosbydb_flag_recreation" );
	//if ($flag_recreacion["state"]=='start') {
		global $wpdb;
		$table_name = get_option( "displayembeddedvideosbydb_tb_name" );
		$deleted = $wpdb->query(
		"
		TRUNCATE TABLE ".$table_name."
		"
		);
		$data_flag_recreacion = array(
			"timestamp_start" => null,
			"state" => null,
			"timestamp_end" => null,
			"last_post_insp" => null,
			"timestamp_last_post_insp" => null
		);
		update_option( "displayembeddedvideosbydb_flag_recreation", $data_flag_recreacion );
	//}

}
register_deactivation_hook( __FILE__, 'display_embedded_videos_by_db_deactivate' );



////////////////////////////////////////////////ADMIN MENU///////////////////////

function displayembeddedvideosbydb_page() {
		 add_submenu_page(  
		'options-general.php',		//pagina de menu de la que cuelga este submenu.
		'Display Embedded Videos by D.Biota',         // El valor usado para llenar la barra de titulo del navegador cuando esta pagina de menu esta activa.  
		'Display Embedded Videos by D.Biota',         // El texto del menu en la barra lateral de administracion  
		'administrator',                // Que roles pueden acceder a este menu 
		'display-embedded-videos-by-db',         // The ID usado para asociar elementos submenu a este menu -> No se porque, pero si lo cambio afecta a permisos
		'display_embedded_videos_by_db_page'    // funcion callback usada para renderizar la pagina de este menu, cuando se entra 
		);  
  
}
add_action('admin_menu', 'displayembeddedvideosbydb_page');

/*renderizamos la página del menu*/ 
function display_embedded_videos_by_db_page() {
?>
		<!-- Contenedor 'wrap' por defecto de wordpress --> 
		<div class="wrap"> 
			<div id="icon-themes" class="icon32"></div> 
			<h2>Display Embedded Videos by D.Biota - Plugin</h2> 
			<div style="overflow:hidden; width:100%;">
			<div style="float:left;">
			<h3>Shortcodes Wizard</h3> 
			<p>Create your shortcode/s and paste it/them wherever you want your videos displayed.</p>
			<div style="overflow:hidden;">
			<div style="width:48%;padding:2px 8px 0 0;float:left; text-align:right;">Ordering mode:</div> <div style="width:48%;padding:0 10px 0 0;float:right;"><select id="select_mode">
				<option value="chronological" >chronological</option>
				<option value="daily_random" >daily_random</option>
			</select></div></br>
			</div>
			<div style="overflow:hidden;">
			<div style="width:48%;padding:2px 8px 0 0;float:left; text-align:right;">Number of videos to display: </div> <div style="width:48%;padding:0 10px 0 0;float:right;"> <input type="text" id="input_vids_to_display" size="3" value="4"></div></br>
			</div>
			<div style="overflow:hidden;">
			<div style="width:48%;padding:2px 8px 0 0;float:left;text-align:right;">Number of videos per line: </div> <div style="width:48%;padding:0 10px 0 0;float:right;"> <input type="text" id="input_vids_per_line" size="3" value="1"></div></br>
			</div>
			<div style="overflow:hidden;">
			<div style="width:48%;padding:2px 8px 0 0;float:left;text-align:right;">Show "More..." button: </div> <div style="width:48%;padding:0 10px 0 0;float:right;"> <select id="select_more">
				<option value="no" >no</option>
				<option value="yes" >yes</option>
			</select></div></br>
			</div>
			<div style=”clear:both;”></div>  <!-- fin para estructura en columnas -->
			
			
			<p class="submit">
			<input id="shortcode_generation" class="button button-primary" type="submit" value="Generate Shortcode!" name="shortcode_generation"></input>
			</p>
			<span id="sc_result"> <b>[display_embedded_videos <span id="sc_mode"></span><span id="sc_num"></span><span id="sc_per_line"></span><span id="sc_more"></span><span id="sc_cat"></span><span id="sc_tag"></span><span id="sc_forum"></span>]</b> <p>Copy the line above and paste it wherever you want your video list displayed</p></span>
			</br></br>
			</div>
			
			<div style="float:right; margin:20px 0 20px 20px;"><a href="http://www.diegobiota.com/tecnologias-web/wordpress/display-embedded-videos-by-d-biota-pro-wordpress-plugin/" target="_blank"><img src="<?php echo plugins_url( 'images/gopro.png', __FILE__ ) ?>"/></a></div>
			</div>
			
			
			
			<div>
			<h3>Video Collection</h3>


<?php
			
			global $wpdb;
			//revisamos si hay datos en la tabla de videos, y advertimos
			$table_name = get_option( "displayembeddedvideosbydb_tb_name" );
			$flag_recreacion = get_option( "displayembeddedvideosbydb_flag_recreation" );
			$videos_count = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );
			

			//Si no hay colección hecha
			if ($flag_recreacion["state"]== null) {
				echo '<p>This plugin needs to detect all the videos <b>posted before it was installed</b>. Do it by pushing the button below.</p> ';
				echo "<p>Once you get a successful result, next <b>videos posted will be detected automatically</b>.</p>";
			}
			
			//Si hay coleccion iniciada y en curso todavía
			if ($flag_recreacion["state"] == 'start' AND (time() < ($flag_recreacion["timestamp_last_post_insp"] + 90))) {
				echo '<div id="vid_counter"><p>...Inspecting blog for videos detection... </p><p>...Have been detected '.$videos_count.' videos for the moment, but not finished yet... </p></div>';
			} 
			
			//Si hay coleccion iniciada y se ha interrumpido
			if ($flag_recreacion["state"] == 'start' AND (time() > ($flag_recreacion["timestamp_last_post_insp"] + 90))) {
				echo '<div id="vid_counter"><p>It seems the Videos Detection has been interrupted for external reasons... </p><p>Until then, '.$videos_count.' videos were detected. Continue the process by pressing the button below. </p>';
				echo "<p>Once you get a successful result you won´t have to worry about this process ever, because next videos posted will be detected automatically.</p></div>";
			}
			
			//Si se ha hecho una carga satisfactoria
			if ($flag_recreacion["state"] == 'end') {  
				echo '<div id="vid_counter"><p>'.$videos_count.' videos detected. Next videos posted will be detected automatically.</p></div>';
			} 
			
			//Si no es justo la activacion
			if ($flag_recreacion["state"] <> null) {
				echo '<div id="view_vid_list"><a href="#">View collection</a></div>
				<div id="close_vid_list"><a href="#">Hide collection</a></br></div>
				<div id="vid_list">';

				$videos = $wpdb->get_results( "SELECT post_id, post_title, post_url FROM ".$table_name." ORDER BY post_id DESC;");
				
				foreach ($videos as $vid) {
					echo '> Video in post: <a href="'.$vid->post_url.'">'.$vid->post_title.'</a></br >';
				}
				echo '</div>'; 
			}
			
			
			//Si es la primera vez, ponemos el boton de inicio.
			if ($flag_recreacion["state"] == null) {
				echo '<p class="submit">';
				echo '<input id="carga" class="button button-primary" type="submit" value="Start the Previous Vids Detection" name="carga"></input>';
				echo '</p>';
			}

			//Si hay una recreacion interrumpida
			if ($flag_recreacion["state"] == 'start' AND (time() > ($flag_recreacion["timestamp_last_post_insp"] + 90))) {
				//echo '</div>'; 
				echo '<p class="submit">';
				echo '<input id="carga" class="button button-primary" type="submit" value="Continue the Previous Vids Detection" name="carga"></input>';
				echo '</p>';
			}
?>
			
			<img id="gif_cargando" style="float:left;" src="<?php echo plugins_url( 'images/ajax-loader.gif', __FILE__ ) ?>" >
			
			<div id="cargando" style="display:none; color: grey; float:left; margin-left: 22px;"><p>Inspecting posts, please don't close the browser to avoid interrupting the process, it coud take some minutes depending on the site's size.</p> <p>Wait for the success message without reloading nor closing this page.</p></div>
			<div id="destino"></div>

			</div>
		</div>
<?php  


} //fin display_embedded_videos_by_db_page	




//Incluir javascript en admin
function devdb_javascript_con_jquery_admin($hook) {
    if ( 'settings_page_display-embedded-videos-by-db' != $hook ) {
        return;
    }  
    wp_register_script( 'js_plugin_settings', plugins_url( 'js/js_plugin_settings.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'js_plugin_settings' );
	
	// Esto es para AJAX, para pasar valores PHP a javascript. In JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'js_plugin_settings', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'admin_enqueue_scripts', 'devdb_javascript_con_jquery_admin' );  //Este hook actua en todas las paginas del admin, así que pondremos una comprobacion al principio de la funcion

//Incluir javascript en frontend
function devdb_javascript_con_jquery($hook) { 
    wp_register_script( 'js_plugin_front', plugins_url( 'js/js_plugin_front.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'js_plugin_front' );
	
	// Esto es para AJAX, para pasar valores PHP a javascript. In JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'js_plugin_front', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'devdb_javascript_con_jquery' );  //Este hook actua en todas las paginas del admin, así que pondremos una comprobacion al principio de la funcion


//Incluir styles en frontend. Revisa antes si el tema tiene el archivo, por si el autor quiere override los estilos.
function devdb_include_css_stylesheets() {
	//wp_register_style( 'hoja_estilo', plugins_url( 'display-embedded-videos-by-dbiota/css/display-embedded-videos-by-dbiota.css' ) );
	//wp_enqueue_style( 'hoja_estilo' );                                                  
	
	if(@file_exists(get_stylesheet_directory().'/devbdb.css')) {
		wp_enqueue_style('hoja_estilo', get_stylesheet_directory_uri().'/devbdb.css');
	} else {                                                             
		wp_enqueue_style( 'hoja_estilo', plugins_url( 'display-embedded-videos-by-dbiota/css/devbdb.css' ));
	}

	wp_register_style( 'hoja_layout', plugins_url( 'display-embedded-videos-by-dbiota/css/devbdb_layout.css' ) );
	wp_enqueue_style( 'hoja_layout' );
	
}
add_action( 'wp_enqueue_scripts', 'devdb_include_css_stylesheets', 100 );


//Notificaciones en Admin

function devdb_my_admin_notice() {
		
		global $wpdb;
		//Revisamos si es la primera vez
		$flag_recreacion = get_option( "displayembeddedvideosbydb_flag_recreation" );
		
		if ($flag_recreacion["state"]==null) {
?>
			<div class="update-nag">
				<p><?php _e( 'You have activated DISPLAY EMBEDDED VIDEOS BY D.BIOTA plugin. IMPORTANT! go to the "Video Collection" section of the plugin settings to run the "Previous Vids Detection" process.', 'my-text-domain' ); ?></p>
			</div>
<?php
		} 
		
		//Si es una recarga en proceso
		if ($flag_recreacion["state"] == 'start' AND (time() < ($flag_recreacion["timestamp_last_post_insp"] + 90))) { 
		
			/*CALCULAR TIEMPO
			$secs_transcurridos = time() - $flag_recreacion[timestamp];
			$hours = floor($secs_transcurridos / 3600);
			$hours = str_pad($hours, 2, "0", STR_PAD_LEFT);
			$mins = floor(($secs_transcurridos - ($hours*3600)) / 60);
			$mins = str_pad($mins, 2, "0", STR_PAD_LEFT);
			$secs = floor($secs_transcurridos % 60);
			$secs = str_pad($secs, 2, "0", STR_PAD_LEFT);
			$t_transcurrido = $hours.":".$mins.":".$secs; */
?>
			<div class="update-nag">
				<p><?php _e( "The video detection process for the plugin DISPLAY EMBEDDED VIDEOS BY D.BIOTA is running. Please be patient, if your site is big it is difficult to predict how long it may take. (For a site with about 1000 videos published, it should take less than 5 minutes)", 'my-text-domain') ; ?></p>
			</div>
<?php
		}
		
		//Si es una recarga interrumpida
		if ($flag_recreacion["state"] == 'start' AND (time() > ($flag_recreacion["timestamp_last_post_insp"] + 90))) { 
		
?>
			<div class="update-nag">
				<p><?php _e( "It seem the Video Detection process has been interrupted for external reasons... Please, go to the Video Collection section of the plugin settings to end the process", 'my-text-domain') ; ?></p>
			</div>
<?php
		}
}
add_action( 'admin_notices', 'devdb_my_admin_notice' );


//Añade un link a settings en el menu de plugins
function devdb_plugin_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        // The "page" query string value must be equal to the slug
        // of the Settings admin page we defined earlier
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=display-embedded-videos-by-db">Settings</a>';
        array_unshift($links, $settings_link);
    }
	
    return $links;
}
add_filter('plugin_action_links', 'devdb_plugin_action_links', 10, 2);



////////////////////////////////////////////////CORE FUNCTIONALITY///////////////////////



/////////////////////////////////////GUARDAR VIDEOS///////
//Procesamos el contenido de un post, 
//al guardarlo en la base de datos
//para buscar la url de un video.
//Guarda la url y el post_id en la base de datos del plugin

function devdb_save_embedded_vids( $post_id ) {	

devdb_analize_post_and_save_vid($post_id, '', '', '', '', '', '', TRUE);

}
add_action( 'save_post', 'devdb_save_embedded_vids' );  //Hook al guardarse un post. Envia el post_id.


//Si se borra un post, borramos los registros de videos que tuviera
function devdb_delete_vids( $post_id ) {	
	global $wpdb;
	$table_name = get_option( "displayembeddedvideosbydb_tb_name" );
	
	//Borramos todos los registros de videos anteriores que pueda haber para este post
	$wpdb->delete( $table_name, array( 'post_id' => $post_id ) );
}
add_action( 'delete_post', 'devdb_delete_vids' );



/////////////////////////////////////MOSTRAR VIDEOS///////

//SHORTCODE

//[display_embedded_videos_by_db mode="chronological/daily_random" vids_to_display="n" vids_per_line="n" more="yes/no" cat="xxxx" tag="xxxx" forum="xxxxx"]  --> FALTA PONER LOAD MORE
function display_embedded_videos_short_code_func( $atts ){
	//Entradas del shortcode

	$a = shortcode_atts( array(
        'vids_to_display' => '4',
		'vids_per_line' => '1',  //Hacer que no se pueda poner 0 ni negativos
        'mode' => 'chronological',
		'more' => 'no',		  //PRO
		'cat' => 'all',       //PRO
		'tag' => 'all',       //PRO
		'forum' => 'all'	  //PRO
		), $atts );
	
	//Hacemos la query de todos los videos, y nos quedamos con un array solo con los filtrados
	$posted_videos_records = devdb_query_filter($a);

	//Generamos el HTML para mostrar los videos en el array.
	$html = devdb_post_video_records($posted_videos_records, 0, $a['vids_to_display'],$a['vids_per_line'], $a['mode'], $a['more'], $a['cat'], $a['tag'], $a['forum']);

	return $html; //DEVOLVEMOS RESULTADO DEL SHORTCODE
}
add_shortcode( 'display_embedded_videos', 'display_embedded_videos_short_code_func' );
?>