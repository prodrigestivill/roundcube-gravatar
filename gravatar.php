<?php

require_once(dirname(__FILE__) . '/gravatar_addressbook_backend.php');

class gravatar extends rcube_plugin
{
  public $task = 'mail|addressbook|settings';
  private $abook_id = 'gravatar_addressbook';
  private $abook_name = gravatar_addressbook_backend::name;

  function init()
  {
    $rcmail = rcmail::get_instance();

    $this->add_hook('preferences_list', array($this, 'preferences_list'));
    $this->add_hook('preferences_save', array($this, 'preferences_save'));
    $this->add_hook('addressbooks_list', array($this, 'addressbooks_list'));
    $this->add_hook('addressbook_get', array($this, 'addressbook_get'));

  }

  function is_enabled()
  {
    $prefs = rcmail::get_instance()->user->get_prefs();
    return (bool)$prefs['gravatar_enabled'];
  }

  // roundcube collects information about available addressbooks
  function addressbooks_list($params)
  {
    if($this->is_enabled()) {
      $params['sources'][] = array('id' => $this->abook_id,
                                   'name' => $this->abook_name,
                                   'groups' => false,
                                   'readonly' => true,
                                   'autocomplete' => false,
                                   'hidden' => true);
    }
    return $params;
  }

  // user opens addressbook
  function addressbook_get($params)
  {
    if($params['id'] == $this->abook_id) {
      $params['instance'] = new gravatar_addressbook_backend();
      $params['writable'] = false;
      $params['hidden'] = true;
    }
    return $params;
  }

  function preferences_list($params)
  {
    if($params['section'] == 'addressbook') {
        $rcmail = rcmail::get_instance();
        $params['blocks'][$this->abook_id]['name'] = $this->abook_name;
        $field_id = 'rc_gravatar_plugin';
        $checkbox = new html_checkbox(array('name' => $field_id, 'id' => $field_id, 'value' => 1));
        $params['blocks'][$this->abook_id]['options'][$field_id] = array(
        'title' => html::label($field_id, "Enable"),
        'content' => $checkbox->show($rcmail->config->get('gravatar_enabled'))
        );
    }
    return $params;
  }

  function preferences_save($params)
  {
    if($params['section'] == 'addressbook') {
      $params['prefs']['gravatar_enabled'] = isset($_POST['rc_gravatar_plugin']) ? true : false;
    }
    return $params;
  }
}
