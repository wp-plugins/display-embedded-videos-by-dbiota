					jQuery(document).ready(function(){

					//RECREACION COLECCION					
					jQuery("#sc_result").css("display", "none");
					jQuery("#vid_list").css("display", "none");
					jQuery("#close_vid_list").css("display", "none");
					jQuery("#gif_cargando").css("display", "none");
					
					jQuery("#view_vid_list").click(function(){
					jQuery("#view_vid_list").css("display", "none");
					jQuery("#close_vid_list").css("display", "block");
					jQuery("#vid_list").css("display", "block"); 					
					});
					
					jQuery("#close_vid_list").click(function(){
					jQuery("#view_vid_list").css("display", "block");
					jQuery("#close_vid_list").css("display", "none");
					jQuery("#vid_list").css("display", "none"); 					
					});
					
					jQuery("#carga").click(function(){
					//jQuery("#destino").css("display", "none");
					jQuery("#view_vid_list").css("display", "none");
					jQuery("#close_vid_list").css("display", "none");
					jQuery("#carga").css("display", "none"); //Esta linea es añadida para ocultar el boton si se clica el boton (al final se recupera). 
					jQuery("#vid_counter").css("display", "none"); //Esta linea es añadida para ocultar el contador de videos si se clica el boton. 
					jQuery("#vid_list").css("display", "none"); //Esta linea es añadida para ocultar la lista de videos si se clica el boton.
					jQuery("#cargando").css("display", "block");
					jQuery("#gif_cargando").css("display", "block");
					jQuery.ajaxSetup({
						timeout: 1200000
					});
					//jQuery("#destino").load(window.location.protocol + "//" + window.location.hostname + "/wp-content/plugins/display-embedded-videos-by-dbiota/display-embedded-videos-by-dbiota-recreate-collection.php", function(){
					var data = {
						'action': 'recreate_collection',
					};					
					jQuery("#destino").load(ajax_object.ajax_url, data, function(){
					jQuery("#cargando").css("display", "none");
					jQuery("#gif_cargando").css("display", "none");
					jQuery(".error").css("display", "none");
					//jQuery("#destino").css("display", "block");
					//jQuery("#carga").css("display", "block");
					});
					});
					
					//GENERACION SHORTCODE
					jQuery("#shortcode_generation").click(function(){
					
						jQuery("#sc_result").fadeOut(400,function() {
						
							//Borramos los que pueden aparecer o no, por si tenian un valor de una ejecucion anterior
							jQuery("#sc_more").text("");
							jQuery("#sc_cat").text("");
							jQuery("#sc_tag").text("");
							jQuery("#sc_forum").text("");
						
							var a = document.getElementById("select_mode");
							var strMode = " mode=\"" + a.options[a.selectedIndex].text + "\"";
							jQuery("#sc_mode").text(strMode);
							
							var strVidsToDisplay = " vids_to_display=\"" + document.getElementById("input_vids_to_display").value + "\"";
							jQuery("#sc_num").text(strVidsToDisplay);
							
							var strVidsPerLine = " vids_per_line=\"" + document.getElementById("input_vids_per_line").value + "\"";
							jQuery("#sc_per_line").text(strVidsPerLine);
							
							var e = document.getElementById("select_more");
								if (e.options[e.selectedIndex].text == "yes") {
									var strMode = " more=\"" + e.options[e.selectedIndex].text + "\"";
									jQuery("#sc_more").text(strMode);
								}

							
							if ( jQuery("#cat-dropdown").length ) {
								var b = document.getElementById("cat-dropdown");
								if (b.options[b.selectedIndex].value != "all") {
									var strCat = " cat=\"" + b.options[b.selectedIndex].text + "\"";
									jQuery("#sc_cat").text(strCat);
								}
							}
							if ( jQuery("#tag-dropdown").length ) {
								var c = document.getElementById("tag-dropdown");
								if (c.options[c.selectedIndex].value != "all") {
									var strTag = " tag=\"" + c.options[c.selectedIndex].text + "\"";
									jQuery("#sc_tag").text(strTag);
								}
							}
							if ( jQuery("#forum-dropdown").length ) {
								var d = document.getElementById("forum-dropdown");
								if (d.options[d.selectedIndex].value != "all") {
									var strForum = " forum=\"" + d.options[d.selectedIndex].text + "\"";
									jQuery("#sc_forum").text(strForum);
								}
							}
							
							jQuery("#sc_result").fadeIn(400);
							
						}); //hacemos al principio fadeout realmente para que si se pulsa más el boton luego se vean aparecer
					
	
					});
					
					
					
					})