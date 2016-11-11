<?php

require 'vendor/autoload.php'; 
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseClient;
use Parse\ParseUser;
ParseClient::initialize('ErqyPBHQ3prx0MYvB65SXo0VJTOume0maoWqp5vD','gj1EBRkb8hU5gsHEHaWGT2RxeWYzJGOb5xwiKTLn', '', 'pwKvmJUiH11vSzO5LOCn1z0nF2NziHypFNSoN5Ut',true);
ParseClient::setServerURL('https://parseapi.back4app.com/');

function gameLogin($facebookId){
	$query = new ParseQuery("Player");
	$query->equalTo("facebookId", $facebookId);
	$query->includeKey("Classroom");
	$results = $query->find();
	if(count($results)>0){
		$results[0]->set("sessionToken",md5(uniqid(rand(), true)));
		$results[0]->save();			
		$post_data = array(
						'facebookId' => $results[0]->get("facebookId"),
						'name' => $results[0]->get("name"),
						'sessionToken' => $results[0]->get("sessionToken")
						);
		return $post_data;
	}
	else{
		return array('msg' => "not found" );		
	}
}

function gameRegister($name,$facebookId){
	$query = new ParseQuery("Player");
	$query->equalTo("facebookId", $facebookId);
	$results = $query->find();
	if(count($results)>0){
		return array('msg' => "user already exists!" );
	}
	else{
		$object = ParseObject::create("Player");
		$object->set("name", $name);
		$object->set("facebookId",$facebookId);
		$object->set("sessionToken",md5(uniqid(rand(), true)));
		$object->save();
		return array('token' => $object->get("sessionToken"),'msg' =>true);
	}
}

function gameGetProfile($facebookId,$sessionToken){
	$query = new ParseQuery("Player");
	$query->equalTo("facebookId", $facebookId);
	$query->includeKey("Classroom");
	$results = $query->find();
	if(count($results)>0){
		$post_data = array(
						'name' => $results[0]->get("name"),
						'classroomCode'=> $results[0]->get("Classroom") == null ?'null':$results[0]->get("Classroom")->getObjectId(),
						'classroomName'=> $results[0]->get("Classroom") == null ?'null':$results[0]->get("Classroom")->get("name")
					  );
		return $post_data;
	}
	else{
		return array('msg' => "not found" );		
	}
}

