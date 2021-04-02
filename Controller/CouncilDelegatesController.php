<?

App::uses("StandardController", "Controller");

class CouncilDelegatesController extends StandardController {
  // Class name, used by cake
  public $name = "CouncilDelegates";

  /*
   * Index action for this controller.
   * - postcondition: council_delegates view variable set
   * - postcondition: title_for_layout view variable set
   *
   * @since  COmanage Registry v3.3.1
   * @return Array Permissions
   */
  function index() {
    $couId = $this->params['named']['cou'];
    $this->set('cou_id', $couId);

    $this->set('title_for_layout', _txt('ct.council_delegates.pl'));

    // Query for current delegates.
    $args = array();
    $args['conditions']['CouncilDelegate.cou_id'] = $couId;
    $args['contain']['CoPerson'] = 'PrimaryName';

    $councilDelegates = $this->CouncilDelegate->find('all', $args);

    // Process incoming POST CouncilDelegate data.
    if($this->request->is('post')) {
      foreach($this->data['CouncilDelegate']['rows'] as $d) {
        // Reset model state between save/delete calls.
        $this->CouncilDelegate->clear();

        // CO Person is current delegate and selected to remain a delegate.
        if(!empty($d['id']) && $d['delegate'] == 1) {
          continue;
        }

        // CO Person is current delegate and selected to be removed so delete.
        if(!empty($d['id']) && $d['delegate'] == 0) {
          if(!$this->CouncilDelegate->delete($d['id'])) {
            // TODO Flash error here.
          }
          continue;
        }

        // CO Person is not current delegate and selected to become delegate.
        if(empty($d['id']) && $d['delegate'] == 1) {
          $newDelegate['CouncilDelegate'] = $d;
          if(!$this->CouncilDelegate->save($newDelegate)) {
            // TODO Flash error here.
          }
        }

        //TODO History Records
      }

      // Redirect back to index to render updated delegate information.
      $redir = array();
      $redir['plugin'] = 'ligo_council';
      $redir['controller'] = 'council_delegates';
      $redir['action'] = 'index';
      $redir['co'] = $this->cur_co['Co']['id'];
      $redir['cou'] = $couId;

      $this->redirect($redir);

    } // End of process incoming POST.

    $this->set('council_delegates', $councilDelegates);

    // Query for active CO Person Roles for this COU and include
    // PrimaryName for rendering the form.
    $coPersonRoleModel = ClassRegistry::init('CoPersonRole');

    $args = array();
    $args['conditions']['CoPersonRole.cou_id'] = $couId;
    $args['conditions']['CoPersonRole.status'] = StatusEnum::Active;
    $args['contain']['CoPerson'] = 'PrimaryName';

    $coPersonRoles = $coPersonRoleModel->find('all', $args);
    usort($coPersonRoles, array($this, "coPersonPrimaryNameCmp"));

    $this->set('co_person_roles', $coPersonRoles);
  }

  /*
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for authz decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v3.3.1
   * @return Array Permissions
   */
  
  function isAuthorized() {
    $roles = $this->Role->calculateCMRoles();
    $couId = null;
    if (!empty($this->params['named']['cou'])) {
      $couId = $this->params['named']['cou'];  
    }

    $isNamedCouAdmin = $roles['couadmin'] && array_key_exists($couId, $roles['admincous']);

    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();
    
    // Determine what operations this user can perform
    //
    // Manage all existing Council Delegates?
    $p['index'] = ($roles['cmadmin'] || $roles['coadmin'] || $isNamedCouAdmin);

    $this->set('permissions', $p);
    return($p[$this->action]);
  }

  /*
   * Sort CoPersonRole objects by the CoPerson->PrimaryName associated model using
   * the given name attribute.
   */
  function coPersonPrimaryNameCmp($coPerson1, $coPerson2) {
    return strcmp($coPerson1['CoPerson']['PrimaryName']['family'], $coPerson2['CoPerson']['PrimaryName']['family']);
  }
}
