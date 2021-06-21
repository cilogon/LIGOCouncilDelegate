<?php

App::uses('CakeEventListener', 'Event');

class LigoCouncilListener implements CakeEventListener {
  public $components = array('Flash');

  public function implementedEvents() {
    return array(
      'Controller.beforeRender' => 'denyEdit',
      'Controller.startup' => 'denyDelete'
    );
  }

  public function denyDelete(CakeEvent $event) {
    // The subject of the event is a Controller object.
    $controller = $event->subject();

    // We only intend to intercept the CoGroups controller.
    if(!($controller->name === "CoGroups")) {
      return true;
    }

    // We only intend to intercept the delete action.
    if(!($controller->action == "delete")) {
      return true;
    }

    // Obtain the ID of the object being deleted.
    $coGroupId = $controller->passedArgs[0];

    // Pull the model data.
    $args = array();
    $args['conditions']['CoGroup.id'] = $coGroupId;
    $args['contain'] = 'Identifier';

    $group = $controller->CoGroup->find('first', $args);

    // If the CoGroup has an identifier of type iscouncilgroup
    // then set the flash and redirect to prevent rendering of the
    // views to edit the group.
    if(!empty($group['Identifier'])) {
      foreach($group['Identifier'] as $identifier) {
        if($identifier['type'] == 'iscouncilgroup') {

            // Allow the platform admin to edit.
            $roles = $controller->Role->calculateCMRoles();
            if($roles['cmadmin']) {
              return true;
            }

          $controller->log("Preventing delete of Council Delegate CoGroup with ID $coGroupId");
          $controller->Flash->set(_txt('pl.ligo_council.error.noedit'), array("key" => "error"));
          $controller->redirect($controller->referer());
          return false;
        }
      }
    }

    return true;
  }

  public function denyEdit(CakeEvent $event) {
    // The subject of the event is a Controller object.
    $controller = $event->subject();

    // We only intend to intercept the CoGroups controller.
    if(!($controller->name === "CoGroups")) {
      return true;
    }

    $group = $controller->data;

    // If the CoGroup has an identifier of type iscouncilgroup
    // then set the flash and redirect to prevent rendering of the
    // views to edit the group.
    if(isset($group['Identifier'])) {
        foreach($group['Identifier'] as $identifier) {
          if($identifier['type'] == 'iscouncilgroup') {

            // Allow the platform admin to edit.
            $roles = $controller->Role->calculateCMRoles();
            if($roles['cmadmin']) {
              return true;
            }

            $controller->log("Preventing editing of LSC Council Group " . $group['CoGroup']['name']);
            $controller->Flash->set(_txt('pl.ligo_council.error.noedit'), array("key" => "error"));
            $controller->redirect($controller->referer());
          }
        }
    }

    return true;
  }
}
