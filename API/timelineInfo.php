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
        "whisperList" => [],
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
    
     require_once "./common/mysqlConnect.php"; //database接続
        
    
    try{
            $sql = "SELECT w.whisperNo, w.userId, u.userName, w.postDate, w.content ";
            $sql .= "FROM whisper w left join user u on u.userId = w.userId ";
            $sql .= "WHERE w.userId = :userId1 ";
            $sql .= "OR w.userId in (select followUserId from follow where userId = :userId2) order by w.postDate DESC";
            
            
            $stmt = $pdo->prepare($sql);
            $stmt -> bindParam(":userId1",$userId,PDO::PARAM_STR);
            $stmt -> bindParam(":userId2",$userId,PDO::PARAM_STR);
            $stmt -> execute();

            while($row = $stmt->fetch()){
                if($row==null){
                    errResult('004');
                    exit();
                }
                array_push($response["whisperList"],[
                    "whisperNo" => $row["whisperNo"],
                    "userId" => $row["userId"],
                    "userName" => $row["userName"],
                    "postDate" => $row["postDate"],
                    "content" => $row["content"],
                    "goodFlg" => FALSE
                ]);
            }
            $stmt2=null;
            
            for($i=0; $i<count($response["whisperList"]); $i++){
                $sql = "SELECT whisperNo FROM goodInfo WHERE whisperNo = :whisperNo AND userId = :userId";
                $stmt = $pdo->prepare($sql);
                $stmt -> bindParam(":whisperNo",$response["whisperList"][$i]["whisperNo"],PDO::PARAM_STR);
                $stmt -> bindParam(":userId",$userId,PDO::PARAM_STR);
                $stmt -> execute();
                while($row = $stmt->fetch()){
                    $response["whisperList"][$i]["goodFlg"] = TRUE;
                }
                $stmt=null;
            }
        $response["result"] = "success";
    } catch (PDOException $e) {
           throw new PDOException($e->getMessage(),(int)$e->getCode());
    }    

    

    require_once './common/mysqlClose.php';    //データベース接続解除
    
    
    //レスポンスの送信
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

    
    
    
   
    