<?

class CouncilDelegate extends AppModel {
  // Required by COmanage Plugins
  public $cmPluginType = "other";

  // Define class name for cake
  public $name = "CouncilDelegate";

  // Add behaviors
  public $actsAs = array(
    'Containable',
    //'Changelog' => array('priority' => 5)
  );

  // Document foreign keys
  public $cmPluginHasMany = array(
    "Cou" => array("CouncilDelegate"),
    "CoPerson" => array("CouncilDelegate"),
  );

  // Association rules from this model to other models
  public $belongsTo = array(
    "Cou",
    "CoPerson"
  );

  // Default display field for cake generated views
  public $displayField = "cou_id";

  // Validation rules for table elements
  public $validate = array(
    'cou_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'allowEmpty' => false
    ),
    'co_person_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'allowEmpty' => false
    )
  );

}
