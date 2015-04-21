<?php


add_action( 'wp_ajax_recreate_collection', 'recrear_coleccion' );
function recrear_coleccion() {
	global $wpdb;

	devdb_recreate_collection();
		
	//Imprimimos un texto con el resultado de las detecciones

	global $wpdb;
	$table_name = get_option( "displayembeddedvideosbydb_tb_name" );

	$videos = $wpdb->get_results( 
			"
			SELECT post_id, post_title, post_url
			FROM ".$table_name."
			ORDER BY post_id DESC;
			"
			);
			echo '<p>Inspection FINISHED SUCCESSFULLY, '.count($videos).' videos detected</p>';
			foreach ($videos as $vid) {
				echo '> Video detected in post: <a href="'.$vid->post_url.'">'.$vid->post_title.'</a></br >';
			}
	wp_die();
}


add_action( 'wp_ajax_function_loadmore', 'funcion_loadmore' );
add_action( 'wp_ajax_nopriv_function_loadmore', 'funcion_loadmore' );
function funcion_loadmore() {
	$offset = $_POST['offset'];
	$todisplay = $_POST['todisplay'];
	$perline = $_POST['perline'];
	$mode = $_POST['mode'];
	$more = $_POST['more'];
	$cat = $_POST['cat'];
	$tag = $_POST['tag'];
	$forum = $_POST['forum'];

	If (!is_numeric($offset)) {exit();}
	If (!is_numeric($todisplay)) {exit();}
	If (!is_numeric($perline)) {exit();}
	
	//Damos los valores por defecto
	if (!$todisplay) { $todisplay = '4';}
	if (!$perline) { $perline = '1';}
	if (!$mode) { $mode = 'chronological';}
	if (!$more) { $more = 'no';}
	if (!$cat) { $mode = 'all';}
	if (!$tag) { $tag = 'all';}
	if (!$forum) { $forum = 'all';}

	$updated_offset = $offset + $todisplay;
	
	$a = array(
        'vids_to_display' => $todisplay,
		'vids_per_line' => $perline,  //Hacer que no se pueda poner 0 ni negativos
        'mode' => $mode,
		'more' => $more,		  //PRO
		'cat' => $cat,       //PRO
		'tag' => $tag,       //PRO
		'forum' => $forum	  //PRO
		);


	//Hacemos la query de todos los videos, y nos quedamos con un array solo con los filtrados
	$posted_videos_records = devdb_query_filter($a);

	//Generamos el HTML para mostrar los videos en el array.
	$html = devdb_post_video_records($posted_videos_records, $updated_offset, $todisplay, $perline, $mode, $more, $cat, $tag, $forum);

	echo $html;
	
	wp_die();
}






?>