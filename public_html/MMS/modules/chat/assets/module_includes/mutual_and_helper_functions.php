<script>
    /**
     * Main Settings for clients chat
     * Used in chat_clients_view.php
     */
    var customerSettings = {
        "clientPusherAuth": '<?php echo site_url('chat/chat_ClientsController/pusherCustomersAuth'); ?>',
        "getMutualMessages": '<?php echo site_url('chat/chat_ClientsController/getMutualMessages'); ?>',
        "clientsMessagesPath": '<?php echo site_url('chat/chat_ClientsController/initClientChat'); ?>',
        "getStaffUnreadMessages": '<?php echo site_url('chat/chat_ClientsController/getStaffUnreadMessages'); ?>',
        "updateStaffUnread": '<?php echo site_url('chat/chat_ClientsController/updateClientUnreadMessages'); ?>',
        "hasComeOnlineText": "<?php echo _l('chat_user_is_online'); ?>",
        "groupNameChanged": "<?php echo _l('chat_group_renamed'); ?>",
        "groupNewName": "<?php echo _l('chat_group_newname'); ?>",
        "chatRenameLabel": "<?php echo _l('chat_rename_label'); ?>",
        "noMoreMessagesText": "<?php echo _l('chat_no_more_messages_to_show'); ?>",
        "uploadMethod": '<?php echo site_url('chat/chat_ClientsController/uploadMethod'); ?>',
        "debug": <?php if (ENVIRONMENT != 'production') { ?> true <?php } else { ?> false <?php }; ?>,
    };
    var _scrollEvent = true;
    $(function () {
        $(window).on("load resize", function (e) {
            if (is_mobile()) {
                // Wait until metsiMenu, collapse and other effect finish and set wrapper height
                setTimeout(function () {
                    $("#wrapper").css("min-height", "100%");
                }, 500);

            }
            ;
        });
    });
    /*---------------* Parse emojies in chat area do not touch *---------------*/
    emojify.setConfig({
        emojify_tag_type: "div",
        "img_dir": site_url + "/modules/chat/assets/chat_implements/emojis"
    });
    emojify.run();

    /*-------* Lity prevent duplicating images in click *-------*/
    $("body").on("click", ".prchat_convertedImage", function () {
        if ($("body").find(".lity-opened")) {
            $("body").find(".lity-opened").remove();
        }
    });


    /*-------* Simple enter key function *-------*/
    $.fn.enterKey = function (fnc) {
        return this.each(function () {
            $(this).keypress(function (e) {
                var keycode = (e.keyCode ? e.keyCode : e.which);
                if (keycode == "13") {
                    fnc.call(this, e);
                }
            });
        });
    };

    // Shows the full screen wrapper with buttons to start recording
    function showRecordingWrapper()
    {
        ifRecordingCancelledAndClose();
    }

    /*-------* Live internet connection tracker *-------*/
    function internetConnectionCheck()
    {
        return navigator.onLine ? true : false;
    }

    function isAudioMessage(message)
    {
        /**
         * Check if it is audio message and decode html
         */
        if (message.match("type=\"audio/ogg\"&gt;&lt;/audio&gt")) {
            return message = renderHtmlForAudio(message);
        }
        return message;
    }

    // Html audio helper function to decode html
    function renderHtmlForAudio(unsafe)
    {
        return unsafe
            .replace(/&amp;/g, "&")
            .replace(/&lt;/g, "<")
            .replace(/&gt;/g, ">")
            .replace(/&quot;/g, "\"")
            .replace(/&#039;/g, "'");
    }

    /*---------------* Security escaping html in chatboxes prevent database injection *---------------*/
    function escapeHtml(unsafe)
    {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/'/g, "&#039;");
    }

    /*---------------* Check messages if contains any links or images *---------------*/
    function createTextLinks_(text)
    {
        var regex = (/\.(gif|jpg|jpeg|tiff|png|swf)$/i);
        return (text || "").replace(/([^\S]|^)(((https?\:\/\/)|(www\.))(\S+))/gi, function (match, string, url) {
            var hyperlink = url;
            if (!hyperlink.match("^https?:\/\/")) {
                hyperlink = "//" + hyperlink;
            }
            if (hyperlink.match("^http?:\/\/")) {
                hyperlink = hyperlink.replace("http://", "//");
            }
            if (hyperlink.match(regex)) {
                return string + "<a href=\"" + hyperlink + "\" target=\"blank\" data-lity><img class=\"prchat_convertedImage\" src=\"" + hyperlink + "\"/></a>";
            } else {
                return string + "<a data-lity target=\"blank\" href=\"" + hyperlink + "\">" + url + "</a>";
            }
        });
    }

    /*---------------* Capitalize first string of letter *---------------*/
    function strCapitalize(string)
    {
        if (string != undefined) {
            var firstCP = string.codePointAt(0);
            var index = firstCP > 0xFFFF ? 2 : 1;

            return String.fromCodePoint(firstCP).toUpperCase() + string.slice(index);
        }
    }


    /*---------------* Function that handles clients events after clicked back to .crm_clients in tabs *---------------*/
    function clientsListCheck()
    {
        $(".client_messages").removeClass("isFocused");
        $("body").find(".contact_name.selected").removeClass("selected");
    }

    /*---------------* Get user avatar picture *---------------*/
    function fetchUserAvatar(id, image_name)
    {
        var type = "thumb";
        var url = site_url + "/assets/images/user-placeholder.jpg";

        if (image_name == false || image_name == null) {
            return url;
        }

        if (image_name != null) {
            url = site_url + "/uploads/staff_profile_images/" + id + "/" + type + "_" + image_name;
        } else {
            url = site_url + "/assets/images/user-placeholder.jpg";
        }
        return url;
    }

    /*---------------* Check if element has scrollbar *---------------*/
    (function ($) {
        $.fn.hasScrollBar = function () {
            return this.get(0).scrollHeight > this.get(0).clientHeight;
        };
    })(jQuery);

    /*---------------* Set no more messages if not found in database *---------------*/
    // Used in chat_full_view.php
    function prchat_setNoMoreMessages()
    {
        if ($("#no_messages").length == 0) {

            $(".client_messages").prepend("<div class=\"text-center mtop5\" id=\"no_messages\">" + prchatSettings.noMoreMessagesText + "</div>");

            $(".messages").prepend("<div class=\"text-center mtop5\" id=\"no_messages\">" + prchatSettings.noMoreMessagesText + "</div>");
        }
    }

    // Used in chat_full_view.php
    function prchat_setNoMoreGroupMessages()
    {
        if ($("#no_messages").length == 0) {
            $(".group_messages .chat_group_messages").prepend("<div class=\"text-center mtop5\" id=\"no_messages\">" + prchatSettings.noMoreMessagesText + "</div>");
        }
    }

    // Used in chat_clients_view.php
    function prchat_setNoMoreStaffMessages()
    {
        if ($("#no_messages").length == 0) {
            $(".m-area").prepend("<div class=\"text-center mtopbottomfixed\" id=\"no_messages\">" + customerSettings.noMoreMessagesText + "</div>");
        }
    }

    /*---------------* Chat shortcuts for better user experience Ctrl+Alt+Z and Ctrl+Alt+S *---------------*/
    $(document).keydown(function (e) {
        if ((e.which === 90 || e.keyCode == 90) && e.ctrlKey && e.altKey) {
            $(".messages").animate({
                scrollTop: $(".messages").prop("scrollHeight")
            }, 500);
        }

        if ((e.which === 83 || e.keyCode == 83) && e.ctrlKey && e.altKey) {
            $("#frame").find("#search_field").focus();
        }

        if ((e.which === 81 || e.keyCode == 81) && e.ctrlKey && e.shiftKey) {
            $(".quickMentions a").trigger("click");
        }

        if ((e.which === 70 || e.keyCode == 70) && e.ctrlKey && e.altKey) {
            if ($("#shared_user_files").is(":visible")) {
                $("#shared_user_files").click();
            }
        }
    });

    var decodeChatMessageHtml = function (html) {
        var text = document.createElement("textarea");
        text.innerHTML = html;
        return text.value;
    };

    if ($(window).width() > 733) {
        $("body").removeClass("hide-sidebar").addClass("show-sidebar");
    } else {
        $("body").removeClass("show-sidebar").addClass("hide-sidebar");
    }

    function animateContent()
    {
        /**
         * After all notifications are read show all staff
         */
        $(".chat_contacts_list li a:visible").filter(function (i, el) {
            const user = $(el);
            if (user.find("span.unread-notifications").is(":hidden")) {
                $(".chat_contacts_list li a:hidden").show();
            }
        });

        if (window.matchMedia("only screen and (max-width: 735px)").matches) {
            var contentWidth = $("#frame .content");
            setTimeout(function () {
                isContentActive = true;
            }, 1000);
            contentWidth.show().animate({
                "left": "0",
                "opacity": "1"
            }, 50, "linear");
            $("#frame #sidepanel").fadeOut(50);
        }
    }

    function chatBackMobile()
    {
        if (window.matchMedia("only screen and (max-width: 735px)").matches) {
            $("#frame #sidepanel").fadeIn(50);
            $("#frame .content").animate({
                "left": "+=100%",
                "opacity": "0"
            }, 200, "linear", function () {
                $(this).hide();
            });
            isContentActive = false;
        }
    }

    function _debounce(func, wait, immediate)
    {
        var timeout;
        return function () {
            var context = this,
                args = arguments;
            var later = function () {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    };

    $("#frame textarea").on("focus click", function () {
        if (window.matchMedia("only screen and (max-width: 735px)").matches) {
            setTimeout(function () {
                scroll_event();
            }, 500);
        }
    });

    /**
     * In case internet connection returns back and field stays happends on mobile
     */
    $("body").on("click", ".connection_field", function () {
        $(this).fadeOut();
    });


    /**
     * OptionsMore hover evenets show and hide options three dots
     */
    $("body").on("mouseenter dblclick", "li.sent, li.replies", function () {
        $(this).find(".chooseOption").show();
    }).on("mouseleave dblclick", "li.sent, li.replies", function () {
        $(this).find(".chooseOption, .optionsMore").hide();
    });

    /**
     * Show options for Remove or Forward
     */
    $("body").on("click", ".chooseOption", function () {
        $(this).next().css("display", "initial");
    });

    /**
     * Remove message click event
     */
    $("body").on("click", ".messages ._removeMessage", function () {
        $(this).attr("disabled", true);
        delete_chat_message($(this).parent().attr("data-id"));
    });


    /**
     * Copy message click event
     */
    $("body").on("click", "._copyMessage", function () {
        const cantCopy = "<?= _l('chat_cant_copy') ?>";
        const messgeCopied = "<?= _l('chat_message_copied') ?>";
        const copyText = $(this).parents("li").children("p:first").text();

        if (!copyText) {
            alert_float("warning", cantCopy);
            return false;
        }

        const el = document.createElement("textarea");
        el.value = copyText;
        document.body.appendChild(el);
        el.select();
        document.execCommand("copy");
        document.body.removeChild(el);
        alert_float("info", messgeCopied);
    });

    /**
     * Forward message click event
     */
    $("body").on("click", "._forwardMessage", function () {

        let message = $(this).parents("li").find("p").html();
        let messageEscaped = $(this).parents("li").find("p").text();

        $(".modal_container").load(prchatSettings.showForwardModal, function () {
            if ($(".modal-backdrop.fade").hasClass("in")) {
                $(".modal-backdrop.fade").remove();
            }

            $("#forwardToModal").modal({
                show: true
            });

            $("#forwardToModal").append(`
            <input class="_dataMessage" hidden data-message="${message}"/>
            <input class="_dataMessage escaped" hidden data-message-escaped="${messageEscaped}"/>
            `);
        });
    });

    /**
     * Delete or forward html for message
     */
    function deleteOrForward(messageId, ticket = null)
    {
        const l_ticket = "<?= _l('ticket') ?>";
        const l_copy = "<?= _l('copy') ?>";
        const l_forward = "<?= _l('chat_forward_message_btn') ?>";
        const l_remove = "<?= _l('chat_delete_message') ?>";
        const l_reply = "<?= _l('chat_reply_message') ?>";

        return `
            <div class="messageOptionsDiv">
            <svg height="22px" class="chooseOption" width="22px" viewBox="0 0 22 22"><circle fill="#777777" cx="11" cy="6" r="2" stroke-width="1px"></circle><circle fill="#777777" cx="11" cy="11" r="2" stroke-width="1px"></circle><circle fill="#777777" cx="11" cy="16" r="2" stroke-width="1px"></circle></svg>
            <div class="optionsMore" data-id="${messageId}">
            <button class="pointer optionBtn _copyMessage">${l_copy}</button>
            <button class="pointer optionBtn _forwardMessage">${l_forward}</button>
            <button class="pointer optionBtn _replyMessage">${l_reply}</button>

            ${(ticket) ? "<button class=\"pointer optionBtn btn_convert_conversation_to_ticket\">" + l_ticket + "</button>" : ""}
            </div>
            </div>
            `;
    }
  
  // Event listener for pressing Enter in the chatbox
$(".chatbox").on("keypress", function (e) {
    if (e.which === 13 && !e.shiftKey) { // Enter key and not holding shift
        e.preventDefault(); // Prevent the default action (form submission)

        const editMsgId = $(this).data("editMsgId");
        const replyMsgId = $(this).data("replyMsgId");
        const message = $(this).val().trim();

        if (message) {
            if (editMsgId) {
                edit_chat_message(editMsgId, message);
                $(this).removeData("editMsgId").val("").attr("placeholder", "Type a message...");
            } else if (replyMsgId) {
                send_reply(replyMsgId, message);
                $(this).removeData("replyMsgId").val("");
                $('.reply-header').remove(); // Assuming you have some UI element for reply
                adjustMessagesContainer();
                resetChatboxHeight();
                restoreMessagesContainer();
                isReplying = false; // Reset the replying state
            }
        }
    }
});
  
// Global variables to store original state
var originalMaxHeight = '';
var originalMinHeight = '';

// Function to truncate text by characters with an option to limit by lines
function truncateText(text, maxLines, lineLength) {
    const lines = text.split("\n");
    let truncated = lines.slice(0, maxLines).join("\n");
    if (truncated.length > maxLines * lineLength) {
        truncated = truncated.slice(0, maxLines * lineLength) + '...'; // Limit by characters if too long
    }
    return truncated;
}

var isReplying = false; // Initialize isReplying flag

  
// Event listener for reply button
$("body").on("click", "._replyMessage", function () {
    if (isReplying) {
        alert('Finish replying to the current message before starting another reply.');
        return; // Exit if already replying
    }
    isReplying = true; // Set the flag as a reply is being started

    const messages = document.querySelector('.messages');
    originalMaxHeight = messages.style.maxHeight || window.getComputedStyle(messages).maxHeight;
    originalMinHeight = messages.style.minHeight || window.getComputedStyle(messages).minHeight;

    const msgId = $(this).closest(".optionsMore").attr("data-id");
    const messageElement = $("body").find(".messages li#" + msgId + " p");

    // Clone the message element
    const clonedMessageElement = messageElement.clone();
    clonedMessageElement.find("svg, .edited-time").remove();

    // Remove nested reply elements
    clonedMessageElement.find(".original-message").remove();

    // Get the trimmed and optionally truncated message
    let originalMessage = clonedMessageElement.text().trim().replace(/Edited at:.*$/, '').trim();
    originalMessage = truncateText(originalMessage, 3, 100); // Truncate after 3 lines or 100 characters

    const replyHeader = `<div class="reply-header">
        <i class="fa-solid fa-reply"></i> Replying to: <span class="reply-text">${originalMessage}</span>
        <button class="close-reply" onclick="$(this).parent().remove(); $('.chatbox').val('').removeData('replyMsgId').focus(); restoreMessagesContainer(); resetChatboxHeight(); isReplying = false;">&times;</button>
    </div>`;

    const chatbox = $(".chatbox");
    chatbox.val('').data("replyMsgId", msgId).focus().before(replyHeader);
    adjustMessagesContainer();
});
  
  
  // Define the scrollToMessage function
function scrollToMessage(messageId) {
    var messageElement = document.getElementById(messageId);
    var messagesContainer = document.querySelector('.messages');
    
    if (messageElement) {
        // Message is already loaded, scroll to it
        scrollToElement(messageElement);
    } else {
        // Message is not loaded, load more messages
        loadMoreMessages(messageId, messagesContainer);
    }
}

function scrollToElement(element) {
    var messagesContainer = document.querySelector('.messages');
    var offsetTop = element.offsetTop;
    var containerHeight = messagesContainer.clientHeight;
    var elementHeight = element.clientHeight;

    // Adjust the scroll position
    var scrollPosition = offsetTop - containerHeight + elementHeight + 20; // Adjust 20px to have some padding
    messagesContainer.scrollTo({ top: scrollPosition, behavior: 'smooth' });

    // Highlight the element
    element.classList.add('highlight');
    setTimeout(function () {
        element.classList.remove('highlight');
    }, 2000);
}

function loadMoreMessages(messageId, container) {
    var to = $("#contacts ul li.contact").children("a.active_chat").attr("id");
    var from = userSessionId;
    var not_seen_icon = "";

    if (offsetPush >= 10) {
        $("#frame .messages").find(".message_loader").show();
        $.get(prchatSettings.getMessages, {
            from: from,
            to: to,
            offset: offsetPush,
        }).done(function (message) {
            message = JSON.parse(message);
            if (Array.isArray(message) == false) {
                endOfScroll = true;
                $("#frame .messages").find(".message_loader").hide();
                if ($(container).hasScrollBar() && endOfScroll == true) {
                    prchat_setNoMoreMessages();
                }
            } else {
                offsetPush += 10;
            }
            $(message).each(function (i, value) {
                // existing code to append/prepend messages
                appendMessage(value);
            });

            // Check again if the message is now loaded
            var messageElement = document.getElementById(messageId);
            if (messageElement) {
                scrollToElement(messageElement);
                $("#frame .messages").find(".message_loader").hide();
            } else if (endOfScroll == false) {
                // Scroll to bottom to load more messages
                $(container).scrollTop(200);
                loadMoreMessages(messageId, container);
            } else {
                $("#frame .messages").find(".message_loader").hide();
            }
        });
        activateLoader();
    }
}
  
  function appendMessage(value) {
    var not_seen_icon = "";
    var optionsMore = deleteOrForward(value.id);
    var element = $(".messages#id_" + value.reciever_id).find("ul");
    var isViewed = value.viewed == 1;

    if (!isViewed) {
        not_seen_icon = '<i class="fa-solid fa-circle-check circle-unseen" data-toggle="tooltip" data-container="body" data-placement="left" title="<?= _l('chat_msg_delivered'); ?>" aria-hidden="true"></i>';
    }

    if (value.reciever_id == userSessionId) {
        element.prepend("<li class=\"replies\" id=\"" + value.id + "\"><img class=\"friendProfilePic\" src=\"" + fetchUserAvatar(value.sender_id, value.user_image) + "\" data-toggle=\"tooltip\" data-container=\"body\" data-placement=\"right\" title=\"" + value.time_sent_formatted + "\"/><p class=\"friend\">" + (value.original_message ? "<br><span class=\"original-message\" onclick='scrollToMessage(" + value.original_message_id + ")' style=\" color: gray;\">" + "<i class='fa-solid fa-reply' aria-hidden='true'></i>" + "<i class='fa-solid fa-reply' aria-hidden='true'></i>" + value.original_message + "</span><br><br>" : "") + value.message + (value.edited_formatted ? "<br><span class=\"edited-time\" style=\"font-size: 10px; color: gray;\"><i class='fa fa-pencil' aria-hidden='true'></i> Edited at: " + moment(value.edited_formatted).format("h:mm:ss A, DD MMM YYYY") + "</span>" : "") + "</p>" + optionsMore + "</li>");
    } else {
        element.prepend("<li class=\"sent\" id=\"" + value.id + "\">" + not_seen_icon + "<img class=\"myProfilePic\" src=\"" + fetchUserAvatar(value.sender_id, value.user_image) + "\" data-toggle=\"tooltip\" data-container=\"body\" data-placement=\"left\" title=\"" + value.time_sent_formatted + "\"/><p class=\"you\" id=\"msg_" + value.id + "\" data-toggle=\"tooltip\" data-title=\"" + (!isViewed ? '<?= _l('chat_not_seen'); ?>' : '<?= _l('chat_msg_seen'); ?> ' + moment(value.viewed_at).format("h:mm:ss A, DD MMM YYYY")) + "\">" + (value.original_message ? "<br><span class=\"original-message\" onclick='scrollToMessage(" + value.original_message_id + ")' style=\" color: gray;\">" + "<i class='fa-solid fa-reply' aria-hidden='true'></i>" + "<i class='fa-solid fa-reply' aria-hidden='true'></i>" + value.original_message + "</span><br><br>" : "")  + value.message + (value.edited_formatted ? "<br><span class=\"edited-time\" style=\"font-size: 10px; color: gray;\"><i class='fa fa-pencil' aria-hidden='true'></i> Edited at: " + moment(value.edited_formatted).format("h:mm:ss A, DD MMM YYYY") + "</span>" : "") + "</p>" + optionsMore + "</li>");
    }
}
  
  // Helper function to restore the original height of the messages container
function restoreMessagesContainer() {
    const messages = document.querySelector('.messages');
    messages.style.maxHeight = originalMaxHeight;
    messages.style.minHeight = originalMinHeight;
}



// Function to adjust the messages container
function adjustMessagesContainer() {
    const textarea = document.querySelector('.chatbox');
    const messages = document.querySelector('.messages');
    var headerAndFooterHeight = 142;
    var additionalPadding = 98;
    messages.style.maxHeight = `calc(100% - ${headerAndFooterHeight + textarea.offsetHeight}px)`;
    messages.style.minHeight = `calc(100% - ${headerAndFooterHeight + additionalPadding}px)`;
    messages.scrollTop = messages.scrollHeight;
}

// Function to reset the chatbox height to default
function resetChatboxHeight() {
    var textarea = document.querySelector('.chatbox');
    textarea.style.height = '45px'; // Directly set to the default height
}
</script>