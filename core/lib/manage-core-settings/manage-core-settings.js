

	$(document).ready(function () {
		
		if($("select#logfile").length) {
			$("select#logfile").change(function () {
				$(location).attr("href","/manage-core-settings/log-files/"+$(this).val());	
			});
			$("#remove-parenthesis").click(function () {
					
				$("span.brackets").toggle();
			});
		}
			
			
		$("div.editable").on("click", "button.plus-minus", function (e) {
			e.preventDefault();
			var state = (window.getComputedStyle(this,':before').content).charCodeAt(1);
			var section = $(this).closest("button").data("section");
			
			switch(state) {
			case 61543:
				$(this).closest("div.row-edit").clone().find("input:text").val("").end().insertAfter("div.row-edit."+section+":last-child");
			break;
			case 61544:
				//alert("minus");
				$(this).closest("div.row-edit").remove();
				break;
			}	
		});
		
		
		if($("#toggle-all").length) {
			$("#toggle-all").click(function () {
				
					$("ul.fancyarray ul").each(function () {
						$(this).toggle();	
					});
				if($(this).children("span").hasClass("fa-toggle-on")) {
					$(this).children("span").removeClass("fa-toggle-on").addClass("fa-toggle-off");
				} else {
					$(this).children("span").removeClass("fa-toggle-off").addClass("fa-toggle-on");	
				}
			});
		}
           
		$("ul.fancyarray a.folder").click(function(e) {
			e.preventDefault();
			$("ul.fancyarray li a.folder").removeClass("active");
			$(this).addClass("active");
			var toggle = $(this).data("toggle");
			$("ul.fancyarray.toggle"+toggle).toggle();
			//$("#files ul.child").each(function() {
			//	if($(this).hasClass("toggle"+toggle)) {
			//		console.log("has "+toggle);
			//		$(this).show();
			//	} else {
			//		$(this).hide(); 
			//		console.log("shshd");
			//	}
			//});
		});

		
		
	});