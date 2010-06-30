<?php
// B.H.

/**
 * @author mkaganer
 * @desc A base class for location providers: locate a location in external data source and create hcal_location object
 */
class hcal_location_provider {
    
    /**
     * @var string Output language
     */
    public $lang;
    
    public function __construct($lang) {
        $this->lang = $lang;
    }
    
    /**
     * @desc Looks up the location in the external sources and returns hcal_location instance or false
     * @param string $id
     * @return hcal_location
     */
    final public function lookup($id) {
        return $this->get_location_by_id($id);
    }
    
    /**
     * @desc To be overrided by the specific location providers
     * @param string $id
     * @return hcal_location
     */
    protected function get_location_by_id($id) {
        throw new Exception('Not implemented');
    }
    

    /**
     * @desc Gets a 2 level associative array of location id vs human readable names (in the selected language)
     * @return array
     */
    public function get_all_locations($sort=true) {
        throw new Exception('Not implemented');
    }
    
}