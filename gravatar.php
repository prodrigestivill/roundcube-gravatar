<?php

require_once(dirname(__FILE__) . '/gravatar_addressbook_backend.php');

class gravatar extends rcube_plugin
{
  public $task = 'mail|addressbook|settings';
  private $abook_id = 'gravatar_addressbook';
  private $abook_name = 'Gravatar'; //Replaced in init

  function init()
  {
    $this->add_texts('localization');
    $this->abook_name = $this->gettext('gravatar_plugin');

    $this->load_config();

    $this->add_hook('preferences_list', array($this, 'preferences_list'));
    $this->add_hook('preferences_save', array($this, 'preferences_save'));
    $this->add_hook('addressbooks_list', array($this, 'addressbooks_list'));
    $this->add_hook('addressbook_get', array($this, 'addressbook_get'));
  }

  function is_enabled()
  {
    $config = rcmail::get_instance()->config;
    return (bool)($config->get('gravatar_enabled', false) || $config->get('gravatar_custom', false));
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
      $params['instance']->set_name($this->abook_name);
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
        $field_id = 'rc_gravatar_enabled';
        $checkbox = new html_checkbox(array('name' => $field_id, 'id' => $field_id, 'value' => 1));
        $params['blocks'][$this->abook_id]['options'][$field_id] = array(
            'title' => html::label($field_id, rcube::Q($this->gettext('gravatar_use'))),
            'content' => $checkbox->show($rcmail->config->get('gravatar_enabled', false))
        );
        $field_id = 'rc_gravatar_size';
        $input = new html_inputfield(array('name' => $field_id, 'id' => $field_id, 'size' => 45));
        $params['blocks'][$this->abook_id]['options'][$field_id] = array(
            'title' => html::label($field_id, rcube::Q($this->gettext('gravatar_size'))),
            'content' => $input->show($rcmail->config->get('gravatar_size', 128))
        );
        $field_id = 'rc_gravatar_rating';
        $select   = new html_select(array('name' => $field_id, 'id' => $field_id));
        $select->add(array(rcube::Q($this->gettext('gravatar_G')),
                           rcube::Q($this->gettext('gravatar_PG')),
                           rcube::Q($this->gettext('gravatar_R')),
                           rcube::Q($this->gettext('gravatar_X'))),
                     array('g', 'pg', 'r', 'x'));
        $params['blocks'][$this->abook_id]['options'][$field_id] = array(
            'title'   => html::label($field_id, $this->gettext('gravatar_rating')),
            'content' => $select->show($rcmail->config->get('gravatar_rating', 'g')),
        );
        if ($rcmail->config->get('gravatar_custom_photo_api')!=null) {
            $field_id = 'rc_gravatar_custom';
            $checkbox = new html_checkbox(array('name' => $field_id, 'id' => $field_id, 'value' => 1));
            $params['blocks'][$this->abook_id]['options'][$field_id] = array(
                'title' => html::label($field_id, rcube::Q($this->gettext('gravatar_use_custom'))),
                'content' => $checkbox->show($rcmail->config->get('gravatar_custom', false))
            );
        }
    }
    return $params;
  }

  function preferences_save($params)
  {
    if($params['section'] == 'addressbook') {
        $rcmail = rcmail::get_instance();
        $params['prefs']['gravatar_enabled'] = isset($_POST['rc_gravatar_enabled']) ? true : false;
        $params['prefs']['gravatar_size'] = isset($_POST['rc_gravatar_size']) && intval($_POST['rc_gravatar_size']) != 0 ? intval($_POST['rc_gravatar_size']) : 128;
        $params['prefs']['gravatar_rating'] = isset($_POST['rc_gravatar_rating']) ? $_POST['rc_gravatar_rating'] : 'g';
        if ($rcmail->config->get('gravatar_custom_photo_api')!=null) {
            $params['prefs']['gravatar_custom'] = isset($_POST['rc_gravatar_custom']) ? true : false;
        }
    }
    return $params;
  }
}
