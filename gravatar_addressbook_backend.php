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
    function list_records($cols=null, $subset=0, $nocount = false){
        return new rcube_result_set();
    }

    private static function _get_api_url($email, $api, &$vars){
        $emailExploded = explode("@", $email);
        $d = strtolower(trim($emailExploded[1]));
        $e = strtolower(trim($email));
        $o = array('%e' => urlencode($e), '%m' => md5($e), '%a' => sha1($e), '%d' => urlencode($d));
        return strtr($api, array_merge($vars, $o));
    }

    private static function _get_contents_apis($urls){
        foreach ($urls as $url) {
            $str = @file_get_contents($url);
            if ($str !== false){
                return $str;
            }
        }
        return false;
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
        $fil = is_array($fields)?$fields:array($fields);
        $val = is_array($value)?$value:array($value);
        $req = is_array($required)?$required:array($required);
        $res = new rcube_result_set();

        $config = rcmail::get_instance()->config;
        $acfg = array('%%' => '%',
                      '%z' => intval($config->get('gravatar_size', 128)),
                      '%r' => urlencode($config->get('gravatar_rating', 'g')),
                      '%s' => $config->get('gravatar_https', false) ? 'https' : 'http'
                  );
        $ge = $config->get('gravatar_enabled', false);
        $gpapi = $config->get('gravatar_photo_api', '%s://www.gravatar.com/avatar/%m?s=%z&r=%r&d=404');
        $ce = $config->get('gravatar_custom', false);
        $cpapi = $config->get('gravatar_custom_photo_api');

        if ($mode == 1 && in_array('email', $fil) &&
          count(array_diff($req, array('ID', 'email', 'photo')))==0){
            $total = 0;
            foreach ((array)$val as $idx => $col) {
                $v = $val[$idx];
                $id = strtolower(trim($v));
                $record = array();
                $record['ID'] = $id;
                $record['email'] = array($v);
                $urls = array();
                if ($ce && $cpapi != null) {
                    //First custom API
                    $urls[] = gravatar_addressbook_backend::_get_api_url($v, $cpapi, $acfg);
                }
                if ($ge) {
                    //Then Gravatar
                    $urls[] = gravatar_addressbook_backend::_get_api_url($v, $gpapi, $acfg);
                }
                $p = gravatar_addressbook_backend::_get_contents_apis($urls);
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
