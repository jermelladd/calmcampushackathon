<?php

include_once("functions.php");
date_default_timezone_set('America/Los_Angeles');
ignore_user_abort(false); // Do not ignore user aborts

$con = dbConnect("CalmCampusv1");

//echo getTime(); l();


include_once("chat-ai-backend.php");



$a = new WebAI();
#$a->One();


// All HTTP request logic gets handled in HandleRequest
if($_POST['r']) {
    $a->HandleRequest($_POST);
}


$AppName = "CalmCampus";



class WebAI {
public $con; 
public $current_time; 
#public $table_name; 
#public $var2 
 
public $wpObj = array();
    
public $wpID = 1;

public $check_for_saved_answers = 1; 

public $isAdmin = 0;
public $websiteId = 0;

public $ERActiveIDs = '70';
    
#public $ar_1 = array();

//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
public function __construct() {




//parent::__construct();

    global $con; 
    $this->con = $con;
    $this->current_time = getTime();

    //p($this->current_time);
    
    $this->tt();
    
    
    $q = "
     SELECT 
            a.id, a.date_created, a.last_updated, a.is_deleted, a.is_active, 
            a.name, a.description, a.url, a.website_id, a.default_html,
            b.id AS b_id, b.date_created AS b_date_created, b.last_updated AS b_last_updated, b.is_deleted AS b_is_deleted, b.is_active AS b_is_active,
            b.name AS b_name, b.url AS b_url, b.description AS b_description, b.goal AS b_goal, b.erMode, b.ext2 AS b_ext2, b.ext3 AS b_ext3
        FROM xWebPage AS a
        JOIN xWebsite AS b ON a.website_id = b.id
        WHERE b.id = '{$this->wpID}' LIMIT 1
    ";
    
    $this->wpObj = basicQuery($this->con, '', $q, 10, 2);
   
    
    global $b; 
    



    $this->ChatAI = $b; //p($b); 
    $this->ChatAI->ro = 1;
    
    
    
    // NEED TO GET CODE THAT FETCHES SESSIONS ID
    $this->sessionObj = array();
    $this->sessionID = 1;


    
    



}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function start_web_session() {
    
    // Check if there is an existing session
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $current_time = date("Y-m-d H:i:s");

    $session_query = "SELECT * FROM Sessions WHERE ip_address = '$ip_address' AND end_time IS NULL";
    $session_result = basicQuery($this->con, "", $session_query, 0, 2);

    if (!empty($session_result)) {
        // Session exists, update last_active_time
        $session_id = $session_result[0]['id'];
        $update_data = array('last_active_time' => $current_time);
        basicUpdate("Sessions", $update_data, "id = $session_id", 1, 0);
        return $session_result[0];
    } else {
        // Create a new session
        $session_data = array(
            'ip_address' => $ip_address,
            'start_time' => $current_time,
            'last_active_time' => $current_time,
            'has_interacted' => 0
        );
        basicInsert("Sessions", array($session_data), 1, 0);
        
        // Fetch and return the newly created session
        $new_session_query = "SELECT * FROM Sessions WHERE ip_address = '$ip_address' AND start_time = '$current_time'";
        $new_session_result = basicQuery($this->con, "", $new_session_query, 0, 2);
        return $new_session_result[0];
    }
}

//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function send_base_questions($one = 0) {
    // Retrieve base questions query
    $query = "SELECT * FROM BaseQuestion WHERE is_featured = 1";
    
    // Execute query
    $result = basicQuery($this->con, "", $query, '', 2);
    
    // Format base questions as JSON to FrontEnd
    $json_result = json_encode($result);
    
    
    
    
    return $json_result;
    
    
}

//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function SendDefaultWebSection($one = 0) {
    // Retrieve default HTML ID from wpObj
    $default_html_id_query = "
        SELECT a.default_html
        FROM xWebPage AS a
        JOIN xWebsite AS b ON a.website_id = b.id
        WHERE b.id = '{$this->wpID}' LIMIT 3
    ";
    
    $default_html_id_result = basicQuery($this->con, "", $default_html_id_query, 0, 2);
    
   p($one, 'one');

    if($one) {
        if($one == '911') {


            $query = "SELECT TO_BASE64(bws.childWSHTML) as b64txt
            FROM BaseWebSection bws
            INNER JOIN childAnswer ca ON bws.childAnswerID = ca.id
            INNER JOIN childQuestion cq ON ca.childQuestionID = cq.id
            INNER JOIN BaseQuestion bq ON cq.baseQuestionID = bq.id
            WHERE  bq.id IN ($this->ERActiveIDs)
            LIMIT 1";
            
            

            $results = basicQuery($this->con, '', $query, 0, 2);

            //p($results , '$results ');

            return  json_encode($results);

        }
        if (!empty($default_html_id_result)) {
        
                $default_html_id = $default_html_id_result[0]['default_html'];
                
                // Retrieve from BaseWebSection where default_html_id matches
                $base_web_section_query = "
                    SELECT * 
                    FROM BaseWebSection
                    WHERE id = '$default_html_id'
                ";
                
                $base_web_section_result = basicQuery($this->con, "", $base_web_section_query, 10, 2);
                // p($base_web_section_result);
                // Return JSON value
                $json_result = json_encode($base_web_section_result);
                return $json_result;
            } else {
                // p( $default_html_id_result , 1);
                return json_encode( $default_html_id_result ['default_html']);
            }
      
        } else {


            $query = "SELECT TO_BASE64(bws.childWSHTML) as b64txt
        FROM BaseWebSection bws
        INNER JOIN childAnswer ca ON bws.childAnswerID = ca.id
        INNER JOIN childQuestion cq ON ca.childQuestionID = cq.id
        INNER JOIN BaseQuestion bq ON cq.baseQuestionID = bq.id
        WHERE bq.is_featured = 1
        LIMIT 3";
        
        ;

        $results = basicQuery($this->con, '', $query, 0, 2);

        return  json_encode($results);



    } 

 
}   

//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function QUESTION_RECOMMENDATION ($question_txt, $chat_history = null) {

    $this->tt();
    
    p($question_txt, '$question_txt');
    
    $user_question = array(
        'user_currently_typed' => $question_txt
    );
    
    $user_question_json = json_encode($user_question);

    
    
    
    $instructions = "Your task is to function as a search suggestion engine. The objective is to assist users with type-ahead search functionality. I will provide you with the text that the user has typed so far, and you need to provide 3-5 search suggestions based on that input. The aim is to help users complete their queries quickly by providing relevant suggestions, reducing the need for them to type the entire query.

Your suggestions must begin with the exact text that the user has typed so far. The suggestions should be based on your knowledge of the subject you’ve been trained on. Provide your response in JSON format only. This chat will be used by a PHP application, so ensure that each suggestion in the JSON includes a relevance score indicating how well it matches what you believe the user is trying to type.

Remember, in the next chat, respond only with JSON data. If you do not, my application will not be able to process the reply. In your suggestions make the question suggestion using key 'childQuestionText'. 

Below is what the user has currently typed. I will provide this input in JSON under the key “user_currently_typed”. Based on this input, generate your suggestions.
    
    $user_question_json
    
    ";
    
    $content = array(
        'message' =>  $instructions,
        'status' => null, 
        'r' => 'chatmsg'
    );
    

    $update_db = array(
        'query_1' => $question_txt
    );

    basicUpdate("Sessions", $update_db, "id = '{$this->sessionID}'", 1, 0);
    $this->qr_txt = $question_txt;

    $results = $this->ChatAI->ChatMsgFlow($content);
    $this->ChatAI->qrec = 1;
    
    p($results, '$results in QUESTION_RECOMMENDATION');
    
    $final = $this->CheckForUpdate($results);
    
    p($final, '$final in QUESTION_RECOMMENDATION');
    
    
    
    // Example JSON response with triple backticks
    $jsonResponse = $final['messages'][0]['message'];

    // Remove the triple backticks and the "json" part
    $jsonResponse = str_replace(['```json', '```'], '', $jsonResponse);

    // Trim any leading or trailing whitespace
    $jsonResponse = trim($jsonResponse);

    // Decode the JSON response into a PHP associative array
    $js_suggestions = json_decode($jsonResponse, true);
    
    p($js_suggestions, '$js_suggestions');

    
    $js_suggestions = $js_suggestions['suggestions'];
    
    
    echo json_encode($js_suggestions);

    $this->tt();
    
    return $js_suggestions; 
    
    

    
    
    





}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function CheckForUpdate ($results, $max_attempts = null) {
   
    
    if(!$max_attempts) {
        $max_attempts = 1;
    }
    
    $attempts = 0;
    p($results, 'results in CheckForUpdate');
    $q = "SELECT * FROM AiRuns WHERE id = '{$results['rid']}' LIMIT 1";
    $runsObj = basicQuery($this->con, "", $q, 10, 2);

    $search_query = "SELECT `query_1` FROM `Sessions` where `id` = '{$this->sessionID}' LIMIT 1";

    $this->tt();

    while (($results['status'] == 'in_progress' || $results['status'] == 'queued') && $attempts < $max_attempts) {
        // Call ProgressFlow

        
        $check_to_run = basicQuery($this->con, '', $q, 10, 2);

        
        if (connection_status() != CONNECTION_NORMAL) {
            // Connection is not normal, stop execution
            mysqli_close($this->con); 
            mysqli_close($this->ChatAI->con); 

            die;
        }

        $this->tt();

        $most_current_result = $this->ChatAI->ProgressFlow($runsObj);
        
        // Save the response from the previous function to $most_current_result
        $results = $most_current_result;
        
        // Increment attempts
        $attempts++;
        
        // Check status again
        if ($results['status'] == 'in_progress' || $results['status'] == 'queued') {
            continue;
        } else {
            break;
        }
    }
    
    $this->tt();

    return $results;
}