function gameEditName($facebookId,$sessionToken,$name){
	$query = new ParseQuery("Player");
	$query->equalTo("facebookId", $facebookId);
	$results = $query->find();
	if(count($results)>0){
		if( $sessionToken == $results[0]->get("sessionToken")){
			$results[0]->set("name",$name);
			$results[0]->save();
			$post_data = array(
				'facebookId' => $results[0]->get("facebookId"),
				'name' => $results[0]->get("name"),
				'sessionToken' => $results[0]->get("sessionToken")
			  );
			return $post_data;
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "player not found";
	}
}

function gameAssignPlayerToClassroom($classroomCode,$facebookId,$sessionToken){
	$query = new ParseQuery("Player");
	$query->equalTo("facebookId", $facebookId);
	$results = $query->find();
	if(count($results)>0){		
		if( $sessionToken == $results[0]->get("sessionToken")){
			$classroomQuery = new ParseQuery("Classroom");
			$classroomQuery->equalTo("objectId",$classroomCode);
			$classroomResults = $classroomQuery->find();
			if(count($classroomResults)>0){
				$results[0]->set("Classroom",$classroomResults[0]);
				$results[0]->save();
				return array(
					"classroomCode"=>$classroomResults[0]->getObjectId(),
					"classroomName"=>$classroomResults[0]->get("name")
					);
			}
			else{
				return "classroom not found";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "player not found";
	}
}

function gameGetGames($facebookId,$sessionToken){
	$query = new ParseQuery("Player");
	$query->equalTo("facebookId", $facebookId);
	$results = $query->find();
	if(count($results)>0){		
		if( $sessionToken == $results[0]->get("sessionToken")){
			$gameQuery = new ParseQuery("Game");
			$gameQuery->includeKey("Topic");
			$games = $gameQuery->find();
			if(count($gameQuery)>0){
				$gameArray = [];
				for($i =0 ; $i<count($games);$i++ ){
					$currentGame = array(
						'gameId' => $games[$i]->getObjectId(),
						'name' => $games[$i]->get("name"),
						'description' => $games[$i]->get("description"),
						'topic' => $games[$i]->get("Topic")->get("name"),
						'topicId'=> $games[$i]->get("Topic")->getObjectId()
					  );
					array_push($gameArray, $currentGame);
				}
				return $gameArray;
			}
			else{
				return "no games found";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "player not found";
	}
}

function gameGetGameQuestions($facebookId,$sessionToken,$topicId){
	$query = new ParseQuery("Player");
	$query->equalTo("facebookId", $facebookId);
	$results = $query->find();
	if(count($results)>0){		
		if( $sessionToken == $results[0]->get("sessionToken")){
			$questionQuery = new ParseQuery("Question");	
			$questionQuery->includeKey("Topic");
			$questionQuery->equalTo("Topic",['__type' => "Pointer", 'className'=> "Topic", 'objectId' => $topicId]);
			$questions = $questionQuery->find();
			if(count($questionQuery)>0){
				$questionArray = [];
				for($i =0 ; $i<count($questions);$i++ ){
					$optionsQuery = new parseQuery("Option");
					$optionsQuery->equalTo("Question",$questions[$i]);
					$options = $optionsQuery->find();
					$optionsArr = [];
					for($j=0;$j<count($options);$j++){
						$currentOption = array(
						'optionText' => $options[$j]->get("optionText"),
						'isCorrect' => $options[$j]->get("isCorrect"),
						'optionId' => $options[$j]->getObjectId()
					  );
						array_push($optionsArr, $currentOption);
					}
					$currentQuestion = array(
						'questionId' => $questions[$i]->getObjectId(),
						'questionText' => $questions[$i]->get("questionText"),
						'options' => $optionsArr
					  );
					array_push($questionArray, $currentQuestion);
				}
				return $questionArray;
			}
			else{
				return "no questions found";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "player not found";
	}
}

function gameCreateGameAttempt($facebookId,$sessionToken,$gameId,$selectedOptions){
	$query = new ParseQuery("Player");
	$query->equalTo("facebookId", $facebookId);
	$results = $query->find();
	$score = 0;
	if(count($results)>0){		
		if( $sessionToken == $results[0]->get("sessionToken")){
			$attempt = ParseObject::create("Attempt");
			$attempt->set("Player", $results[0]);
			//$attempt->set("Game",['__type' => "Pointer", 'className'=> "Game", 'objectId' => $gameId]);
			$attempt->setAssociativeArray("Game", array('__type' => 'Pointer', 'className' => 'Game', 'objectId' => $gameId));
			$attempt->save();
			$question_attempt_array = [];
			for($i=0;$i<count($selectedOptions);$i++){
				$question_attempt = ParseObject::create("Question_Attempt");
				$optionQuery = new ParseQuery("Option");
				$optionQuery->includeKey("Question");
				try {
				  $option = $optionQuery->get($selectedOptions[$i]);
				  // The object was retrieved successfully.
				} catch (ParseException $ex) {
				  return "invalid option id at index ". $i;
				}
				$question_attempt->set("Attempt",$attempt);
				$question_attempt->set("Question",$option->get("Question"));
				$question_attempt->set("selectedChoice",$option);
				if($option->get("isCorrect"))
					$score++;
				array_push($question_attempt_array, $question_attempt);
			}
			ParseObject::saveAll($question_attempt_array);
			$score =  $score * 10;
			$attempt->set("score",(string)$score);
			$attempt->save();
			return "success";
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "player not found";
	}
}

function gameLeaveClassroom($classroomCode,$facebookId,$sessionToken){
	$query = new ParseQuery("Player");
	$query->equalTo("facebookId", $facebookId);
	$query->equalTo("Classroom",['__type' => "Pointer", 'className'=> "Classroom", 'objectId' => $classroomCode]);
	$query->includeKey("Classroom");
	$results = $query->find();
	if(count($results)>0){		
		if( $sessionToken == $results[0]->get("sessionToken")){
			if($results[0]->get("Classroom")->getObjectId()==$classroomCode){
				$results[0]->delete("Classroom");
				$results[0]->save();
				return "success";
			}
			else{
				return "classroom not found";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "player not found || not enrolled";
	}
}

function gameGetTopPlayers($facebookId,$sessionToken,$gameId){
	$query = new ParseQuery("Player");
	$query->equalTo("facebookId", $facebookId);
	$results = $query->find();
	if(count($results)>0){		
		if( $sessionToken == $results[0]->get("sessionToken")){
			$gameQuery = new ParseQuery("Game");
			$gameQuery->equalTo("objectId",$gameId);
			$gameQuery->includeKey("Topic");
			$game = $gameQuery->find();
			if(count($game)>0){
				$attemptQuery = new ParseQuery("Attempt");
				$attemptQuery->equalTo("Game",$game[0]);
				$attemptQuery->descending("createdAt","Score");
				$attemptQuery->includeKey("Player");
				$attempts = $attemptQuery->find();
				$topPlayers = [];
				for($i=0;$i<count($attempts);$i++){
					if(count($topPlayers)>=5)
						break;
					$playerFound = false;
					for($j=0;$j<count($topPlayers);$j++){
						if($topPlayers[$j]['facebookId']==$attempts[$i]->get("Player")->get("facebookId")){
							$playerFound = true;
							break;
						}
					}
					if($playerFound == false){
						$currentPlayer = array(
							'facebookId' => $attempts[$i]->get("Player")->get("facebookId"),
							'name' => $attempts[$i]->get("Player")->get("name"),
							'playerScore'=>$attempts[$i]->get("score")
						  );
					array_push($topPlayers, $currentPlayer);
					}
				}
				//sort($topPlayers);
				usort($topPlayers,"cmp");
				return array_reverse($topPlayers);
			}
			else{
				return "invalid gameId";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "player not found || not enrolled";
	}
}

//Portal Codes

function portalLogIn($teacherId,$password){
	try {
	  $user = ParseUser::logIn($teacherId,$password);
	  $user->set("sessionString",md5(uniqid(rand(), true)));
	  $user->save();
	  return $user->get("sessionString");
	} catch (ParseException $error) {
	  return "invalid credentials";
	}
}

function portalChangePassword($teacherId,$sessionToken,$oldPassword,$newPassword){
	try {
	  $user = ParseUser::logIn($teacherId,$oldPassword);
	  $user->set("password",$newPassword);
	  $user->save();
	  return "success";
	} catch (ParseException $error) {
	  return "invalid credentials";
	}
}

function portalGetClassrooms($teacherId,$sessionToken){
	$query = new ParseQuery($class = '_User');
	$query->equalTo("username", $teacherId);
	$results = $query->find();
	$classroomArray = [];
	if(count($results)>0){	
		//return $results[0];
		if( $sessionToken == $results[0]->get("sessionString")){
			$classroomQuery = new ParseQuery("Classroom");
			$classroomQuery->equalTo("User",$results[0]);
			$classrooms = $classroomQuery->find();
			if(count($classrooms)>0){
				for($i=0;$i<count($classrooms);$i++){
					$currentClassroom = array(
							'classroomId' => $classrooms[$i]->getObjectId(),
							'name' => $classrooms[$i]->get("name")
						  );
					array_push($classroomArray, $currentClassroom);
				}				
				return $classroomArray;
			}
			else{
				return "no classrooms";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "teacher not found";
	}
}

function portalCreateClassrooms($teacherId,$sessionToken,$name){
	$query = new ParseQuery($class = '_User');
	$query->equalTo("username", $teacherId);
	$results = $query->find();
	if(count($results)>0){	
		if( $sessionToken == $results[0]->get("sessionString")){
			$object = ParseObject::create("Classroom");
			$object->set("name", $name);
			$object->set("User",$results[0]);
			$object->save();
			return 'success';
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "teacher not found";
	}
}

function portalDeleteClassrooms($teacherId,$sessionToken,$classroomId){
	$query = new ParseQuery($class = '_User');
	$query->equalTo("username", $teacherId);
	$results = $query->find();
	$toSaveArray = [];
	if(count($results)>0){	
		if( $sessionToken == $results[0]->get("sessionString")){
			$classroomQuery = new ParseQuery("Classroom");
			$classroomQuery->equalTo("User",$results[0]);
			$classroomQuery->equalTo("objectId",$classroomId);
			$classroomResult = $classroomQuery->find();
			if(count($classroomResult)>0){
				$playerQuery = new ParseQuery("Player");
				$playerQuery->equalTo("Classroom",$classroomResult[0]);
				$affectedPlayers = $playerQuery->find();
				if(count($affectedPlayers)>0){
					for($i=0;$i<count($affectedPlayers);$i++){
						$affectedPlayers[$i].delete("Classroom");
						array_push($toSaveArray, $affectedPlayers[$i]);
					}
					ParseObject::saveAll($toSaveArray);
				}
				$classroomResult[0]->destroy();
				return "success";
			}
			else{
				return "invalid classroomId";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "teacher not found";
	}
}

function portalEditClassrooms($teacherId,$sessionToken,$classroomId,$name){
	$query = new ParseQuery($class = '_User');
	$query->equalTo("username", $teacherId);
	$results = $query->find();
	if(count($results)>0){	
		if( $sessionToken == $results[0]->get("sessionString")){
			$classroomQuery = new ParseQuery("Classroom");
			$classroomQuery->equalTo("User",$results[0]);
			$classroomQuery->equalTo("objectId",$classroomId);
			$classroomResult = $classroomQuery->find();
			if(count($classroomResult)>0){
				$classroomResult[0]->set("name",$name);
				$classroomResult[0]->save();
				return "success";
			}
			else{
				return "invalid classroomId";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "teacher not found";
	}
}

function portalGetTopics($teacherId,$sessionToken){
	$query = new ParseQuery($class = '_User');
	$query->equalTo("username", $teacherId);
	$results = $query->find();
	$topicArray = [];
	if(count($results)>0){
		if( $sessionToken == $results[0]->get("sessionString")){
			$topicQuery = new ParseQuery("Topic");
			$topics = $topicQuery->find();
			if(count($topics)>0){
				for($i=0;$i<count($topics);$i++){
					$currentTopic = array(
							'topicId' => $topics[$i]->getObjectId(),
							'name' => $topics[$i]->get("name")
						  );
					array_push($topicArray, $currentTopic);
				}				
				return $topicArray;
			}
			else{
				return "no topics";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "teacher not found";
	}
}

function portalGetStudentsScoreByTopic($teacherId,$sessionToken,$topicId,$classroomId){
	$query = new ParseQuery($class = '_User');
	$query->equalTo("username", $teacherId);
	$results = $query->find();
	if(count($results)>0){
		if( $sessionToken == $results[0]->get("sessionString")){
			//get all my students
			$playerQuery = new ParseQuery("Player");
			$classroomQuery = new ParseQuery("Classroom");
			$classroomQuery->equalTo("User",$results[0]);
			if($classroomId !=null){
				$classroomQuery->equalTo("objectId",$classroomId);
			}
			$playerQuery->matchesQuery("Classroom",$classroomQuery);
			$attemptQuery = new ParseQuery("Attempt");
			$attemptQuery->matchesQuery("Player",$playerQuery);
			$topicQuery = new ParseQuery("Topic");
			$topicQuery->equalTo("objectId",$topicId);
			$gameQuery = new ParseQuery("Game");
			$gameQuery->matchesQuery("Topic",$topicQuery);
			$attemptQuery->matchesQuery("Game",$gameQuery);		
			$attemptQuery->descending("createdAt");
			$attemptQuery->includeKey("Player");
			$attempts = $attemptQuery->find();
			$studentScoreArr = [];
			if(count($attempts)>0){
				for($i=0;$i<count($attempts);$i++){	
					$studentFound = false;				
					$currentAttempt = array(
							'facebookId'=>$attempts[$i]->get("Player")->get("facebookId"),
							'studentName' => $attempts[$i]->get("Player")->get("name"),
							'score' => $attempts[$i]->get("score")
						  );
					for($j=0;$j<count($studentScoreArr);$j++){
						if($studentScoreArr[$j]['facebookId'] == $currentAttempt['facebookId']){
							$studentFound = true;
							break;
						}
					}
					if($studentFound == false)
						array_push($studentScoreArr, $currentAttempt);
				}
				return $studentScoreArr;
			}
			else{
				return "no attempts found";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "teacher not found";
	}
}

function portalGetTopicQuestionsWithStats($teacherId,$sessionToken,$topicId){
	$query = new ParseQuery($class = '_User');
	$query->equalTo("username", $teacherId);
	$results = $query->find();
	if(count($results)>0){
		if( $sessionToken == $results[0]->get("sessionString")){
			//get all my students
			$playerQuery = new ParseQuery("Player");
			$classroomQuery = new ParseQuery("Classroom");
			$classroomQuery->equalTo("User",$results[0]);
			$playerQuery->matchesQuery("Classroom",$classroomQuery);//all students from my classes
			$attemptQuery = new ParseQuery("Attempt");
			$attemptQuery->matchesQuery("Player",$playerQuery);//all attempts of all my students
			$topicQuery = new ParseQuery("Topic");
			$topicQuery->equalTo("objectId",$topicId);
			$gameQuery = new ParseQuery("Game");
			$gameQuery->matchesQuery("Topic",$topicQuery);
			$attemptQuery->matchesQuery("Game",$gameQuery);		
			$attemptQuery->descending("createdAt");
			$attemptQuery->includeKey("Player");
			$attemptQuery->includeKey("Game");
			$attemptQuery->includeKey("Game.Topic");
			$attempts = $attemptQuery->find();
			$latestAttempts = [];
			if(count($attempts)>0){
				for($i=0;$i<count($attempts);$i++){
					if(!in_array($attempts[$i], $latestAttempts)){
						array_push($latestAttempts, $attempts[$i]);
					}
				}
				$topicName = $attempts[0]->get("Game")->get("Topic")->get("name");
				
				$questionAttemptQuery = new ParseQuery("Question_Attempt");
				$questionAttemptQuery->containedIn("Attempt", $latestAttempts);
				$questionAttemptQuery->includeKey("Question");
				$questionAttemptQuery->includeKey("selectedChoice");
				$questionAttempts = $questionAttemptQuery->find();
				$questionStats = [];
				for($j=0;$j<count($questionAttempts);$j++){
					$questionFound = false;
					for($k=0;$k<count($questionStats);$k++){
						if($questionStats[$k]['questionId'] == $questionAttempts[$j]->get("Question")->getObjectId()){
							$questionFound = true;
							break;
						}
					}
					if($questionFound == false){// new question
						$newQuestion = array(
							'questionId'=>$questionAttempts[$j]->get("Question")->getObjectId(),
							'questionText' => $questionAttempts[$j]->get("Question")->get("questionText"),
							'noStudentsAttempted' => 1,
							'noOfCorrect' => $questionAttempts[$j]->get("selectedChoice")->get("isCorrect")==true?1:0,
							'percentageCorrect' => 0
						  );
						array_push($questionStats, $newQuestion);
					}
					else{ // question already in question stats array
						for($l = 0 ; $l<count($questionStats);$l++){
							if($questionAttempts[$j]->getObjectId() == $questionStats[$l]['questionId']){
								$questionStats[$l]['noStudentsAttempted']++;
								if($questionAttempts[$j]->get("selectedChoice")->get("isCorrect")==true)
									$questionStats[$l]['noOfCorrect']++;
							}
						}
					}
				}
				for($m = 0; $m<count($questionStats);$m++){
					$percentage = ($questionStats[$m]['noOfCorrect']/$questionStats[$m]['noStudentsAttempted'])*100.0;
					$percentage = number_format($percentage, 2, '.', '');
					$questionStats[$m]['percentageCorrect']= $percentage;
				}
				$returnArray = array(
						'topicId'=>$topicId,
						'topicName'=>$topicName,
						'questionStats'=>$questionStats
				);
				return $returnArray;
			}
			else{
				return "no attempts found";
			}
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "teacher not found";
	}
}

function portalGetQuestionOptionsStats($teacherId,$sessionToken,$questionId){
	$query = new ParseQuery($class = '_User');
	$query->equalTo("username", $teacherId);
	$results = $query->find();
	if(count($results)>0){
		if( $sessionToken == $results[0]->get("sessionString")){
			$playerQuery = new ParseQuery("Player");
			$classroomQuery = new ParseQuery("Classroom");
			$classroomQuery->equalTo("User",$results[0]);
			$playerQuery->matchesQuery("Classroom",$classroomQuery);// get all students from my classes
			$attemptQuery = new ParseQuery("Attempt");
			$attemptQuery->matchesQuery("Player",$playerQuery); // get all attempts by my students
			$questionQuery = new ParseQuery("Question");//get topic of input question
			$questionQuery->equalTo("objectId",$questionId);
			$questionQuery->includeKey("Topic");
			$question = $questionQuery->find();			
			$topic = $question[0]->get("Topic");
			$gameQuery = new ParseQuery("Game");
			$gameQuery->equalTo("Topic",$topic);//get game of that topic
			$attemptQuery->matchesQuery("Game",$gameQuery);//match attempts of that game
			$attemptQuery->includeKey("Player");
			$attemptQuery->descending("createdAt");
			$attempts = $attemptQuery->find();
			//get all the latest attempts
			$latestAttemptArr = [];
			if(count($attempts)>0){
				for($i=0;$i<count($attempts);$i++){	
					$studentFound = false;	
					for($j=0;$j<count($latestAttemptArr);$j++){
						if($latestAttemptArr[$j]->get("Player")->getObjectId() == $attempts[$i]->get("Player")->getObjectId()){
							$studentFound = true;
							break;
						}
					}
					if($studentFound == false)
						array_push($latestAttemptArr, $attempts[$i]);
				}
			}
			else{
				return "no attempts found";
			}			
			//prepare return array of question option stats
			$optionsQuery = new ParseQuery("Option");
			$optionsQuery->equalTo("Question",$question[0]);
			$options = $optionsQuery->find();
			$optionStatsArr = array(
							'questionId'=>$question[0]->getObjectId(),
							'questionText' => $question[0]->get("questionText"),
							'optionArray' =>[],
							'noStudentsAnswered' =>0
						  );
			for($k=0;$k<count($options);$k++){
				$currentOption = array(
									'optionId'=>$options[$k]->getObjectId(),
									'optionText' => $options[$k]->get("optionText"),
									'isCorrect' => $options[$k]->get("isCorrect"),
									'chosenFrequency'=> 0
								  );
				array_push($optionStatsArr['optionArray'],$currentOption);
			}
			//query of questionattempt containedIn the latest attempts
			$questionAttemptQuery = new ParseQuery("Question_Attempt");
			$questionAttemptQuery->containedIn("Attempt", $latestAttemptArr);
			$questionAttemptQuery->equalTo("Question",['__type' => "Pointer", 'className'=> "Question", 'objectId' => $questionId]);
			$latestQuestionAttempts = $questionAttemptQuery->find();
			for($l=0;$l<count($latestQuestionAttempts);$l++){
				$optionStatsArr['noStudentsAnswered']++;
				for($m=0;$m<count($optionStatsArr['optionArray']);$m++){
					if($optionStatsArr['optionArray'][$m]['optionId']==$latestQuestionAttempts[$l]->get("selectedChoice")->getObjectId())
						$optionStatsArr['optionArray'][$m]['chosenFrequency']++;
				}
				
			}
			return $optionStatsArr;
		}
		else{
			return "invalid session token. please relogin.";
		}
	}
	else{
		return "teacher not found";
	}
}

function portalGetStudentAttemptByTopic($teacherId,$sessionToken,$topicId,$facebookId){
	$query = new ParseQuery($class = '_User');
	$query->equalTo("username", $teacherId);
	$results = $query->find();
	if(count($results)>0){
		if( $sessionToken == $results[0]->get("sessionString")){
			$attemptQuery = new ParseQuery("Attempt");
			$playerQuery = new ParseQuery("Player");
			$gameQuery = new ParseQuery("Game");
			$topicQuery = new ParseQuery("Topic");
			$playerQuery->equalTo("facebookId",$facebookId);
			$topicQuery->equalTo("objectId",$topicId);
			$topic = $topicQuery->find();
			$player = $playerQuery->find();
			if(count($topic)>0){
				$gameQuery->equalTo("Topic",$topic[0]);
				$game = $gameQuery->find();
				if(count($game)>0){
					if(count($player)>0){
						$attemptQuery->equalTo("Player",$player[0]);
						$attemptQuery->equalTo("Game",$game[0]);
						$attemptsArr = $attemptQuery->find();
						if(count($attemptsArr)>0){
							$returnArray = [];
							for($i=0;$i<count($attemptsArr);$i++){
								$currentAttempt = array(
									'attemptId'=>$attemptsArr[$i]->getObjectId(),
									'score' => $attemptsArr[$i]->get("score"),
									'dateTime' => formatDateTime($attemptsArr[$i]->getCreatedAt())
								  );
								  array_push($returnArray,$currentAttempt);								  
							}
							return $returnArray;
						}
						else{
							return "no attempts found!";
						}
					}
					else{
						return 'invalid facebookId';
					}
				}
				else{
					return "no game from input topicId found!";
				}
			}
			else{
				return "invalid topicId";
			}
		}
		else{
			return "invalid sessionToken";
		}
	}
}

function formatDateTime($datetimeobj){
	$datetimeobj->setTimezone(new DateTimeZone('Asia/Singapore'));
	return $datetimeobj->format('d-m-Y H:i:s');
}

function cmp($a, $b) {
   return $a['playerScore'] - $b['playerScore'];
}



