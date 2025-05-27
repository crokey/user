<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://code.iconify.design/iconify-icon/1.0.0/iconify-icon.min.js"></script>


<style>
    .dt-buttons {
  display: none !important;
}

.tw-bg-yellow-50 {
    background-color: #fef9c3;
}

.tw-bg-gray-50 {
    background-color: #f9fafb;
}

.badge-success {
    background-color: green!important;
    color: white!important;
}

.badge-danger {
    background-color: red!important;
    color: white!important;
}



  </style>

<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                
                <div class="tw-mb-2 sm:tw-mb-4">
                    <!-- Button to open the modal -->
                    <button type="button" class="btn btn-primary" onclick="$('#addUserBetModal').modal('show');">
    Add My Bet
</button><br>

</div>
                 

                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        
                        

 
                        <h4>My Active Bets</h4>
<table class="table">
    <thead>
    <tr>
        <th>Bet Type</th>
        <th>Date</th>
        <th>Event</th>
        <th>Tip</th>
        <th>Odds</th>
        <th>Total Bet</th>
        <th>Return</th>
    </tr>
</thead>



    <tbody>
<?php foreach ($mybets as $bet): ?>
    <?php if ($bet['type'] === 'single'): ?>
        <?php
            $return = $bet['total_bet'] * $bet['odds'];
        ?>
        <tr class="bet-row"
        data-id="<?= $bet['id'] ?>"
    data-toggle="modal"
    data-target="#betModal"
    data-type="Single"
    data-date="<?= _d($bet['date']) ?>"
    data-totalbet="$<?= number_format($bet['total_bet'], 2) ?>"
    data-return="$<?= number_format($return, 2) ?>"
    data-selections="<?= htmlspecialchars("{$bet['event']} - {$bet['tip']} @ {$bet['odds']}") ?>"
    data-info="<?= htmlspecialchars($bet['information'] ?? '-') ?>">

            <td><span class="badge badge-info">Single</span></td>
            <td><?= _d($bet['date']) ?></td>
            <td><?= htmlspecialchars($bet['event'] ?? '-') ?></td>
            <td><?= htmlspecialchars($bet['tip'] ?? '-') ?></td>
            <td><?= $bet['odds'] ?? '-' ?></td>
            <td>$<?= number_format($bet['total_bet'], 2) ?></td>
            <td>$<?= number_format($return, 2) ?></td>
        </tr>
        <tr><td colspan="7" style="height: 10px;"></td></tr>
    <?php else: ?>
        <?php
            $parlay_odds = array_reduce($bet['items'], function ($carry, $item) {
                return $carry * $item['odds'];
            }, 1);
            $return = $bet['total_bet'] * $parlay_odds;
            $parlay_selections = implode("\n", array_map(function($item) {
                return "{$item['event']} - {$item['tip']} @ {$item['odds']}";
            }, $bet['items']));
        ?>
        <tr class="table-active bet-row"
        data-id="<?= $bet['id'] ?>"
    data-toggle="modal"
    data-target="#betModal"
    data-type="Parlay"
    data-date="<?= _d($bet['date']) ?>"
    data-totalbet="$<?= number_format($bet['total_bet'], 2) ?>"
    data-return="$<?= number_format($return, 2) ?>"
    data-selections="<?= htmlspecialchars($parlay_selections) ?>"
    data-info="<?= htmlspecialchars($bet['information'] ?? '-') ?>">

            <td><span class="badge badge-warning">Parlay</span></td>
            <td><?= _d($bet['date']) ?></td>
            <td colspan="3"><strong><?= count($bet['items']) ?> selections</strong></td>
            <td>$<?= number_format($bet['total_bet'], 2) ?></td>
            <td>$<?= number_format($return, 2) ?></td>
        </tr>
        <?php foreach ($bet['items'] as $item): ?>
            <tr>
                <td></td>
                <td></td>
                <td><?= htmlspecialchars($item['event']) ?></td>
                <td><?= htmlspecialchars($item['tip']) ?></td>
                <td><?= $item['odds'] ?></td>
                <td></td>
                <td></td>
            </tr>
        <?php endforeach; ?>
        <tr><td colspan="7" style="height: 12px;"></td></tr>
    <?php endif; ?>
<?php endforeach; ?>
</tbody>







</table>


                    </div>
                    
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
<h4><i class="ph ph-receipt"></i> My Bet History</h4>

<?php
$totalBets = count($mybets_history);
$totalWins = array_sum(array_map(fn($b) => $b['result'] === 'win' ? 1 : 0, $mybets_history));
$totalLosses = array_sum(array_map(fn($b) => $b['result'] === 'lose' ? 1 : 0, $mybets_history));
?>

