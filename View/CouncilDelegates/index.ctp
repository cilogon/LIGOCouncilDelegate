<script type="text/javascript">

function checkDelegateCount(e) {
  var delegates_selected = $("input:checked").length;

  if(delegates_selected > window.allowed_delegate_number) {
    $(e).prop("checked", false);
    $("#confirm-delegate-max-dialog").dialog("open");
  }
}

function js_local_onload() {
  window.allowed_delegate_number = <?php print $allowed_delegate_number ?>;

  $("#confirm-delegate-max-dialog").dialog({
    autoOpen: false,
    buttons: {
    "<?php print _txt('pl.ligo_council.dialog.close') ?>": function() {
        $(this).dialog("close");
      }
    },
    modal: true,
    show: {
      effect: "fade"
    },
    hide: {
      effect: "fade"
    }
  });

}

</script>


<?php
  // Add page title
  $params = array();
  $params['title'] = $title_for_layout;

  // Add top links
  $params['topLinks'] = array();

  print $this->element("pageTitleAndButtons", $params);

  $submit_label = _txt('op.add');

  print $this->Form->create(
    "LigoCouncil.CouncilDelegate",
    array(
      'inputDefaults' => array(
        'label' => false,
        'div' => false
      )
    )
  );

?>


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
            $args['onclick'] = 'checkDelegateCount(this)';

            $class = "line" . strval(($i % 2) + 1);
            print '<tr class="' . $class . '">';
            print '<td>';
            print $this->Form->hidden('CouncilDelegate.rows.'.$row.'.id', array('default' => $d['CouncilDelegate']['id']));
            print $this->Form->hidden('CouncilDelegate.rows.'.$row.'.cou_id', array('default' => $cou_id));
            print $this->Form->hidden('CouncilDelegate.rows.'.$row.'.co_person_id', array('default' => $d['CouncilDelegate']['co_person_id']));
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

      <tr>
        <td colspan="1">
          <hr></hr>
        </td>
      </tr>

    <?php
      // Reset the table row <tr> class for alternating shading.
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
        $args['onclick'] = 'checkDelegateCount(this)';

        $class = "line" . strval(($i % 2) + 1);
        print '<tr class="' . $class . '">';
        print '<td>';
        print $this->Form->hidden('CouncilDelegate.rows.'.$row.'.cou_id', array('default' => $cou_id));
        print $this->Form->hidden('CouncilDelegate.rows.'.$row.'.co_person_id', array('default' => $coPersonId));
        print $this->Form->checkbox($fieldName, $args);
        print $this->Form->label($fieldName, $displayName) . "\n";
        print '</td>';

        $row++;
        $i++;
      }  
    ?>
    </tbody>


    <tfoot>
      <tr>
        <th colspan="1">
        </th>
      </tr>
      <tr>
        <td>
          <?php
            $options = array('style' => 'float:left;');
            print $this->Form->submit(_txt('op.save'), $options);
            print $this->Form->end();
          ?>
        </td>
      </tr>
    </tfoot>

  </table>


</div>

<div id="confirm-delegate-max-dialog" title="<?php print _txt('pl.ligo_council.dialog.title')?>">
  <p><?php print _txt('pl.ligo_council.dialog.text', array($allowed_delegate_number)) ?></p>
</div>
