<?php

App::uses('CakeEventListener', 'Event');

class LigoCouncilListener implements CakeEventListener {
  public $components = array('Flash');

  public function implementedEvents() {
    return array(
      'Controller.beforeRender' => 'denyRender'
    );
  }

  public function denyRender(CakeEvent $event) {
    // The subject of the event is a Controller object.
    $controller = $event->subject();

    // We only intend to intercept the CoGroups controller.
    if(!$controller->name === "CoGroups") {
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
  }
}
