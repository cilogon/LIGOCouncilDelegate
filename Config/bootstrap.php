<?php

App::uses('CakeEventManager', 'Event');
App::uses('LigoCouncilListener', 'LigoCouncil.Lib');

CakeEventManager::instance()->attach(new LigoCouncilListener());
