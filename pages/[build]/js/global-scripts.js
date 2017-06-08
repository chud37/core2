
$(document).ready(function () {
		
		
	var scrollto = $("#scrollto").val();
	
	$("#goto-list-books a").click(function () {
		var chapters = $(this).data("chapters");
		var book = $(this).attr("alt");
		$(".fsp-wrapper").hide();
		$("#goto-list-chapters .chapter").show().each(function () {
			var chapter = $(this).data("chapter");
			$(this).find("a").attr("href","/home/"+book+"/"+chapter);
			if(chapter > chapters) $(this).hide();
		});
		
	});
	
	$("form.modify-note").submit(function (e) {
		// e.preventDefault();
		var noteID = $(this).data("id");
		var noteContents = $("div.note-"+noteID).html();
		console.log(noteID);
		console.log(noteContents);
		$("textarea.note-"+noteID).val(noteContents);
		
		var frm = $(this);
		var btn = $(document.activeElement);
		if( btn.length &&
			frm.has(btn) &&
			btn.is('button[type="submit"], input[type="submit"], input[type="image"]') &&
			btn.is('[name]')) {
			frm.append('<input type="hidden" name="' + btn.attr('name') + '" value="' + btn.val() + '">');
		}
		
		// $(this).submit();
	});
	
	
	$(".open-popup").fullScreenPopup({
		// Options
		bgColor: "rgba(0,0,0,.8)"
	});

	$("a.btn.back-to-top").click(function () {
		$('html, body').animate({
			scrollTop: 0
		}, 1000);
	});
	
	$("#living-commentary span.bible-verse, #living-commentary span.verse-number, a.expand").click(function () {
		var verseID = $(this).data("verseid");
		if($("a.expand.verse-"+verseID).children("i").hasClass("fa-plus-circle")) {
			$("a.expand.verse-"+verseID).children("i").removeClass("fa-plus-circle").addClass("fa-minus-circle");
		} else {
			$("a.expand.verse-"+verseID).children("i").addClass("fa-plus-circle").removeClass("fa-minus-circle");
		}
		$("div.toggle-row.verse-"+verseID).toggle();
	});
	
	$("#selectBook.dial").knob({
		min:0,
		max:books.length-1,
		displayInput:false,
		'change' : function (v) {
			var book = books[Math.round(v)]['book'];
			var chapters = books[Math.round(v)]['chapters'];
			$("#selected-book").val(book);
			$("#linkSelectedBook").html("Read " + book + " 1");
			$("#linkSelectedBook").attr("href","/home/"+book.replace(" ","").replace(" ","")+"/1");
		},
		'release': function(v) {
			var book = books[Math.round(v)]['book'];
			var chapters = books[Math.round(v)]['chapters'];
			$('#selectChapter.dial').trigger(
				'configure',
				{"max":chapters}
			).val(1);
		},
		draw: function () {
			// "tron" case
			if(this.$.data('skin') == 'tron') {
				var a = this.angle(this.cv), sa = this.startAngle, sat = this.startAngle, ea, eat = sat + a, r = true;    
				this.g.lineWidth = this.lineWidth;
				this.o.cursor && (sat = eat - 0.3) && (eat = eat + 0.3);
				if (this.o.displayPrevious) {
					ea = this.startAngle + this.angle(this.value);
					this.o.cursor && (sa = ea - 0.3) && (ea = ea + 0.3);
					this.g.beginPath();
					this.g.strokeStyle = this.previousColor;
					this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
					this.g.stroke();
				}
				this.g.beginPath();
				this.g.strokeStyle = r ? this.o.fgColor : this.fgColor ;
				this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
				this.g.stroke();
				this.g.lineWidth = 2;
				this.g.beginPath();
				this.g.strokeStyle = this.o.fgColor;
				this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
				this.g.stroke();
				return false;
			}
		}
	}).val($("#current-book-number").val());
	
	
	$("#selectChapter.dial").knob({
		min:1,
		displayInput:false,
		'change': function (v) {
			$("#selected-chapter").val(Math.round(v));
			$("#linkSelectedBook").html("Read " + $("#selected-book").val() + " " + Math.round(v));
			$("#linkSelectedBook").attr("href","/home/"+$("#selected-book").val().replace(" ","").replace(" ","")+"/"+Math.round(v));
			
		},
		draw : function () {
			// "tron" case
			if(this.$.data('skin') == 'tron') {
				var a = this.angle(this.cv), sa = this.startAngle, sat = this.startAngle, ea, eat = sat + a, r = true;    
				this.g.lineWidth = this.lineWidth;
				this.o.cursor && (sat = eat - 0.3) && (eat = eat + 0.3);
				if (this.o.displayPrevious) {
					ea = this.startAngle + this.angle(this.value);
					this.o.cursor && (sa = ea - 0.3) && (ea = ea + 0.3);
					this.g.beginPath();
					this.g.strokeStyle = this.previousColor;
					this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
					this.g.stroke();
				}
				this.g.beginPath();
				this.g.strokeStyle = r ? this.o.fgColor : this.fgColor ;
				this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
				this.g.stroke();
				this.g.lineWidth = 2;
				this.g.beginPath();
				this.g.strokeStyle = this.o.fgColor;
				this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
				this.g.stroke();
				return false;
			}
		} 
	}).val($("#current-chapter-number").val());

	
	
	if((scrollto != 1) && (scrollto != "") && (scrollto != undefined)) {
		$('html, body').animate({
			scrollTop: $("#verse"+scrollto).offset().top - 10
		}, 1000);
		$("#verse"+scrollto).css("background","#FFFDE2");
	}
		
	$("a.add_bookmark").click(function (e) {
		e.preventDefault();
		var verseID = $(this).data("verseid");
		var number = $("img.logo").data("number");
		var thishref = $(this);
		if(verseID) {
			$.ajax({
				type: 'POST',
				url: '/bin/[build]/bookmark.php?id='+verseID+"&n="+number,
				success: function(result){
					switch(result) {
						case "0":
							alert("Failure to toggle bookmark.");
						break;
						case "-37":                                                                                                                                                 
							console.log("Removed bookmark: " + result);
							thishref.find("span.not-bookmarked").addClass("active");
							thishref.find("span.bookmarked").removeClass("active");
						break;
						default:
							console.log("Bookmarked: " + result);
							thishref.find("span.bookmarked").addClass("active");
							thishref.find("span.not-bookmarked").removeClass("active");
						break;
					}
				},
				error:function(){
					console.log("Failure to like teaching.");
				}
			});
		} 		
	});
});