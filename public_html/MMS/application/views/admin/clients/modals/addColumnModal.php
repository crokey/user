<!-- Modal -->
                            <div class="modal fade" id="addColumnModal" tabindex="-1" role="dialog" aria-labelledby="addColumnModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                      <?php echo form_open('admin/clients/client/' . $client->userid); ?>
                                        <!-- Adjusted form in the modal to add entries to reconcilliation -->
                                        
                                            <div class="modal-header">
                                                <h5 class="modal-title">Add New Entry</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="column_name">Column Name</label>
                                                    <input type="text" class="form-control" id="column_name" name="column_name">
                                                </div>
                                                <!-- No need for column type as we're adding entries, not columns -->
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <input type="submit" class="btn btn-primary" value="Add Entry">
                                            </div>
                                       
<?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>         