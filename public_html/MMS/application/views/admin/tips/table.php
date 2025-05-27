<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://code.iconify.design/iconify-icon/1.0.0/iconify-icon.min.js"></script>




<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                
                <div class="tw-mb-2 sm:tw-mb-4">
                    

<?php if (!empty($todays_tips)) { ?>
    <h4 class="tw-font-semibold tw-mb-3">Today's Tips</h4>
    <div class="row g-4">
        <?php foreach ($todays_tips as $tip) {
            $category = strtolower($tip['category']);
            switch ($category) {
    case 'football':
        $icon = '<iconify-icon icon="mdi:soccer" style="font-size: 24px; color: #4b5563;"></iconify-icon>';
        break;
    case 'american_football':
        $icon = '<iconify-icon icon="mdi:football" style="font-size: 24px; color: #4b5563;"></iconify-icon>';
        break;
    case 'tennis':
        $icon = '<iconify-icon icon="mdi:tennis" style="font-size: 24px; color: #4b5563;"></iconify-icon>';
        break;
    case 'horse_racing':
        $icon = '<iconify-icon icon="mdi:horse-variant" style="font-size: 24px; color: #4b5563;"></iconify-icon>';
        break;
    default:
        $icon = '<iconify-icon icon="mdi:trophy" style="font-size: 24px; color: #4b5563;"></iconify-icon>';
}



        ?>
            <div class="col-md-3">
    <div class="betting-card" 
     data-toggle="modal" 
     data-target="#tipModal" 
     data-event="<?php echo htmlspecialchars($tip['event']); ?>"
     data-category="<?php echo ucfirst(str_replace('_', ' ', $category)); ?>"
     data-tip="<?php echo htmlspecialchars($tip['tip']); ?>"
     data-odds="<?php echo number_format($tip['odds'], 2); ?>"
     data-info="<?php echo htmlspecialchars($tip['information'] ?? ''); ?>"

     style="cursor: pointer; min-height: 130px; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 1rem; padding: 1.25rem; position: relative; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: box-shadow 0.3s ease; margin-bottom: 15px;">

        
        <!-- ICON ABSOLUTE TOP RIGHT -->
        <div style="position: absolute; top: 10px; right: 12px;">
    <?php echo $icon; ?>
</div>


        <!-- MAIN CONTENT -->
        <div style="padding-right: 2.5rem;">
            <h5 style="font-weight: 600; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($tip['event']); ?></h5>
            <p style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 0.5rem;">
                <?php echo ucfirst(str_replace('_', ' ', $category)); ?>
            </p>
            <p style="font-size: 16px;">
                <?php echo htmlspecialchars($tip['tip']); ?>
                <strong>@ <?php echo number_format($tip['odds'], 2); ?></strong>
            </p>
        </div>
    </div>
</div>





        <?php } ?>
    </div>


                </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <!-- Tabs for filtering URLs -->
                         

 
                        <!-- The URLs Table -->
                        <table class="table dt-table table-urls">
                            <thead>
                                <tr>
                                    <th><?php echo _l('Date'); ?></th>
                                    <th><?php echo _l('Event'); ?></th>
                                    <th><?php echo _l('Tip'); ?></th>
                                    <th><?php echo _l('Odds'); ?></th>
                                    <th><?php echo _l('Result'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
    <?php foreach ($tips as $tip) { ?>
        <tr>
            <td><?php echo _d($tip['date']); ?></td>
            <td><?php echo htmlspecialchars($tip['event']); ?></td>
            <td><?php echo htmlspecialchars($tip['tip']); ?></td>
            <td><?php echo $tip['odds']; ?></td>
            <td><?php echo $tip['result']; ?></td>
        </tr>
    <?php } ?>
</tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tipModal" tabindex="-1" role="dialog" aria-labelledby="tipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-border-b">
  <h5 class="tw-font-semibold tw-text-lg tw-mb-0">Betting Tip Details</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem; opacity: 0.7!important;">
    <span aria-hidden="true">&times;</span>
  </button>
</div>



      <div class="modal-body">

  <!-- Top summary box -->
  <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
    <p><strong>Event:</strong> <span id="modal-event"></span></p>
    <p><strong>Category:</strong> <span id="modal-category"></span></p>
    <p><strong>Tip:</strong> <span id="modal-tip"></span></p>
    <p><strong>Odds:</strong> <span id="modal-odds"></span></p>
  </div>

  <!-- More information section -->
  <div>
  <h6 class="tw-font-semibold tw-mb-2">More Information</h6>
  <div id="modal-info"
       style="
         background-color: #ffffff;
         border: 1px solid #d1d5db;            /* darker outer border */
       border-left: 4px solid #2563eb;       /* re-declare left border after full border */
         padding: 1rem;
         border-radius: 1.375rem;
         box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
         font-size: 1rem;
         color: #1f2937;
         line-height: 1.7;
         white-space: pre-wrap;">
  </div>
</div>


</div>

    </div>
  </div>
</div>





<?php init_tail(); ?>
<script>
$(document).on('click', '.betting-card', function () {
    $('#modal-event').text($(this).data('event'));
    $('#modal-category').text($(this).data('category'));
    $('#modal-tip').text($(this).data('tip'));
    $('#modal-odds').text($(this).data('odds'));
    $('#modal-info').text($(this).data('info'));
});
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const subscriptionSelect = document.getElementById('subscriptions');
    new Choices(subscriptionSelect, {
      removeItemButton: true,
      placeholder: true,
      placeholderValue: 'Select subscriptions...',
      searchEnabled: true,
      shouldSort: false
    });
  });
</script>



<script>
tinymce.init({
  selector: '#tip-information',
  height: 300,
  menubar: true,
  plugins: [
    'advlist autolink lists link image charmap preview anchor',
    'searchreplace visualblocks code fullscreen',
    'insertdatetime media table code help wordcount'
  ],
  toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code fullscreen',
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
});
</script>

<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<!-- Choices.js JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

