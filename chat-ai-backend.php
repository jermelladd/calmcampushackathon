<?php


// Include the necessary PHP files
include_once("functions.php");
//l(5);
session_start();


if (!$con2) $con2 = dbConnect("CalmCampusv1");



//https://chatgpt.com/c/a57bab4f-ccaa-4575-9f74-ba80e8aecb17

$b = new ChatAI();
$b->Flow();


class ChatAI
{
    public $con;
    public $current_time;
    #public $table_name; 


    public $openAIKey = 'xx';
    public $baseEnd = 'https://api.openai.com/v1/';

    public $assistant_id = '';
    public $ass = array(
        array("name" => "x", "id" => "asst_111"), // 0
        array("name" => "x", "id" => "asst_2222"), // 1
        array("name" => "x", "id" => "asst_3333"), // 2
        array("name" => "x", "id" => "asst_4444"), // 3
        array("name" => "Check for similar questions", "id" => "asst_8tuf9TYwFv3BBojeAuEsC5Id"), // 4
        array("name" => "CalmCampus AI", "id" => "asst_Qm5oejJalIUfbnHppzdgtdcC"), // 5 

    );
    #public $ar_1 = array();


    public $db_all = array(
        // Threads, Message, Runs
        array("AiThreads", "AiMessages", "AiRuns")

    );
    public $db = array();

