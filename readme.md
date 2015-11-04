# Alma Hours Widget

## Configuration
### Create an Alma Hours API Key ###
You will need to go to the Alma Developer Network and create an Application. The Application Name can be anything (For example: Alma Hours Widget). The Platfrom should be "Web application". Under the API Management tab, add the Configuration API with read-only access. Once you have added the application, an API Key will be created. Enter this API Key in the alma_hours_widget.php file in the configuration section at the top of the file:
```php
// alma_hours_widget.php
// set your Alma Hours API Key - Replace XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX with your Alma API Key
define("ALMA_HOURS_API_KEY","XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX");
```

### Cross-Site Script Access ###
If you will be including your hours widget on a site other than the location where the alma_hours_widget.php file will be hosted, then you will need to enable XSS access. Simply add the list of domains where you will be displaying the hours widget in the $allowed_domains array in the alma_hours_widget.php in the configuration section at the top of the file:
```php
// alma_hours_widget.php
// example setting for allowing cross-site scripting (XSS)
$allowed_domains = array("http://www.allowed-website.edu","https://secure.allowed-website.edu");
```

## CSS
CSS can be used to completely change the styles of the widget design. Every part of the widget has a relevant class that can be targeted with CSS. The default stylesheet alma_hours_widget.css can be used as a starting point for your widget's design.
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
- The **data-library** and **data-title** attributes are required attributes.

### data-library
The value for data-library can be found in Alma by navigating to the Fulfillment Configuration - Configuration Menu. In the "You are configuring:" dropdown menu, select the library for which you would like to create an Hours Widget. Then select "Opening Hours" under Library. Select the "Summary" tab to reveal the library's "Code". The value for the Library Code should be used for the data-library attribute to create a widget for that library.

### Full Usage Example:
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
