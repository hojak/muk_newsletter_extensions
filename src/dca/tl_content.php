<?php




$GLOBALS['TL_DCA']['tl_content']['palettes']['nl_muk_event'] = 
	.'{type_legend},type;'
	.'{event_legend},muk_calendar,muk_event;'
	.'{headline_legend},headline;{text_legend},text;'
	.'{image_legend},addImage;{template_legend:hide},customTpl;'
	//.'{protected_legend:hide},protected;'
	.'{expert_legend:hide},guests,cssID;'
	.'{invisible_legend:hide},invisible,start,stop';
	
	

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



class tl_content_muk_nl extends \tl_content {
	
	
	function get_calendar_options ( $dc ) { 
		$dbResult = $this->Database->prepare ( "select id, title from tl_calendar order by name")->execute();
        
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
		$calendar_id = $dc->activeRecord->hjk_vbphoenix_season;

        
        if ( ! $calendar_id)
            return array ( "0" => "Bitte wählen Sie zuerst einen Kalender aus!");
        
        $dbResult = $this->Database->prepare ( "select id, title, startDate from tl_calendarevents where pid=? order startDate desc, title")->execute( $calendar_id );
        
        if ( $dbResult->numRows == 0) {
            return array ( "0" => "Im ausgewählten Kalender sind noch keine Ereignisse eingetragen!");
        }
        
        $result = array ();
        while ( $dbResult->next () ) {
            $result[ $dbResult->id ] = $dbResult->title . " (" . date ( "d.m.Y", $dbResult->startDate ).")";
        }
        
        return $result;
	}
	
}