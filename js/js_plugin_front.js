					jQuery(document).ready(function(){
				
					//LOAD MORE
					jQuery("#lpc_loadmore").click(function(){
						var strOffset = document.getElementById("d_offset").value;
						var strTodisplay = document.getElementById("d_todisplay").value;
						var strPerline = document.getElementById("d_perline").value;
						var strMode = document.getElementById("d_mode").value;
						var strMore = document.getElementById("d_more").value;
						var strCat = document.getElementById("d_cat").value;
						var strTag = document.getElementById("d_tag").value;
						var strForum = document.getElementById("d_forum").value;
						var data = {
							action: 'function_loadmore',
							offset: strOffset, 
							todisplay: strTodisplay, 
							perline: strPerline, 
							mode: strMode, 
							more: strMore, 
							cat: strCat, 
							tag: strTag, 
							forum: strForum
						};
						jQuery.post(ajax_object.ajax_url, data, function(response){
						//jQuery.post(ajax_object.ajax_url, {'action': 'function_loadmore', offset: strOffset, todisplay: strTodisplay, perline: strPerline, mode: strMode, more: strMore, cat: strCat, tag: strTag, forum: strForum}, function(response){
						jQuery("#d_offset").remove();
						jQuery("#d_todisplay").remove();
						jQuery("#d_perline").remove();
						jQuery("#d_mode").remove();
						jQuery("#d_more").remove();
						jQuery("#d_cat").remove();
						jQuery("#d_tag").remove();
						jQuery("#d_forum").remove();
						jQuery(response).insertBefore( "#lpc_loadmore" );
						});
					});
					
					
					})