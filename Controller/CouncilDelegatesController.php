<?

App::uses("StandardController", "Controller");

class CouncilDelegatesController extends StandardController {

  /*
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for authz decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v3.3.0
   * @return Array Permissions
   */

  function isAuthorized() {
    $roles = $this->Role->calculateCMRoles();

    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();

    // Determine what operations this user can perform
    //
    // Add a Council Delegate?
    $p['add'] = ($roles['cmadmin'] || $roles['coadmin']);

    // Delete an existing Council Delegate?
    $p['delete'] = ($roles['cmadmin'] || $roles['coadmin']);

    // Edit an existing Council Delegate?
    $p['edit'] = ($roles['cmadmin'] || $roles['coadmin']);

    // View all existing Council Delegates?
    $p['index'] = ($roles['cmadmin'] || $roles['coadmin']);

    // View an existing Council Delegate?
    $p['view'] = ($roles['cmadmin'] || $roles['coadmin']);

    $this->set('permissions', $p);
    return($p[$this->action]);
  }


}
