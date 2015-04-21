<?php

/*///////////////////////////////////////////////
//Entra el vector con las opciones de filtro del shortcode
//Devuelve un vector con los videos seleccionados
////////////////////////////////////////////////*/

function devdb_query_filter( $a ) {
	//Hacemos la query de los videos

	global $wpdb;

	$query_string = "
	SELECT * 
	FROM ".$wpdb->prefix ."displayembeddedvideosbydb ";
	If ($a['mode'] == 'daily_random') {  //si es random, sacaremos los videos desde ayer hasta el inicio de los tiempos
	$query_string .= "
	WHERE post_time < CURDATE()";
	}
	$query_string .= "
	ORDER BY post_id DESC;";
	$posted_videos_records = $wpdb->get_results($query_string);

	

	
	//QUITAMOS DE LOS VALORES DE ENTRADA DEL SHORTCODE TODOS LOS CARACTERES PROBLEMATICOS
	$a = devdb_sanitizar_textos_comp ($a);	


	//Si Random, desordenamos los videos 
	If ($a['mode'] == 'daily_random') {		
		
		//Semilla de random diferente para cada dia, mes y año.
		$semilla = date('dmy');	
		srand($semilla);
		
		shuffle($posted_videos_records);
	}

return $posted_videos_records;

}




/*///////////////////////////////////////////////
//Entra el vector con los videos filtrados
//Devuelve el HTML de los videos filtrados y limitados al num. $vids_to_display
////////////////////////////////////////////////*/
 
function devdb_post_video_records( $posted_videos_records, $offset, $vids_to_display, $vids_per_line, $mode, $more, $cat, $tag, $forum ) {

    //Extraemos los primeros n videos.	
	$posted_videos_records = array_slice($posted_videos_records, $offset, $vids_to_display, true);

	$percent_right_padding = 3;
	$percent_width = ( (100-(($vids_per_line-1)*$percent_right_padding)) / $vids_per_line );
	
	$i = 1;
	$html = "";
 	foreach ( $posted_videos_records as $posted_video ) {
			$title = $posted_video->post_title; 
			$url_post = $posted_video->post_url;
			
			if ($i == 1) { $html .= '<div class="linea_vids">'; } //Si es el inicio de una linea, ponemos un div de linea
			if ($i < $vids_per_line) {
				$html .= '<div class="cont_video_titulo" style="width:'.$percent_width.'%;padding:0 '.$percent_right_padding.'% 0 0;float:left;">';
				$html .= '<div class="videoWrapper">';
				$html .= '<iframe width="640" height="360" id="ytplayer" src="https://www.youtube.com/embed/'.$posted_video->vid_url.'" frameborder="0" allowfullscreen></iframe>';
				$html .= '</div>';
				$html .= '<div class="video_post_title"><a href="'.$url_post.'">'.$title.'</a></div>'; 
				$html .= '</div>';
				$i++;
			} else {
				$html .= '<div class="cont_video_titulo" style="width:'.$percent_width.'%;padding:0 0 0 0;float:right;">';
				$html .= '<div class="videoWrapper">';
				$html .= '<iframe width="640" height="360" id="ytplayer" src="https://www.youtube.com/embed/'.$posted_video->vid_url.'" frameborder="0" allowfullscreen></iframe>';
				$html .= '</div>';
				$html .= '<div class="video_post_title"><a href="'.$url_post.'">'.$title.'</a></div>';
				$html .= '</div>';
				$i = 1;
				$html .= '</div>'; //si es ultimo video de la linea, cerramos el div de linea
			}
	};
	
	$html .= '<div style="clear:both;"></div>';
	
	
	//Añadimos el LoadMore si estaba solicitado
	if ($more == 'yes') {
		if ($offset == '0') {
		$html .= '<div>';
		$html .= '<input id="lpc_loadmore" class="button" type="submit" value="More..." name="loadmore_displayembeddedvideosbydb"></input>';
		$html .= '</div>';
		}
		
		
		$html .= '<input type="hidden" id="d_offset" value="'.$offset.'" />';
		$html .= '<input type="hidden" id="d_todisplay" value="'.$vids_to_display.'" />';
		$html .= '<input type="hidden" id="d_perline" value="'.$vids_per_line.'" />';
		$html .= '<input type="hidden" id="d_mode" value="'.$mode.'" />';
		$html .= '<input type="hidden" id="d_more" value="'.$more.'" />';
		$html .= '<input type="hidden" id="d_cat" value="'.$cat.'" />';
		$html .= '<input type="hidden" id="d_tag" value="'.$tag.'" />';
		$html .= '<input type="hidden" id="d_forum" value="'.$forum.'" />';

	}
		
	return $html;
}			

