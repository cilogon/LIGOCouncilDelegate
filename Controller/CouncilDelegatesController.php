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

    $args = array();
    $args['conditions']['Cou.id'] = $couId;
    $args['contain'] = false;
    $cou = $this->CouncilDelegate->Cou->find('first', $args);

    $this->set('title_for_layout', _txt('ct.cou_council_delegates', array($cou['Cou']['name'])));

    // Query for current delegates.
    $args = array();
    $args['conditions']['CouncilDelegate.cou_id'] = $couId;
    $args['contain']['CoPerson'] = 'PrimaryName';

    $councilDelegates = $this->CouncilDelegate->find('all', $args);
    $currentDelegateNumber = count($councilDelegates);

    // Query for active CO Person Roles for this COU and include
    // PrimaryName for rendering the form.

    $args = array();
    $args['conditions']['CoPersonRole.cou_id'] = $couId;
    $args['conditions']['CoPersonRole.status'] = StatusEnum::Active;
    $args['contain']['CoPerson'] = 'PrimaryName';

    $coPersonRoles = $this->CouncilDelegate->CoPerson->CoPersonRole->find('all', $args);
    usort($coPersonRoles, array($this, "coPersonPrimaryNameCmp"));

    // Compute the number of allowed council delegates.
    // TODO This is not the correct algorithm.
    $groupWorkContribution = count($coPersonRoles);
    $allowedDelegateNumber = intval(ceil($groupWorkContribution/5.0));

    // Process incoming POST CouncilDelegate data.
    if($this->request->is('post')) {
      $selectedCount = 0;
      foreach($this->data['CouncilDelegate']['rows'] as $d) {
        if($d['delegate'] == 1) {
          $selectedCount++;
        }
      }

      // If the number of selected delegates is greater than the number of
      // allowed delegates reject the POST and set the flash, else process
      // the delegate changes.
      if($selectedCount > $allowedDelegateNumber) {
        $this->Flash->set(_txt('pl.ligo_council.error.delegate.count.high', array($allowedDelegateNumber)), array('key' => 'error'));
      } else {
        $err = False;
        foreach($this->data['CouncilDelegate']['rows'] as $d) {
          // Reset model state between save/delete calls.
          $this->CouncilDelegate->clear();

          // CO Person is current delegate and selected to remain a delegate.
          if(!empty($d['id']) && $d['delegate'] == 1) {
            continue;
          }

          // CO Person is current delegate and selected to be removed so delete.
          if(!empty($d['id']) && $d['delegate'] == 0) {
            if($this->CouncilDelegate->delete($d['id'])) {
              $this->CouncilDelegate->CoPerson->HistoryRecord->record($d['co_person_id'],
                                                                      null,
                                                                      null,
                                                                      $this->Session->read('Auth.User.co_person_id'),
                                                                      CouncilDelegateActionEnum::Remove,
                                                                      _txt('pl.ligo_council.rs.removed', array($cou['Cou']['name']))
                                                                      );
            } else {
              $this->log("Error deleting CouncilDelegate with ID " . print_r($d['id'], true));
              $err = True;
              $this->Flash->set(_txt('pl.ligo_council.error.unexpected'), array('key' => 'error'));
            }
            continue;
          }

          // CO Person is not current delegate and selected to become delegate.
          if(empty($d['id']) && $d['delegate'] == 1) {
            $newDelegate['CouncilDelegate'] = $d;
            if($this->CouncilDelegate->save($newDelegate)) {
              $this->CouncilDelegate->CoPerson->HistoryRecord->record($newDelegate['CouncilDelegate']['co_person_id'],
                                                                      null,
                                                                      null,
                                                                      $this->Session->read('Auth.User.co_person_id'),
                                                                      CouncilDelegateActionEnum::Add,
                                                                      _txt('pl.ligo_council.rs.added', array($cou['Cou']['name']))
                                                                      );
            } else {
              $this->log("Error saving CouncilDelegate " . print_r($newDelegate, true));
              $err = True;
              $this->Flash->set(_txt('pl.ligo_council.error.unexpected'), array('key' => 'error'));
            }
          }
        }
        
        if(!$err) {
          $this->Flash->set(_txt('pl.ligo_council.success.delegate.count.updated'), array('key' => 'success'));
        }
      }

      // Synchronize memberships in the related CoGroup.
      $this->synchronizeGroup($cou);

      // Redirect back to index to render updated delegate information.
      $redir = array();
      $redir['plugin'] = 'ligo_council';
      $redir['controller'] = 'council_delegates';
      $redir['action'] = 'index';
      $redir['co'] = $this->cur_co['Co']['id'];
      $redir['cou'] = $couId;

      $this->redirect($redir);

    } // End of process incoming POST.

    // Process incoming GET and display current delegate status.

    // Inform the user that the current number of delegates is low.
    if($currentDelegateNumber < $allowedDelegateNumber) {
      $this->Flash->set(_txt('pl.ligo_council.info.delegate.count.low', array($allowedDelegateNumber)),
                        array(
                          'key' => 'information',
                          'clear' => True)
                        );
    }

    $this->set('council_delegates', $councilDelegates);

    $this->set('co_person_roles', $coPersonRoles);

    $this->set('allowed_delegate_number', $allowedDelegateNumber);
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

  /*
   * Synchronize the CoGroup representing the council delegates.
   *
   * @since COmanage Registry v3.3.1
   * @return null
   */

  function synchronizeGroup($cou) {
    // Create the parent LSC Council group if not already created.
    $args = array();
    $args['conditions']['CoGroup.name'] = LSCCouncilEnum::ParentGroupName;
    $args['contain'] = 'Identifier';

    $lscCouncilGroup = $this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->find('first', $args);

    if(empty($lscCouncilGroup)) {
      $this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->clear();

      $data = array();
      $data['CoGroup']['co_id'] = $cou['Cou']['co_id'];
      $data['CoGroup']['name'] = LSCCouncilEnum::ParentGroupName;
      $data['CoGroup']['description'] = LSCCouncilEnum::ParentGroupDescription;
      $data['CoGroup']['open'] = false;
      $data['CoGroup']['status'] = SuspendableStatusEnum::Active;
      $data['CoGroup']['group_type'] = GroupEnum::Standard;

      if(!$this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->save($data)) {
        $this->Flash->set(_txt('pl.ligo_council.error.group.save'), array('key' => 'error'));
        return;
      }

      $lscCouncilGroupId = $this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->id;

      $this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->Identifier->clear();
      
      $data = array();
      $data['Identifier']['identifier'] = LSCCouncilEnum::ParentGroupName;
      $data['Identifier']['type'] = 'iscouncilgroup';
      $data['Identifier']['status'] = SuspendableStatusEnum::Active;
      $data['Identifier']['co_group_id'] = $lscCouncilGroupId;

      $args = array();
      $args['validate'] = false;

      if(!$this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->Identifier->save($data, $args)) {
        $this->Flash->set(_txt('pl.ligo_council.error.group.save'), array('key' => 'error'));
        return;
      }
    } else {
      $lscCouncilGroupId = $lscCouncilGroup['CoGroup']['id'];
    }

    // Create the COU LSC Council group if not already created.
    $groupName = $cou['Cou']['name'] . ' ' . 'Council Delegates';
    $args = array();
    $args['conditions']['CoGroup.name'] = $groupName;
    $args['contain'] = 'Identifier';

    $councilGroup = $this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->find('first', $args);

    if(empty($councilGroup)) {
      $this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->clear();

      $data = array();
      $data['CoGroup']['co_id'] = $cou['Cou']['co_id'];
      $data['CoGroup']['name'] = $groupName;
      $data['CoGroup']['description'] = $groupName;
      $data['CoGroup']['open'] = false;
      $data['CoGroup']['status'] = SuspendableStatusEnum::Active;
      $data['CoGroup']['group_type'] = GroupEnum::Standard;

      if(!$this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->save($data)) {
        $this->Flash->set(_txt('pl.ligo_council.error.group.save'), array('key' => 'error'));
        return;
      }

      $councilGroupId = $this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->id;

      $this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->Identifier->clear();
      
      $data = array();
      $data['Identifier']['identifier'] = $groupName;
      $data['Identifier']['type'] = 'iscouncilgroup';
      $data['Identifier']['status'] = SuspendableStatusEnum::Active;
      $data['Identifier']['co_group_id'] = $councilGroupId;

      $args = array();
      $args['validate'] = false;

      if(!$this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->Identifier->save($data, $args)) {
        $this->Flash->set(_txt('pl.ligo_council.error.group.save'), array('key' => 'error'));
        return;
      }
    } else {
      $councilGroupId = $councilGroup['CoGroup']['id'];
    }

    // Make the COU LSC Council group nested in the LSC Council group if not already.
    $args = array();
    $args['conditions']['CoGroupNesting.co_group_id'] = $councilGroupId;
    $args['conditions']['CoGroupNesting.target_co_group_id'] = $lscCouncilGroupId;
    $args['contain'] = false;

    $nesting = $this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->CoGroupNesting->find('first', $args);

    if(empty($nesting)) {
      $data = array();
      $data['CoGroupNesting']['co_group_id'] = $councilGroupId;
      $data['CoGroupNesting']['target_co_group_id'] = $lscCouncilGroupId;

      if(!$this->CouncilDelegate->CoPerson->CoGroupMember->CoGroup->CoGroupNesting->save($data)) {
        $this->Flash->set(_txt('pl.ligo_council.error.group.save'), array('key' => 'error'));
        return;
      }
    }

    // Synchronize the COU LSC Council group memberships.

    // Find the current delegates.
    $args = array();
    $args['conditions']['CouncilDelegate.cou_id'] = $cou['Cou']['id'];
    $args['contain'] = false;

    $councilDelegates = $this->CouncilDelegate->find('all', $args);
    $councilDelegatesByCoPersonId = array();
    foreach($councilDelegates as $d) {
      $councilDelegatesByCoPersonId[] = $d['CouncilDelegate']['co_person_id'];
    }

    // Find the current group members.
    $args = array();
    $args['conditions']['CoGroupMember.co_group_id'] = $councilGroupId;
    $args['contain'] = false;

    $memberships = $this->CouncilDelegate->CoPerson->CoGroupMember->find('all', $args);
    $membershipsByCoPersonId = array();
    foreach($memberships as $m) {
      $membershipsByCoPersonId[] = $m['CoGroupMember']['co_person_id'];
    }

    // Make sure each delegate is a member of the LSC council CoGroup.
    foreach($councilDelegatesByCoPersonId as $coPersonId) {
      if(!in_array($coPersonId, $membershipsByCoPersonId)) {
        $this->CouncilDelegate->CoPerson->CoGroupMember->clear();

        $data = array();
        $data['CoGroupMember']['co_group_id'] = $councilGroupId;
        $data['CoGroupMember']['co_person_id'] = $coPersonId;
        $data['CoGroupMember']['member'] = true;
        $data['CoGroupMember']['owner'] = false;

        $this->CouncilDelegate->CoPerson->CoGroupMember->save($data);
      }
    }

    // Delete any group member that is not a delegate.
    foreach($membershipsByCoPersonId as $coPersonId) {
      if(!in_array($coPersonId, $councilDelegatesByCoPersonId)) {
        foreach($memberships as $m) {
          if($m['CoGroupMember']['co_person_id'] == $coPersonId) {
            $this->CouncilDelegate->CoPerson->CoGroupMember->delete($m['CoGroupMember']['id']);
          }
        }
      }
    }
  }
}
