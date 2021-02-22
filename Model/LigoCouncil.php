<?

class LigoCouncil extends AppModel {
  // Required by COmanage Plugins
  public $cmPluginType = "other";

  // Define class name for cake
  public $name = "LigoCouncil";

  /**
   * Expose menu items.
   *
   * @return Array with menu location type as key array of labels, controllers, actions as values.
   */
  public function cmPluginMenus() {

    $menus = array();

    $coPersonId = CakeSession::read('Auth.User.co_person_id');

    if(!empty($coPersonId)) {
      $coPersonModel = ClassRegistry::init('CoPerson');
      $args = array();
      $args['conditions']['CoPerson.id'] = $coPersonId;
      $args['contain']['CoGroupMember']['CoGroup'] = 'Cou';
      $coPerson = $coPersonModel->find('first', $args);

      foreach($coPerson['CoGroupMember'] as $m) {
        if($m['CoGroup']['group_type'] == GroupEnum::Admins && $m['CoGroup']['cou_id']) {
          $couId = $m['CoGroup']['cou_id'];
          $couName = $m['CoGroup']['Cou']['name'];
          $menus["copeople"] = array(_txt('pl.ligo_council.menu.copeople', array($couName)) => array('controller' => 'council_delegates', 'action' => 'index', 'cou' => $couId));
          break;
        }
      }
    }

    return $menus;
  }

}
