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
    $this->set('title_for_layout', _txt('ct.council_delegates.pl'));

    $couId = $this->params['named']['cou'];

    $args = array();
    $args['conditions']['CouncilDelegate.cou_id'] = $couId;
    $args['contain']['CoPerson'] = 'PrimaryName';

    $councilDelegates = $this->CouncilDelegate->find('all', $args);

    $this->set('council_delegates', $councilDelegates);

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
    // Add a Council Delegate?
    $p['add'] = ($roles['cmadmin'] || $roles['coadmin'] || $isNamedCouAdmin);
    
    // Delete an existing Council Delegate?
    $p['delete'] = ($roles['cmadmin'] || $roles['coadmin'] || $isNamedCouAdmin);

    // Edit an existing Council Delegate?
    $p['edit'] = ($roles['cmadmin'] || $roles['coadmin'] || $isNamedCouAdmin);

    // View all existing Council Delegates?
    $p['index'] = ($roles['cmadmin'] || $roles['coadmin'] || $isNamedCouAdmin);
    
    // View an existing Council Delgate?
    $p['view'] = ($roles['cmadmin'] || $roles['coadmin'] || $isNamedCouAdmin);
    
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