function devdb_sanitizar_textos_comp ($texto){
$texto = str_replace('…', '', $texto);
$texto = str_replace('...', '', $texto);
$texto = str_replace('&#8230;', '', $texto);
return $texto;
}







/*/////////////////////////////////////////////////////////////////////////////////////////
//Recorre todos los posts del blog y llama a la funcion de analizar post para cada post
//Se guardan las detecciones en la base de datos
//No devuelve nada.
/////////////////////////////////////////////////////////////////////////////////////////*/

function devdb_recreate_collection() {
	global $wpdb;
	
	$table_name = get_option( "displayembeddedvideosbydb_tb_name" );
	$flag_recreation = get_option( "displayembeddedvideosbydb_flag_recreation" );
	
	//Registramos el inicio de la recreacion. No tocamos el valor "last_post_insp" ya que será null si es la primera vez, o tendra el valor del ultimo post inspeccionado, que necesitaremos.	
	$flag_recreation["timestamp_start"] = time();
	$flag_recreation["state"] = "start";
		
	update_option( "displayembeddedvideosbydb_flag_recreation", $flag_recreation );


	$query_string = "SELECT ID, post_content, post_date, post_type, post_status, post_parent, post_title
	FROM ".$wpdb->prefix ."posts
	WHERE ";
	if ($flag_recreation["last_post_insp"] != null) {$query_string .= "ID > '".$flag_recreation["last_post_insp"]."' AND ";}
	$query_string .= "post_status = 'publish' AND post_type <> 'revision' AND post_type <> 'attachment' AND post_type <> 'nav_menu_item'
	ORDER BY ID ASC;";
	
	$posts = $wpdb->get_results($query_string); 


	// $query_string = "SELECT ID, post_content
	// FROM ".$wpdb->prefix ."posts
	// WHERE ";
	// if ($flag_recreation["last_post_insp"] != null) {$query_string .= "ID > ".$flag_recreation["last_post_insp"]." AND";}
	// $query_string .= "post_status = 'publish' AND post_type <> 'revision' AND post_type <> 'attachment' AND post_type <> 'nav_menu_item'
	// ORDER BY ID ASC;";
	// $posts = $wpdb->get_results($query_string); 

	// $posts = $wpdb->get_results( 
	// "
	// SELECT t1.ID, t1.post_content
	// FROM ".$wpdb->prefix ."posts t1
	// LEFT JOIN ".$table_name." t2 ON t2.post_id = t1.ID 
	// WHERE t2.post_id IS NULL 
	// AND  t1.post_status = 'publish' AND t1.post_type <> 'revision' AND t1.post_type <> 'attachment' AND t1.post_type <> 'nav_menu_item'
	// ORDER BY t1.ID ASC;
	// "
	// );


	foreach ( $posts as $post ) {
		
		devdb_analize_post_and_save_vid($post->ID, $post->post_type, $post->post_status, $post->post_content, $post->post_date, $post->post_parent, $post->post_title, FALSE);
	
		//Actualizamos el flag de last_post_insp para cada post inspeccionado, para saber donde nos hemos quedado si falla
		$flag_recreation["last_post_insp"] = $post->ID;
		$flag_recreation["timestamp_last_post_insp"] = time();
		update_option( "displayembeddedvideosbydb_flag_recreation", $flag_recreation );
		
	}
	
	//FIN, registramos que hemos terminado
	$flag_recreation["state"] = "end";
	$flag_recreation["timestamp_end"] = time();
	$flag_recreation["last_post_insp"] = 0;
	$flag_recreation["timestamp_last_post_insp"] = time();
	update_option( "displayembeddedvideosbydb_flag_recreation", $flag_recreation );

}