<div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-3 tw-gap-2 tw-mb-4">
    <div class="md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 tw-flex-1 tw-flex tw-items-center">
        <span class="tw-font-semibold tw-mr-3 tw-text-lg"><?= $totalBets ?></span>
        <span class="tw-text-dark tw-truncate sm:tw-text-clip">Total Bets</span>
    </div>
    <div class="md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 tw-flex-1 tw-flex tw-items-center">
        <span class="tw-font-semibold tw-mr-3 tw-text-lg tw-text-green-600"><?= $totalWins ?></span>
        <span class="tw-text-success-700 tw-truncate sm:tw-text-clip">Wins</span>
    </div>
    <div class="tw-flex-1 tw-flex tw-items-center">
        <span class="tw-font-semibold tw-mr-3 tw-text-lg tw-text-red-600"><?= $totalLosses ?></span>
        <span class="tw-text-danger-700 tw-truncate sm:tw-text-clip">Losses</span>
    </div>
</div>


<table class="table dt-table table-mybets">
    <thead>
    <tr>
        <th><?php echo _l('Bet Type'); ?></th>
        <th><?php echo _l('Event/Selections'); ?></th>
        <th><?php echo _l('Result'); ?></th>
        <th><?php echo _l('Settled At'); ?></th>
    </tr>
</thead>

    <tbody>
    <?php foreach ($mybets_history as $history): ?>
        <tr class="<?= $history['is_parlay'] ? 'tw-bg-yellow-50' : '' ?>">
            <td>
                <span class="badge badge-<?= $history['is_parlay'] ? 'warning' : 'info' ?>">
                    <?= $history['is_parlay'] ? 'Parlay' : 'Single' ?>
                </span>
            </td>
            <td><?= htmlspecialchars($history['tip']) ?></td>
            <td>
    <?php if ($history['result'] === 'win'): ?>
        <span class="badge badge-success">Win</span>
    <?php elseif ($history['result'] === 'lose'): ?>
        <span class="badge badge-danger">Lose</span>
    <?php else: ?>
        <span class="badge badge-secondary"><?= ucfirst($history['result']) ?></span>
    <?php endif; ?>
