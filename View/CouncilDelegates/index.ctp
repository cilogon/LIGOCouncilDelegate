<?php
  // Add page title
  $params = array();
  $params['title'] = $title_for_layout;

  // Add top links
  $params['topLinks'] = array();

  print $this->element("pageTitleAndButtons", $params);

?>

<div class="table-container">
  <table id="council_delegates">
    <thead>
      <tr>
        <th><?php print $this->Paginator->sort('cou_id', 'cou_id'); ?></th>
        <th><?php print $this->Paginator->sort('coperson_id', 'coperson_id'); ?></th>
        <th><?php print _txt('fd.actions'); ?></th>
      </tr>
    </thead>

    <tbody>
      <?php $i = 0; ?>
      <?php foreach ($council_delegates as $d): ?>
      <tr class="line<?php print ($i % 2)+1; ?>">
        <td>
          <?php
            print $this->Html->link(
              $d['CouncilDelegate']['cou_id'],
              array(
                'plugin' => 'ligo_council',
                'controller' => 'council_delegates',
                'action' => ($permissions['edit'] ? 'edit' : ($permissions['view'] ? 'view' : '')),
                $d['CouncilDelegate']['id']
              )
            );
          ?>
        </td>
        <td>
          <?php print $d['CouncilDelegate']['coperson_id']; ?>
        </td>
        <td>
          <?php
            if($permissions['edit']) {
              print $this->Html->link(
                  _txt('op.edit'),
                  array(
                    'plugin' => 'ligo_council',
                    'controller' => 'council_delegates',
                    'action' => 'edit', $d['CouncilDelegate']['id']
                  ),
                  array('class' => 'editbutton')) . "\n";
            }
            if($permissions['delete']) {
              print $this->Html->link(             
                  _txt('op.delete'),
                  array(
                    'plugin' => 'ligo_council',
                    'controller' => 'council_delegates',
                    'action' => 'delete', $d['CouncilDelegate']['id']
                  ),
                  array('class' => 'editbutton')) . "\n";
            }
          ?>
        </td>
      </tr>
      <?php $i++; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

