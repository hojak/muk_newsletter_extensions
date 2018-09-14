<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'MUK',
	'MUK\\NewsletterExtensions',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // Classes

    // Models
    
    // BE-Modules

    // FE-Modules

    // ContentElements
	//'MUK\\NewsletterExtensions\\EventElement' => 'system/modules/muk_newsletter_extensions/elements/EventElement.php',

    // other
));


/**
 * Register the templates
 */

TemplateLoader::addFiles(array( 
	'nl_muk_event'  => 'system/modules/muk_newsletter_extensions/templates',
));
