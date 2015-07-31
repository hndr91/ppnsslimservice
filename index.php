<?php

require_once '../include/dbhandler.php';
require_once '../include/passhash.php';
require_once '../libs/slim/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

//$student_number = NULL;

$app->post('/register', function() use($app) {
	$response = array();
	
	$student_id = $app->request->post('student_id');
	$pass = $app->request->post('pass');
	
	$db = new DbHandler();
	$result = $db->createUser($student_id, $pass);
	
	if($result == USER_CREATED_SUCCESSFULLY) {
		$response["error"] = false;
		$response["message"] = "You are successfully registered";
	}
	else if($result == USER_CREATE_FAILED) {
		$response["error"] = true;
		$response["message"] = "Ooops ! Failed to register!";
	}
	else if($result == USER_ALREADY_EXISTED) {
		$response["error"] = true;
		$response["message"] = "User already existed";
	}
	
	echoRespnse(201, $response);
	
});

$app->post('/login', function() use($app) {
	
	$response = array();
	
	$student_number = $app->request->post('student_id');
	$pass = $app->request->post('pass');
	
	$db = new DbHandler();
	
	$check = $db->loginCheck($student_number, $pass);
	$res = $db->getStudentDetail($student_number);
	
	if ($check != FALSE) {
		$response["error"] = false;
		$response["student_number"] = $res["student_number"];
		$response["student_name"] = $res["student_name"];
		$response["message"] = "Login success !";
	}
	else {
		$response["error"] = true;
		$response["message"] = "Login failed !";
	}
	
	echoRespnse(200, $response);
	
});

$app->get('/student/:id', function($student_id) {
	
	$res = array();
	$db = new DbHandler();

	// fetching student detail
	$result = $db->getStudentDetail($student_id);
	
	if ($result != NULL) {
		
		$tmp = array();
		$tmp["student_number"] = $result["student_number"];
		$tmp["student_name"] = $result["student_name"];
		array_push($res, $tmp);
		echoRespnse(200, $res);
		
	}
	else {
		$res["message"] = "The requested resource doesn't exists!";
		echoRespnse(404, $res);
	}			
 });
		

/**
	-- GET ALL COURSE SCORE--
**/
$app->get('/score/:id', function($student_number) {

	$res = array();
	$db = new DbHandler();
	
	$result = $db->getAllCourseResult($student_number);
	
	while ($score = $result ->fetch_assoc()) {
		$tmp = array();
		$tmp["curriculum_subjectname"] = $score["curriculum_subjectname"];
		$tmp["score_scoreconversion"] = $score["score_scoreconversion"];
		$tmp["curriculum_credit"] = $score["curriculum_credit"];
		array_push($res,$tmp);
	}
	echoRespnse(200, $res);
	
});


/**
	-- GET STUDENT SCORE IN SPECIFIC SMT --
**/
$app->get('/scoresmt/:stu_id/:smt', function($stu_id, $smt) {
	
	//Dummy student paramater
	//Will be replace when login method is done
	//$stu_id = '6911040033';
	
	$response = array();
	$db = new DbHandler();
	
	$result = $db->getCourseSmt($stu_id, $smt);
	
	//$response["error"] = false;
	//$response["score_result"] = array();
	
	while($score = $result->fetch_assoc()) {
		$tmp = array();
		$tmp["curriculum_subjectname"] = $score["curriculum_subjectname"];
		$tmp["score_scoreconversion"] = $score["score_scoreconversion"];
		$tmp["curriculum_credit"] = $score["curriculum_credit"];
		array_push($response, $tmp);
	}
	echoRespnse(200, $response);
});

$app->get('/ips/:stu_id/:smt', function($stu_id,$smt){
	
	$response = array();
	$db = new DbHandler();
	
	$result = $db->getIps($stu_id, $smt);
	
	if ($result != NULL) {
		
		$tmp = array();
		$tmp["IPS"] = $result["IPS"];
		array_push($response, $tmp);
		echoRespnse(200, $response);
		
	}
	else {
		$res["message"] = "The requested resource doesn't exists!";
		echoRespnse(404, $res);
	}
	
});

$app->get('/ipk/:id', function($student_id){
	
	
	$response = array();
	$db = new DbHandler();
	
	$result = $db->getIpk($student_id);
	
	if ($result != NULL) {
		
		$tmp = array();
		$tmp["IPK"] = $result["IPK"];
		array_push($response, $tmp);
		echoRespnse(200, $response);
		
	}
	else {
		$res["message"] = "The requested resource doesn't exists!";
		echoRespnse(404, $res);
	}
	
});

$app->get('/jadwaldosen', function(){

	$res = array();
	$db = new DbHandler();
	
	$result = $db->getLectureSchadule();
	
	while ($schadule = $result ->fetch_assoc()) {
		$tmp = array();
		$tmp["lecturer_name"] = $schadule["lecturer_name"];
		$tmp["hour_day"] = $schadule["hour_day"];
		$tmp["room_name"] = $schadule["room_name"];
		$tmp["from"] = $schadule["from"];
		$tmp["to"] = $schadule["to"];
		array_push($res,$tmp);
	}
	echoRespnse(200, $res);
});


/** JSON Encode **/
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
 
    // setting response content type to json
    $app->contentType('application/json');
 
    echo json_encode($response);
}

$app->run();
?>