<?php


$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_muk_event__step1'] = 
	'{type_legend},type;'
	.'{event_legend},muk_calendar;';


$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_muk_event__step2'] = 
	'{type_legend},type;'
	.'{event_legend},muk_calendar,muk_event;';
	
$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_muk_event__step3'] = 
	'{type_legend},type;'
	.'{headline_legend},headline;{text_legend},text;'
	.'{image_legend},addImage;{template_legend:hide},customTpl;'
	//.'{protected_legend:hide},protected;'
	.'{expert_legend:hide},guests,cssID;'
	.'{invisible_legend:hide},invisible,start,stop';
	
	
$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_muk_event'] =
	$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_muk_event__step1'];
	
	

$GLOBALS['TL_DCA']['tl_content']['fields']['muk_calendar'] = array (
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['muk_calendar'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'eval'                    => array( 
		'submitOnChange' => true,
		'mandatory'      => true,
		'includeBlankOption' => true,
	),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
	'options_callback' => array ('tl_content_muk_nl', 'get_calendar_options') ,
    'relation'                => array ('type' => 'hasOne', 'load' => 'lazy'),
    'foreignKey'              => 'tl_calendar.title',
);
	
$GLOBALS['TL_DCA']['tl_content']['fields']['muk_event'] = array (
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['muk_event'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'eval'                    => array( 
		'submitOnChange' => true,
		'mandatory'      => true,
		'includeBlankOption' => true,
	),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
	'options_callback' => array ('tl_content_muk_nl', 'get_event_options') ,
    'relation'                => array ('type' => 'hasOne', 'load' => 'lazy'),
    'foreignKey'              => 'tl_calendar_events.title',
);


$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array ( 'tl_content_muk_nl', 'onLoad' );
$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][] = array ( 'tl_content_muk_nl', 'onSubmit' );


class tl_content_muk_nl extends \tl_content {
	
	private $stored = array ();
	
	
	function onLoad ( $dc ) {
		if ( \Input::get('do') != 'newsletter') {
			\log_message ("PRECHECK-> out !!!!!");
			return;
		}
		
		// get the current dataset from the database
		$dbResult = $this->Database->prepare ( "select * from tl_content where id=?")->execute (\Input::get('id'));
		
		$this->stored = array (
			"calendar" => $dbResult->muk_calendar,
			"event"    => $dbResult->muk_event
		);
		
		if ( $dbResult->type != 'nl_muk_event') {
			\log_message ( "returning !!!!!!!");
			return;
		}
			
		if ( $dbResult->muk_event )
			$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_muk_event'] =
				$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_muk_event__step3'];
		else if ( $dbResult->muk_calendar )
			$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_muk_event'] =
				$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_muk_event__step2'];
	}
	
	
	function onSubmit ( $dc  ) {
		if ( ! $this->stored['event'] && $dc->activeRecord->muk_event  ) {
			// ok, an event has been selected! -> set the content!
			$thisModel = \ContentModel::findById ( $dc->activeRecord->id );
            $event = \CalendarEventsModel::findById ( $dc->activeRecord->muk_event);
			
			$elements = \ContentModel::findByPid ( $dc->activeRecord->muk_event, array ( 'order' => 'sorting' ));
			if ( $elements ) 
				$elements = $elements->getModels();
			else
				$elements = array ();

			$thisModel->overwriteMeta = 1;
			$thisModel->imageUrl = \Events::generateEventUrl ( $event ); 
			$thisModel->headline = serialize ( array ( 'unit' => 'h2', 'value' => $event->title ));
			
			$eventImage = false;
			if ( $event->addImage ) {
				$thisModel->addImage = $event->addImage;
				$thisModel->singleSRC = $event->singleSRC;
				$thisModel->imagemargin = $event->imagemargin;
				$thisModel->alt = $event->alt;
				$thisModel->imageTitle = $event->imageTitle;
				$thisModel->size = $event->size;
				$thisModel->imageMargin = $event->imageMargin;
				$thisModel->fullsize = $event->fullsize;
				$thisModel->caption = $event->caption;
				$thisModel->floating = $event->floating;
				
				$eventImage = true;
				\Message::addInfo ( "Die Bildeinstellungen wurden vom ausgewählten Event übernommen!");
			}
			
            $found = false; 
			
			if ( sizeof ( $elements) == 0 ) {
				\Message::addInfo ( "Das ausgewählte Event hat keinen Content!");
			} else {
				$i = 0;
				while ( ! $found && $i < sizeof ( $elements) ) {
					$element = $elements[$i];
					
					if ( $element->type == "text") {
						$thisModel->text = $element->text;
						\Message::addInfo ( "Der Text vom " . ($i+1). ". gefundenen Element wurde in dieses Formular kopiert.");
                        
						if ( ! $eventImage ) {
							if ( $element->addImage ) {
								$thisModel->addImage = $element->addImage;
								$thisModel->singleSRC = $element->singleSRC;
								$thisModel->imagemargin = $element->imagemargin;
								$thisModel->alt = $element->alt;
								$thisModel->imageTitle = $element->imageTitle;
								$thisModel->size = $element->size;
								$thisModel->imageMargin = $element->imageMargin;
								$thisModel->fullsize = $element->fullsize;
								$thisModel->caption = $element->caption;
								$thisModel->floating = $element->floating;
								
								\Message::addInfo ( "Das Event hatte kein Bild. Die Bildeinstellungen wurden vom ersten Text-Inhaltselement übernommen!");
							} else {
								\Message::addInfo ( "Weder für das Event noch für das erste Text-Element ist ein Bild definiert!");
							}
						}
						
						$found = true;
					}
					
					$i++;
				}
			}
			
			if ( ! $found ) {
				$thisModel->text = $event->teaser;
				\Message::addInfo ( "Das ausgewählte Event enthält keine Text-Elemente als Inhalt!");
			}
			
			$thisModel->save();
			
		}
	}
	
	
	function get_calendar_options ( $dc ) {
 
		$dbResult = $this->Database->prepare ( "select id, title from tl_calendar order by title")->execute();
        
        if ( $dbResult->numRows == 0) {
            return array ( "0" => "Bitte legen Sie mindestens einen Kalender an!");
        }
 	
		$result = array ();
        while ( $dbResult->next () ) {
            $result[ $dbResult->id] = $dbResult->title;
        }
		
		return $result;
	}
	
	
	
	function get_event_options ( $dc ) {
		$calendar_id = $dc->activeRecord->muk_calendar;

        
        if ( ! $calendar_id)
            return array ( "0" => "Bitte wählen Sie zuerst einen Kalender aus!");
        
        $dbResult = $this->Database->prepare ( "select id, title, startDate from tl_calendar_events where pid=? order by startDate desc, title")->execute( $calendar_id );
        
        if ( $dbResult->numRows == 0) {
            return array ( "0" => "Im ausgewählten Kalender sind noch keine Ereignisse eingetragen!");
        }
        
        $result = array ();
        while ( $dbResult->next () ) {
            $result[ $dbResult->id ] = $dbResult->title . " (" . date( "d.m.Y", $dbResult->startDate ).")";
        }
        
        return $result;
	}
	
}