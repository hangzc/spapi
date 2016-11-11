<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require '../parseservice/parse.php';

require '../vendor/autoload.php';
$config['displayErrorDetails'] = true;

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

// Game APIs
$app->post('/game/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
	if(strlen($facebookId)!=0){
		$results = gameLogin($facebookId);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid facebookId";
});

$app->post('/game/getProfile', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['facebookId'])&& isset($data['sessionToken'])){
		$facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$results = gameGetProfile($facebookId,$sessionToken);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/game/register', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['name']) && isset($data['facebookId'])){
		$name = filter_var($data['name'], FILTER_SANITIZE_STRING);
		$facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
		$results = gameRegister($name, $facebookId);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/game/editPlayerName', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['name']) && isset($data['facebookId'])&& isset($data['sessionToken'])){
		$name = filter_var($data['name'], FILTER_SANITIZE_STRING);
		$facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$results = gameEditName($facebookId,$sessionToken,$name);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/game/assignPlayerToClassroom', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['classroomCode']) && isset($data['facebookId'])&& isset($data['sessionToken'])){
		$classroomCode = filter_var($data['classroomCode'], FILTER_SANITIZE_STRING);
		$facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$results = gameAssignPlayerToClassroom($classroomCode,$facebookId,$sessionToken);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/game/getGames', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['facebookId'])&& isset($data['sessionToken'])){
		$facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$results = gameGetGames($facebookId,$sessionToken);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});


$app->post('/game/getGameQuestions', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['facebookId'])&& isset($data['sessionToken'])&& isset($data['topicId'])){
		$facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$topicId = filter_var($data['topicId'], FILTER_SANITIZE_STRING);
		$results = gameGetGameQuestions($facebookId,$sessionToken,$topicId);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/game/createGameAttempt', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['facebookId']) && isset($data['sessionToken'] )&& isset($data['selectedOptions']) && isset($data['gameId'])){
		$facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$selectedOptions = $data['selectedOptions'];
		$selectedOptions = json_decode($selectedOptions,true);
		$gameId = filter_var($data['gameId'], FILTER_SANITIZE_STRING);		
		$results = gameCreateGameAttempt($facebookId,$sessionToken,$gameId,$selectedOptions);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/game/leaveClassroom', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['classroomCode']) && isset($data['facebookId'])&& isset($data['sessionToken'])){
		$classroomCode = filter_var($data['classroomCode'], FILTER_SANITIZE_STRING);
		$facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$results = gameLeaveClassroom($classroomCode,$facebookId,$sessionToken);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/game/getTopPlayers', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['gameId']) && isset($data['facebookId'])&& isset($data['sessionToken'])){
		$gameId = filter_var($data['gameId'], FILTER_SANITIZE_STRING);
		$facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$results = gameGetTopPlayers($facebookId,$sessionToken,$gameId);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});


// Teacher Portal
$app->post('/portal/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['password'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$password = filter_var($data['password'], FILTER_SANITIZE_STRING);
		$results = portalLogIn($teacherId,$password);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/portal/changePassword', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['sessionToken']) && isset($data['oldPassword']) && isset($data['newPassword'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$oldPassword = filter_var($data['oldPassword'], FILTER_SANITIZE_STRING);
		$newPassword = filter_var($data['newPassword'], FILTER_SANITIZE_STRING);
		$results = portalChangePassword($teacherId,$sessionToken,$oldPassword,$newPassword);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/portal/getClassrooms', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['sessionToken'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$results = portalGetClassrooms($teacherId,$sessionToken);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/portal/createClassrooms', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['sessionToken'])&& isset($data['name'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$name = filter_var($data['name'], FILTER_SANITIZE_STRING);
		$results = portalCreateClassrooms($teacherId,$sessionToken,$name);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/portal/deleteClassrooms', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['sessionToken'])&& isset($data['classroomId'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$classroomId = filter_var($data['classroomId'], FILTER_SANITIZE_STRING);
		$results = portalDeleteClassrooms($teacherId,$sessionToken,$classroomId);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/portal/editClassrooms', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['sessionToken'])&& isset($data['classroomId'])&& isset($data['name'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$classroomId = filter_var($data['classroomId'], FILTER_SANITIZE_STRING);
		$name = filter_var($data['name'], FILTER_SANITIZE_STRING);
		$results = portalEditClassrooms($teacherId,$sessionToken,$classroomId,$name);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/portal/getTopics', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['sessionToken'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$results = portalGetTopics($teacherId,$sessionToken);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/portal/getStudentsScoreByTopic', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['sessionToken'])&& isset($data['topicId'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$topicId = filter_var($data['topicId'], FILTER_SANITIZE_STRING);
		if(isset($data['classroomCode'])){
			$classroomId = filter_var($data['classroomCode'], FILTER_SANITIZE_STRING);
			$results = portalGetStudentsScoreByTopic($teacherId,$sessionToken,$topicId,$classroomId);
		}
		else{
			$results = portalGetStudentsScoreByTopic($teacherId,$sessionToken,$topicId,null);
		}		
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/portal/getTopicQuestionsWithStats', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['sessionToken'])&& isset($data['topicId'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$topicId = filter_var($data['topicId'], FILTER_SANITIZE_STRING);
		$results = portalGetTopicQuestionsWithStats($teacherId,$sessionToken,$topicId);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/portal/getQuestionOptionsStats', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['sessionToken'])&& isset($data['questionId'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$questionId = filter_var($data['questionId'], FILTER_SANITIZE_STRING);
		$results = portalGetQuestionOptionsStats($teacherId,$sessionToken,$questionId);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->post('/portal/getStudentAttemptByTopic', function (Request $request, Response $response) {
    $data = $request->getParsedBody();    
	if(isset($data['teacherId']) && isset($data['sessionToken'])&& isset($data['topicId'])&& isset($data['facebookId'])){
		$teacherId = filter_var($data['teacherId'], FILTER_SANITIZE_STRING);
		$sessionToken = filter_var($data['sessionToken'], FILTER_SANITIZE_STRING);
		$topicId = filter_var($data['topicId'], FILTER_SANITIZE_STRING);
		$facebookId = filter_var($data['facebookId'], FILTER_SANITIZE_STRING);
		$results = portalGetStudentAttemptByTopic($teacherId,$sessionToken,$topicId,$facebookId);
		$response = json_encode($results);
		return $response;
	}
	else
		return "invalid request parameters";
});

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', 'http://sp.t05.sg')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->run();
