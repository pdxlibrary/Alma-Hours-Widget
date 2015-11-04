<html>
<body>
<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>


<link rel="stylesheet" href="alma_hours_widget.css" />
<script src="alma_hours_widget.js"></script>

<p>
	<?php print(htmlentities('<div class="alma_hours_widget" data-library="MILLAR" data-title="Basic Hours"></div>')); ?>
</p>

<div class="alma_hours_widget" data-library="MILLAR" data-title="Basic Hours"></div>

<div style="clear:both"></div>
<hr />

<p>
	<?php print(htmlentities('<div class="alma_hours_widget" data-library="MILLAR" data-title="Custom Date/Time Format" data-date-format="m-d-y" data-time-format="H:i:s"></div>')); ?>
</p>
<div class="alma_hours_widget" data-library="MILLAR" data-title="Custom Date/Time Format" data-date-format="m-d-y" data-time-format="H:i:s"></div>

<div style="clear:both"></div>
<hr />

<p>
	<?php print(htmlentities('<div class="alma_hours_widget" data-library="MILLAR" data-title="Custom Date Range" data-start-date="2015-11-24" data-end-date="2015-12-04"></div>')); ?>
</p>
<div class="alma_hours_widget" data-library="MILLAR" data-title="Custom Date Range" data-start-date="2015-11-24" data-end-date="2015-12-04"></div>

<div style="clear:both"></div>
<hr />

</body>
</html>