/* ALMA HOURS WIDGET */

/* requires jQuery library */

var script_path = "alma_hours_widget.php";

jQuery(document).ready(function(){
	// load all widgets
	jQuery(".alma_hours_widget").each(function(){
		
		console.log("start...");
		
		// extract html5 parameters for widgets
		var input_library = jQuery(this).attr("data-library");
		var input_title = jQuery(this).attr("data-title");
		var input_start_date = jQuery(this).attr("data-start-date");
		var input_end_date = jQuery(this).attr("data-end-date");
		var input_date_format = jQuery(this).attr("data-date-format");
		var input_time_format = jQuery(this).attr("data-time-format");
		var widget = $(this);
		
		console.log(" | " + input_library + " | " + input_start_date + " | " + input_end_date + " | " + input_date_format + " | " + input_time_format);
		
		// load hours
		$.getJSON( script_path, { library: input_library, from: input_start_date, to: input_end_date, date_format: input_date_format, time_format: input_time_format } )
		.done(function( json ) {
			console.log("got hours...");
			var widget_days = [];
			$.each(json,function(date,day) {
				console.dir(day);
				if(day.closed)
					widget_days.push( "<li class='alma_hours_row'><span class='alma_hours_row_date'>"+day.date+"</span><span class='alma_hours_row_closed'>Closed</span></li>");
				else
					widget_days.push( "<li class='alma_hours_row'><span class='alma_hours_row_date'>"+day.date+"</span><span class='alma_hours_row_open'>" + day.open + "</span>-<span class='alma_hours_row_close'>" + day.close + "</span></li>" );
			});
			$(widget).html($( "<ul/>",{"class": "alma_hours_list",html: widget_days.join("")})).prepend("<div class='alma_hours_widget_title'>"+input_title+"</div>").show();
		})
		.fail(function( jqxhr, textStatus, error ) {
			var err = textStatus + ", " + error;
			console.log( "Request Failed: " + err );
		});
	});
});

