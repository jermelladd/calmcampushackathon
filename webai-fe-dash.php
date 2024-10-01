<?


// Include the necessary PHP files
include_once("functions.php");
include_once("webai-backend-3.php");

session_start();

unset($_SESSION['activeRuns']);
$_SESSION['thread_id'] = null;

generic_login($con, "users", "username", "password");

$questions = $a->DashQuestions(array('ask' => 'answers_without_html'));
//p($questions);

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
    <title><?= $AppName ?> Dashboard <?= $version ?></title>

    <!-- Head-2: External Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="src/style-dash.css">

    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">


    <!-- TinyMCE Script -->
    <script src="lib/tinymce/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>




    <!-- Head-3: Global Styles and Scripts -->

    <script>
        // Minimal global scripts
        var c = function(...args) {
        };
        var je = JSON.stringify;
        var jd = JSON.parse;
    </script>
</head>

<body>
    <!-- Body-1: Header -->


    <header class="app-header <?= $erMode == 1 ?  "dark-red" : '';  ?>">
        <div id="nav-area">
            <div>
                <a href="webai-fe-2.php">Home Page</a>
                <a href="#" id="helpbox">Help</a>
            </div>
        </div>
        <h1 class="app-title"><?= $AppName ?> Dashboard v<?= $version ?> </h1>
        <div class="toggle-container">
            <label for="emergencyToggle">Toggle Emergency Mode</label>
            <div class="custom-control custom-switch">
                <!-- Add the checked attribute to make the toggle on by default -->
                <input type="checkbox" class="custom-control-input" id="emergencyToggle" <?= $erMode == 1 ?  "checked" : '';  ?>>
                <label class="custom-control-label" for="emergencyToggle"></label>
            </div>
        </div>
    </header>




    <script>
        $(document).ready(function() {
            // Add an event listener for when the toggle is changed
            $('#emergencyToggle').change(function() {
                if ($(this).is(':checked')) {
                    // Apply the dark-red class to fade the background color
                    $('.app-header').addClass('dark-red');
                    dataSendBtn({
                        rExtended: 'er-button',
                        kind: 1,
                        r: "dash"
                    }, 1);
                    showErrorMessage("<b>EMERGENCY MODE ACTIVATED.</b> Use this mode when your Campus is in an active emergency. The frontend of your site has now been updated to Emergency Mode. To end Emergency Mode, toggle the switch back to the off position.", "danger", 'EMERGENCY MODE ');
                    loadStudentTable(studentData);
                } else {
                    // Remove the dark-red class to revert to the original color
                    $('.app-header').removeClass('dark-red');
                    dataSendBtn({
                        rExtended: 'er-button',
                        kind: 0,
                        r: "dash"
                    }, 1);
                    $("#questionsContainer").empty();
                    google.charts.setOnLoadCallback(drawChart);


                }
            });
            $('#helpbox').on('click', function(e) {
                e.preventDefault(); // Prevent the default action
                // Your code to execute when #helpbox is clicked
                showErrorMessage(`<div style="padding: 20px; background-color: #f9f9f9; border: 1px solid #ccc; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333;">
                                <h2 style="margin-top: 0; font-size: 18px; color: #0056b3;">Welcome to CalmCampus AI Dashboard</h2>
                                <p>This page is designed for school or district admins to configure their site's settings. The chatbot below connects directly to the AI Assistant, which can answer questions about the content stored in the database. Please note, any information asked here will not be saved (unless otherwise noted) for parents or students to view.</p>
                                
                                <p>The "Generate Questions" button below allows site admins to generate common questions that their community might ask. While the app can function without pre-generated questions, it’s highly recommended to have a robust set of pre-generated questions. This ensures that the CalmCampus AI bot provides tailored responses for your community.</p>
                                
                                <p>You can also customize responses by editing the Rich Text or Plain Text replies. This allows flexibility in how the chatbot interacts with users.</p>
                                
                                <p>In the top header, you can toggle <strong>Emergency Mode</strong>. This should only be activated during an actual emergency. When toggled, the frontend site for your community will change its styling, providing an additional line of communication to parents and students during critical events.</p>
                            </div>`, '', 'Help');
            });
        });
    </script>




    <!-- Body-2: Main Content -->
    <main>
        <!-- Dynamic and functional content will be placed here -->


        <style>

                .webcontent-image {
                        flex: 0 0 20vh;
                        /* Set the height to 20% of the viewport */
                        background-image: url('imgs/springfield-mascot.jpeg');
                        background-size:contain;
                        background-position: center;
                        width: 100%;
                        height: 100px;
                        margin-bottom: 10px;
                  
                    }

        </style>

        <div class="container mcontain" style="margin-top: 50px;">

        <div class="webcontent-image "></div>
            <div class="main-container">
                <div class="chat-container">
                    <div id="chatBox" class="chat-box">
                        <!-- Chat messages will appear here -->
                    </div>
                    <textarea id="userInput" class="form-control" placeholder="Type your message here..."></textarea>
                    <button id="sendButton" class="btn btn-default btn-block mt-3">Send</button>
                    <div>
                        <button id="" class="btn btn-primary mt-3 sbutton" data-kind='questions'>
                            Generate Questions
                        </button>
                        <button id="" class="btn btn-primary mt-3 sbutton" data-kind='NA'>Add Custom Question</button>
                    </div>

                </div>


















                <script>
                    $(document).ready(function() {
                        $('#sendButton').click(function() {
                            sendMessage();
                        });

                        $('.sbutton').click(function() {
                            var kind = $(this).data('kind');
                            if (kind == 'NA') {
                                //showErrorMessage("This feature is currently inactive. This button is a placeholder button that would give admins additional functionality.")
                                CustomQuestion();
                                return;
                            }
                            c("sbutton", kind);
                            dataSendBtn({
                                kind: kind,
                                rExtended: 'sbutton',
                                r: 'dash'
                            });
                        });

                        $('#userInput').keypress(function(e) {
                            if (e.which == 13) { // Enter key has keycode 13
                                e.preventDefault(); // Prevent the default actio,n (form submission)
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

                        if (!displayBotMessage && !scd) {
                            return;
                        }

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

                    function sendToBackend(message, statusRQ, qid, tid, rid, d) {


                        //showSpinner('#displayContainer');


                        c(currentRequest, 'currentRequest');
                        if (currentRequest) {
                            currentRequest.abort(); // Abort the current request if it exists
                        }


                        var isAdmin = 1;
                        if (!d) d = {};
                        if (d.qa == 1) {
                            isAdmin = 0;
                        }

                        var t1 = d.t;
                        if (d.t) {
                            delete d.t;
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
                                rid: rid,
                                admin: isAdmin,
                                d: d
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


                                if (response.status == 'failed') {
                                    c("FAILED", response.messages.message);
                                    showErrorMessage(response.messages.message);
                                    $('#loadingGif').hide();
                                    $('#loadingGifContainer').remove();

                                    if (d.qa == 1) {
                                        var $button = $(t1).find('.addqbut'); //
                                        $button.text('Add Question').removeClass("disabled").attr("style", ""); //

                                    }

                                    return;
                                }

                                // Save the threadid to the chat
                                $("#chatBox").data("tid", response.tid)

                                for (var i = 0; i < response.messages.length; i++) {
                                    displayBotMessage(response.messages[i].message, response);
                                    $('#loadingGif').hide();
                                    $('#loadingGifContainer').remove();


                                    if (d.qa == 1) {
                                        processAfterMsg(response, t1);
                                        return;
                                    }


                                    if ($("#chatBox").data("sbutton") == 1) {
                                        $("#chatBox").data("sbutton", 0);
                                        // Put items in box
                                        let cleanedString = response.messages[i].message_plain.replace(/\`\`\`json/g, '').replace(/\`\`\`/, '').trim();

                                        c(cleanedString, 'cleanedString');
                                        var jdata = jd(cleanedString);
                                       
                                        displayItems(jdata, {
                                            kind: 'questions'
                                        });
                                    }


                                }

                                if (response.messages.length > 0) {
                                    // $("#userInput").val("Thank you!")
                                    if (d.qa == 1) {
                                        processAfterMsg(response);
                                    }
                                    //
                                }




                                if (response.status == "in_progress" || response.status == 'queued') {
                                    d.t = t1;
                                    sendToBackend("", "in_progress", response.db_qid, response.tid, response.rid, d);
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




                    function processAfterMsg(d, t) {

                        if (!d) d = {
                            "r": "afterm"
                        };
                        else d.r = "afterm";

                        d.admin = 1;

                        $.ajax({
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

                                //updateDisplayHtml(response.bws.base_64);


                                c($(t), 'this t');
                                var $button = $(t).find('.addqbut'); //
                                $button.text('Question Added!'); //
                                $button.removeClass("").addClass('qa-success-button'); // 

                                $("#chatBox").data("activeFetching", 0);
                                var nextQuestions = $("#chatBox").data("jsonNext");
                                if (nextQuestions.length == 1) {
                                    c("length 1");
                                    $("#chatBox").data("jsonNext", []);
                                } else if (nextQuestions.length > 1) {
                                    dataSendBtn(nextQuestions[1]);
                                    nextQuestions = nextQuestions.slice(1);
                                    $("#chatBox").data("jsonNext", nextQuestions);
                                }


                                $('#displayContainer').empty();
                                UpdateAfterMsg(response.bws_answers, response.bws_question);


                                setTimeout(function() {
                                    // Code to run after 10 seconds
                                    c('response', response)
                                    console.log('Times up.');
                                    $(t).remove();
                                    $('#displayContainer').empty();

                                }, 3000);


                                return;

                                $("#item-" + d.id).remove();

                                GoThroughItems();


                            },
                            error: function() {


                            }
                        });


                    }

                    function dataSendBtn(d, done) {

                        if (!d) d = {
                            "admin": 1
                        };
                        else d.admin = 1;


                        var activeQuestion = $("#chatBox").data("activeFetching");

                        if (d.kind == 'addQuestion') {

                            if (activeQuestion == 1) {
                                var nextQuestions = $("#chatBox").data("jsonNext");
                                nextQuestions.push(d);
                                $("#chatBox").data("jsonNext", nextQuestions);
                                return;
                            } else {
                                $("#chatBox").data("activeFetching", 1);
                                var nextQuestions = [d];
                                $("#chatBox").data("jsonNext", nextQuestions)
                            }
                            $('#chatBox').append('<div class="bot-message" id="loadingGifContainer"><div class="typing-indicator" id="loadingGif"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
                            $('#loadingGif').show();

                        }



                        if (d.kind == "questions") {
                            $('#chatBox').append('<div class="bot-message" id="loadingGifContainer"><div class="typing-indicator" id="loadingGif"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
                            $('#loadingGif').show();
                        }

                        var t = d.this;
                        if (d.this) {
                            delete d.this;
                        }

                        $.ajax({
                            // url: '../chatai/chat-ai-backend.php', // Replace with your actual backend endpoint
                            url: '<?= $backend ?>', // Replace with your actual backend endpoint
                            method: 'POST',
                            data: d,
                            success: function(responseRaw) {

                                c("process dataSendBtn completed", d);


                                // Split the string into lines
                                const lines = responseRaw.trim().split('\n');

                                // Retrieve the last line
                                const lastLine = lines[lines.length - 1];

                                // Parse JSON from the last line without try/catch
                                const response = JSON.parse(lastLine);
                                c(response, 'respon dataSendBtn');

                                if (done) {
                                    return;
                                }


                                $("#chatBox").data("sbutton", 1);

                                var qa = d.kind == "questions" ? 0 : 1;

                                sendToBackend(null, response.status, response.db_qid, response.tid, response.rid, {
                                    'qa': qa,
                                    t: t
                                });
                                return;




                            },
                            error: function() {
                                c("error in dataSendBtn");

                            }
                        });


                    }





                    const textarea = document.getElementById('userInput');

                    textarea.addEventListener('input', function() {
                        this.style.height = 'auto'; // Reset height to auto to calculate new height
                        this.style.height = (this.scrollHeight) + 'px'; // Set the new height based on scrollHeight
                    });
                </script>





















                <!-- Question BOX Question BOX Question BOX Question BOX Question BOX Question BOX -->


                <!-- Start of Code Snippet -->
                <script>
                    // Function to create and display questions
                    function displayItems(items, data) {
                        c("displayItems", items, data);
                        const questionsContainer = $('#questionsContainer');
                        questionsContainer.empty(); // Clear the current list of questions
                        items.forEach((item, index) => {
                            c(item[index], 'item obj');
                            var button = '';
                            if (data.kind == 'questions') {
                                button = `
                <button class='btn btn-light addqbut' 
                        data-id='' 
                        data-kind='addqbut'
                        data-htm=''
                        data-section=''>Add Question</button>
            `;
                            }

                            const questionElement = $(`<div class='question' id='item-${item.itemid}' data-iid='${item.itemid}'>
        ${item.textfordiv} ${button}
        </div>`);
                            const buttonElement = questionElement.find('.addqbut');
                            buttonElement.on('click', function(e) {
                                // Check if the button already has the disabled class
                                if ($(this).hasClass('disabled')) {
                                    e.preventDefault(); // Prevent further action if button is disabled
                                    return;
                                }

                                // Add logic for when button is not disabled
                                if (data.kind == 'questions') {
                                    c("clicked questions", item.textfordiv);
                                    //c($(this), 'this targ'); return;
                                    dataSendBtn({
                                        kind: 'addQuestion',
                                        rExtended: 'addQuestion',
                                        r: 'dash',
                                        content: item.textfordiv,
                                        this: $(this.parentElement)
                                    });

                                    // Add disabled class and cursor-not-allowed style
                                    $(this).addClass('disabled').css('cursor', 'not-allowed').text("Pending");
                                }
                            });
                            questionsContainer.append(questionElement);
                        });
                    }

                    // Function to handle question click
                    function handleQuestionClick(question, qid) {
                        console.log('Question clicked:', question);
                        displayUserMessage(question);
                        //sendMessageFromSuggestion(question, qid);
                    }

                    function sendMessageFromSuggestion(msg, qid) {
                        sendToBackend(msg, '', qid);
                    }



                    function updateSuggestions(newQuestions) {
                        // Clear current questions and display new suggestions
                        displayQuestions(newQuestions);
                    }

                    function GoThroughItems() {

                        var dataIidArray = [];


                        $('#questionsContainer .question').each(function() {
                            var dataIid = $(this).data('iid');
                            var temp = {
                                id: dataIid,
                                r: 'getHtml'
                            }
                            dataIidArray.push(temp);
                            nowGoDo(temp);
                            return false;
                        });

                        //console.log(dataIidArray);


                    }

                    function nowGoDo(info, next) {

                        c(info, 'info');

                        if (info.r == 'getHtml') {
                            var d = {
                                bid: info.id,
                                id: info.id
                            };
                            processAfterMsg(d);
                        }


                    }



                    function UpdateAfterMsg(a, b) {

                        a = JSON.parse(a);
                        var a1 = a;
                        displayItemsSec(a1, $("#baanswer-container"), "baanser", $("#baanswer-left"), {
                            'addItems': 1
                        });


                        b = JSON.parse(b);
                        var b1 = b;

                        displayItemsSec(b1, $("#baquest-container"), "baquest", $("#baquest-left"), {
                            'addItems': 1
                        });



                        // We are currently stuck right now. Solution need to figure out how to add question. I think i got backend-3 working however cyrretnly only only undefined undefinedundefined looads in the quesiton box


                    }




                    $(document).ready(function() {
                        // Initial call to display default questions on the page
                        var defaultQuestions = [

                        ];
                        defaultQuestions = <?= $questions  ?>;
                        //displayItems(defaultQuestions);
                        GoThroughItems();

                        // LEXI 
                        //displayItems([{ 'textfordiv': "What's the process for factory resetting Camera 4's wireless transmitter?"}] , {kind: 'questions'});





                    });
                </script>









                <?php
                // Function to generate the last 5 days, with today as day 5
                function getLastFiveDays()
                {
                    $days = [];
                    for ($i = 4; $i >= 0; $i--) {
                        $day = date('D', strtotime("-$i days")); // 'D' returns a 3-letter day (e.g., Sun)
                        $fullDay = date('l', strtotime("-$i days")); // 'l' returns the full day name (e.g., Sunday)
                        $days[] = [
                            'shortDay' => $day,
                            'fullDay' => $fullDay
                        ];
                    }
                    return $days;
                }

                // Example question data for each day
                $questionData = [
                    "Sun" => 10,
                    "Mon" => 23,
                    "Tue" => 18,
                    "Wed" => 15,
                    "Thu" => 30,
                    "Fri" => 45,
                    "Sat" => 6,

                ];

                $lastFiveDays = getLastFiveDays();
                ?>


                <script type="text/javascript">
                    google.charts.load('current', {
                        packages: ['corechart', 'bar']
                    });



                    function drawChart() {
                        // PHP generated data passed to JavaScript
                        const daysData = <?php echo json_encode($lastFiveDays); ?>;
                        const questionData = <?php echo json_encode($questionData); ?>;

                        // Prepare the data for the chart
                        const data = new google.visualization.DataTable();
                        data.addColumn('string', 'Day');
                        data.addColumn('number', 'Number of Questions');
                        data.addColumn({
                            type: 'string',
                            role: 'tooltip'
                        });

                        // Loop over the PHP generated data and prepare the rows
                        daysData.forEach(day => {
                            const shortDay = day.shortDay;
                            const fullDay = day.fullDay;
                            const questionCount = questionData[shortDay];
                            data.addRow([shortDay, questionCount, `${questionCount} Questions Asked on ${fullDay}`]);
                        });

                        const options = {
                            title: 'Questions Asked in the Last 5 Days',
                            hAxis: {
                                title: 'Last 5 Days'
                            },
                            vAxis: {
                                title: 'Number of Questions'
                            },
                            legend: {
                                position: 'none'
                            }, // Removes the legend
                            colors: ['#3357FF', '#F333FF', '#FF33A1'], // Different colors for each bar
                            tooltip: {
                                isHtml: true
                            }
                        };
                        const chart = new google.visualization.ColumnChart(document.getElementById('questionsContainer'));
                        chart.draw(data, options);
                    }
                </script>







                <div class="suggestions-container">
                    <div>
                        <h3>Your Board</h3>
                    </div>
                    <div id="questionsContainer">
                        <!-- Questions will be dynamically loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- End of Code Snippet -->





















        <!-- DISPLAY HTML DISPLAY HTML DISPLAY HTML DISPLAY HTML DISPLAY HTML DISPLAY HTML DISPLAY HTML -->


        <?


        $defaultHtml =  "";
        //p($defaultHtml);



        ?>


        <!-- Start of Code Snippet -->
        <script>
            // Function to update the display section with new HTML content
            function updateDisplayHtml(content) {
                const displayContainer = $('#displayContainer');

                // Check if the content is base64-encoded
                if (isBase64(content)) {
                    // Decode the base64 content to HTML
                    content = atob(content);
                }

                displayContainer.empty(); // Clear the current content
                displayContainer.html(content); // Render the new HTML content
            }

            // Helper function to check if a string is base64-encoded
            function isBase64(str) {
                try {
                    return btoa(atob(str)) === str;
                } catch (err) {
                    return false;
                }
            }


            // Sample data for now however in live version we would connect to school campus API to get actual student data.
            let studentData = [{
                    name: "Emily Adams",
                    id: "2241234"
                },
                {
                    name: "Michael Brown",
                    id: "2245678"
                },
                {
                    name: "Sophia Carter",
                    id: "2243456"
                },
                {
                    name: "Jackson Davis",
                    id: "2247890"
                },
                {
                    name: "Aiden Edwards",
                    id: "2244321"
                },
                {
                    name: "Mia Ford",
                    id: "2248765"
                },
                {
                    name: "Noah Garcia",
                    id: "2242345"
                },
                {
                    name: "Liam Harris",
                    id: "2249876"
                },
                {
                    name: "Ella Jenkins",
                    id: "2246543"
                },
                {
                    name: "Olivia Kelly",
                    id: "2243452"
                },
                {
                    name: "Ethan Lewis",
                    id: "2241237"
                },
                {
                    name: "Ava Martinez",
                    id: "2246789"
                },
                {
                    name: "Lucas Nelson",
                    id: "2244567"
                },
                {
                    name: "Grace Ortiz",
                    id: "2247654"
                },
                {
                    name: "Benjamin Perez",
                    id: "2242341"
                },
                {
                    name: "Amelia Quinn",
                    id: "2245432"
                },
                {
                    name: "Elijah Roberts",
                    id: "2248763"
                },
                {
                    name: "Harper Sanchez",
                    id: "2243458"
                },
                {
                    name: "Alexander Turner",
                    id: "2249872"
                },
                {
                    name: "Isabella Vargas",
                    id: "2242343"
                },
                {
                    name: "Sebastian Wallace",
                    id: "2247659"
                },
                {
                    name: "Madison Young",
                    id: "2245431"
                },
                {
                    name: "Lucas Anderson",
                    id: "2246782"
                },
                {
                    name: "Chloe Brooks",
                    id: "2244561"
                },
                {
                    name: "Daniel Cooper",
                    id: "2249871"
                },
                {
                    name: "Scarlett Diaz",
                    id: "2241239"
                },
                {
                    name: "James Evans",
                    id: "2247653"
                },
                {
                    name: "Mason Fisher",
                    id: "2242346"
                },
                {
                    name: "Avery Gray",
                    id: "2245439"
                },
                {
                    name: "Charlotte Hill",
                    id: "2246781"
                }
            ];

            $(document).ready(function() {
                // Example of invoking the function with HTML content
                const dhtml64 = '<? echo base64_encode($defaultHtml['childWSHTML']);  ?>';
                updateDisplayHtml(dhtml64);

                <? if (!$erMode) { ?>

                    google.charts.setOnLoadCallback(drawChart);

                <? } else { ?>
                    loadStudentTable(studentData);


                <? }
                ?>


            });








            // Custom function that adds a new row with 3 cols
            // Custom function that adds a new row with 3 cols
            function CustomQuestion() {
                let newRow = `
    <div style="display: none;" id="slidingRow">
        <div class="row mt-3" >
            <!-- Col 1: Question by User -->
            <div class="col-md-6">
                <label>Question by User 
                    <i class="fas fa-info-circle" data-toggle="tooltip" data-bs-placement="top" title="This is the text that you expect your user to type when searching for the answer of the response you are creating. Your responses will go in the next two boxes."></i>
                </label>
                <textarea placeholder="How do parents contact us during an emergency?" class="form-control" id="newQuestionCustom" rows="5"></textarea>
            </div>
            <!-- Col 2: Chatbot Response (Plain Text) -->
            <div class="col-md-6">
                <label>Chatbot Response (Plain Text)
                    <i class="fas fa-info-circle" data-toggle="tooltip" data-bs-placement="top" title="This is the chatbot response text that will dictate what the chatbot outputs"></i>
                </label>
                <textarea placeholder="Parents can contact you during an emergency by reaching out to the email springfieldER@springfielddistrict.org."  class="form-control" id="newPlainTextCustom" rows="5"></textarea>
            </div>
            <!-- Col 3: Rich Text Page Response (Full Width) -->
            <div class="col-md-12 mt-3">
                <label>Rich Text Page Response 
                    <i class="fas fa-info-circle" data-toggle="tooltip" data-bs-placement="top" title="This content is the 'Rich Text' content that will load above the chatbot after the chatbot outputs its plain text. With 'Rich Text' you can add custom styling, input images and links, and also embed video."></i>
                </label>
                <textarea class="form-control" id="newRichTextCustom"></textarea>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <button class="btn btn-success" id="saveBtn">Save</button>
            </div>
        </div>
        </div>
    `;

                // Append the new row to the container
                $('#displayContainer').append(newRow);

                // Slide down the new row with animation
                $('#slidingRow').slideDown('slow', function() {
                    // Initialize Bootstrap tooltips AFTER the row is fully added to the DOM
                    $('[data-toggle="tooltip"]').tooltip();
                });

                // Initialize TinyMCE editor on the new textarea
                tinymce.init({
                    selector: '#newRichTextCustom',
                    height: 500,
                    plugins: 'lists link image table code',
                    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image',
                    branding: false,
                    license_key: 'gpl',
                });
            }

            // Listen for the save button click
            $(document).on('click', '#saveBtn', function() {
                // Get values from the text areas
                let questionValue = $('#newQuestionCustom').val();
                let plainTextValue = $('#newPlainTextCustom').val();
                let richTextValue = tinymce.get('newRichTextCustom').getContent();

                // Create an object with the values
                let responseObject = {
                    question: questionValue,
                    plainTextResponse: plainTextValue,
                    richTextResponse: richTextValue
                };


                var response = `For the purpose of this demo, this functionality has been disabled. If it were active, your responses would be saved, and a new question, plain text answer, and rich text answer would be added to the database. These would then be immediately accessible to students and parents via the CalmCampus AI bot on the frontend.`;
                showErrorMessage(response, '', 'Sample Reply');

                $('#slidingRow').slideUp('slow', function() {
                    // Initialize Bootstrap tooltips AFTER the row is fully added to the DOM

                });

                console.log(responseObject); // For testing purposes, you can replace this with an AJAX call if needed
            });



            function loadStudentTable(students) {
                // Empty the container
                $('#questionsContainer').empty();

                // Build table structure with Bootstrap and DataTables
                let tableHTML = `
    <table id="studentTable" class="table table-striped table-bordered" style="width:100%">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>ID</th>
          <th>Accounted For</th>
        </tr>
      </thead>
      <tbody>
      ${students.map(student => `
        <tr>
          <td>${student.name}</td>
          <td>${student.id}</td>
          <td>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="checkbox-${student.id}">
              <label class="form-check-label" for="checkbox-${student.id}">
                Accounted For
              </label>
            </div>
          </td>
        </tr>`).join('')}
      </tbody>
    </table>
    <div class="text-center mt-3">
      <button id="saveButton" class="btn btn-primary">Save</button>
    </div>
  `;

                // Append the table to the container
                $('#questionsContainer').append(tableHTML);

                // Initialize DataTable with only search and sorting (disable other features)
                $('#studentTable').DataTable({
                    paging: false,
                    info: false,
                    searching: true,
                    columnDefs: [{
                            orderable: true,
                            targets: [0, 1]
                        }, // Enable sorting only for Full Name and ID columns
                        {
                            orderable: false,
                            targets: [2]
                        } // Disable sorting for the "Accounted For" column
                    ],
                    language: {
                        search: '', // Customization of search box
                        searchPlaceholder: "Search students"
                    }
                });

                // Add event listener for the save button
                $('#saveButton').on('click', function() {
                    var msg = `This functionality has not been fully implemented yet. The concept behind this feature is that CalmCampus AI can serve as an additional line of communication when parents want to find out if their child has been marked as SAFE or ACCOUNTED FOR. This can be especially helpful during the chaos of an emergency situation. Parents would search in the chatbot using their student's ID code, and the chatbot would automatically respond by performing a real-time search in the database.`;
                    showErrorMessage(msg, '', 'Sample Reply');
                });
            }
        </script>

        <style>

        </style>

        <div id="displayContainer">
            <!-- Content will be dynamically loaded here -->





        </div>
        <!-- End of Code Snippet -->

        <style>

        </style>














































        <style>

        </style>

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




























        <style>



        </style>

        <?

        // Get All Questions 
        $baQuestions = $a->DashQuestions(array('ask' => 'all_base_questions'));
        ?>

        <div class="section-div">
            <div class="row">
                <div class="col-12 col-md-5 ">
                    <!-- Content for the 4-column section -->
                    <div class="sec sec-right">
                        <h3>Rich Text Answers</h3>
                        <div id="baquest-container"></div>
                    </div>
                </div>
                <div class="col-12 col-md-7">
                    <!-- Content for the 8-column section -->
                    <div class="sec left-sec" id='baquest-left'>

                    </div>
                </div>

            </div>
        </div>


        <?
        // Get All Answers 
        $answers = $a->DashQuestions(array('ask' => 'all_base_answers'));
        ?>

        <div class="section-div">
            <div class="row">

                <div class="col-12 col-md-5 ">
                    <!-- Content for the 4-column section -->
                    <div class="sec sec-right">
                        <h3>Chatbot Answers</h3>
                        <div id="baanswer-container"></div>
                    </div>
                </div>
                <div class="col-12 col-md-7">
                    <!-- Content for the 8-column section -->
                    <div class="sec left-sec" id='baanswer-left'>

                    </div>
                </div>
            </div>
        </div>





        <script>
            // Function to create and display questions
            function displayItemsSec(items, questionsContainer, pref, leftSection, d1 = {}) {
                // const questionsContainer = $('#questionsContainer');

                if (!d1.addItems) {
                    // Clear the current list of questions
                    questionsContainer.empty();
                }
                c(items, 'items')
                items.forEach((item, index) => {
                    // c(item, 'item obj', 1);
                    var stats;
                    if (item.childItemCount) {
                        stats = `<div class='question-number'>${item.childItemCount}</div>`;
                    }

                    var activeClass = '';
                    if (item.is_active == '0') {
                        activeClass = 'inactive-item';
                    }

                    var is_featured = '';
                    if (item.is_featured == '1') {
                        is_featured = `<div title='Promoted Content' class="promoted-flag"><i class="fas fa-star"></i> </div>`;
                    }


                    const questionElement = $(`
        <div class='question ${activeClass}' id='question-${pref}-${item.itemid}'>
            ${stats}
            <div class='question-text' 
            id='${pref}-${item.itemid}' 
            data-iid='${item.itemid}'>
                <div>${item.textfordiv}</div>
                 ${is_featured}
            </div>
        </div>
       
        `);
                    questionElement.on('click', function(e) {
                        handleItemClick(item.textfordiv, item.id, pref, leftSection, {
                            parentID: `question-${pref}-${item.itemid}`
                        });
                    });
                    questionsContainer.append(questionElement);
                    if (d1.addItems) {
                        handleItemClick(item.textfordiv, item.id, pref, leftSection, {
                            parentID: `question-${pref}-${item.itemid}`
                        });
                    }
                });
            }

            // Function to handle question click
            function handleItemClick(item, iid, pref, section, d1 = {}) {
                console.log('Item clicked:', item);

                var r = {
                    rExtended: 'clickeditem'
                };
                if (pref == 'baanser') {
                    r.w = pref;
                    r.iid = iid;
                    r.parentItem = d1.parentID;
                    ClickedItem(r, section);
                }
                if (pref == 'baquest') {
                    r.w = pref;
                    r.iid = iid;
                    r.parentItem = d1.parentID;
                    ClickedItem(r, section);
                }
                //displayUserMessage(question);
                //sendMessageFromSuggestion(question, qid);
            }

            function updateDisplayContent(content, section) {
                // The section (display container) is passed directly as an argument
                const displayContainer = section;

                // Check if the content is base64-encoded
                let contentHtml = content.bws.base_64;
                if (isBase64(contentHtml)) {
                    contentHtml = atob(contentHtml); // Decode the base64 content to HTML

                }

                displayContainer.empty(); // Clear the current content

                c(content, 'content'); // Assuming c() is a logging function

                // Determine itemKey based on whether it's for a question ${content.bws[itemKey]}
                // Update db and below so it's always bwsId in 
                let itemKey = content.bws.forAQuestion ? 'bwsId' : 'baseAnswerIDWeb';
                c('itemKey', itemKey, content);


                var buttonKind = {};
                if (content.bws.is_active == '1') {
                    buttonKind.a = 'inactive';
                    buttonKind.b = 'Inactive';
                } else {
                    buttonKind.a = 'active';
                    buttonKind.b = 'Active';
                }

                // Render content with stats if present
                if (content.stats) {
                    c("stats");
                    // 

                    displayContainer.html(`
           <div class='stats-x'>Times Asked: ${content.stats.itemCount} | Last Edited: ${content.bws.last_updated_nice}  </div>
          
            ${contentHtml}
            <div class='button-row'>
                <button class='btn btn-primary section-edit-btn' 
                        data-id='${content.bws.bwsId}' 
                        data-kind='${itemKey}'
                        data-htm='${content.bws.base_64}'
                        data-parent='${content.parentID}'
                        data-isActive='${content.bws.is_active}'
                        data-kindextended='${itemKey}'
                        data-section='.displayContainer-${itemKey}-${content.bws.bwsId}'>Edit</button>
                        

                        <button class='btn btn-info section-${buttonKind.a}-btn' 
                        data-id='${content.bws.bwsId}' 
                        data-kind='${itemKey}'
                        data-parent='${content.parentID}'
                        data-kindextended='${itemKey}'
                        data-section='.displayContainer-${itemKey}-${content.bws.bwsId}'>Set ${buttonKind.b}</button>


                        <button class='btn btn-success promote' 
                        data-id='${content.bws.bwsId}' 
                        data-kind='${itemKey}'
                        data-parent='${content.parentID}'
                        data-kindextended='${itemKey}'
                        data-section='.displayContainer-${itemKey}-${content.bws.bwsId}'>Promote </button>
            </div>
        `);
                    $(displayContainer).data('stats', content.stats);
                } else {
                    c("no stats");
                    displayContainer.html(contentHtml);
                }

                // Assign a unique class to the display container for easier retrieval later
                displayContainer.addClass(`displayContainer-${itemKey}-${content.bws.bwsId}`);
            }



            $(document).ready(function() {
                // Delegated event handler using $(document).on for dynamically added elements
                $(document).on('click', '.section-edit-btn', function() {
                    // Get the data-id, data-kind, and data-section attributes
                    var sectionId = $(this).data('id');
                    var sectionKind = $(this).data('kind');

                    var sectionSelector = $(this).data('section');
                    var base64htm = $(this).data('htm');
                    var base64htm2 = atob(base64htm);
                    var parentElement = $(this).data('parent');
                    var isActive = $(this).data('isActive');
                    var itemKey = $(this).data('kindextended');

                    c("itemKey", itemKey, this)
                    // Use the sectionSelector to find the section
                    var section = $(sectionSelector);

                    // Call the function to make the section editable
                    makeSectionEditable(section, sectionId, sectionKind, base64htm2, parentElement, isActive, itemKey);
                });


                // Delegated event handler using $(document).on for dynamically added elements
                $(document).on('click', '.section-inactive-btn', function() {
                    // Get the data-id, data-kind, and data-section attributes
                    var sectionId = $(this).data('id');
                    var sectionKind = $(this).data('kind');
                    var sectionSelector = $(this).data('section');
                    var base64htm = $(this).data('htm');
                    var parentElement = $(this).data('parent');
                    var itemKey = $(this).data('kindextended');

                    c(sectionId, sectionKind, sectionSelector, parentElement)





                    $(`#${parentElement}`).addClass("inactive-item");
                    //$(this).parent().parent().addClass("inactive-item");;


                    $(this).html(`Set Active`).removeClass("section-inactive-btn").addClass("section-active-btn");


                    // Use the sectionSelector to find the section
                    var section = $(sectionSelector);





                    // Set to inactive
                    // Send to database for saving 
                    var dataSend = {
                        sectionKind: sectionKind,
                        sectionId: sectionId,
                        rExtended: 'deactiveItem'
                    }
                    ClickedItem(dataSend);





                    return;



                });


                $(document).on('click', '.promote', function() {
                    // Get the data-id, data-kind, and data-section attributes


                    showErrorMessage("This feature is currently inactive. The intent behind it is that when a user clicks, the selected content will be displayed in the home screen carousel, which appears when the page first loads. ")

                    return;



                });

                // Delegated event handler using $(document).on for dynamically added elements
                $(document).on('click', '.section-active-btn', function() {
                    // Get the data-id, data-kind, and data-section attributes
                    var sectionId = $(this).data('id');
                    var sectionKind = $(this).data('kind');
                    var sectionSelector = $(this).data('section');
                    var base64htm = $(this).data('htm');
                    var parentElement = $(this).data('parent');

                    c(sectionId, sectionKind, sectionSelector, parentElement)





                    $(`#${parentElement}`).removeClass("inactive-item");
                    //$(this).parent().parent().addClass("inactive-item");;


                    $(this).html(`Set Inactive`).addClass("section-inactive-btn").removeClass("section-active-btn");







                    // Set to inactive
                    // Send to database for saving 
                    var dataSend = {
                        sectionKind: sectionKind,
                        sectionId: sectionId,
                        rExtended: 'activeItem'
                    }
                    ClickedItem(dataSend);





                    return;



                });






                // Function to make the section editable
                function makeSectionEditable(section, id, kind, content, parentElement, isActive, itemKey) {
                    // Get the current content
                    c("make section editable", section, id, kind + "kind", itemKey, 'jeremel')
                    var currentContent = section.html();
                    var secid = $(section)[0].id + "-editor";
                    //c('id of sect', secid)

                    // Create a textarea with Bootstrap form-control class
                    var textarea = $(`<textarea id="${secid}" style="height:80%" class="form-control mb-3" rows="5"></textarea>`).val(content);

                    if ($(section)[0].id == 'baquest-left') {
                        // section = $("#baquest-left");
                    }


                    section.empty().append(textarea);


                    if ($(section)[0].id == 'baquest-left') {

                        tinymce.init({
                            selector: "#" + secid,
                            height: 500,
                            plugins: 'lists link image table code',
                            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image',
                            branding: false, // 
                            promotion: false,
                            license_key: 'gpl',
                        });


                    }



                    // Create a save button with Bootstrap button styles
                    var saveButton = $('<button class="btn btn-success mt-2">Save</button>').on('click', function() {

                        // ON CLICK 
                        // ON CLICK 
                        // ON CLICK 
                        var updatedContent = textarea.val();
                        if ($(section)[0].id == 'baquest-left') {
                            updatedContent = tinymce.get(secid).getContent();
                        }

                        // Content for new edit button
                        var b64c = btoa(updatedContent);

                        c('data', $(section).data('stats'));
                        var stats = $(section).data('stats');

                        // Update the section with the new content
                        section.html(`
                    <div class='stats-x'>Times Asked: ${stats.itemCount} | Last Edited: Now </div>
                    ${updatedContent}
                `);


                        // Send to database for saving 
                        var dataSend = {
                            content: b64c,
                            sectionKind: kind,
                            sectionId: id,
                            rExtended: 'updateItem'
                        }
                        ClickedItem(dataSend);

                        var buttonKind = {};
                        if (isActive == '1') {
                            buttonKind.a = 'inactive';
                            buttonKind.b = 'Inactive';
                        } else {
                            buttonKind.a = 'active';
                            buttonKind.b = 'Active';
                        }

                        // Restore the edit button with Bootstrap button styles
                        section.append(`<div class='button-row'><button class='btn btn-primary section-edit-btn' 
                data-id='${id}' 
                data-kind='${kind}' 
                data-section='.displayContainer-${itemKey}-${id}'
                data-parent='${parentElement}'
                data-isActive='${isActive}'
                data-kindextended='${itemKey}'
                data-htm="${b64c}"
                >Edit</button>
                
                <button class='btn btn-info section-${buttonKind.a}-btn' 
                        data-id='${id}' 
                        data-kind='${kind}'
                        data-kindextended='${itemKey}'
                        data-parent='${parentElement}'
                        data-section='.displayContainer-${itemKey}-${id}'>Set ${buttonKind.b}</button>

                </div>
                `);
                    });

                    // Clear the section content, remove the edit button, and add the textarea and save button
                    section.append(saveButton);
                    //section.empty().append(textarea).append(saveButton);


                }

            });





            $(document).ready(function() {
                // Initial call to display default questions on the page
                var answersItems = <?= $answers  ?>;
                var questionsItems = <?= $baQuestions  ?>;
                displayItemsSec(answersItems, $("#baanswer-container"), "baanser", $("#baanswer-left"));
                displayItemsSec(questionsItems, $("#baquest-container"), "baquest", $("#baquest-left"));
                //GoThroughItems();




            });

            function ClickedItem(d, s) {

                if (!d) d = {
                    "r": "dash",
                    'admin': 1
                };
                else {
                    d.r = "dash";
                    d.admin = 1;
                }

                $.ajax({
                    // url: '../chatai/chat-ai-backend.php', // Replace with your actual backend endpoint
                    url: '<?= $backend ?>', // Replace with your actual backend endpoint
                    method: 'POST',
                    data: d,
                    success: function(responseRaw) {

                        c("processClickedItem completed", d);


                        // Split the string into lines
                        const lines = responseRaw.trim().split('\n');

                        // Retrieve the last line
                        const lastLine = lines[lines.length - 1];

                        // Parse JSON from the last line without try/catch
                        const response = JSON.parse(lastLine);
                        c(response, 'ClickedItem');


                        if (d.rExtended == 'updateItem' || d.rExtended == 'activeItem' || d.rExtended == 'deactiveItem') {
                            c("update item reply");
                            return;

                        } else {
                            response.parentID = d.parentItem;
                            updateDisplayContent(response, s);
                        }




                    },
                    error: function() {


                    }
                });
            }
        </script>









        <?




        $Instructions = array(

            array(
                "label" => "General Instructions",
                "request_name" => "gen_temp",
                "help_text" => "When writing this prompt, focus on setting clear expectations for how the AI should respond to users. Be specific about the tone (e.g., patient, factual) and behaviors you want the AI to exhibit (e.g., avoid speculation, use verified data). Ensure the instructions align with the AI's role in reducing anxiety and providing reliable, direct communication.",
                'data' => $a->CRUDInstructions(array('request_name' => 'gen_temp'))
            ),


            array(
                "label" => "User Chat Instrctions",
                "request_name" => "general_chat",
                "help_text" => " When writing this prompt, provide clear guidelines for how the AI should handle user chat submissions, as this will serve as the main prompt for all user chat interactions. Specify how the AI should respond to both questions and comments, and outline the appropriate tone and style for these interactions. Consider whether the AI should prioritize clarity, helpfulness, or brevity, and adjust the instructions accordingly to ensure consistency across all user submissions.",
                'data' => $a->CRUDInstructions(array('request_name' => 'general_chat'))
            ),


            array(
                "label" => "Generating Questions",
                "request_name" => "gen_questions",
                "help_text" => "When writing this prompt, instruct the AI to generate questions that are specific, focused, and avoid duplication. Make sure the AI asks clear, single-topic questions that add value without repeating existing ones. Encourage specificity by focusing on particular issues users might face, and ensure the questions are written from the user's perspective, addressing real-world concerns or challenges. To improve the AI's understanding, consider providing examples of good versus bad replies to specific questions to help guide the quality of responses.",
                'data' => $a->CRUDInstructions(array('request_name' => 'gen_questions'))

            ),
            array(
                "label" => "Next Question Suggestion Logic",
                "request_name" => "next_question",
                "help_text" => "When writing this prompt, guide the AI to suggest follow-up questions that students or parents might ask next. The focus should be on questions that help alleviate anxiety, especially in scenarios where students and parents may be worried or fearing the worst. Ensure that the AI uses existing questions from the suggestion database when possible. <b>Keep in mind this is perhaps the most influential feature of influencing end user behavior.</b>",
                'data' => $a->CRUDInstructions(array('request_name' => 'next_question'))

            ),



        );

        ?>

        <div class="section-div">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="sec" id="prompt-sec">
                        <h3>Site Prompts</h3>
                        <p class="instructions">Below are prompts that help power the AI engines that your websites and web applications depend on. Each prompt is specifically designed to give the AI engine context of how to best supply answers to your users. Please read each prompt carefully to determine how you want your WebAI to respond.</p>




                        <?

                        foreach ($Instructions as $b => $a) {

                            $valueWithBreaks = str_replace("\n", "<br>", $a['data']['value']);
                            $iid =  $a['data']['id'];

                            echo "
            <div class=\"prompt-area\" data-id=\"$iid\">
            <h4>{$a['label']}</h4>
            <p class=\"instructions-for-small\">{$a['help_text']}</p>

          <p class=\"prompt-text\">$valueWithBreaks</p>
          <textarea class=\"form-control prompt-edit\" style=\"display:none;\"></textarea>
          <button class=\"btn btn-primary btn-sm edit-btn\" data-id=\"$iid\">Edit</button>
          <button class=\"btn btn-success btn-sm save-btn\" data-id=\"$iid\" style=\"display:none;\">Save</button>
          <hr>
        </div>
            ";
                        }

                        ?>


                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                // Function to toggle edit mode
                $('#prompt-sec.sec .edit-btn').click(function() {
                    var parent = $(this).closest('.prompt-area');
                    var text = parent.find('.prompt-text').html().replace(/<br\s*[\/]?>/gi, "\n");

                    // Hide the text and show the textarea
                    parent.find('.prompt-text').hide();
                    parent.find('.prompt-edit').val(text).show();

                    // Toggle buttons
                    $(this).hide();
                    parent.find('.save-btn').show();
                });

                // Function to save the prompt
                $('#prompt-sec.sec .save-btn').click(function() {
                    var parent = $(this).closest('.prompt-area');
                    var newText = parent.find('.prompt-edit').val().replace(/\n/g, "<br>");
                    var rawText = parent.find('.prompt-edit').val();

                    // Hide the textarea and show the updated text
                    parent.find('.prompt-edit').hide();
                    parent.find('.prompt-text').html(newText).show();

                    // Toggle buttons
                    $(this).hide();
                    parent.find('.edit-btn').show();


                    var d = {
                        "admin": 1,
                        r: "update_instruction",
                        data: {
                            iid: $(this).data('id'),
                            value: rawText
                        }
                    };

                    c("data in save instructions", d);

                    $.ajax({
                        url: '<?= $backend ?>',
                        method: 'POST',
                        data: d,
                        success: function(responseRaw) {

                            c("process #prompt-sec.sec button completed", d);


                            // Split the string into lines
                            const lines = responseRaw.trim().split('\n');



                            // Retrieve the last line
                            const lastLine = lines[lines.length - 1];

                            // Parse JSON from the last line without try/catch
                            const response = JSON.parse(lastLine);
                            c(response, 'respon dataSendBtn');

                            if (response.result !== 'success') {
                                c("no success in #prompt-sec.sec button");
                            }


                            return;




                        },
                        error: function() {
                            c("error in #prompt-sec.sec button");

                        }
                    });


                });
            });
        </script>


        </head>

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
                        $(".ui-dialog").addClass("modal-content");
                    }
                });
            }
        </script>




    </main>







    <!-- Body-3: Footer -->
    <footer>
        <!-- Footer content will be added here -->




    </footer>


    <!-- Body-4: Additional JavaScript -->
    <script>
        // Additional JavaScript functionality will be added here
    </script>
</body>

</html>