

<!-- DeleteColumnModal HTML -->
<div class="modal fade" id="deleteColumnModal" tabindex="-1" role="dialog" aria-labelledby="deleteColumnModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            
          <?php echo form_open('admin/clients/deleteColumn/' . $client->userid); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="deleteColumnModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="column_id" value="<?php echo $column['id']; // Corrected to use array notation ?>">
        <input type="hidden" name="client_id" value="<?php echo $client->userid; ?>"> <!-- This will be set by JavaScript -->
                Are you sure you want to delete this charge?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>