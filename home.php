<?


// Include the necessary PHP files
include_once("functions.php");
include_once("webai-backend-3.php");

session_start();

unset($_SESSION['activeRuns']);
$_SESSION['thread_id'] = null;

$baseQuestion = $a->send_base_questions();
//p($baseQuestion);

$version = "1.0";

$backend = 'webai-backend-3.php';

$ses = '1';

$check_recomendations = "0";

$AppName = "CalmCampus";

$erMode = $a->wpObj['erMode'];


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Head-1: Meta Tags -->
    <meta charset="UTF8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $AppName ?> <?= $version ?></title>

    <!-- Head-2: External Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="src/style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>




    <script>
        // Minimal global scripts
        var c = function(...args) {
        };
    </script>






</head>

<body>
    <!-- Body-1: Header -->

    <style>


    </style>




    <!-- Skinny header -->
    <header class="app-header <?= $erMode == 1 ?  "dark-red" : '';  ?>">
    <div id="nav-area">
            <div>
                <a href="#" id="helpbox">Help</a>
            </div>
        </div>
        <h1 class="app-title"><?= $AppName ?> v<?= $version ?> <?= $erMode == 1 ?  "| EMERGENCY MODE AS BEEN ACTIVATED BY CAMPUS ADMINISTRATOR" : '';  ?></h1>
        <div><a href="#" id="googletranslate"></a></div>

    </header>










    <!-- Body-2: Main Content -->
    <main>
        <!-- Dynamic and functional content will be placed here -->




        <div class="container mcontain">

            <div class="main-container">




                <!-- Content area above chatbot -->
                <div id="displayContainer">

                </div>





                <!-- Fade overlay -->
                <div class="fade-overlay"></div>

                <!-- Chatbot container -->
                <div class="chat-container">
                    <div id="chatBox" class="chat-box">
                        <!-- Chat messages will appear here -->
                    </div>

                    <!-- Chatbot input area -->
                    <div class="input-group mt-3 chat-input-group">
                        <input type="text" id="userInput" class="form-control chat-input" placeholder="Chat with CalmCampus AI">
                        <div class="input-group-append">
                            <button id="sendButton" class="btn btn-send">
                                <i class="fa-solid fa-circle-arrow-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="suggestions-container">
                        <div id="questionsContainer">
                            <!-- Questions will be dynamically loaded here -->
                        </div>
                    </div>


                </div>


















                <script>
                    $(document).ready(function() {
                        // jQuery to fade in the overlay on hover of the chatbox
                        $('.chat-container').hover(
                            function() {
                                // On mouse enter (fade in the overlay)
                                $('.fade-overlay').fadeTo(300, 0); // Fade in to full opacity in 300ms
                            },
                            function() {
                                // On mouse leave (fade out the overlay)
                                $('.fade-overlay').fadeTo(300, 1); // Fade out to transparent in 300ms
                            }
                        );
                    });
                    $(document).ready(function() {
                        var originalHeight = $('.chat-container').height() + 35; // Store original chat container height
                        var chatBoxMinimizedHeight = $('#chatBox').height(); // Store the height when chatbox is minimized
                        var clicked = false; // Flag to track if clicking effect has occurred
                        var isInitiallyEmpty = false; // Flag to track if the chatbox was empty on load

                        // Function to shrink chat-container and hide chatbox
                        function shrinkChat() {
                            $('#chatBox').fadeOut(150); // Faster fade out for the chatbox
                            if ($(window).width() <= 768) {
                                // On mobile, shrink to a smaller height
                                $('.chat-container').animate({
                                    height: '10vh' // Shrink chat-container on mobile to 10vh
                                }, 150);
                            } else {
                                // On desktop, shrink to chatBoxMinimizedHeight
                                $('.chat-container').animate({
                                    height: chatBoxMinimizedHeight // Shrink chat-container to the size of chatBox
                                }, 150);
                            }
                            $('#displayContainer').animate({
                                height: '84vh' // Adjust displayContainer to 84vh when clicked
                            }, 150);
                            $('.fade-overlay').fadeOut(150); // Hide the overlay quickly
                            clicked = true; // Set the flag to true indicating the click event has occurred
                        }

                        // Check if there are any chat messages in the chatbox on page load
                        if ($('#chatBox .user-message, #chatBox .bot-message').length === 0) {
                            shrinkChat(); // If no messages, shrink the chat container
                            isInitiallyEmpty = true; // Set flag to indicate chatbox was empty
                        }

                        // On focus, expand the chat container by adding class
                        $('#userInput').focus(function() {
                            if ($(window).width() <= 768) {
                                // On mobile, expand to full screen
                                $('.chat-container').addClass('expanded-mobile').animate({
                                    height: '100vh' // Ensure it expands fully on mobile
                                }, 150);
                                $('.chat-box').addClass('mobile-chat-height');
                            } else {
                                // On desktop/tablet, expand to 50vh
                                $('.chat-container').addClass('expanded-desktop').animate({
                                    height: '50vh' // Ensure it expands properly on desktop
                                }, 150);
                                $('.chat-box').addClass('desktop-chat-height');
                            }
                        });

                        // On blur (when focus is lost), revert to the original height by removing class
                        $('#userInput').blur(function() {
                            $('.chat-container').removeClass('expanded-mobile expanded-desktop');
                            $('.chat-box').removeClass('mobile-chat-height desktop-chat-height');
                            // Ensure the chatbox scrolls to the bottom after resizing
                            var chatBox = $('#chatBox');
                            chatBox.scrollTop(chatBox[0].scrollHeight);
                        });

                        // Fade out chat box, shrink chat-container, adjust #displayContainer height, and hide overlay when displayContainer is clicked
                        $('#displayContainer').click(function() {
                            shrinkChat();
                        });

                        // Bring back the chat box, reset displayContainer height, and overlay only if the clicking effect has occurred
                        $('#userInput').hover(function() {
                            if (clicked || isInitiallyEmpty) { // Only restore if the click effect has occurred or chat was initially empty
                                $('#chatBox').fadeIn(150); // Faster fade in for the chatbox
                                if ($(window).width() <= 768) {
                                    // On mobile, expand back to full height
                                    $('.chat-container').animate({
                                        height: '100vh' // Restore full height on mobile
                                    }, 150);
                                } else {
                                    // On desktop, expand back to original height
                                    $('.chat-container').animate({
                                        height: originalHeight // Expand chat-container back to its original height
                                    }, 150);
                                }
                                $('#displayContainer').animate({
                                    height: '80vh' // Reset displayContainer back to 80vh
                                }, 150);
                                $('.fade-overlay').fadeIn(150); // Show the overlay quickly
                                clicked = false; // Reset the flag after restoring the chatbox
                                isInitiallyEmpty = false; // Reset the initial empty flag
                            }
                        });
                    });
                </script>


                <script>
                    $(document).ready(function() {
                        $('#sendButton').click(function() {
                            sendMessage();
                        });

                        $('#userInput').keypress(function(e) {
                            if (e.which == 13) { // Enter key has keycode 13
                                e.preventDefault(); // Prevent the default action (form submission)
                                sendMessage();
                            }
                        });


                    });

                    function sendMessage() {
                        let userText = $('#userInput').val();
                        if (userText.trim() !== '') {
                            displayUserMessage(userText);
                            $('#userInput').val('');
                            var tid;
                            $('#chatBox').append('<div class="bot-message" id="loadingGifContainer"><div class="typing-indicator" id="loadingGif"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
                            $('#loadingGif').show();
                            $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);


                            // Save the threadid to the chat
                            if ($("#chatBox").data("tid")) {
                                tid = $("#chatBox").data("tid");
                            } else {
                                tid = null;
                            }
                            sendToBackend(userText, null, null, tid);
                        }
                    }

                    function displayUserMessage(message) {

                        // Replace new line characters with HTML line breaks
                        message = message.replace(/\n/g, '<br>');

                        $('#chatBox').append('<div class="user-message"><p>' + message + '</p></div>');
                        $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
                    }

                    function displayBotMessage(message, scd) {
                        // Replace new line characters with HTML line breaks
                        message = message.replace(/\n/g, '<br>');

                        // Create the chat message div with respId
                        var chatMsg = $("<div />", {
                            class: "bot-message",
                            "data-respId": scd.cid // Assuming respId is a property of scd
                        }).html('<p>' + message + '</p>');

                        // Append the chat message to the chatBox
                        $('#chatBox').append(chatMsg);

                        if (scd.isa == 1) {
                            let smallMessage = $("<a />", {
                                href: "#",
                                text: "Click here if your question was not answered.",
                                class: "small-message"
                            });
                            chatMsg.append(smallMessage);
                        }

                        $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);

                        // Event listener for small message click
                        $('.small-message').on('click', function() {
                            var previousChat = $(this).closest('.bot-message');
                            var respId = previousChat.attr('data-respId');
                            console.log("Clicked on small message. RespId:", respId);



                            // Save the threadid to the chat
                            if ($("#chatBox").data("tid")) {
                                tid = $("#chatBox").data("tid");
                            } else {
                                tid = null;
                            }

                            sendToBackend(null, 'respid', respId, tid, null);

                        });
                    }


                    let currentRequest = null;

                    function sendToBackend(message, statusRQ, qid, tid, rid) {


                        showSpinner('#displayContainer');


                        c(currentRequest, 'currentRequest');
                        if (currentRequest) {
                            currentRequest.abort(); // Abort the current request if it exists
                        }
                        if (currentRequestType) {
                            currentRequestType.abort();
                        }





                        currentRequest = $.ajax({
                            // url: '../chatai/chat-ai-backend.php', // Replace with your actual backend endpoint
                            url: '<?= $backend ?>', // Replace with your actual backend endpoint
                            method: 'POST',
                            data: {
                                message: message,
                                status: statusRQ,
                                r: "chatmsg",
                                qid: qid,
                                tid: tid,
                                rid: rid
                            },
                            success: function(responseRaw) {

                                currentRequest = null;

                                // Split the string into lines
                                const lines = responseRaw.trim().split('\n');

                                // Retrieve the last line
                                const lastLine = lines[lines.length - 1];

                                // Parse JSON from the last line without try/catch
                                const response = JSON.parse(lastLine);
                                c(response);



                                // Parse the response and extract the text
                                var botMessage = '';


                                if (!response) {
                                    displayBotMessage('Cannot parse message from server.', {});
                                    return;
                                }

                                // Save the threadid to the chat
                                $("#chatBox").data("tid", response.tid)

                                for (var i = 0; i < response.messages.length; i++) {
                                    displayBotMessage(response.messages[i].message, response);
                                    $('#loadingGif').hide();
                                    $('#loadingGifContainer').remove();


                                }

                                if (response.messages.length > 0) {
                                    //$("#userInput").val("Thank you!")

                                    processAfterMsg(response);
                                }


                                if (response.status == "in_progress" || response.status == 'queued') {
                                    sendToBackend("", "in_progress", response.db_qid, response.tid, response.rid);
                                    return;
                                }




                            },
                            error: function() {
                                currentRequest = null;

                                $('#loadingGif').hide();
                                $('#loadingGifContainer').remove();

                                displayBotMessage('Error occurred while sending message.', {});

                            }
                        });
                    }




                    function processAfterMsg(d) {

                        if (!d) d = {
                            "r": "afterm"
                        };
                        else d.r = "afterm";

                        $.ajax({
                            // url: '../chatai/chat-ai-backend.php', // Replace with your actual backend endpoint
                            url: '<?= $backend ?>', // Replace with your actual backend endpoint
                            method: 'POST',
                            data: d,
                            success: function(responseRaw) {

                                c("processAfterMsg completed", d);


                                // Split the string into lines
                                const lines = responseRaw.trim().split('\n');

                                // Retrieve the last line
                                const lastLine = lines[lines.length - 1];

                                // Parse JSON from the last line without try/catch
                                const response = JSON.parse(lastLine);
                                c(response, 'aftermsg');

                                if (response.bws) {
                                    updateDisplayHtml(response.bws.base_64);
                                    const chatHistory = [];

                                    $('#chatBox').children().each(function() {
                                        const messageText = $(this).find('p').html();

                                        // Check if the messageText exists to prevent null values
                                        if (messageText) {
                                            if ($(this).hasClass('user-message')) {
                                                chatHistory.push({
                                                    type: 'user',
                                                    message: messageText
                                                });
                                            } else if ($(this).hasClass('bot-message')) {
                                                chatHistory.push({
                                                    type: 'bot',
                                                    message: messageText,
                                                    responseId: $(this).attr('data-respid') || null // Handle potential null respid
                                                });
                                            }
                                        }
                                    });
                                    c(chatHistory, 'messageText');
                                    processAfterMsg({
                                        rExtended: 'nextQuestions',
                                        data: JSON.stringify(chatHistory)
                                    });
                                } else if (response.nextQuestions) {
                                    updateSuggestions(response.nextQuestions);
                                }




                            },
                            error: function() {


                            }
                        });


                    }





                    $(document).ready(function() {
                        $('#userInput').on('keypress', function(e) {
                            if (e.which == 32) { // Space key has keycode 32
                                var userText = $(this).val();
                                if ("<?= $check_recomendations; ?>" == 1) {
                                    sendTypingUpdate(userText);
                                }

                            }
                        });
                    });

                    let currentRequestType = null;

                    function sendTypingUpdate(currentText) {

                        c('sendTypingUpdate');

                        if (currentRequestType) {
                            currentRequestType.abort(); // Abort the current request if it exists
                        }

                        var data = {
                            message: currentText,
                            r: "qrec",
                            ses: <?= $ses; ?>
                        };
                        currentRequestType = $.ajax({
                            url: '<?= $backend ?>', //
                            method: 'POST',
                            data: data,
                            success: function(responseRaw) {
                                console.log('Typing update sent successfully');

                                currentRequestType = null;


                                // Split the string into lines
                                const lines = responseRaw.trim().split('\n');

                                // Retrieve the last line
                                const lastLine = lines[lines.length - 1];

                                // Parse JSON from the last line without try/catch
                                const response = JSON.parse(lastLine);

                                c(response);
                                displayQuestions(response);


                            },
                            error: function() {
                                currentRequestType = null;
                                console.log('Error occurred while sending typing update');
                            }
                        });
                    }
                </script>




























                <!-- Start of Code Snippet -->
                <script>
                    // Function to create and display questions
                    function displayQuestions(questions) {
                        const questionsContainer = $('#questionsContainer');
                        questionsContainer.empty(); // Clear the current list of questions
                        questions.forEach((question, index) => {
                            c(questions[index], 'questions obj');
                            const questionElement = $(`<div class='question' id='question${index}' data-qid='${question.id}'>${question.childQuestionText}</div>`);
                            questionElement.on('click', function(e) {
                                handleQuestionClick(question.childQuestionText, question.id);
                            });
                            questionsContainer.append(questionElement);
                        });
                    }

                    // Function to handle question click
                    function handleQuestionClick(question, qid) {
                        console.log('Question clicked:', question);
                        displayUserMessage(question);
                        sendMessageFromSuggestion(question, qid);
                    }

                    function sendMessageFromSuggestion(msg, qid) {
                        sendToBackend(msg, '', qid);
                    }





                    function updateSuggestions(newQuestions) {
                        // Clear current questions and display new suggestions
                        displayQuestions(newQuestions);
                    }

                    $(document).ready(function() {
                        // Initial call to display default questions on the page
                        var defaultQuestions = [];
                        defaultQuestions = <?= $baseQuestion  ?>;
                        displayQuestions(defaultQuestions);



                        $('#helpbox').on('click', function(e) {
                            e.preventDefault(); // Prevent the default action
                            // Your code to execute when #helpbox is clicked
                            showErrorMessage(`<div style="padding: 20px; background-color: #f9f9f9; border: 1px solid #ccc; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333;">
    <h2 style="margin-top: 0; font-size: 18px; color: #0056b3;">Welcome to CalmCampus AI</h2>
    <p>We understand how important it is for both parents and students to feel safe and informed about emergency procedures at school. That's why CalmCampus AI is here to help.</p>

    <p>This platform is powered by advanced AI and designed to make emergency procedures clear, accessible, and easy to understand. Whether it's an active shooter scenario, an earthquake, or a personal medical event, you can now access verified school safety protocols instantly.</p>

    <p>By using CalmCampus AI, you'll be able to ask questions in your own words and receive official responses that have been reviewed and approved by the Springfield School’s safety administrators.</p>

    <p>With this tool, both parents and students can stay informed, reduce anxiety, and gain peace of mind knowing that the school has a clear plan in place for any emergency situation.</p>

    <p>Feel free to explore and ask questions about the emergency procedures, knowing that you’re equipped with accurate and timely information.</p>

    <p>Stay safe, stay informed.</p>
</div>

                            `, '', 'Help');
                                        });


                    });
                </script>
















                <style>
                    <? if ($erMode) { ?>

                    /* Adjust the section layout to have a skinny image header */
                    .webcontent-section {
                        display: flex;
                        flex-direction: column;
                        min-height: 100vh;
                        background-color: #f8f9fa;
                        padding: 0;
                        /* Remove padding from the parent section */
                    }

                    /* Styling the image to make it a long skinny header */
                    .webcontent-image {
                        flex: 0 0 20vh;
                        /* Set the height to 20% of the viewport */
                        background-image: url('imgs/springfield-lockdown.jpeg');
                        background-size: cover;
                        background-position: center;
                        width: 100%;
                    }

                    /* Ensure the text content takes up the remaining full width and height */
                    .webcontent-text {
                        flex: 1;
                        padding: 20px;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        width: 100%;
                        /* Ensure the text takes up full screen width */
                    }

                    /* Responsive behavior for mobile devices */
                    @media (max-width: 767px) {
                        .webcontent-image {
                            flex: 0 0 15vh;
                            /* Make the header a bit shorter on mobile */
                        }

                        .webcontent-text {
                            padding: 15px;
                            /* Slightly reduce padding for smaller screens */
                        }
                    }

                    .webcontent-text h1 {
                        font-size: 2.5rem;
                        margin-bottom: 20px;
                    }

                    .webcontent-text p {
                        font-size: 1.2rem;
                        margin-bottom: 15px;
                    }

                    .webcontent-text ul {
                        list-style-type: disc;
                        padding-left: 20px;
                    }

                    .webcontent-text ul li {
                        margin-bottom: 10px;
                    }

                    <? } ?>
                </style>






            </div>
        </div>

        <!-- End of Code Snippet -->
























        <?


        $defaultHtml =  $a->SendDefaultWebSection();
        if ($erMode) {
            $defaultHtml =  $a->SendDefaultWebSection(911);
        }
        //p($defaultHtml);



        ?>


        <!-- Start of Code Snippet -->
        <script>
            // Function to update the display section with new HTML content
            function updateDisplayHtml(content, car) {
                const displayContainer = $('#displayContainer');

                // Check if the content is base64-encoded
                if (isBase64(content)) {
                    // Decode the base64 content to HTML
                    content = atob(content);
                }

                displayContainer.empty(); // Clear the current content

                // If 'car' is provided and contains items, load the carousel structure
                if (car && car.length > 0) {

                    let carouselId = 'carouselLoad'; // ID for the dynamic carousel
                    let carouselIndicators = ''; // To store the carousel indicators
                    let carouselItems = ''; // To store the carousel items

                    c(car, 'car')
                    // Build the carousel indicators and items based on 'car' array
                    car.forEach((item, index) => {
                        let isActive = index === 0 ? 'active' : ''; // Set the first item as active


                        let thiscontent = atob(item.b64txt);

                        // Carousel indicators
                        carouselIndicators += `<li data-target="#${carouselId}" data-slide-to="${index}" class="${isActive}"></li>`;

                        // Carousel items
                        carouselItems += `
                <div class="carousel-item ${isActive}">
                    ${thiscontent}
                </div>`;
                    });

                    // Complete carousel structure
                    const carouselHtml = `
                        <div id="${carouselId}" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                ${carouselIndicators}
                            </ol>
                            <div class="carousel-inner">
                                ${carouselItems}
                            </div>
                            <a class="carousel-control-prev" href="#${carouselId}" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#${carouselId}" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
        `;

                    // Render the carousel HTML into the display container
                    displayContainer.html(carouselHtml);
                    $('#carouselLoad').carousel({
                        interval: 10000, // Speed between transitions (3000 milliseconds = 3 seconds)
                        ride: 'carousel', // Start the carousel automatically
                        pause: 'hover' // Optional: Pause the carousel on hover
                    });

                } else {
                    // If 'car' is not provided or empty, load the default content
                    displayContainer.html(content);
                }
            }




            // Helper function to check if a string is base64-encoded
            function isBase64(str) {
                try {
                    return btoa(atob(str)) === str;
                } catch (err) {
                    return false;
                }
            }



            $(document).ready(function() {
                // Example of invoking the function with HTML content
                const dhtml64 = <?= $defaultHtml; ?>;
                updateDisplayHtml('', dhtml64);
            });
        </script>





        </div>















        <script>
            function showSpinner(sectionId) {
                // Remove any existing spinner
                $(sectionId).find('.spinner-container').remove();

                $(sectionId).empty();

                // Create the spinner container
                var spinnerContainer = $('<div>', {
                    class: 'spinner-container'
                });

                // Create the spinner image
                var spinner = $('<img>', {
                    src: 'https://res.cloudinary.com/dzpkqiylk/image/upload/v1519172594/spinner_bdflgu.gif',
                    class: 'spinner-image'
                });

                // Append the spinner to the container
                spinnerContainer.prepend(spinner);

                // Append the spinner container to the section
                $(sectionId).prepend(spinnerContainer);
            }
        </script>



        <script>
            function showErrorMessage(errorMessage, kind, title) {
                // Create a Bootstrap-styled error dialog with jQuery UI
                if (!kind) kind = 'info';
                if (!title) title = 'Error';

                $('<div></div>').html(`
                <div class="alert alert-${kind}" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i> ${errorMessage}
                </div>
            `).dialog({
                    title: title, // Set a plain title
                    modal: true,
                    resizable: false,
                    width: $(window).width() > 600 ? 600 : '90%',
                    closeOnEscape: true,
                    draggable: false,
                    buttons: {
                        OK: {
                            text: "OK",
                            class: "btn btn-primary", // Bootstrap button styling
                            click: function() {
                                $(this).dialog("close");
                            }
                        }
                    },
                    open: function() {
                        // Customize close button with Font Awesome icon
                        $(".ui-dialog-titlebar-close").html('<i class="fas fa-times"></i>');
                        $(".ui-dialog-titlebar-close").attr("title", "Close"); // Optional title for accessibility

                        // Add Bootstrap modal styling to the dialog
                       // $(".ui-dialog").addClass("modal-content");
                    }
                });
            }
        </script>











    </main>







    
    <footer>
        <!-- Footer content will be added here -->
















    </footer>


   
    <script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en', 
            includedLanguages: 'en,es,vi,zh-CN,tl', // Common languages in Texas
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
        }, 'googletranslate');
    }
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>


    <script>
        // Additional JavaScript functionality will be added here
    </script>
</body>

</html>