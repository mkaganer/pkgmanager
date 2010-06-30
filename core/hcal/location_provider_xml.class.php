<?php
// B.H.

/**
 * @author mkaganer
 * @desc Implements location lookup in the XML file (like the one attached to the package)
 */
class hcal_location_provider_xml extends hcal_location_provider {
    
    public $xml_paths;
    
    public $countries = null;
    public $locations = null;
    
    /**
     * @param string $lang
     * @param string|array $paths A paths to the XML files to load (single string or array)
     * @param bool $glob if true (default) each path is expanded using glob() function
     */
    public function __construct($lang, $paths=null, $glob=true) {
        $this->lang = $lang;
        $this->xml_paths = array();
        if (empty($paths)) {
            $hcal_pkg = pkgman_manager::getp('hcal');
            $paths = array($hcal_pkg->path.'/locations/*.xml');
        } elseif (!is_array($paths)) $paths = array($paths);
        
        foreach ($paths as $path) {
            if ($glob) foreach(glob($path, GLOB_NOSORT | GLOB_BRACE) as $p) {
                if (file_exists($p)) $this->xml_paths[] = $p;
            } elseif (file_exists($path)) $this->xml_paths[] = $path;
        }
    }
    
    private function load_xmls() {
        if (!is_null($this->locations)) return; // seems like XML already loaded
        $this->countries = array();
        $this->locations = array();
        foreach ($this->xml_paths as $path) {
            $xml = simplexml_load_file($path);
            if (!$xml) throw new Exception("Unable to parse XML file: $path");
            $this->parse_xml($xml);
        }
    }
    
    /**
     * @param SimpleXMLElement $xml
     */
    private function parse_xml($xml) {
        if ($xml->getName()!='location_data') throw new Exception('XML format error');
        if (!isset($xml->countries) || !isset($xml->locations)) throw new Exception('XML format error');
        // parse countries
        foreach ($xml->countries->xpath('country') as $country) {
            $id = (string)$country['id'];
            $name = $country->xpath("name[@lang='$this->lang']");
            if (isset($name[0])) $name = (string)($name[0]);
            else $name = $id;
            $this->countries[$id] = $name;
        }
        foreach ($xml->locations->xpath('location') as $location) {
            $id = (string)$location['id'];
            $name = $location->xpath("name[@lang='$this->lang']");
            if (isset($name[0])) $name = (string)($name[0]);
            else $name = $id;
            $loc_data = array('name'=>$name);
            foreach ($location->attributes() as $k => $v) {
                if ($k=='lon') $loc_data['long'] = (string)$v;
                else $loc_data[$k] = (string)$v;
            }
            $this->locations[strtolower($id)] = $loc_data;
        }
    }
    
    /**
     * @param string $id
     * @return hcal_location
     */
    protected function get_location_by_id($id) {
        if (is_null($this->locations)) $this->load_xmls();
        $idl = strtolower($id);
        if (!isset($this->locations[$idl])) return false;
        return new hcal_location($this->locations[$idl],$id);
    }
    
    /**
     * @desc Gets a 2 level associative array of location id vs human readable names (in the selected language)
     * @return array
     */
    public function get_all_locations($sort=true) {
        if (is_null($this->locations)) $this->load_xmls();
        $locs = array();
        foreach ($this->locations as $loc) {
            $id = $loc['id'];
            $c = substr($id,0,strpos($id,'/'));
            if (isset($this->countries[$c])) $c = $this->countries[$c];
            $locs[$c][$id] = $loc['name'];
        }
        if ($sort) {
            foreach (array_keys($locs) as $c) asort($locs[$c]);
        }
        return $locs;
    }

}