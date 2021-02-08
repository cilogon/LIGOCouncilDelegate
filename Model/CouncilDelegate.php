<?

class CouncilDelegate extends AppModel {
  // Required by COmanage Plugins
  public $cmPluginType = "other";

  // Define class name for cake
  public $name = "CouncilDelegate";

  // Add behaviors
  public $actAs = array('Containable',
                        'Changelog' => array('priority' => 5));

  // Document foreign keys
  public $cmPluginHasMany = array(
    "Cou" => array("CouncilDelegate")
  );

  // Association rules from this model to other models
  public $belongsTo = array(
    "Cou"
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
    'coperson_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'allowEmpty' => false
    )
  );


}
