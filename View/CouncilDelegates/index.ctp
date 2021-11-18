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


<ul id="current_council_delegates" class="fields form-list">

    <?php
      // Tracks current row used with form data.
      $row = 0;

      // Loop over the CO Person roles and if the person is currently
      // a council delegate then emit a <li> with checkbox and label.
      foreach($co_person_roles as $role) {
        $coPerson = $role['CoPerson'];
        $coPersonId = $coPerson['id'];
        $primaryName = $coPerson['PrimaryName'];
        $givenName = $primaryName['given'];
        $familyName = $primaryName['family'];
        $displayName = $givenName . " " . $familyName;

        foreach($council_delegates as $d) {
          if($d['CoPerson']['id'] == $coPersonId) {
            print '<li>';
            print '<div class="form-check">';
            $fieldName = 'CouncilDelegate.rows.'.$row.'.delegate';
            $args = array();
            $args['checked'] = true;
            $args['onclick'] = 'checkDelegateCount(this)';
            $args['class'] = 'form-check-input';
            print $this->Form->hidden('CouncilDelegate.rows.'.$row.'.id', array('default' => $d['CouncilDelegate']['id']));
            print $this->Form->hidden('CouncilDelegate.rows.'.$row.'.cou_id', array('default' => $cou_id));
            print $this->Form->hidden('CouncilDelegate.rows.'.$row.'.co_person_id', array('default' => $d['CouncilDelegate']['co_person_id']));
            print $this->Form->input($fieldName, $args);
            $args = array();
            $args['class'] = 'form-check-label';
            print $this->Form->label($fieldName, $displayName, $args);
            print '</div>';
            print '</li>';

            $row++;
            break;
          }
        }

      }  
    ?>

</ul>

<hr></hr>

<ul id="eligible_council_delegates" class="fields form-list">

    <?php
      // Loop over the CO Person roles and if the person is NOT currently
      // a council delegate then emit a <li> with checkbox and label.
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

        print '<li>';
        print '<div class="form-check">';
        $fieldName = 'CouncilDelegate.rows.'.$row.'.delegate';
        $args = array();
        $args['checked'] = false;
        $args['onclick'] = 'checkDelegateCount(this)';
        $args['class'] = 'form-check-input';
        print $this->Form->hidden('CouncilDelegate.rows.'.$row.'.cou_id', array('default' => $cou_id));
        print $this->Form->hidden('CouncilDelegate.rows.'.$row.'.co_person_id', array('default' => $coPersonId));
        print $this->Form->input($fieldName, $args);
        $args = array();
        $args['class'] = 'form-check-label';
        print $this->Form->label($fieldName, $displayName, $args);
        print '</div>';
        print '</li>';

        $row++;
      }  
    ?>

  <li class="fields-submit">
    <div class="field-info">
      <?php
        print $this->Form->submit(_txt('op.submit'));
        print $this->Form->end();
      ?>
    </div>
  </li>

</ul>


</div>

<div id="confirm-delegate-max-dialog" title="<?php print _txt('pl.ligo_council.dialog.title')?>">
  <p><?php print _txt('pl.ligo_council.dialog.text', array($allowed_delegate_number)) ?></p>
</div>
