EDD License Handler
===================

License / updater handler for Easy Digital Downloads extensions.

This class should be instantiated with all premium EDD extensions sold through EasyDigitalDownloads.com in order to include licensing and updates.

```php
// Load the EDD license handler only if not already loaded. Must be placed in the main plugin file
if( class_exists( 'EDD_License' ) ) {
	// Instantiate the licensing / updater. Must be placed in the main plugin file
	$license = new EDD_License( __FILE__, 'Extension Name Here', '1.0', 'Your Name' );
}
```

Full Example:
=============
```php

// Load the EDD license handler only if not already loaded. Must be placed in the main plugin file
if( class_exists( 'EDD_License' ) )
	$license = new EDD_License( __FILE__, 'PDF Stamper', '1.0.5', 'Daniel J Griffiths' );
}
```

Own Updater API Example:
========================
If you want to use this class with your website's "Easy Digital Downloads - Software Licenses" plugin then you need to provide your website URL as shown below.

```php

// Load the EDD license handler only if not already loaded. Must be placed in the main plugin file
if( ! class_exists( 'EDD_License' ) )
	include( dirname( __FILE__ ) . '/includes/EDD_License_Handler.php' );

$license = new EDD_License( __FILE__, 'Testimonials Widget Premium', '1.13.4', 'Michael Cannon', null, 'http://aihr.us' );
```

Requirements
============

This class can only be used with EDD v1.7 and later.
