<?php

class gravatar_addressbook_backend extends rcube_addressbook
{
    private $name = 'Gravatar';

    /**
    * Returns addressbook name
    */
    function get_name()
    {
        return $this->name;
    }

    function set_name($name)
    {
        $this->name = $name;
    }


    /**
    * Save a search string for future listings
    *
    * @param mixed Search params to use in listing method, obtained by get_search_set()
    */
    function set_search_set($filter){
    }

    /**
    * Getter for saved search properties
    *
    * @return mixed Search properties used by this class
    */
    function get_search_set(){
        return null;
    }

    /**
    * Reset saved results and search parameters
    */
    function reset(){
    }

    /**
    * List the current set of contact records
    *
    * @param  array  List of cols to show
    * @param  int    Only return this number of records, use negative values for tail
    * @return array  Indexed list of contact records, each a hash array
    */
    function list_records($cols=null, $subset=0){
        return new rcube_result_set();
    }

    /**
    * Search records
    *
    * @param array   List of fields to search in
    * @param string  Search value
    * @param int     Matching mode:
    *                0 - partial (*abc*),
    *                1 - strict (=),
    *                2 - prefix (abc*)
    * @param boolean True if results are requested, False if count only
    * @param boolean True to skip the count query (select only)
    * @param array   List of fields that cannot be empty
    * @return object rcube_result_set List of contact records and 'count' value
    */
    function search($fields, $value, $mode=0, $select=true, $nocount=false, $required=array()){
        $res = new rcube_result_set();

        $config = rcmail::get_instance()->config;
        $size = intval($config->get('gravatar_size'));
        $rating = urlencode($config->get('gravatar_rating'));
        $schema = $config->get('gravatar_https', true) ? 'https' : 'http';
        $server = $config->get('gravatar_server', 'www.gravatar.com');

        if ($mode == 1 && in_array('email', $fields) &&
          count(array_diff($required, array('ID', 'email', 'photo')))==0){
            $total = 0;
            $val = is_array($value)?$value:array($value);
            $req = is_array($required)?$required:array($required);
            foreach ((array)$val as $idx => $col) {
                $v = $val[$idx];
                $hash = md5(strtolower(trim($v)));
                $record = array();
                $record['ID'] = $hash;
                $record['email'] = array($v);
                $url = "$schema://$server/avatar/$hash?s=$size&r=$rating&d=404";
                $p = @file_get_contents($url);
                if ($p === false){
                    if (in_array('photo', $req)) continue;
                }else{
                    $record['photo'] = $p;
                }
                $res->add($record);
                $total++;
            }
            $res->count = $total;
        }
        return $res;
    }

    /**
    * Count number of available contacts in database
    *
    * @return rcube_result_set Result set with values for 'count' and 'first'
    */
    function count(){
        return new rcube_result_set(0, 0);
    }

    /**
    * Return the last result set
    *
    * @return rcube_result_set Current result set or NULL if nothing selected yet
    */
    function get_result(){
        return null;
    }

    /**
    * Get a specific contact record
    *
    * @param mixed   Record identifier(s)
    * @param boolean True to return record as associative array, otherwise a result set is returned
    *
    * @return rcube_result_set|array Result object with all record fields
    */
    function get_record($id, $assoc=false){
        return $assoc ? null : new rcube_result_set(0, 0);
    }
}