/*///////////////////////////////////////////////
//Entra el post ID, el contenido y si se trata de un guardado normal o una recreacion de la coleccion
//Guarda las detecciones en la base de datos
////////////////////////////////////////////////*/
 
function devdb_analize_post_and_save_vid( $post_id, $post_type, $post_status, $content, $post_time, $post_parent, $post_title, $is_saving_post ) {
	
global $wpdb;
$table_name = get_option( "displayembeddedvideosbydb_tb_name" );


//Si se está guardando un post (o sea, no es un proceso de recreación) 
if ($is_saving_post) { 
	$wpdb->delete( $table_name, array( 'post_id' => $post_id ) );  //Borramos todos los registros de videos anteriores que pueda haber para este post 
	//Obtenemos el tipo, status, fecha y parent, que cuando es recreacion vienen en la query.
	$post_type = get_post_field( 'post_type', $post_id );
	$post_status = get_post_field( 'post_status', $post_id );
	$post_time = get_post_time('Y-m-d H:i:s', false, $post_id);
	$post_parent = get_post_field( 'post_parent', $post_id );
	$post_title = get_the_title( $post_id );
	$content = get_post_field( 'post_content', $post_id );
} else {  //Si es recreacion, vamos retrasando para no saturar CPU
	set_time_limit(90);
    usleep (50);
}

//Sólo si esta en el estado y tipo adecuado, analizaremos el contenido y guardaremos las coincidencias. 
if ( ( $post_status == 'publish' ) AND ($post_type != 'revision') AND ($post_type != 'attachment') AND ($post_type != 'nav_menu_item') ) {  //Nos aseguramos de que esta publicado, y de que no es una revision


	//DETECCIÓN YOUTUBE
	$vid_provider = 'youtube';
	
			$result_youtube = preg_match_all('~
					# Match non-linked youtube URL in the wild. (Rev:20130823)
					https?://         # Required scheme. Either http or https.
					(?:[0-9A-Z-]+\.)? # Optional subdomain.
					(?:               # Group host alternatives.
					  youtu\.be/      # Either youtu.be,
					| youtube\.com    # or youtube.com followed by
					  \S*             # Allow anything up to VIDEO_ID,
					  [^\w\-\s]       # but char before ID is non-ID char.
					)                 # End host alternatives.
					([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
					(?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
					[?=&+%\w.-]*        # Consume any URL (query) remainder.
					~ix',
				$content, $matches, PREG_SET_ORDER);
				
				
			if ($result_youtube <> 0) {
				
				if ($post_type == 'reply') {
					$post_title = get_the_title($post_parent);
					$post_url = bbp_get_reply_url($post_id);
				} else {
					//$post_title = get_the_title( $post_id );
					$post_url = get_permalink( $post_id );			
				}
				$categories = get_the_category( $post_id);
				$tags = get_the_tags( $post_id );
				if ( $categories == 0) { settype( $categories, "array");}
				if ( $tags == 0) { settype( $tags, "array");}
				$categories = serialize($categories);
				$tags = serialize($tags);
			
				//insertamos los videos detectados en la base de datos
				foreach ($matches as $match) {	
					$wpdb->insert( 
						$table_name, 
						array(
							'vid_url' => $match[1],
							//'vid_url' => substr($match[0], strpos($match[0], '=')+1, 11), //En vez del 32, poner la direccion del =
							'post_title' => $post_title,
							'post_url' => $post_url,
							'post_type' => $post_type,
							'post_parent' => $post_parent,
							'post_id' => $post_id,
							'post_tag' => $tags,
							'post_cat' => $categories,
							'post_time' => $post_time,
							'vid_provider' => $vid_provider 
							
						), 
						array( 
							'%s', 
							'%s',
							'%s', 
							'%s',
							'%d', 				
							'%d',
							'%s',
							'%s',
							'%s',
							'%s'
						) 
					);
				}	
			}

	
	}



}




?>