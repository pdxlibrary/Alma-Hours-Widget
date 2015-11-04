# Alma Hours Widget

## Configuration


## CSS
CSS can be used to completely change the styles of the widget design. Every part of the widget has a relevant class that can be targeted with CSS.
```html
<link rel="stylesheet" href="alma_hours_widget.css" />
```

## JS
Creates the widget using AJAX to query the API script
```html
<script src="alma_hours_widget.js"></script>
```
### jQuery
The Alma Hours Widget requires jQuery to be loaded on the page where the widget is to be displayed.

## PHP
A PHP script is used to query the REST API interface, cleanup/format the output and feedback the results as JSON. Caching can also be easily added at this layer to speed up loading time and reduce API requests to Ex Libris.

## HTML
Each widget is added by adding a DIV tag with the class "alma_hours_widget".


### Basic Widget (displays next 7 days of hours):
```html
<div class="alma_hours_widget" data-library="MILLAR" data-title="Library Hours"></div>
```
- The **date-library** and **data-title** attributes are required attributes.

### Full Usuage Example:
```html
<!-- Alma Hours Widget CSS -->
<link rel="stylesheet" href="alma_hours_widget.css" />

<!-- jQuery Library -->
<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>

<!-- Alma Hours Widget JS -->
<script src="alma_hours_widget.js"></script>

<!-- Alma Hours Widget (next 7 days) -->
<div class="alma_hours_widget" data-library="MILLAR" data-title="Basic Hours"></div>
```


### Optional HTML5 Attributes:
- **data-start-date** – Used to set the start date for the widget
- **data-end-date** – Used to set the end date for the widget
- **data-date-format** – PHP standard date format options (e.g. "m/d/Y", "m/d/y", "m-d-y", etc…)
- **data-time-format** – PHP standard time format options (e.g. "g:ia", "H:i:s", etc…)


### Custom Time/Date Formats:
```html
<div class="alma_hours_widget" data-library="MILLAR" data-title="Custom Date/Time Format" data-date-format="m-d-y" data-time-format="H:i:s"></div>
```


### Custom Date Range:
```html
<div class="alma_hours_widget" data-library="MILLAR" data-title="Custom Date Range" data-start-date="2015-11-24" data-end-date="2015-12-05"></div>
```
