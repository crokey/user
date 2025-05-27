

<?php
$client_id = get_related_client_id_for_staff();
redirect(admin_url('clients/client/' . $client_id)); ?>
