<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */
    
 //レスポンス用のデータの枠組みを用意
    $response=[
        "result" => "error", //実行結果を格納(success or error) 成功時にsuccessに書き換える
        "errCode" => null,  //エラーコードがある場合、格納する
        "errMsg" => null,   //エラーメッセージがある場合、格納する
        "userId" => "",
        "userName"=>"",
        "profile"=>"",
        "userFollowFlg"=>False,
        "followCount"=>"",
        "followerCount"=>"",
        "whisperList" => [],
        "goodList" =>[],
    ];
    
    //リクエストの解析
    if($_SERVER['REQUEST_METHOD'] === 'POST'){   //POSTか確認
        /**
	* POSTで送られてきていた場合、JSON形式のデータを取得し、配列に加工。
	* 1. file_get_contents('php://input')：HTTPリクエストのボディ部分からデータを取得(php://inputストリームを使用)
	* 2. json_decode：取得したJSON形式のデータを配列に変換
	* 3. 変数($postData)に変換した配列のデータを格納
	**/
        
        $postData = json_decode(file_get_contents('php://input'),true);
       
    }else{
        //パラメータがない場合エラーメッセージ(ユーザーID:006,パスワード:007)
        
        echo "error";
        exit();
    }
    require_once './common/errorMsgs.php';//エラーメッセージファイル読み込み
    
    //パラメータチェック
    if($postData["userId"] != ""){
        //内容があれば
        $userId = $postData["userId"];  //取得したメールアドレス代入
    }else{
        //ない場合
       errResult('006');
       exit();
    }
    
    if($postData["LoginUserId"]!= ""){
        //内容があれば
        $LoginUserId = $postData["LoginUserId"];  //取得したパスワード代入
    }else{
        //ない場合
       errResult('015');
       exit();
    }
    
    require_once "./common/mysqlConnect.php"; //database接続
        
    try{
            // 送られてきたユーザIDとパスワードと一致するデータを取得する     
            $sql = "select u.userName,u.profile, f.cnt as f_cnt, fu.cnt as fu_cnt ";
            $sql .= "from user as u join  followCntView as f  on u.userId = f.userId join followerCntView as fu on u.userId = fu.followUserId ";
            $sql .= "where u.userId = :userId";

            $stmt = $pdo->prepare($sql);   
            $stmt -> bindParam(":userId",$userId,PDO::PARAM_STR);
            $stmt -> execute();

            while($row = $stmt->fetch()){
              
                $response["userId"] = $row["userId"];
                $response["userName"] = $row["userName"];
                $response["profile"] = $row["profile"];
                $response["followCount"] = $row["f_cnt"];
                $response["followerCount"] = $row["fu_cnt"];
                
                if($row==null){
                    errResult('004');
                    exit();
                }
            }
            $stmt = null;
            // 送られてきたユーザIDとパスワードと一致するデータを取得する     
            $sql = "select count(followUserId) as cnt from follow where userId = :userId and followUserId = :LoginUserId";

            $stmt = $pdo->prepare($sql);   
            $stmt -> bindParam(":userId",$userId,PDO::PARAM_STR);
            $stmt -> bindParam(":LoginUserId",$LoginUserId,PDO::PARAM_STR);
            $stmt -> execute();

            while($row = $stmt->fetch()){
               
                if($row==null){
                    errResult('004');
                    exit();
                }else{
                    $response["userFollowFlg"]= true;
                }
            }
            $stmt=null;
            
            $sql1 = "select w.whisperNo,u.userId,u.userName,w.postdate,w.content";
            $sql1 .= " from whisper as w join user as u on w.userId = u.userId leftjoin (select useId, true as flag  from goodinfo where userId =:LoginuserId) as g on u.userId = g.userId";
            $sql1 .= " where u.userId = :userId order by w.postdate desc;";
            $stmt1 = $pdo->prepare($sql1);   
            $stmt1 -> bindParam(":userId",$userId,PDO::PARAM_STR);
            $stmt1 -> execute();

            while($row = $stmt1->fetch()){
                
                if($row==null){
                    errResult('004');
                    exit();
                }
                
                $response["whisperList"][] = [
                    
                    "userId" => $row["userId"],
                    "userName" => $row["userName"],
                    "postDate" => $row["postDate"],
                    "content" => $row["content"],
                    "goodCount" => $row["g_cnt"],
           
                ];
                if($row["flag"]!= null){
                    $response["WhisperList"]["goodFlg"]= true; 
                }else{
                    $response["WhisperList"]["goodFlg"]= false; 
                }
              
            }
            $stmt1=null;
            
            $sql2 = "select w.whisperNo,gi.userId,u.userName,w.postdate,w.content";
            $sql2 .= " from goodInfo as gi join whisper as w on gi.whisperNo = w.whisperNo join user as u on w.userId = u.userId left join (select userId, true as flag  from goodinfo where userId =:LoginUserId) as g on u.userId = g.userId";
            $sql2 .= " where gi.userId = :userId order by w.postdate desc;";
            $stmt2 = $pdo->prepare($sql2);   
            $stmt2 -> bindParam(":userId",$userId,PDO::PARAM_STR);
            $stmt2 -> bindParam(":LoginUserId",$userId,PDO::PARAM_STR);
            $stmt2 -> execute();

            while($row = $stmt2->fetch()){
                
                if($row==null){
                    errResult('004');
                    exit();
                }
                
                $response["goodList"][] = [
                    
                    "whisperNo" => $row["WhisperNo"],
                    "userId" => $row["userId"],
                    "userName" => $row["userName"],
                    "postDate" => $row["postDate"],
                    "content" => $row["content"],
           
                ];
                if($row["flag"]!= null){
                    $response["goodList"]["goodFlg"]= true; 
                }else{
                    $response["goodList"]["goodFlg"]= false; 
                }
              
            }
            $stmt2=null;
            
            
        $response["result"] = "success";
    } catch (PDOException $e) {
           throw new PDOException($e->getMessage(),(int)$e->getCode());
    }    

    $stmt2 = null;   //SQL情報クローズ

    require_once './common/mysqlClose.php';    //データベース接続解除
    
    
    //レスポンスの送信
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

    
    
    
   
    