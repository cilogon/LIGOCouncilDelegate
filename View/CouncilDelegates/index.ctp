<?php
  // Add page title
  $params = array();
  $params['title'] = $title_for_layout;

  // Add top links
  $params['topLinks'] = array();

  print $this->element("pageTitleAndButtons", $params);

?>

<!-- First table displays current council delegates -->
<div class="table-container">
  <table id="current_council_delegates">

    <tbody>

    <?php
      // Tracks current row used with form data.
      $row = 0;

      // Specifies table row <tr> class for alternating shading.
      $i = 0;

      // Loop over the CO Person roles and if the person is currently
      // a council delegate then emit a <tr> and <td> with checkbox and label.
      //
      // TODO Add all necessary hidden fields.
      foreach($co_person_roles as $role) {
        $coPerson = $role['CoPerson'];
        $coPersonId = $coPerson['id'];
        $primaryName = $coPerson['PrimaryName'];
        $givenName = $primaryName['given'];
        $familyName = $primaryName['family'];
        $displayName = $givenName . " " . $familyName;

        foreach($council_delegates as $d) {
          if($d['CoPerson']['id'] == $coPersonId) {
            $fieldName = 'CouncilDelegate.rows.'.$row.'.delegate';
            $args = array();
            $args['checked'] = true;

            $class = "line" . strval(($i % 2) + 1);
            print '<tr class="' . $class . '">';
            print '<td>';
            print $this->Form->checkbox($fieldName, $args);
            print $this->Form->label($fieldName, $displayName);
            print '</td>';

            $row++;
            $i++;

            break;
          }
        }

      }  
    ?>

    </tbody>
  </table>
</div>

<hr></hr>

<!-- Second table displays possible council delegates -->
<div class="table-container">
  <table id="not_current_council_delegates">

    <tbody>

    <?php
      // Reset the table row <tr> class for alternating shading since this
      // is a new table.
      $i = 0;

      // Loop over the CO Person roles and if the person is NOT currently
      // a council delegate then emit a <tr> and <td> with checkbox and label.
      //
      // TODO Add all necessary hidden fields.
      foreach($co_person_roles as $role) {
        $coPerson = $role['CoPerson'];
        $coPersonId = $coPerson['id'];
        $primaryName = $coPerson['PrimaryName'];
        $givenName = $primaryName['given'];
        $familyName = $primaryName['family'];
        $displayName = $givenName . " " . $familyName;

        $isDelegate = false;
        foreach($council_delegates as $d) {
          if($d['CoPerson']['id'] == $coPersonId) {
            $isDelegate = true;
            break;
          }
        }

        if($isDelegate) {
            continue;
        }

        $fieldName = 'CouncilDelegate.rows.'.$row.'.delegate';
        $args = array();
        $args['checked'] = false;

        $class = "line" . strval(($i % 2) + 1);
        print '<tr class="' . $class . '">';
        print '<td>';
        print $this->Form->checkbox($fieldName, $args);
        print $this->Form->label($fieldName, $displayName) . "\n";
        print '</td>';

        $row++;
        $i++;
      }  
    ?>
    </tbody>
  </table>
</div>
