<?php $instance = &get_instance(); ?>
<form hidden enctype="multipart/form-data" name="clientFileForm" id="clientFileForm" method="post" onsubmit="uploadClientFileForm(this);return false;">
     <input type="file" class="file" name="userfile" required />
     <input type="submit" name="submit" class="save" value="save" />
     <input type="hidden" name="<?php echo $instance->security->get_csrf_token_name(); ?>" value="<?php echo $instance->security->get_csrf_hash(); ?>">
</form>
<form hidden method="post" enctype="multipart/form-data" name="clientMessagesForm" id="clientMessagesForm" onsubmit="return false;">
     <div class="message-input client_msg_input">
          <div class="wrap">
               <textarea type="text" name="client_message" class="client_chatbox ays-ignore" placeholder="<?= _l('chat_type_a_message'); ?>"></textarea>
               <input type="hidden" class="ays-ignore from" name="from" value="staff_" />
               <input type="hidden" class="ays-ignore to" name="to" value="client_" />
               <input type="hidden" class="ays-ignore typing" name="typing" value="false" />
               <input type="hidden" class="ays-ignore" name="<?php echo $instance->security->get_csrf_token_name(); ?>" value="<?php echo $instance->security->get_csrf_hash(); ?>">
               <i class="fa-regular fa-file-image attachment clientFileUpload" data-container="body" data-toggle="tooltip" title="<?php echo _l('chat_file_upload'); ?>" aria-hidden="true"></i>
               <?php loadChatComponent('MicrophoneIcon'); ?>
               <?php loadChatComponent('SearchMessages', ['props' => 'search_client_messages']); ?>
               <input type="hidden" class="ays-ignore invisibleUnread" value="" />
               <button id="sendMessageBtn" class="submit enterClientBtn" name="enterClientBtn"><svg class="fa-paper-plane" fill="#ffffff" viewBox="0 0 24 24">
                         <path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M8,7.71V11.05L15.14,12L8,12.95V16.29L18,12L8,7.71Z" />
                    </svg></button>
          </div>
     </div>
</form>
<script>

document.addEventListener('DOMContentLoaded', function () {
    var textarea = document.querySelector('.chatbox');
    var sendButton = document.getElementById('sendMessageBtn'); // Ensure you have this ID on your send button
  var messages = document.querySelector('.client_messages'); // Define messages here to ensure it's accessible
    var initialTextareaHeight = 45; // Initial height of the textarea

    // Adjusts the textarea height on input
    textarea.addEventListener('input', function () {
        adjustTextareaHeight(this);
      checkAndResetMessageContainer();
    });
  
  // Handle sending a message when the enter key is pressed without the shift key
    textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            
            
            textarea.style.height = '45px'; // Reset the height
            textarea.focus(); // Optionally refocus on the textarea
          
          var messages = document.querySelector('.client_messages');
        messages.style.bottom = '0px';
            
        }
    });

    // Direct event listener on the button
    if (sendButton) {
        sendButton.addEventListener('click', function(e) {
            console.log('Send button clicked'); // This should now appear in the console
             
            textarea.style.height = '45px'; // Reset the height
            textarea.focus(); // Optionally refocus on the textarea
          
          var messages = document.querySelector('.client_messages');
        messages.style.bottom = '0px';
        });
    } else {
        console.log('Send button not found'); // Helps diagnose missing button
    }
    // Adjusts the height of the textarea based on its content
    function adjustTextareaHeight(textarea) {
        textarea.style.height = '45px';
        var newHeight = Math.min(textarea.scrollHeight, 100);
        textarea.style.height = newHeight + 'px';

        if (textarea.scrollHeight > 100) {
            textarea.style.overflowY = 'scroll';
        } else {
            textarea.style.overflow = 'hidden';
        }

        adjustMessagesContainer();
    }
  
  // Check if textarea is empty and reset message container's bottom margin
    function checkAndResetMessageContainer() {
        if (textarea.value.trim() === '') {
            messages.style.bottom = '0px';
        }
    }


    // Adjusts the messages container's max-height and enables scrolling
    function adjustMessagesContainer() {
        var headerAndFooterHeight = 82; // Adjust based on your header and footer height
        var additionalPadding = 73; // Additional space you want to leave (adjust this value to your layout)

        var newMaxHeight = `calc(100% - ${headerAndFooterHeight + textarea.offsetHeight}px)`;
        var newMinHeight = `calc(100% - ${headerAndFooterHeight + additionalPadding}px)`; // Using additionalPadding to calculate min-height

        messages.style.maxHeight = newMaxHeight;
        messages.style.minHeight = newMinHeight;

        // Scroll to the bottom of the messages container to show the latest message
        messages.scrollTop = messages.scrollHeight;
    }
});
</script>