//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function CHAT_SUBISSION ($message = array()) {



    $ANSWER_FINAL = $this->ANSWER_FINAL($message);
    
   

    return $ANSWER_FINAL;



}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function ANSWER_FINAL ($message = array(), $data = array()) {

    
    $this->tt();
    
    //CHECK IF Question has Answer 
    $endNow = 0;
    if( $this->isAdmin && $message['message']) {
        p("ADMIN in ANSWER FINAL", 10);
        p($data, 'data');
        $quest = array('childQuestionText' => $message['message']);
        $this->GET_ANSWER($quest, null, array("post" => $message));
        $endNow = 1;
    }
    else if(!$data['pending_answer']) {
        $question_record = $this->CREATE_QUESTION($message['message'], $message['qid']);
        $Answer = $this->CHECK_ANSWER($question_record);
    } elseif ($data['pending_answer']) {
        p('elseif ($data[pending_answer]) {', 'elseif ($data[pending_answer]) {');
        $question_record = $this->GET_QUESTION($data['pending_answer']['id']);
        p($question_record, 'question_record');
    } else {
       // $this->GET_ANSWER();
    }
    
    $this->tt();

    if($Answer) {

        p('if($Answer) {', 10);
        $savedAns = $this->SAVE_ANSWER($question_record, $Answer, array("saved_answer" => 1));

        $Answer['childAnswerID'] = $savedAns[0]['id'];

        $checked_answer =  $this->FORMAT_DATA('chat_reply_stored_answer', $Answer);

        $this->tt();
        
        echo $checked_answer;

        
        
        // UPDATE
        #$answer = array($short, $long, $websection)
        
    } elseif($data['pending_answer']) {
        
        
        // Create New Answer 
        $savedAnswer = $this->SAVE_ANSWER($question_record, $data['pending_answer']['r']);

        $data['pending_answer']['r']['bid'] = 
        $savedAnswer['baseAnswerID'];

        $this->tt();

        $json = $this->FORMAT_DATA('bjson', $data['pending_answer']['r']);
        echo $json;
        
        return $json;

        
        
        
    } elseif ($endNow) {

        return;

    } 
    else {
        $Answer = $this->GET_ANSWER($question_record, null, array("post" => $message));
        
        

      



    }





    



}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function CREATE_QUESTION ($message, $qid = null) {

    
     // Insert into childQuestion
    $child_question_data = array(
        'text' => $message,
        'session_id_created_by' => $this->sessionID
    );
    basicInsert("childQuestion", array($child_question_data), 1, 0);
    
    // Get the inserted childQuestion ID
    $child_question_id_query = "SELECT id FROM childQuestion ORDER BY id DESC LIMIT 1";
    $child_question_id_result = basicQuery($this->con, "", $child_question_id_query, 0, 2);
    p($child_question_id_result, 'child_question_id_result');
    $child_question_id = $child_question_id_result[0]['id'];
    $child_question_id2 = $child_question_id;
    
    if($qid) $child_question_id = $qid;
    
    // Check if BaseQuestion exists
    $questionText = mysqli_real_escape_string($this->con,$child_question_data['text'] );
    //$bq = "SELECT * FROM BaseQuestion WHERE childQuestionID = '{$child_question_id}' || childQuestionText = '$questionText'  order by id desc limit 1 ";
    $bq = "SELECT * FROM BaseQuestion WHERE childQuestionID = '{$child_question_id}' order by id desc limit 1 ";
    p($bq, 10);
   

    $base_question_result = basicQuery($this->con, "", $bq, '', 2);  
    p($base_question_result, '$base_question_result');
    
    


    if (empty($base_question_result)) {
        
        p( ' if (empty($base_question_result)) {',"DEBUGGING");

       


        // Insert into BaseQuestion if it does not exist
        $base_question_data = array(
            'childQuestionID' => $child_question_id,
            'childQuestionText' => $message,
            'session_id_created_by' => $this->sessionID
        );
        basicInsert("BaseQuestion", array($base_question_data), 1, 0);
        
        $bq_query = "SELECT * FROM BaseQuestion where childQuestionID = '$child_question_id' ORDER BY id DESC LIMIT 1";
        $bqobj = basicQuery($this->con, "", $bq_query, 10, 2);
        
        
        
        $update = array('baseQuestionID' => $bqobj['id']);
        //"UPDATE childQuestion SET baseQuestionID = '$bqobj[id]' WHERE id = $child_question_id ";
        basicUpdate("childQuestion", $update, "id = $child_question_id ", 1, 0);
        
    } else {

        p("base_question_result = {$base_question_result[0]['id']}", 'DEBUGGING');

        $qid = $base_question_result[0]['id'];
        $child_question_id = $base_question_result[0]['childQuestionID'];


        $update = array('baseQuestionID' => $qid );
        //"UPDATE childQuestion SET baseQuestionID = '$bqobj[id]' WHERE id = $child_question_id ";
        basicUpdate("childQuestion", $update, "id = $child_question_id2 ", 1, 0);

    }
    
    // Query the newly created questionobj
    $query = "
        SELECT a.*, 
               ch.id AS ch_id, ch.text AS ch_text, ch.date_created AS ch_date_created, ch.last_updated AS ch_last_updated,
               ch.baseQuestionID AS ch_baseQuestionID, ch.session_id_created_by AS ch_session_id_created_by, 
               ch.score_of_relivancy_to_base_Question AS ch_score_of_relevancy_to_base_question
        FROM BaseQuestion AS a
        JOIN childQuestion AS ch 
        ON a.id = ch.baseQuestionID
        WHERE ch.id = '$child_question_id'
    ";
    p($query, 10);
    // Execute query
    $result = basicQuery($this->con, '', $query, 10, 2);
    
    $result['sub_qid'] = $qid;
    
    p($result, '$result in CREATE_QUESTION');
    
    // Return the result
    return $result;





}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function CHECK_ANSWER ($question_obj) {


    p("public function CHECK_ANSWER (");
    // Check to see if there is an answer
    
    /*  Array
(
    [id] => 16
    [date_created] => 2024-06-21 19:10:03
    [last_updated] => 2024-06-21 19:10:03
    [childQuestionID] => 23
    [childQuestionText] => hello
    [session_id_created_by] => 1
    [is_featured] => 0
    [ch_id] => 23
    [ch_text] => hello
    [ch_date_created] => 2024-06-21 19:10:03
    [ch_last_updated] => 2024-06-21 19:10:03
    [ch_baseQuestionID] => 16
    [ch_session_id_created_by] => 1
    [ch_score_of_relevancy_to_base_question] => 
    [sub_qid] => 15
)

*/
    // First check if there is qid
    // If there is a qid then that means there's already an answer
    if($question_obj['sub_qid'] && $question_obj['is_question']) {
        
       $q = "select * from JQuestionANDAnswers where baseQuestionID = {$question_obj['sub_qid']}";
        $join_obj = basicQuery($this->con, '', $q, 10, 2);
        p($join_obj, '$join_obj');
        $baseAnswerID = $join_obj['baseAnswerID'];

        


        if($this->check_for_saved_answers == 0) {
            $join_obj = array();
            $baseAnswerID =null;
            p("removing baseAnswerID and baseAnswerID", "DEBUGGING");
          } 

          else {


            
            $this->is_similar_answer = 1;
            







          }


          

          
        
    } else {
        
        

        $q = '';
        $questionFetch = basicQuery($this->con, '', $q, 0, 2);

        p($questionFetch, '$questionFetch ');
        p($q, "query in question fetch");

        $bqid = intval( $questionFetch[1]['baseQuestionID']);

        
       
       
        $q = "select * from JQuestionANDAnswers where `baseQuestionID` = $bqid limit 1;"; //p($q, '$q for bqud');
      
        $join_obj2 = basicQuery($this->con, '', $q, 10, 2);
        
        
        p($join_obj2, '$join_obj2 in else ');
        
        //$baseAnswerID = $join_obj2['baseAnswerID'];
        
        $baseAnswerID = $this->CheckForSimilarQuestions($question_obj);


        

        if($this->check_for_saved_answers == 0) {
            $join_obj2 = array();
            $questionFetch = array();
            p("removing join_obj2 and questionFetch", "DEBUGGING");
          }
        
        
        
    }
    
    
    $q = "
    SELECT a.* , b.*, a.id as ba_id, b.id as ca_id
    FROM BaseAnswer as a
    JOIN childAnswer as b
    ON a.childAnswerID = b.id
    WHERE a.id = '{$baseAnswerID}'
    
    ";
    
    
     $answer_obj = basicQuery($this->con, '', $q, 10, 2);
    
    
     if($this->is_similar_answer ) {
        $answer_obj['isa'] = 1;
     }
    
      

      if($answer_obj ) {
        
        p("if answerObj exist", 10);


        // Add the answer to the thread


        $q = "SELECT * FROM AiThreads WHERE id = '{$this->pa['tid']}' LIMIT 1";
        $threadObj = basicQuery($this->con, "", $q, 10, 2);

        p($threadObj,'threadObj if answer_obj'); 

        $data_for_addMsg = array(

            'tid' => $threadObj['thread_id'],
            'all_post' => $this->pa,
            "message" => $answer_obj['childAnswerText']

        );


        $threadId = $this->ChatAI->AddMsgToThread($data_for_addMsg);

        $answer_obj['thread_id'] =  $threadId;

      }


      p($answer_obj, '$answer_obj');

    return $answer_obj;





}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function GET_ANSWER ($question, $status = null, $data = array()) {


    if(!$status) {

        $this->ChatAI->ro = 1;

    
        $prompt = "
        Please answer the question below. It will start after 'QUESTION BELOW  QUESTION BELOW  QUESTION BELOW' and will end before ' END QUESTION  END QUESTION  END QUESTION'.


        QUESTION BELOW  QUESTION BELOW  QUESTION BELOW
        
         {$question['childQuestionText']}

         END QUESTION  END QUESTION  END QUESTION 
        "; 
        
        $json_txt = array(
            "Question" => array('user_text' => $question['childQuestionText'])

        );
        $json_txt = json_encode($json_txt);

       
        $promptLookUp = $this->CRUDInstructions(array('request_name' => 'general_chat'));

        

        $prompt = "{$promptLookUp['value']}
        
        I will provide the chat submission in json format. It will be Question.user_text. Please reference the entire conversation in this chat for answers as well.

        $json_txt
        
}";

        //$post = array('message' => $prompt, '');
        $post = $data['post'];
        $post['message'] = $prompt;
        
        p($post, 'post');
        
        
        $r = $this->ChatAI->ChatMsgFlow($post); 
    
        
        p($r, '$r in GET_ANSWER');
        
        
        if($r['status'] == 'in_progress' || $r['status'] == 'queued') {
            $r['db_qid'] = $question['id'];
            $json = $this->FORMAT_DATA('bjson', $r);
            echo $json;
            return $json; 
    
        }

    }


    elseif($status =='in_progress' || $status == 'queued') {
        p('$status ==in_progress','$status ==in_progress'); 
        $this->ChatAI->ro = 1;
        //$this->ChatAI->thread_db_id = $data['post']['tid'];
        //$this->ChatAI->run_db_id = $data['post']['rid'];

        // Search for runObj 
        $q = "SELECT * FROM AiRuns WHERE id = '{$data['post']['rid']}' LIMIT 1";
        $runsObj = basicQuery($this->con, "", $q, 10, 2);

        p($runsObj,'runsObj in ==in_progress'); 

        $data_for_progressflw = array(

            'status' => $status,
            'tid' => $data['post']['tid'],
            'rid' => $data['post']['rid'],
            'all_post' => $data['post']

        );
        $r = $this->ChatAI->ProgressFlow($runsObj);


        if($r['status'] == 'in_progress' || $r['status'] == 'queued') {
            
            // Do nothing since server still thinking 
            $r['db_qid'] = $data['db_qid'];
            $json = $this->FORMAT_DATA('bjson', $r);
            echo $json;
            return $json; 
    
        } elseif ($r['status'] == 'failed') {
            

            p($r, 'error debug');
            $r = array(
                'status' => 'failed',
                'messages' => array(
                    'message' => $r['r']['last_error']['message']
                    //'message' => "WE HAVE AN ERROR"
                )
            );

            $json = $this->FORMAT_DATA('bjson', $r);
            echo $json;
            return $json; 



        }else {

            // Start saving reply of answer 
            
            // Format pending answer 
            $r = $this->CleanGPTMsg($r, 1);
            p($r, 'r after CleanGPT');

            $temp = array(
                'pending_answer' => array(
                    'id' => $data['db_qid'],
                    'r' => $r
                )

            );

            if($this->isAdmin) {
                $this->AdminAnswerFinal($temp);
                return;
            }

            $this->ANSWER_FINAL(null, $temp);

           /*  $json = $this->FORMAT_DATA('bjson', $r);
            echo $json;
            return $json;  */


        }




    }

    
    

    



}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function GET_QUESTION ($id) {



     // Query the newly created questionobj
     $query = "
     SELECT a.*, 
            ch.id AS ch_id, ch.text AS ch_text, ch.date_created AS ch_date_created, ch.last_updated AS ch_last_updated,
            ch.baseQuestionID AS ch_baseQuestionID, ch.session_id_created_by AS ch_session_id_created_by, 
            ch.score_of_relivancy_to_base_Question AS ch_score_of_relevancy_to_base_question
     FROM BaseQuestion AS a
     JOIN childQuestion AS ch 
     ON a.id = ch.baseQuestionID
     WHERE a.id = '$id'
 ";
 
    // Execute query
    $result = basicQuery($this->con, '', $query, 10, 2);
    
    
    
    p($result, '$result in GET_QUESTION');
    
    // Return the result
    return $result;





}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function SAVE_ANSWER ($question, $answer, $data = array()) {


    
    if($data['saved_answer']) {

        p(' if($data[aved_answer]) {', 'in function SAVE_ANSWER');

        $BAobj = $answer;

        // 
        if($this->dna_with_base) {
            $BAobj['ba_id'] = null;
        }
        

        $newCA = array(
            'text' => $BAobj['childAnswerText'],
            'session_id_created_by' => $this->sessionID, 
            'childQuestionID' => $question['ch_id'],
            'baseAnswerID' => $BAobj['ba_id']
    
        );

        basicInsert("childAnswer", array($newCA), 1, 0);


        $CAobj = basicQuery($this->con, '', "SELECT * FROM childAnswer where session_id_created_by = {$this->sessionID} order by id desc limit 1", 10, 2);





        p($CAobj, '$CAobj');


        return array($CAobj);

    } else {




    // Go through answers
   // p($answer, '$answer in SAVE_ANSWER');



    
    
    // Trim excess whitespace
    $ans_txt = $this->CleanGPTMsg($answer);


   

    $newBA = array(
        'childAnswerText' => $ans_txt,
        'session_id_created_by' => $this->sessionID 

    );

    p($newBA, '$newBA in SAVE_ANSWER');

    basicInsert("BaseAnswer", array($newBA), 1, 0);


    $BAobj = basicQuery($this->con, '', "SELECT * FROM BaseAnswer where session_id_created_by = {$newBA['session_id_created_by']} order by id desc limit 1", 10, 2);

    p($BAobj, '$BAobj');


    $newCA = array(
        'text' => $ans_txt,
        'session_id_created_by' => $this->sessionID, 
        'childQuestionID' => $question['ch_id'],
        'baseAnswerID' => $BAobj['id']

    );

    basicInsert("childAnswer", array($newCA), 1, 0);


    $CAobj = basicQuery($this->con, '', "SELECT * FROM childAnswer where session_id_created_by = {$newBA['session_id_created_by']} order by id desc limit 1", 10, 2);


    p($CAobj, '$CAobj');



    $update = array("childAnswerID" => $CAobj['id'] );

    basicUpdate("BaseAnswer", $update, "id = {$BAobj['id']}", 1, 0);


    // Now need to add it to join table
    $jtNew = array(

        'baseQuestionID' => $question['id'],
        'baseAnswerID' => $BAobj['id']
    );
    

    basicInsert("JQuestionANDAnswers", array($jtNew), 1, 0);

    $jtObj = basicQuery($this->con, '', "SELECT * FROM JQuestionANDAnswers where baseQuestionID = {$jtNew['baseQuestionID']} and  baseAnswerID = {$jtNew['baseAnswerID']} order by id desc limit 1", 10, 2);

    $jtObj['childAnswerID'] = $CAobj['id'];

    p($jtObj, '$jtObj (JQuestionANDAnswers)');




    return $jtObj;
   }

   

}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function tt() {
    $trace = debug_backtrace();
    $line = $trace[0]['line'];
    $function = isset($trace[1]['function']) ? $trace[1]['function'] : 'global scope';
    $time = date("Y-m-d H:i:s");
    //echo "Current Time: $time, Invoked at Line: $line in Function: $function";
}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function CheckForSimilarQuestions ($question_obj) {


    // NexT STEP 
    // update teh top here so that it sees if answer exists

    p($question_obj, '$question_obj in CheckForSimilarQuestions ');

    $searchString = trim(mysqli_real_escape_string($this->con, $question_obj['childQuestionText']));

    $q = "
    SELECT a.*
FROM childQuestion AS a
INNER JOIN JQuestionANDAnswers jqa 
ON a.baseQuestionID = jqa.baseQuestionID
WHERE a.text = '$searchString'

ORDER BY a.id DESC limit 2; 
    ";

    $q = "
    SELECT a.*
FROM childQuestion AS a
INNER JOIN JQuestionANDAnswers jqa 
    ON a.baseQuestionID = jqa.baseQuestionID
INNER JOIN childAnswer ca 
    ON ca.childQuestionID = a.id
INNER JOIN BaseQuestion bq 
    ON bq.id = a.baseQuestionID
WHERE 
    a.text = '$searchString'
    AND bq.is_question = 1  
ORDER BY 
    a.id DESC 
LIMIT 2;
    ";





    $questionFetch = basicQuery($this->con, '', $q, 0, 2);

    p($questionFetch, '$questionFetch ');
    p($q, "query in question fetch");

    $non_question_q= "
    SELECT a.*
FROM childQuestion AS a
INNER JOIN JQuestionANDAnswers jqa 
    ON a.baseQuestionID = jqa.baseQuestionID
INNER JOIN childAnswer ca 
    ON ca.childQuestionID = a.id
INNER JOIN BaseQuestion bq 
    ON bq.id = a.baseQuestionID
WHERE 
    a.text = '$searchString'
    AND bq.is_question <> 1  
ORDER BY 
    a.id DESC 
LIMIT 2;
    ";

    $nonquestionFetch = basicQuery($this->con, '', $non_question_q, 0, 2);

    p($non_question_q, '$non_question_q ');
    p($nonquestionFetch, '$nonquestionFetch ');
    

   

    if($this->check_for_saved_answers == 0) {
        p('$this->check_for_saved_answers == ', 10);
        return;
    }


    // THis means there was a direct match in the database 
    if(count($questionFetch) > 0) {
        $bqid = intval( $questionFetch[0]['baseQuestionID']);


        $q = "select * from JQuestionANDAnswers where `baseQuestionID` = $bqid limit 1;"; //p($q, '$q for bqud');
          
        $join_obj2 = basicQuery($this->con, '', $q, 10, 2);
        
        
        p($join_obj2, '$join_obj2 in else ');
        
        $baseAnswerID = $join_obj2['baseAnswerID'];
    
        if($this->check_for_saved_answers == 0) {
            $join_obj2 = array();
            $questionFetch = array();
            p("removing join_obj2 and questionFetch", "DEBUGGING");
         }
    


        // If there is a match then we will want to delete the old base question obj
            // and update the child questions
            $delete_query = "DELETE FROM BaseQuestion where id = {$question_obj['id']} Limit 1";
            basicQuery($this->con, '', $delete_query, 1, 2);

            
            // Now need to update the childanswer with the new ba_id 
            $update = array('baseQuestionID' => $join_obj2['baseQuestionID']);
            basicUpdate('childQuestion', $update, "id = {$question_obj['ch_id']} ", 1);
             
    
    
    
            $this->is_similar_answer = 1;
            return $join_obj2['baseAnswerID'];
    }


    elseif (count($nonquestionFetch) > 0) {


        p('count($nonquestionFetch) > 0', 10);

        return null;


    }

    // This means now we're going to do a quick look up
    else {

        p("check for similar questions else", "DEBUGGING");

         // First pull all BaseQuestion records and make sure to only pull unqiue on
        // Childquestion text | make sure to pull ID
        $q = "
        SELECT MIN(bq.id) AS id, bq.childQuestionText
    FROM BaseQuestion bq
    INNER JOIN JQuestionANDAnswers jqa ON bq.id = jqa.baseQuestionID
    WHERE bq.is_question = 1
        GROUP BY bq.childQuestionText
    ORDER BY id
    ;
        ;
        ";

        $lookup_records = basicQuery($this->con, '', $q, 0, 2);

        p($lookup_records, 'lookup_records');


        // Then we need to format this. Perhaps json is fine
        $lookup_json = json_encode($lookup_records);


        

        $prompt = "
     Your task is to function as a search suggestion engine. The objective is to optimize my search engine so that I don't have to do a new question/answer lookup. I will provide you with my current database (CURRENT_DB) that has questions users have asked already. Each of these questions has a saved answer to it. I will also provide you with the USER_TEXT of what the user submitted.

I need you to look at the USER_TEXT and tell me if there are any results in the CURRENT_DB that match the USER_TEXT. Provide the top three matches in JSON with a matching score of 1-100.

Let 100 be a word-for-word match. Let 90 be pretty close in terms of what is being asked, but ensure that specific entities (such as camera numbers, dates, or names) match exactly. Let 80 be different ways of writing but overall question is the same, ensuring that all critical entities are the same. Let anything less than 50 be no match at all.

For example, if the user types \"When is youth camp?\" and there is something in the database that says \"What are the dates of camp?\", that would be a 95% match. However, if the user types \"Is there a way to correct coloring on CAM 4?\" and there is something in the database that says \"How can I correct the coloring on Camera 3?\", it should be considered a no match due to the different camera numbers.

This chat will be used by a PHP application, so ensure that each suggestion in the JSON includes a relevance score indicating how well it matches what you believe the user is trying to type.

Remember, in the next chat, respond only with JSON data. If you do not, my application will not be able to process the reply. In your suggestions make the question suggestion using key 'childQuestionText'. Also, include the `id` in each object. Finally, let the matching score be called \"matchingScore\" in each object.

        USER_TEXT: {$question_obj['childQuestionText']}

        CURRENT_DB: $lookup_json
        
        ";
       

        p( $prompt , ' $prompt ');
        
        
        
        


        // Call API using technique used in QUESTION_RECOMMENDATION
        $content = array(
            'message' =>  $prompt,
            'status' => null, 
            'r' => 'chatmsg'
        );


        // using Assistant vs Chat Completion 
        if(0) {
            $this->ChatAI->assistant_id = $this->ChatAI->ass[4]['id'];
            $results = $this->ChatAI->ChatMsgFlow($content);
            $this->ChatAI->qrec = 1;
        } else {

            $results = $this->ChatAI->ChatCompletions($content);
            
        }
       
        

        // Then need to get update from CheckForUpdate 
        // Perhaps we can increase max attempts if needed
        p($results, '$results in CheckForSimilarQuestions');
        
        
        //$final = $this->CheckForUpdate($results, 20);
        $final = $results;

        p($final, '$final in CheckForSimilarQuestions');



        // Example JSON response with triple backticks
        $jsonResponse = $final['messages'][0]['message'];

        // Remove the triple backticks and the "json" part
        $jsonResponse = str_replace(['```json', '```'], '', $jsonResponse);

        // Trim any leading or trailing whitespace
        $jsonResponse = trim($jsonResponse);

        // Decode the JSON response into a PHP associative array
        $js_suggestions = json_decode($jsonResponse, true);
        
        p($js_suggestions, '$js_suggestions');

        
        //$js_suggestions = $js_suggestions['suggestions'];

        usort($js_suggestions, function ($a, $b) {
            return $b['matchingScore'] <=> $a['matchingScore'];
        });

        p($js_suggestions, 'updated js_suggestions array');

        $match = null;
        foreach($js_suggestions as $zz => $suggest) {

            if($suggest['matchingScore'] > 70) {
                p( "there is a match of {$suggest['matchingScore']} for {$suggest['id']}","DEBUGGING");
                $match = $suggest;
                break;
            }


        }

        // Once you get ID you can match based upon previous set
        if($match) {

            $q = "select * from JQuestionANDAnswers where `baseQuestionID` = {$match['id']} limit 1;"; //p($q, '$q for bqud');
          
            $join_obj3 = basicQuery($this->con, '', $q, 10, 2);
            
            
            p($join_obj3, '$join_obj3 in else ');
            
            $baseAnswerID = $join_obj3['baseAnswerID'];
        
            
            
            // If there is a match then we will want to delete the old base question obj
            // and update the child questions
            $delete_query = "DELETE FROM BaseQuestion where id = {$question_obj['id']} Limit 1";
            basicQuery($this->con, '', $delete_query, 1, 2);

            
            // Now need to update the childanswer with the new ba_id 
            $update = array('baseQuestionID' => $join_obj3['baseQuestionID']);
            basicUpdate('childQuestion', $update, "id = {$question_obj['ch_id']} ", 1);

        
        
            $this->is_similar_answer = 1;
            return $join_obj3['baseAnswerID'];


        } else {
            return null;
        }



     



    }

    



}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function AfterMessage ($data) {


    p($data, 'data in AfterMessage');

    // Get question 
    $id = mysqli_real_escape_string($this->con, $data['bid']);
    
    


  
    $baq = $this->getBAQ(array('baseAnswerID', $id ));



    $final = array ();



    // First Decide what  if there is a question or not
    if($baq['is_question'] == '') {
       
        p('$baq[is_question] ==  ""', 10);

        $prompt = "
        Your task is to function as a YES / NO engine for my below text from a ChatBot. I am storing your response in my web app database. 
    
    I need to understand if the below question is a QUESTION or STATEMENT. Questions are defined as text that has a question to it. 
    
    If there is any personal identifiable information (PII) automatically declare it as a statement even if there is a question. 
    
    Example 1: \"What does Camera 3 do?\"
    Answer 1: {\"IS_QUESTION\" : 1}
    Explanation 1: This is clearly a question. Even if there is no question mark at the end it is a question. 
    
    Example 2: \"Thank you, have a good day.\"
    Answer 2: {\"IS_QUESTION\" : 0}
    Explanation 2: This is clearly a statement. Even if there is a question mark at the end, there is no question. 
    
    Example 3: \"Thank you, my name is Bill. What is your name?\"
    Answer 3: {\"IS_QUESTION\" : 0}
    Explanation 3: This is a statement and a question. Along with the PII information this is considered a STATEMENT. 
    
    Example 4: \"Because my name is Amanda, I need to know how many people are at church?\"
    Answer 4: {\"IS_QUESTION\" : 0}
    Explanation 4: This is a question along with PII information and therefore is a statement. 

     Example 4: \"Can you please repeat that?\"
    Answer 4: {\"IS_QUESTION\" : 0}
    Explanation 4: All though there is no PII data and this is a clear question, it is not considered a question here because the response to this answer is subject to a previous question. 
    
    Based upon the 5 examples, you can get the feel for what Im looking for. To further clarify, I want you only to mark things as questions if they are clear questions. 
    
    This chat will be used by a PHP application, so ensure that each suggestion in the JSON includes a relevance score indicating how well it matches what you believe the user is trying to type.
    
    Remember, in the next chat, respond only with JSON data. If you do not, my application will not be able to process the reply. Format should be {\"IS_QUESTION\" : (1 or 0)}
    
    
    BELOW IS THE TEXT of the Chat Message:
    
    {$baq['childQuestionText']}
    
    ----- END CHAT MESSAGE -- 
    
        ";

        p($prompt, 10);


         // Call API using technique used in QUESTION_RECOMMENDATION
         $content = array(
            'message' =>  $prompt,
            'status' => null, 
            'r' => 'chatmsg'
        );


        $results = $this->ChatAI->ChatCompletions($content);

        p($results, '$results in AfterMessage for checking question');

        // Example JSON response with triple backticks
        $jsonResponse = $results['messages'][0]['message'];

        // Remove the triple backticks and the "json" part
        $jsonResponse = str_replace(['```json', '```'], '', $jsonResponse);

        // Trim any leading or trailing whitespace
        $jsonResponse = trim($jsonResponse);

        // Decode the JSON response into a PHP associative array
        $js_answer = json_decode($jsonResponse, true);

        p($js_answer, 'js_answer');

        $update = array('is_question' => $js_answer['IS_QUESTION']);
        basicUpdate("BaseQuestion", $update, "id = {$baq['baseQuestionID']}", 1, 0);
        
        $baq = $this->getBAQ(array('baseQuestionID',$baq['baseQuestionID'] ));


    }

    
   

    // Start HTML Part
    p("STARTING HTML Lookup", 10);


    // JERMEL JERMEL

    if($this->dashAftermsg) {

        p(' if($this->dashAftermsg) {', 10);

        //$BWSa = $this->DashQuestions(array('ask' => 'all_base_questions', 'iid' => $baq['baseQuestionID']));
        $BWSa =  $this->DashQuestions(array('ask' => 'all_base_answers', 'iid' => $baq['childAnswerID']));

        $final['bws_answers'] = $BWSa;
        
        $BWSq = $this->AnswerHTMLMain($baq );

        $final['bws_question'] = $this->DashQuestions(array('ask' => 'all_base_questions', 'iid' => $baq['baseQuestionID']));

        


    } else {

        $BWS = $this->AnswerHTMLMain($baq );

        $final['bws'] = $BWS;


    }

    


    
    echo json_encode($final);

    return;




}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function getBAQ ($d = array()) {


    switch ($d[0]) {
        
        case "baseAnswerID":
            $whe = "ba.id = {$d[1]}";
            break;

         case "baseQuestionID":
            $whe = "bq.id = {$d[1]}";
            break;
        
            case "childAnswerID":
                $whe = "ca.id = {$d[1]}";
                break;

            case "BaseWebSection":
                $whe = "bws.id = {$d[1]}";
                break;

        default : 
            break; 
    }


    if(!$whe) {
        p("no Whe!!", 10);
        return;
    }

    $q = "
    SELECT 
    ba.*, 
    ca.*,
    cq.*,
    bq.*,
    bws.childWSHTML,bws.childWSID, bws.childWSHTML as textfordiv, bws.id as bwsId,
    DATE_FORMAT(bws.last_updated, '%M %D %Y @ %h:%i %p PT') as last_updated_nice
FROM 
    BaseAnswer ba
LEFT JOIN childAnswer ca ON ca.id = ba.childAnswerID
LEFT JOIN childQuestion cq ON cq.id = ca.childQuestionID
LEFT JOIN BaseQuestion bq ON bq.id = cq.baseQuestionID
LEFT JOIN BaseWebSection bws ON ba.childAnswerID = bws.childAnswerID
WHERE 
    $whe 
ORDER BY 
    ca.id DESC
LIMIT 1;
    ";


    p($q, 10);

   $baq = basicQuery($this->con, '', $q, 10, 2); p($baq, 'BQ in getBAQ');

    return $baq; 




}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function ForceQuestion ($childAnswerID) {


    p("ForceQuestion Start", 10);

    
    $childAnswerID = mres($childAnswerID);

    $q = "
    select * from childAnswer where id = $childAnswerID limit 1;
    ";

    $Answer = basicQuery($this->con, '', $q, 10, 2); 
    p($Answer, 'question in ForceQuestion');



    // Look up question
    $q = "
    select * from childQuestion where id = {$Answer['childQuestionID']} limit 1;
    ";

    $Question = basicQuery($this->con, '', $q, 10, 2); 
    p($Question, 'question in ForceQuestion');


    $this->check_for_saved_answers = 0;
    $this->dna_with_base = 1;



    $ANSWER_FINAL = $this->ANSWER_FINAL(array('message' => $Question['text']));
    
   
    


    // Return 

    return $ANSWER_FINAL;









}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
public function AnswerHTMLMain ($baq) {



    p("STARTING AnswerHTMLMain", 10);


    // Check to see if HTML Exisit 


    $q = "
    SELECT *
    FROM BaseWebSection
    WHERE childAnswerID = {$baq['childAnswerID']}
    LIMIT 1
    ";

    $BWS = basicQuery($this->con, '', $q, 10, 2);
    p($BWS, 'BWS');

    if($BWS) {

        p('BWS is true', 10);


        $BWS['base_64'] = base64_encode($BWS['childWSHTML']);

        return $BWS;


    } else {

        // Need to fetch new BWS 

        $json_info = array(
            'childAnswer' => $baq['childAnswerText']
        );

        $json_prompt = json_encode($json_info);


        
        $instructions = "
        I am looking to create a web HTML snippet using existing CSS. The page already has the following CSS:

<style>
  /* Custom styling for the section */
  .webcontent-section {
    display: flex;
    flex-direction: row;
    min-height: 50vh;
    background-color: #f8f9fa;
    padding: 20px;
  }

  /* For mobile devices, make the section take 100% of the height */
  @media (max-width: 767px) {
    .webcontent-section {
      flex-direction: column;
      min-height: 100vh;
    }
  }

  .webcontent-image {
    flex: 1;
    background-image: url('https://images.tech.co/wp-content/uploads/2023/06/29040706/AdobeStock_605201447-min-scaled-e1691503225805-1072x536.jpeg');
    background-size: cover;
    background-position: center;
  }

  .webcontent-text {
    flex: 1;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
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
</style>

Please provide only the relevant `<div>`, `<p>`, `<h1>`, `<ul>`, and `<li>` tags needed to structure the content.

Use Bootstrap 4 (assume that the page has already loaded the necessary HTML/CSS/JS files).

For the content, use the information provided in the JSON object below, specifically from the key 'childAnswer'.

This will be read by people who have asked a question to a chatbot and will read the answer via html. Please reply in JSON only. Put the results in json_key 'html_result'. Only reply wiht JSON. If there other information you want to tell me put it as other keys in the json. 

        $json_prompt

        ";



        p($instructions,10);



        // Call API using technique used in QUESTION_RECOMMENDATION
        $content = array(
            'message' =>  $instructions,
            'status' => null, 
            'r' => 'chatmsg'
        );



        $results = $this->ChatAI->ChatCompletions($content, array("max_tokens" => 500));


        p($results, '$results in generate html');

        // Example JSON response with triple backticks
        $jsonResponse = $results['messages'][0]['message'];

        // Remove the triple backticks and the "json" part
        $jsonResponse = str_replace(['```json', '```'], '', $jsonResponse);


        $jsonResponse = trim($jsonResponse);

        // Decode the JSON response into a PHP associative array
        $jsonResponse = json_decode($jsonResponse, true);

        p($jsonResponse, '$jsonResponse');



        $BWS = array(
            'base_64' => base64_encode($jsonResponse['html_result'])
        );


        if($jsonResponse['html_result']) {


            $newBSW = array(

                'childWSHTML' => $jsonResponse['html_result'],
                'childAnswerID' => $baq['childAnswerID'],
                'session_id_created_by' => $this->sessionID,
                'is_featured' => 0

            );

            basicInsert("BaseWebSection", array($newBSW), 1, 0);

            $q = "
            SELECT *
            FROM BaseWebSection
            WHERE childAnswerID = {$baq['childAnswerID']}
            LIMIT 1
            ";
        
            $BWS = basicQuery($this->con, '', $q, 10, 2);
            p($BWS, 'BWS');



            $BWS['base_64'] = base64_encode($BWS['childWSHTML']);

            return $BWS;



        } else {
            return array("error" => "Could not generate HTML");
        }

        return $BWS ;



    }





}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function DashQuestions ($r = array()) {


    if($r['ask'] == 'questions_without_answers') {

        $query = "
        SELECT bq.*
FROM BaseQuestion bq
LEFT JOIN JQuestionANDAnswers jqa ON bq.id = jqa.baseQuestionID
WHERE jqa.baseAnswerID IS NULL

;
        
        ";



    } elseif($r['ask'] == 'answers_without_html') {

        $query = "
        SELECT ba.*, ba.childAnswerText as textfordiv, ba.id as itemid
FROM BaseAnswer ba
LEFT JOIN BaseWebSection bws ON ba.childAnswerID = bws.childAnswerID
WHERE bws.childAnswerID IS NULL;
        
        ";



    } elseif($r['ask'] == 'all_base_answers') {

        $WHERE = '';
        if($r['iid']) {
            $WHERE = " AND ca.id = {$r['iid']}";
        }
        
        $query = "
       SELECT 
    ba.*, 
    ba.childAnswerText AS textfordiv, 
    ba.id AS itemid,
    COUNT(ca.id) AS childItemCount 
FROM 
    BaseAnswer ba
LEFT JOIN 
    childAnswer ca 
ON 
	ca.baseAnswerID = ba.id
WHERE 
    1 = 1 $WHERE 
    
GROUP BY 
    ba.id;
        
        ";

      //  p ($query, 'query in all_base_answers');



    } elseif($r['ask'] == 'all_base_questions') {


        $WHERE = '';
        if($r['iid']) {
            $WHERE = " AND bq.id = {$r['iid']}";
        }

        $query = "
    SELECT 
    bq.*,  
    bq.childQuestionText as textfordiv, 
    bq.id as itemid,
    COUNT(cq.id) AS childItemCount, DATE_FORMAT(bq.last_updated, '%M %D %Y @ %h:%i %p PT') as last_updated_nice
FROM 
    BaseQuestion bq
LEFT JOIN 
    childQuestion cq ON cq.baseQuestionID = bq.id
LEFT JOIN 
    JQuestionANDAnswers jqa ON bq.id = jqa.baseQuestionID
WHERE 
    jqa.baseAnswerID IS NOT NULL  
    AND bq.is_question = 1
    $WHERE
GROUP BY 
    bq.id
ORDER BY 
    bq.is_featured DESC;

        
        ";

       // p ($query, 10);



    }
    else {
        $query = "SELECT * FROM BaseQuestion WHERE is_featured = 1";
    }

    // Retrieve base questions query
   
    
    // Execute query
    $result = basicQuery($this->con, '', $query, '', 2);

    //p($result, 1);
    
    // Format base questions as JSON to FrontEnd
    $json_result = json_encode($result);
    
    
    
    
    return $json_result;







}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function ItemData ($data) {


    p('START function ItemData ', 10);


    switch($data['w']) {
        case "baanser":
            $selection = 'baseAnswerID';
            $parseHtml = 1;
            $kind = "answers";
            
            break;
        
       

        case "baquest":
            $selection = 'baseQuestionID';
            $parseHtml = 1;
            $kind = "questions";
            $forAKey = 1; 
            break;
        
        default:

        $selection = 'baseAnswerID';
        break;

    }

    $baq = $this->getBAQ(array(
        $selection, $data['iid']
    ));


    $baq['forAQuestion'] = $forAKey ;

    $stats = $this->GetStats($baq, array(
        "kind" => $kind
    ) );

    if($data['w'] == 'baanser') {
        $baq['childAnswerText'] =  $baq['childAnswerText'] ;
        $baq['base_64'] = base64_encode($baq['childAnswerText']);
        $baq['bwsId'] = $baq['childAnswerID'] ;

    }else if($parseHtml) {
        $baq['base_64'] = base64_encode($baq['childWSHTML']);
       // $baq['base64id'] = 
    }


    $final = array(
        'bws' => $baq,
        'stats' => $stats
    );
    
    echo json_encode($final);
    return ;






}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function GetStats ($baq, $data = array()) {


    p("GetStats", 10);

    $final = array();

    if($data['kind'] == 'questions') {
        $q = "
       SELECT count(1) AS child_question_number 
        FROM childQuestion 
        WHERE baseQuestionID = {$baq['baseQuestionID']}
        ";

        $itemCount = basicQuery($this->con, '', $q, 10, 2);

        $itemCount = $itemCount ['child_question_number'];

        $final['itemCount'] = $itemCount;

        $q = "
        SELECT `text`, count(1) AS question_same_text 
         FROM childQuestion 
         WHERE baseQuestionID = {$baq['baseQuestionID']}
         group by `text`
         ";

         $question_same_text = basicQuery($this->con, '', $q, 0, 2);


        $final['question_same_text'] = json_encode($question_same_text);
        
    }


    if($data['kind'] == 'answers') {
        $q = "
       SELECT count(1) AS child_answer_number 
        FROM childAnswer
        WHERE baseAnswerID = {$baq['baseAnswerID']}
        ";

        $itemCount = basicQuery($this->con, '', $q, 10, 2);

        $itemCount = $itemCount ['child_answer_number'];

        $final['itemCount'] = $itemCount;

        $q = "
        SELECT `text`, count(1) AS answer_same_text 
         FROM childQuestion 
         WHERE baseQuestionID = {$baq['baseAnswerID']}
         group by `text`
         ";

         $question_same_text = basicQuery($this->con, '', $q, 0, 2);


        $final['answer_same_text'] = json_encode($question_same_text);
        
    }




   
   
   
   
   
   
    p($final, 'final in GetStats');

    
    

    return $final;






}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function AdminAnswerFinal ($data) {


    $answer = $data['pending_answer']['r'];

    $ans_txt = $this->CleanGPTMsg($answer);

    p($ans_txt, 10);

   

    $ans_txt = trim($ans_txt);



    

    $json = $this->FORMAT_DATA('format_html_admin',  $answer);

    

    echo $json;

    return;







}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function ItemUpdate($data) {
    global $con;

    p("ItemUpdate Start", 10);
    p($data, 'data');

    // Get BAQ

    // STEPS 
    // GET BAQ.. I should be able to get it off of sectionID in post body
    // BAQ can only come after switch so we know what were looking for 
    // 

    // Convert content in $data from base64 to regular
    $decodedContent = base64_decode($data['content']);
    p($decodedContent, 'Decoded Content');

    // Get the table of what we're updating by calling a switch on sectionKind
    $table = '';
    $contentField = ''; 
    $createNewCA = 0; $baq = array();
    
    switch ($data['sectionKind']) {
        case 'bwsId':
            $table = 'BaseWebSection';
            $contentField = 'childWSHTML';
            break;
        // Add other cases here if needed for different section kinds

        case 'baseAnswerIDWeb': 
            $table = 'BaseAnswer';
            $contentField = 'childAnswerText';
            $createNewCA = 1;
            $baq = $this->getBAQ(array(
                'baseAnswerID', $data['sectionId']
            ));
            
            break;

        default:
            p('Invalid section kind: ' . $data['sectionKind']);
            return ['success' => false, 'message' => 'Invalid section kind'];
    }

    // Build the update array
    $updateData = array(
        $contentField => $decodedContent, // Store the content in the appropriate field
        'last_updated' => getTime() // Use getTime() to set the current timestamp
    );

    // Define the condition for the update query
    $condition = "id = " . mres($data['sectionId']);



    $updateSuccess = 0;

    if($createNewCA) {
    
        p($baq, 'baq');


        // First see if there was a change 
        p($decodedContent, 'decodedContent');
        if(trim($baq['childAnswerText']) == trim($decodedContent)) {
            
            p("no update needed because text is similar", 10);
            $updateSuccess = 1;

        } else {

            $temp = array(
                'baseAnswerID' => $baq['baseAnswerID'],
                'childQuestionID' => $baq['childQuestionID'],
                'text' => $decodedContent,
                'session_id_created_by' => $this->sessionID
            );
    
            basicInsert('childAnswer', array($temp), 1, 0);

            $q = "select id from childAnswer where baseAnswerID = '{$baq['baseAnswerID']}' and childQuestionID = '{$baq['childQuestionID']}' order by id desc limit 1";
            $newCA = basicQuery($this->con, '', $q, 10, 2);

            $updateData = array(
                $contentField => $decodedContent, // Store the content in the appropriate field
                'last_updated' => getTime(), // Use getTime() to set the current timestamp
                'childAnswerID' =>  $newCA['id']
            );
            $updateSuccess = basicUpdate($table, $updateData, $condition, 1, 0, 1);
           

        }

        
        

       


    } else {


         // Call basicUpdate to update the content in the database
         $updateSuccess = basicUpdate($table, $updateData, $condition, 1, 0, 1);

    }


   

    // Prepare the final response
    $final = [];
    if ($updateSuccess) {
        // If update is successful
        $final = [
            'success' => true,
            'message' => 'Update successful',
            'updated_data' => $updateData
        ];
    } else {
        // If update fails
        $final = [
            'success' => false,
            'message' => 'Update failed'
        ];
    }

    // Log the final response
    p($final, 'Final Response');

    // Return and echo the JSON response
    echo json_encode($final);
    return $final;
}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function GenQuestions () {

    p('GenQuestions ()', 10);


    $q = "SELECT childQuestionText FROM BaseQuestion WHERE is_question = 1";
    $questionsDB = basicQuery($this->con, '', $q, 0, 1);
    $questionsDBJson = JE($questionsDB, 1, 1);


    $newQUESTIONNUMBER = 5;


    $prompt_admin = $this->CRUDInstructions(array('request_name' => 'gen_questions'));



    $prompt = "
   Your task is to generate questions based on the data available in your instructions and uploaded files. Focus solely on creating $newQUESTIONNUMBER new questions that are specific, singular, and have concrete, clear answers derived from the data you have been given. Each question should be between 5 to 20 words and concentrate on one specific item or issue.

   DO NOT PROVIDE QUESTIONS TO QUESTIONS THAT ARE SIMILAR to THE questions listed below. This is very important. Take your time to search through the documents I've provided you. 

    Guidelines:

    {$prompt_admin['value']}

    Format: Provide all responses exclusively in JSON format as a simple array of objects:

    [
    {\"textfordiv\": \"Your first question here\"},
    {\"textfordiv\": \"Your second question here\"},
    ...
    ]
    Note: Do not include any introductory text, explanations, or additional formatting. Only provide the JSON array with the questions.


    So now that I've instructed you, below is the list of questions that are already in my database:

    $questionsDBJson
    ";


    p($prompt, 'prompt');


    $post = array(
        'r' => 'chatmsg',
        'message' => $prompt,
        'admin' => 1,

    );

    $this->ChatAI->modelOR = 'gpt-4o';
    $this->ChatAI->modelOR = 'gpt-4o-mini';

    $this->HandleRequest($post);

    return;





}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function DashQA ($data) {


    p("start function DashQA", 10);


    // Get question 

    // Send to regular question flow


    $this->DashQA =1;

    $post = array(
        'r' => 'chatmsg',
        'message' => $data['content'],
        'admin' => 0,

    );


    // Update so it can do the normal Q/A
    $this->isAdmin = 0;
    
    $this->check_for_saved_answers = 0;

    $this->ChatAI->modelOR = 'gpt-4o-mini';

    $this->HandleRequest($post);







}//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function CRUDInstructions($data = array()) {

    if(!$data['kind'] ||  $data['kind'] == 'read') {

    
        switch($data['request_name']) {

            case "general_chat":
                $prompt_q = "
                SELECT * FROM xInstructions 
                WHERE ext1 = 'general_chat' AND is_active = 1 AND webpage_id = 1
                ORDER BY id DESC
                LIMIT 1 
                ";

                break;
                
            case "gen_questions":
                $prompt_q = "
                SELECT * FROM xInstructions 
                WHERE ext1 = 'gen_questions' AND is_active = 1 AND webpage_id = 1
                ORDER BY id DESC
                LIMIT 1 
                ";

                break;

        case "gen_temp":
                $prompt_q = "
                SELECT * FROM xInstructions 
                WHERE ext1 = 'gen_temp' AND is_active = 1 AND webpage_id = 1
                ORDER BY id DESC
                LIMIT 1 
                ";

                break;

            case "next_question":
                    $prompt_q = "
                    SELECT * FROM xInstructions 
                    WHERE ext1 = 'next_question' AND is_active = 1 AND webpage_id = 1
                    ORDER BY id DESC
                    LIMIT 1 
                    ";
    
                    break;


            default:
                return;


        }

        $promptLookUp = basicQuery($this->con, '', $prompt_q, 10, 2);



        return $promptLookUp;

   
    }




    if($data['kind'] == 'update') {


        $up = $data['data']['data'];
        $iid = mres($up['iid']);

        p($up, '$up');

        $update = array(
            'value' => $up['value'],
        );

      

        p( $update, ' $update');

        $s = basicUpdate("xInstructions", $update, " id = $iid", 1, 0, 1);

        if($s == 1) {
            $final = array("result" => 'success');
        } else {
            $final = array("result" => 'failed');
        }

        $json = $this->FORMAT_DATA('bjson', $final);

        echo $json; 

        return;
        

    }

    
    





}//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function CleanGPTMsg ($answer, $keep_array = 0) {


    if(!$keep_array) {


        $ans_txt = "";
        foreach ($answer['messages'] as $b => $a) {
            // Replace problematic characters with preg_replace or str_replace
            $clean_message = $a['message'];
            
            // Remove encoded sequences like "ã4:0â sourceã" using regex or direct replace
            $clean_message = preg_replace('/ã4:0â sourceã/', '', $clean_message);
            
    
            $clean_message = preg_replace('/【.*?】/', '', $clean_message);
    
            // Alternatively, remove any non-UTF-8 or problematic characters
            $clean_message = preg_replace('/[^\x00-\x7F]+/', '', $clean_message);
    
            
            // Optional: Remove markdown formatting (e.g., **bold**)
            $clean_message = preg_replace('/\*\*(.*?)\*\*/', '$1', $clean_message);
            
            // Append to the final answer text
            $ans_txt .= "{$clean_message}\n\n";
        }
        // Trim excess whitespace
        $ans_txt = trim($ans_txt);
    
    
        return  $ans_txt ;

    } else {


        foreach ($answer['messages'] as $b => $a) {
            // Replace problematic characters with preg_replace or str_replace
            $clean_message = $a['message'];
            
            // Remove encoded sequences like "ã4:0â sourceã" using regex or direct replace
            $clean_message = preg_replace('/ã4:0â sourceã/', '', $clean_message);
            
    
            $clean_message = preg_replace('/【.*?】/', '', $clean_message);
    
            // Alternatively, remove any non-UTF-8 or problematic characters
            $clean_message = preg_replace('/[^\x00-\x7F]+/', '', $clean_message);
    
            
            // Optional: Remove markdown formatting (e.g., **bold**)
            $clean_message = preg_replace('/\*\*(.*?)\*\*/', '$1', $clean_message);
            
            // Update the final answer array
            $answer['messages'][$b]['message'] = "{$clean_message}";
            
        }


        return $answer;




    }




}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function ItemActive ($post) {

    p("item active function", 10);

    switch($post['sectionKind']) {
        case "bwsId" :
            $baq = $this->getBAQ(array(
                'BaseWebSection', $post['sectionId']
            ));
            break;
            
        case "baseAnswerIDWeb" :
            $baq = $this->getBAQ(array(
                'baseAnswerID', $post['sectionId']
            ));
            break;

         default:
            return;
    }

    if(!$baq) {
        
        $final = array("result" => "failed", "reason" => "no baq");
        echo json_encode($final);

        return $final;
    }
  
    if($post['rExtended'] == 'deactiveItem') {
        // Decactivating item


        $update = array(
            'is_active' => 0
        );

    
        

    } else if($post['rExtended'] == 'activeItem') {
        // Decactivating item


        $update = array(
            'is_active' => 1
        );

        
        

    }


    basicUpdate("BaseAnswer", $update, "id = {$baq['baseAnswerID']}", 1, 0);
    basicUpdate("BaseQuestion", $update, "id = {$baq['baseQuestionID']}", 1, 0);


    $final = array("result" => "success");
    echo json_encode($final);

    return $final;

    




}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function ERMode ($data, $d2 = array()) {


    p("public function ERMode", 10);
    p($data, 'data');


    if($data['rExtended'] == 'er-button') {

        $update = array(
            'erMode' => $data['kind'] 
        );

        p($this->wpObj);
        $id = $this->wpObj['id'];
        $results = basicUpdate('xWebsite', $update, "id = '{$id}'", 1, 0, 1);

        if($results) {
            $json = json_encode(array("status" => 'success'));
        } else {
            $json = json_encode(array("status" => 'failed'));
        }

        echo $json;

        return json_decode($json, true);

    }

    //xWebsite






}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function NextMsg ($chatHistoryArray = array()) {


    p("NextMsg function", 10);


    $data = json_decode($chatHistoryArray, true);
    $chatHistorytxt = str_replace("<br>", "", $chatHistoryArray); 
    $current_questions = $this->DashQuestions(array('ask' => 'all_base_questions'));

    $current_questions = json_decode($current_questions, true);
    $currentQuestionsForSeach = array();
    foreach( $current_questions as $b => $a) {
        $currentQuestionsForSeach [] = array("id" => $a['id'], "childQuestionText" => $a["childQuestionText"]);

    }

    p($currentQuestionsForSeach, 'childQuestionText');

    $currentQuestionsForSeach = json_encode($currentQuestionsForSeach);

    p($data, 'data in NextMsg');

    $generalGuidelines = $this->CRUDInstructions(array('kind' => 'read', 'request_name' => 'gen_temp' ));

    $addtionalGuidelines =  $this->CRUDInstructions(array('kind' => 'read', 'request_name' => 'next_question' ));

    $prompt = "
    Your job as an AI bot is to make question recommendations based upon chat history. Below I will provide you the chat history in JSON format in CHAT_HISTORY. There are two kinds of chats: user and bot. User messages were sent by the user. Bot messages were generated by the AI. I will also provide you a list of current questions in database called CURRENT_QUESTIONS. Try your best to select from the list of these questions when thinking about which questions to recommend. The reason for this is that I already have saved responses in CURRENT_QUESTIONS. However feel free to generate some other questions as well depending on what the user is querying. Keep all question reommendations using the following guidelines. Do not break these guidelines. 

    Suggest 2-4 questions. It's okay if you only suggest 2. 

    Make sure your reply is in JSON. VERY IMPORTANT. 

Since you do not have access to the content database, assume what the content is on based upon the questions in CURRENT_QUESTIONS. 

General Guidelines: 

{$generalGuidelines['value']}

-- End General Guidlines: 

Additional Guidelines: 

{$addtionalGuidelines['value']}


CURRENT_QUESTIONS
$currentQuestionsForSeach 

CHAT_HISTORY
$chatHistorytxt


 IMPORTANT: Do not include any introductory text, explanations, or additional formatting. Only provide the JSON array with the questions. 
 
 Let the format be id = to the ID you got from the CURRENT_QUESTIONS. If this is a question that is not in the CURRENT_QUESTIONS then make this null. Let the childQuestionText = to the current text in  CURRENT_QUESTIONS. If this is a question that is not in the CURRENT_QUESTIONS then make this the value of the new question. 

 Format your responses like this:

  [
    {\"id\": 22, \"childQuestionText\": \"Sample text\"},
    {\"id\": null, \"childQuestonText\": \"Sample text of new question\"},
    ...
    ]

";


    p($prompt, 'prompt');


    // Call API using technique used in QUESTION_RECOMMENDATION
    $content = array(
        'message' =>  $prompt,
        'status' => null, 
        'r' => 'chatmsg'
    );


    $this->ChatAI->modelOR = 'gpt-4o-mini';
    $results = $this->ChatAI->ChatCompletions($content);


    p($results, '$results in CheckForSimilarQuestions');
        
        
    //$final = $this->CheckForUpdate($results, 20);
    $final = $results;

    p($final, '$final in CheckForSimilarQuestions');



    // Example JSON response with triple backticks
    $jsonResponse = $final['messages'][0]['message'];

    // Remove the triple backticks and the "json" part
    $jsonResponse = str_replace(['```json', '```'], '', $jsonResponse);

    // Trim any leading or trailing whitespace
    $jsonResponse = trim($jsonResponse);

    // Decode the JSON response into a PHP associative array
    $js_suggestions = json_decode($jsonResponse, true);
    
    p($js_suggestions, '$js_suggestions');


    p($js_suggestions);

    $final = array(
        'nextQuestions' => $js_suggestions
    );

    $final = json_encode($final);

    echo $final; 

    return $final;



}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function function_9 () {









}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function function_10 () {









}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function function_11 () {









}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function function_12 () {









}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function function_13 () {









}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function function_14 () {









}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function FORMAT_DATA ($kind, $data = array()) {

    if($kind == 'chat_reply_stored_answer') {
        
        //p($data, 10);

        $final = array(
            'status' =>"completed",
            'tid' => $data['thread_id'],
            'bid' => $data['ba_id'],
            'isa' => $data['isa'],
            'cid' => $data['childAnswerID'],
            'messages' => array(
                array(
                    'message' => $data['text'],
                    'source' => 'GPT',
                    'id' => 'msg_db_' . $data['id']
                )
            )
        );




        
        return json_encode($final);
        
        
        
    }

    if ($kind == 'format_html_admin') {

        p("format_html_admin", 10);
    
        $raw_text = $data['messages'][0]['message'];
    
        // Use a regex to detect the JSON blocks and capture everything between ```json and ```
        $pattern = '/```json(.*?)```/s';
        preg_match_all($pattern, $raw_text, $matches);
    
        // Remove the ```json blocks from the original text
        $raw_text = preg_replace($pattern, "<json-block></json-block>", $raw_text);
    
        // Start formatting with div
        $formatted_text = "<div class='bot-message'>";
    
        // Explode the raw text into lines
        $lines = explode("\n", $raw_text);
    
        $json_counter = 0; // To keep track of JSON blocks
    
        foreach ($lines as $line) {
            // Check if it's a marker for the JSON block
            if (strpos($line, '<json-block>') !== false) {
                // Add the JSON block with styling, pulling from the captured match
                $json_content = htmlentities(trim($matches[1][$json_counter]));
                $formatted_text .= "<pre class='json-code' style='margin: 0;'>$json_content</pre>";
                $json_counter++;
            } else {
                // Regular line of text
                $formatted_text .= "<p>" . htmlentities(trim($line)) . "</p>";
            }
        }
    
        // Close the div
        $formatted_text .= "</div>";
    
        // Use the p() function to print the result
        p($formatted_text, 'formatted_text');
    
        // Update the data array
        $data['messages'][0]['message_plain'] = $data['messages'][0]['message'];
        $data['messages'][0]['message'] = $formatted_text;
    
        l(3);
    
        return json_encode($data);
    }
    
    
    
    
    if($kind == 'bjson') {

        return json_encode($data);

    }



        
        
        


    




}
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//____________________________________________________________________________________________________\
//
public function HandleRequest ($post = array()) {

    $this->tt();




    // Runs logic checking if request is coming from dashboard
    p($post,'post');
    if($post['admin'] != '1') {

        // This is for basic type ahead question recommendation 
        if($post['r'] == 'qrec') {
            p('qrec', 'r');
            $this->QUESTION_RECOMMENDATION($post['message']);
        }

        // This is for when a user forces an AI lookup to happen regardless of what's in DB
        if($post['status'] == 'respid') {
            p("respid = {$post['qid']}", 10);
            $this->ForceQuestion($post['qid']);
            return;
        }
        


    

        

        if($post) {
            $this->pa = $post;
        }

        // For handling all chat submissions in the dashboard page
        if($post['r'] == 'chatmsg' && !$post['status']) {
            p('chatmsg', 'r');
            $this->CHAT_SUBISSION($post);
        }elseif ($post['status'] == 'in_progress' || $post['status'] == 'queued') { // This is true if the OpenAI Run has not been completed yet
            $this->GET_ANSWER(null, $post['status'], array("db_qid" => $post['qid'], 'post' => $post));
        }



        // Runs after chat queries 
        if($post['r'] == 'afterm' ) {
            p("post == afterm", 10);

            if($post['rExtended']  == 'nextQuestions') {
                $this->NextMsg($post['data']);
                return;
            }


            $this->AfterMessage($post);

        }


    }
    


    if($post['admin'] == 1) {


        // DASH DASH
        p("post == dash", 10);
        
        $this->isAdmin = 1;

        // Returns item data from either questions or answers 
        if($post['rExtended'] == 'clickeditem' ) {

            p("['rExtended'] == 'clickeditem'", 10);

            $this->ItemData($post);


        }

         // Activates or deactives a chat response by admin 
         if($post['rExtended'] == 'activeItem' || $post['rExtended'] == 'deactiveItem') {

            p("['rExtended'] == 'activeItem'", 10);

            $this->ItemActive($post);


        }

        
        // Update Items of questions and answers 
        if($post['rExtended'] == 'updateItem' ) {

            p("['rExtended'] == 'updateItem'", 10);

            $this->ItemUpdate($post);


        }

        // Add Question / Answer
        if($post['rExtended'] == 'addQuestion' ) {

            p("['rExtended'] == 'addQuestion'", 10);

            $this->DashQA($post);


        }
        

        // Generate random questions based upon content in OpenAI db
        if($post['rExtended'] == 'sbutton' ) {

            p("['rExtended'] == 'sbutton'", 10);

            if($post['kind'] == 'questions' ) {

                p("['kind'] == 'questions'", 10);

                $this->GenQuestions();

                return;

            }


        }



        // CRUD on Prompt Instructions
        if($post['r'] == 'update_instruction' ) {

            p("['r'] == 'update_instruction'", 10); 

            $this->CRUDInstructions(array('kind' => 'update','data' => $post));

            return;


        }

        // Activating / Deactivating Active Emergency 
        if($post['rExtended'] == 'er-button' ) {

            p("['rExtended'] == 'er-button'", 10);

            if(isset($post['kind'])) {

                $this->ERMode($post);

                return;

            }


        }


        



        // Handling frontend question submissions 
        if($post['r'] == 'chatmsg' && !$post['status']) {
            p('chatmsg', 'r');
            $this->CHAT_SUBISSION($post);
        }elseif ($post['status'] == 'in_progress' || $post['status'] == 'queued') {
            //  This is true if the OpenAI Run has not been completed yet
            $this->GET_ANSWER(null, $post['status'], array("db_qid" => $post['qid'], 'post' => $post));
        }


        // This is the function that runs after chat has been submitted and logic is used to generate the html and also determine if the submission was a question or statement
        if($post['r'] == 'afterm' ) {
            p("post == afterm", 10);
            $this->dashAftermsg = 1;
            $this->AfterMessage($post);

        }


        



    }

    
    





}



}