    public $debug = 0;

    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    public function __construct()
    {


        //parent::__construct();

        global $con2;
        $this->con = $con2;


        $this->assistant_id = $this->ass[5]['id'];



        $this->db = $this->db_all[0];
    }
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //
    public function Flow()
    {



        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $content = file_get_contents('php://input');
            $content = json_decode($content, true);
            //p($content);

            p($_SESSION);

            if ($content['r'] == 'chatmsg' && $content['status'] !== 'in_progress') {
                // p($_SESSION); return;
                $this->ChatMsgFlow($content);
                return;
            }

            if ($content['status'] == 'in_progress') {
                $this->ProgressFlow($content);
                return;
            }
        }
    }
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //
    public function OpenCURL($endpoint, $param = array(), $extra = array())
    {



        $curl = curl_init();
        $apiKey = $this->openAIKey;
        $url = $this->baseEnd . $endpoint;

        if (isset($extra['auth'])) {
            $apiKey = $extra['auth'];
        }

        $headers = array(
            "Authorization: Bearer $apiKey",
            'OpenAI-Beta: assistants=v2',
        );

        if ($endpoint == 'chat/completions') {
            unset($headers[1]);
        }

        if ($extra['headers']) {
            $headers[] = $extra['headers'];
        }

        if (strtolower($extra['method']) == 'get') {
            $url .= '?' . http_build_query($param);
        } else { // Default to POST if not specified or specified as POST
            curl_setopt($curl, CURLOPT_POST, true);

            // Check if 'json' is true in $extra. If so, assume $param is already a JSON string.
            if (isset($extra['json']) && $extra['json'] === true) {
                // $param is already a JSON string, so use it directly.
                curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
            } else {
                // If not a JSON request, use http_build_query.\
                //l(4); echo ($param) . "echo "; l();
                curl_setopt($curl, CURLOPT_POSTFIELDS, ($param));
            }
            // Ensure the content type is set correctly for JSON requests.
            $headers[] = 'Content-Type: application/json';
        }


        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $err = curl_error($curl);



        // Prepare to log headers and response
        if ($response) {
            $loggedResponse = "{$extra['method']} REQUEST:
        $param
        
        -----------------------------------------------
        RESPONSE:
        $response
        ";
        } else {
            $loggedResponse = $err;
        }
        $loggedHeaders = json_encode($headers);
        //$loggedResponse = $response ? $response : $err; // Log error if curl_exec fails


        // Log the request and response in StripeAPIReply
        $apiReplyData = [
            'endpoint' => $endpoint,
            'response' => $loggedResponse,
            'headers' => $loggedHeaders
        ];
        basicInsert('ApiReplies', array($apiReplyData), $this->PR, 0, null, $this->con);


        curl_close($curl);

        $results = array();
        if ($err) {
            $results['error'] = $err;
        } else {
            $results = json_decode($response, true); // Assuming you want the result as an array
        }

        return $results;
    }
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //
    // Below function is to be used for OpenAI Assistant
    // First checks to see if there is an active thread in the database given $post paramters 
    // If no thread, it creates a thread using $this->OpenCURL();
    // If there is a thread it pulls the record from the db
    // Then it adds a message to the thread using $this->OpenCURL();
    // Then it starts the run using $this->OpenCURL();
    // Once completed it goes to $this->CheckResults($runsObj); to check status
    // $this->modelOR can override default model
    public function ChatMsgFlow($post = array())
    {


        $message = $post['message'];
        $threadEndpoint = "threads";

        if ($this->debug) {
            p("Start of ChatMsgFlow", 'Debugging');
        }

        if (!$post['tid']) {
            // Step 1: Create a new thread

            if ($this->debug) {
                p("Step 1 Start", 'Debugging');
            }

            $createThreadParams = array(); // No arguments
            $createThreadExtra = array('method' => 'POST'); // Extra parameters including the HTTP method

            $createThreadResponse = $this->OpenCURL($threadEndpoint, $createThreadParams, $createThreadExtra);
            if (!isset($createThreadResponse['id'])) {
                // Handle error: No thread ID in response
                return ['error' => 'Failed to create thread'];
            }

            //$_SESSION['thread_id'] = $createThreadResponse['id'];
            $thread_id = $createThreadResponse['id'];

            //$_SESSION['activeRuns'] = array();
            $activeRuns = array();

            // Insert into AiThreads
            $threadData = array(
                array(
                    'thread_id' => $createThreadResponse['id'],
                    'session_id' => session_id()
                )
            );
            basicInsert($this->db[0], $threadData, 1, 0, null, $this->con);

            // Retrieve the newly created thread object
            $q = "SELECT * FROM {$this->db[0]} WHERE thread_id = '{$createThreadResponse['id']}' LIMIT 1";
            $threadObj = basicQuery($this->con, "", $q, 10, 2);

            if ($this->debug) {

                p('thread_id =' . $thread_id, 'Debugging');
                p('$threadObj =' . $threadObj, 'Debugging');
            }

            $threadId = $threadObj['thread_id'];
        } else { // 

            $q = "SELECT * FROM {$this->db[0]} WHERE id = '{$post['tid']}' LIMIT 1";
            $threadObj = basicQuery($this->con, "", $q, 10, 2);
            $threadId = $threadObj['thread_id'];
            $thread_id = $threadObj['thread_id'];
        }




        if ($this->debug) {
            p($createThreadResponse, '$createThreadResponse');
            p($threadObj, '$threadObj');
        }

        // Step 2: Add the message to the thread

        if ($this->debug) {
            p("Step 2 Start", 'Debugging');
        }

        $messagesEndpoint = "threads/" . $thread_id . "/messages";
        $messageData = json_encode(array(
            "role" => "user",
            "content" => $message
        ));
        $addMessageExtra = array('method' => 'POST', 'headers' => 'Content-Type: application/json');

        $addMsgReply = $this->OpenCURL($messagesEndpoint, $messageData, $addMessageExtra);
        if (!isset($addMsgReply['id'])) {
            // Handle error: No message ID in response
            $error = ['error' => 'Failed to add message'];
            echo json_encode($error);



            return;
        }

        //$_SESSION['activeRuns']['messages'][] = array("source" => "User", "message" => $message, id => $addMsgReply[id]);
        $activeRuns['activeRuns']['messages'][] = array(
            "source" => "User",
            "message" => $message,
            'id' => $addMsgReply['id']
        );


        // Insert into AiMessages
        $messageDataArray = array(
            array(
                'thread_id' => $threadObj['id'],
                'message_id' => $addMsgReply['id'],
                'content' => $message,
                'role' => 'user'
            )
        );
        basicInsert($this->db[1], $messageDataArray, 1, 0, null, $this->con);

        // Retrieve the newly created message object
        $q = "SELECT * FROM {$this->db[1]} WHERE message_id = '{$addMsgReply['id']}' LIMIT 1";
        $msgObj = basicQuery($this->con, "", $q, 0, 2);

        if ($this->debug) {
            p($activeRuns['activeRuns']['messages'], 'Debugging $activeRuns[activeRuns][messages]');
            p($msgObj, 'Debugging $msgObj');
        }




        // Step 3: Start the thread run
        if ($this->debug) {
            p("Step 3 Start", 'Debugging');
        }

        $runEndpoint = "threads/" . $thread_id . "/runs";
        $runData = array('assistant_id' => $this->assistant_id);
        if ($this->modelOR) {
            $runData['model'] = $this->modelOR;
        }
        $runData  = JE($runData, 0, 1);
        $runExtra = array('method' => 'POST', 'headers' => 'Content-Type: application/json');

        $runResponse = $this->OpenCURL($runEndpoint, $runData, $runExtra);

        if (!isset($runResponse['id'])) {
            // Handle error: No run ID in response
            $error =  ['error' => 'Failed to start run'];

            if ($this->ro) {
                return $error;
            }

            echo json_encode($error);
            return;
        }

        $runId = $runResponse['id'];


        $activeRuns['id'] = $runId;
        $activeRuns['activeRuns']['status'] =  $runResponse['status'];


        // Insert into AiRuns
        $runDataArray = array(
            array(
                'assistant_id' => $this->assistant_id,
                'run_id' => $runResponse['id'],
                'thread_id' => $threadObj['thread_id'], //
                'status' => $runResponse['status'],
                'model' => isset($runResponse['model']) ? $runResponse['model'] : null,
                'instructions' => isset($runResponse['instructions']) ? json_encode($runResponse['instructions']) : null,
                'usage_info' => isset($runResponse['usage_info']) ? json_encode($runResponse['usage_info']) : null
            )
        );
        basicInsert($this->db[2], $runDataArray, 1, 0, null, $this->con);

        // Retrieve the newly created run object
        $q = "SELECT * FROM {$this->db[2]} WHERE run_id = '{$runResponse['id']}' LIMIT 1";
        $runsObj = basicQuery($this->con, "", $q, 10, 2);

        if ($this->debug) {
            p($activeRuns['activeRuns'], 'Debugging $_SESSION[activeRuns]');
            p($runsObj, 'Debugging $runsObj');
        }

        // Wait 2 seconds before checking results
        //sleep(2);

        // Optionally check results and handle accordingly
        $CheckResults = $this->CheckResults($runsObj);


        if ($this->qrec) {
            return $runsObj;
        }

        if ($CheckResults) {

            if ($this->ro) {
                return $CheckResults;
            }

            echo json_encode($CheckResults);
            return;
        }
    }
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    // Check results takes a $runArray which contains the run information needed to look up the status of an OpenAI Run
    // If status equals pending or queud then function returns the status and ends
    // If status is completed in success or error the information is parsed and returned in the format of an array
    public function CheckResults($runArray)
    {




        // Return the content of the new message along with other relivant information such as the status of the run 


        if ($this->debug) {
            p("CheckResults Start", 'Debugging');
            p($runArray, "runArray");

            $this->tt();
        }

        // Assuming $runArray contains information about the current active run including its ID and status
        if (empty($runArray) || !isset($runArray['id'])) {
            $error = ['error' => 'No active run information provided'];
            echo json_encode($error);
            return;
        }

        $runId = $runArray['run_id'];
        //$threadId = $_SESSION['thread_id']; // Assuming thread ID is still managed through session
        $threadId =  $runArray['thread_id'];

        $q = "select * from {$this->db[0]} where thread_id = '$threadId' limit 1";
        $threadObj = basicQuery($this->con, "", $q, 10, 2);
        if ($this->debug) {
            p($threadObj, 'threadObj');
        }



        // Check status of run
        $statusCheckEndpoint = "threads/$threadId/runs/$runId";
        $statusCheckResponse = $this->OpenCURL($statusCheckEndpoint, array(), array('method' => 'GET'));

        if (!isset($statusCheckResponse['status'])) {
            $error = ['error' => 'Failed to check run status'];
            echo json_encode($error);
            return;
        }

        // Check if the run is still not completed
        if ($statusCheckResponse['status'] !== 'completed') {
            $response = ['status' => 'in_progress', 'message' => 'Run is still in progress'];
            // Stand by for further logic instructions
            $final = array(
                'messages' => array(),
                'status' => $statusCheckResponse['status'],
                'tid' => $threadObj['id'],
                'rid' => $runArray['id'],
            );

            if ($this->debug) {
                p('if ($statusCheckResponse[status] !== completed) CHECK RUN', 'Debugging');
            }

            if ($statusCheckResponse['status'] == 'failed') {
                $final['r'] = $statusCheckResponse;
            }

            return $final;
        }







        // Assuming run is completed, fetch new messages
        $messagesEndpoint = "threads/$threadId/messages";
        $newMessagesResponse = $this->OpenCURL($messagesEndpoint, array(), array('method' => 'GET'));







        if (!isset($newMessagesResponse) || empty($newMessagesResponse)) {
            $error = ['error' => 'Failed to fetch new messages or no new messages'];
            echo json_encode($error);
            return;
        }


        if ($this->debug) {
            p($newMessagesResponse, 'Debugging $newMessagesResponse GET');
        }




        if (!isset($newMessagesResponse['data']) || empty($newMessagesResponse['data'])) {
            $error = ['error' => 'Failed to fetch new messages or no new messages'];
            echo json_encode($error);
            return;
        }

        // Initialize the session storage for messages if not already initialized


        // 
        $newMsgForFront = array();

        $q = "SELECT message_id FROM {$this->db[1]} WHERE thread_id = '{$threadObj['id']}' AND thread_id IS NOT NULL";
        $search_msg_array = basicQuery($this->con, "", $q, 0, 2);
        if ($this->debug) {
            p($search_msg_array, 'Debugging $search_msg_array');
        }

        foreach ($newMessagesResponse['data'] as $newMsg) {


            $isNew = true; // Assume the message is new

            // Check if the new message ID is already in the session messages
            // foreach ($_SESSION['activeRuns']['messages'] as $sessionMsg) {
            foreach ($search_msg_array as $sessionMsg) {
                if ($sessionMsg['message_id'] == $newMsg['id']) {
                    $isNew = false; // Message is already in session, not new
                    break;
                }
            }

            // If the message is new, add it to the session
            if ($isNew && $newMsg['content'][0]['text']['value']) {
                $newMsgArr = array(
                    "source" => "GPT",
                    "message" => $newMsg['content'][0]['text']['value'], // Assuming 'message' is the key for message content
                    "id" => $newMsg['id'] // Assuming 'id' is the key for the message ID
                );
                //$_SESSION['activeRuns']['messages'][] = $newMsgArr;
                $newMsgForFront[] = $newMsgArr;

                // Insert into db
                // Insert into AiMessages
                $messageDataArray = array(
                    array(
                        'thread_id' => $threadObj['id'],
                        'message_id' => $newMsgArr['id'],
                        'content' => $newMsgArr['message'],
                        'role' => 'webai'
                    )
                );
                basicInsert($this->db[1], $messageDataArray, 1, 0, null, $this->con);
            }
        }



        // Stand by for further logic instructions
        $final = array(
            'messages' => $newMsgForFront,
            'status' => $statusCheckResponse['status'],
            'tid' => $threadObj['id'],
            'rid' => $runArray['id']
        );


        if ($this->debug) {
            p("CheckResults End", 'Debugging');
        }


        return $final;
    }

    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //
    public function ProgressFlow($post = array())
    {

        if ($this->debug) {
            p("ProgressFlow Start", 'Debugging');
        }



        $CheckResults = $this->CheckResults($post);



        if ($CheckResults) {

            if ($this->debug) {
                p('if($CheckResults) {', 'Debugging');
            }

            if ($this->ro) {
                return $CheckResults;
            }

            echo json_encode($CheckResults);
            return;
        }
    }
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //
    public function tt()
    {
        $trace = debug_backtrace();
        $line = $trace[0]['line'];
        $function = isset($trace[1]['function']) ? $trace[1]['function'] : 'global scope';
        $time = date("Y-m-d H:i:s");
        echo "Current Time: $time, Invoked at Line: $line in Function: $function";
    }
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    public function AddMsgToThread($data = array())
    {


        p($data, '$data in AddMsgToThread');


        $message = $data['message'];
        $threadEndpoint = "threads";
        $threadId = $data['tid'];

        if (!$threadId) {
            // Step 1: Create a new thread

            if ($this->debug) {
                p("Step 1 Start", 'Debugging');
            }

            $createThreadParams = array(); // No arguments
            $createThreadExtra = array('method' => 'POST'); // Extra parameters including the HTTP method

            $createThreadResponse = $this->OpenCURL($threadEndpoint, $createThreadParams, $createThreadExtra);
            if (!isset($createThreadResponse['id'])) {
                // Handle error: No thread ID in response
                return ['error' => 'Failed to create thread'];
            }

            //$_SESSION['thread_id'] = $createThreadResponse['id'];
            $thread_id = $createThreadResponse['id'];

            //$_SESSION['activeRuns'] = array();
            $activeRuns = array();

            // Insert into AiThreads
            $threadData = array(
                array(
                    'thread_id' => $createThreadResponse['id'],
                    'session_id' => session_id()
                )
            );
            basicInsert($this->db[0], $threadData, 1, 0, null, $this->con);

            // Retrieve the newly created thread object
            $q = "SELECT * FROM {$this->db[0]} WHERE thread_id = '{$createThreadResponse['id']}' LIMIT 1";
            $threadObj = basicQuery($this->con, "", $q, 10, 2);

            if ($this->debug) {
                // p('$_SESSION[thread_id] ='. $_SESSION['thread_id'] , 'Debugging');
                // p('$threadObj[thread_id] ='. $threadObj['thread_id'] , 'Debugging');
                p('thread_id =' . $thread_id, 'Debugging');
                p('$threadObj =' . $threadObj, 'Debugging');
            }

            $threadId = $threadObj['thread_id'];
        } else { // 
            //$threadId = $_SESSION['thread_id'];
            // Retrieve the newly created thread object
            //$q = "SELECT * FROM {$this->db[0]} WHERE thread_id = '$threadId' LIMIT 1";
            $q = "SELECT * FROM {$this->db[0]} WHERE thread_id = '{$data['tid']}' LIMIT 1";
            $threadObj = basicQuery($this->con, "", $q, 10, 2);
            $threadId = $threadObj['thread_id'];
            $thread_id = $threadObj['thread_id'];
        }




        if ($this->debug) {
            p($createThreadResponse, '$createThreadResponse');
            p($threadObj, '$threadObj');
        }












        // Step 2: Add the message to the thread

        if ($this->debug) {
            p("Step 2 Start", 'Debugging');
        }

        $messagesEndpoint = "threads/" . $threadId . "/messages";
        $messageData = json_encode(array(
            "role" => "assistant",
            "content" => $message
        ));
        $addMessageExtra = array('method' => 'POST', 'headers' => 'Content-Type: application/json');

        $addMsgReply = $this->OpenCURL($messagesEndpoint, $messageData, $addMessageExtra);
        if (!isset($addMsgReply['id'])) {
            // Handle error: No message ID in response
            $error = ['error' => 'Failed to add message'];
            echo json_encode($error);



            return;
        }

        //$_SESSION['activeRuns']['messages'][] = array("source" => "User", "message" => $message, id => $addMsgReply[id]);
        $activeRuns['activeRuns']['messages'][] = array(
            "source" => "User",
            "message" => $message,
            'id' => $addMsgReply['id']
        );


        // Insert into AiMessages
        $messageDataArray = array(
            array(
                'thread_id' => $threadObj['id'],
                'message_id' => $addMsgReply['id'],
                'content' => $message,
                'role' => 'webai_auto'
            )
        );
        basicInsert($this->db[1], $messageDataArray, 1, 0, null, $this->con);

        // Retrieve the newly created message object
        $q = "SELECT * FROM {$this->db[1]} WHERE message_id = '{$addMsgReply['id']}' LIMIT 1";
        $msgObj = basicQuery($this->con, "", $q, 0, 2);

        if ($this->debug) {
            p($activeRuns['activeRuns']['messages'], 'Debugging $activeRuns[activeRuns][messages]');
            p($msgObj, 'Debugging $msgObj');
        }


        return $threadObj['id'];
    }
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //
    // Simple function that does NOT use OpenAI assistant and used ChatCompletions
    // The information of $data is passed as a parameter. This contains text of the prompt.
    // Since ChatCompletions endpoint doesnt finish running until the response is done, only one CURL request is needed here. 
    // GPT model can be overriden using $this->
    // Model always uses gpt-4o-mini
    public function ChatCompletions($post = array(), $data = array())
    {


        $message = $post['message'];
        $threadEndpoint = "threads";


        if ($this->debug) {
            p("Start of ChatCompletions", 'Debugging');
        }





        if ($this->debug) {
            p("Messages Start", 'Debugging');
            p($data, 'data');
        }


        // "temperature":0.2,"max_tokens":256,"top_p":1,"frequency_penalty":0,"presence_penalty":0,"seed":null,"model":"gpt-4o-mini"}

        $messageData = array(
            array(
                "role" => "system",
                "content" => array()
            ),
            array(
                "role" => "user",
                "content" => array(array('type' => 'text', 'text' => $message))
            )
        );


        if ($this->debug) {
            p($messageData, 'MessageArray');
        }


        $msgfullbody = array(
            "messages" => $messageData,
            "temperature" => 0.2,
            "max_tokens" => $data['max_tokens'] ? $data['max_tokens']  : 256,
            "top_p" => 1,
            "frequency_penalty" => 0,
            "presence_penalty" => 0,
            "seed" => null,
            "model" => "gpt-4o-mini",

        );


        $addMessageExtra = array('method' => 'POST', 'headers' => 'Content-Type: application/json');


        $addMsgReply = $this->OpenCURL("chat/completions", json_encode($msgfullbody), $addMessageExtra);
        if (!isset($addMsgReply['id'])) {
            // Handle error: No message ID in response
            $error = ['error' => 'Failed to add message'];
            echo json_encode($error);



            return;
        }


        if ($this->debug) {
            p($addMsgReply, 'addMsgReply');
        }

        if ($addMessageExtra['choices']) {
            $result = $addMessageExtra['choices'][0]['message']['content'];
        }


        $newMsgForFront = array();

        foreach ($addMsgReply['choices'] as $newMsg) {


            $isNew = true; // Assume the message is new


            // If the message is new, add it to the session
            if ($isNew && $newMsg['message']['content']) {
                $newMsgArr = array(
                    "source" => "GPT",
                    "message" => $newMsg['message']['content'], // Assuming 'message' is the key for message content
                    "id" => $addMsgReply['id'] // Assuming 'id' is the key for the message ID
                );
                //$_SESSION['activeRuns']['messages'][] = $newMsgArr;
                $newMsgForFront[] = $newMsgArr;

                // Insert into db
                // Insert into AiMessages
                $messageDataArray = array(
                    array(
                        'message_id' => $newMsgArr['id'],
                        'content' => $newMsgArr['message'],
                        'role' => 'chat_completetions'
                    )
                );
                basicInsert($this->db[1], $messageDataArray, 1, 0, null, $this->con);
            }
        }



        // Stand by for further logic instructions
        $final = array(
            'messages' => $newMsgForFront,
            'status' => 'completed'
        );






        return  $final;
    }
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\

    public static function preprocessMessage($message)
    {
        // Preprocess the message. For now, it's just returning the same message.
        // You can extend this to include more complex logic.
        return trim($message);
    }
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\
    //____________________________________________________________________________________________________\





}
