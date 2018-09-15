<?php


namespace MUK\NewsletterExtentions;


\log_message ( "loaded Event Element" );

class EventElement extends \NewsletterContent\Elements\ContentText {


	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'nl_muk_event';


	/**
	 * Generate the content element
	 */
	protected function compile() {
		log_message ( "compiling event element!" );
		
		parent::compile();
		
		// make event available to the template
		
		$this->Template->event = \CalendarEventsModel::findById ( $this->muk_event );
		
		
		log_message ( "template: " . $this->strTemplate );
	}
	
}