</td>

            <td><?= _dt($history['settled_at']) ?></td>
        </tr>

        <?php if ($history['is_parlay']): ?>
            <?php foreach ($history['parlay_items'] as $item): ?>
                <tr class="tw-text-sm tw-text-neutral-600 tw-bg-gray-50">
                    <td></td>
                    <td colspan="3"><?= $item['event'] ?> - <?= $item['tip'] ?> @ <?= $item['odds'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>
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


<div class="modal fade" id="addUserBetModal" tabindex="-1" role="dialog" aria-labelledby="addUserBetLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="addUserBetForm" action="<?php echo admin_url('mybets/add_user_bet'); ?>" method="post">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" 
               value="<?php echo $this->security->get_csrf_hash(); ?>">

        <div class="modal-header">
          <h5 class="modal-title" id="addUserBetLabel">Submit Bet (Parlay or Single)</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>

        <div class="modal-body">

          <div class="form-group">
            <label>Bet Type</label>
            <select name="type" id="bet-type-select" class="form-control" required>
              <option value="single">Single</option>
              <option value="parlay">Parlay</option>
            </select>
          </div>

          <div class="form-group">
            <label for="date">Date</label>
            <input type="date" name="date" class="form-control" required>
          </div>

          <div id="bet-items">
            <div class="bet-item border p-3 rounded mb-3">
              <h6>Bet Item</h6>
              <div class="form-group">
                <label>Event</label>
                <input type="text" name="events[]" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Category</label>
                <select name="categories[]" class="form-control" required>
                  <option value="football">Football</option>
                  <option value="american_football">American Football</option>
                  <option value="tennis">Tennis</option>
                  <option value="horse_racing">Horse Racing</option>
                </select>
              </div>
              <div class="form-group">
                <label>Tip</label>
                <input type="text" name="tips[]" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Odds</label>
                <input type="number" step="0.01" name="odds[]" class="form-control" required>
              </div>
            </div>
          </div>

          <div class="form-group">
  <label for="total_bet">Total Bet Amount</label>
  <input type="number" step="0.01" name="total_bet" class="form-control" required>
</div>


          <div id="add-bet-button-container" class="mb-3">
  <button type="button" class="btn btn-secondary" onclick="addBetItem()">+ Add Another Bet</button>
</div>


          <div class="form-group">
            <label for="information">Notes</label>
            <textarea name="information" class="form-control" rows="4"></textarea>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit Bet Slip</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="betModal" tabindex="-1" role="dialog" aria-labelledby="betModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-border-b">
        <h5 class="tw-font-semibold tw-text-lg tw-mb-0">Betting Tip Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <!-- Top summary -->
        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
          <p><strong>Bet Type:</strong> <span id="modal-bet-type"></span></p>
          <p><strong>Date:</strong> <span id="modal-bet-date"></span></p>
          <p><strong>Total Bet:</strong> <span id="modal-total-bet"></span></p>
          <p><strong>Return:</strong> <span id="modal-return"></span></p>
        </div>

        <!-- Selections -->
        <div>
          <h6 class="tw-font-semibold tw-mb-2">Selections</h6>
          <div id="modal-bet-selections" style="background-color: #ffffff; border: 1px solid #d1d5db; border-left: 4px solid #10b981; padding: 1rem; border-radius: 1.375rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); font-size: 1rem; color: #1f2937; line-height: 1.7; white-space: pre-wrap;"></div>
        </div>

        <!-- Notes -->
        <div class="tw-mt-4">
          <h6 class="tw-font-semibold tw-mb-2">More Information</h6>
          <div id="modal-bet-info" style="background-color: #ffffff; border: 1px solid #d1d5db; border-left: 4px solid #2563eb; padding: 1rem; border-radius: 1.375rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); font-size: 1rem; color: #1f2937; line-height: 1.7; white-space: pre-wrap;"></div>
        </div>

        <!-- Result Buttons -->
        <div class="tw-mt-5">
          <h6 class="tw-font-semibold tw-mb-2">Record Result</h6>
          <form method="post" action="<?= admin_url('mybets/record_result'); ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="bet_id" id="result-bet-id">
            <input type="hidden" name="bet-type" id="modal-bet-type">
            <button type="submit" name="result" value="win" class="btn btn-success btn-sm mr-2">Mark as Win</button>
            <button type="submit" name="result" value="lose" class="btn btn-danger btn-sm">Mark as Lose</button>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>







<?php init_tail(); ?>

<script>
$(document).on('click', '.bet-row', function () {
  $('#modal-bet-type').text($(this).data('type'));
  $('#modal-bet-date').text($(this).data('date'));
  $('#modal-total-bet').text($(this).data('totalbet'));
  $('#modal-return').text($(this).data('return'));
  $('#modal-bet-selections').text($(this).data('selections'));
  $('#modal-bet-info').text($(this).data('info'));
  $('#result-bet-id').val($(this).data('id')); // Set hidden input for result
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
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('addUserBetModal');
  const form = document.getElementById('addUserBetForm');
  const betItemsContainer = document.getElementById('bet-items');

  $('#addUserBetModal').on('hidden.bs.modal', function () {
    // Reset the entire form
    form.reset();

    // Keep only the first bet item, remove extras
    const betItems = betItemsContainer.querySelectorAll('.bet-item');
    betItems.forEach((item, index) => {
      if (index > 0) item.remove();
    });

    // Remove any extra separators (e.g., <hr> or titles)
    betItemsContainer.querySelectorAll('hr, .tw-border-t-4').forEach(el => el.remove());

    // Trigger bet type change to hide "Add Another Bet" button
    document.getElementById('bet-type-select').dispatchEvent(new Event('change'));
  });
});
</script>


<script>
tinymce.init({
  selector: 'textarea[name="information"]',
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

<script>
function addBetItem() {
  const betItemsContainer = document.getElementById('bet-items');
  const existingItems = betItemsContainer.querySelectorAll('.bet-item');
  const separator = existingItems.length > 0 ? `
    <div class="tw-my-4 tw-border-t-4 tw-border-gray-400 tw-pt-3">
      <h6 class="tw-text-sm tw-uppercase tw-font-semibold tw-text-gray-700">Additional Bet</h6>
    </div>` : '';

  const betItem = `
    ${separator}
    <div class="bet-item border p-3 rounded mb-3 tw-shadow-sm tw-bg-white">
      <h6>Bet Item</h6>
      <div class="form-group">
        <label>Event</label>
        <input type="text" name="events[]" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Category</label>
        <select name="categories[]" class="form-control" required>
          <option value="football">Football</option>
          <option value="american_football">American Football</option>
          <option value="tennis">Tennis</option>
          <option value="horse_racing">Horse Racing</option>
        </select>
      </div>
      <div class="form-group">
        <label>Tip</label>
        <input type="text" name="tips[]" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Odds</label>
        <input type="number" step="0.01" name="odds[]" class="form-control" required>
      </div>
    </div>`;

  betItemsContainer.insertAdjacentHTML('beforeend', betItem);
}
</script>



<script>
document.addEventListener('DOMContentLoaded', function () {
  const betTypeSelect = document.getElementById('bet-type-select');
  const betItemsContainer = document.getElementById('bet-items');
  const addButtonContainer = document.getElementById('add-bet-button-container');

  function resetToSingle() {
    const betItems = betItemsContainer.querySelectorAll('.bet-item');
    betItems.forEach((item, index) => {
      if (index > 0) item.remove(); // Keep only the first one
    });
  }

  betTypeSelect.addEventListener('change', function () {
    const selected = this.value;

    if (selected === 'single') {
      addButtonContainer.style.display = 'none';
      resetToSingle();
    } else {
      addButtonContainer.style.display = 'block';
    }
  });

  // Trigger on page load (in case modal is reused)
  betTypeSelect.dispatchEvent(new Event('change'));
});
</script>
