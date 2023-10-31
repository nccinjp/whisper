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
        "list" => [],
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
        //パラメータがない場合エラーメッセージ
        echo "error";
        exit();
    }
    require_once './common/errorMsgs.php';//エラーメッセージファイル読み込み
    
    // パラメータの必須チェックを行う。
    if($postData["userId"] != "" ){
        //内容があれば
        $userId = $postData["userId"];  //取得したメールアドレス代入
    }else{
       errResult('006');
       exit();
    }
    
    
    try{
        //３．フォローリストを取得するSQL文を実行する。
        //対象列：userID、userName、whisperCount、followCount、followerCount　※データがNULLの時は0を取得すること
        //対象テーブル：follow、user、whisperCntView、followCntView、followerCntView
        //取得対象：inputパラメータのユーザIDと一致するデータ
        $sql = "sselect u.userId,u.userName,ifNull(wc.cnt,0) AS whisperCount ,ifNull(fc.cnt,0) AS followCount,ifNull(fwc.cnt,0) AS followerCount";
	$sql .=" from user as u join whisperCntView as wc on u.userId = wc.userId join followCntView as fc on u.userId= fc.userId join followerCntView as fwc on u.userId = fwc.followUserId";
	$sql .= " Where u.userId = :userId;'";
        
        
        $stmt = $pdo->prepare($sql);   
        $stmt -> bindParam(":userId",$userId,PDO::PARAM_STR);
        $stmt -> execute();
        
        while($row = $stmt->fetch()){
              
            $response["followlist"][] = [
                "userId" => $row["userId"],
                "userName" => $row["userName"],
                "whisperCount" => $row["whisperCount"],
                "followCount" => $row["followCount"],
                "followerCount" => $row["followerCount"],    
            ];
    
        }
        $stmt = null;   // 5.SQL情報クローズ
        
        $sql2 = "select f.userId,u.userName,ifNull(wc.cnt,0) AS whisperCount ,ifNull(fc.cnt,0) AS followCount,ifNull(fwc.cnt,0) AS followerCount";
	$sql2 .=" from follow as f join user as u on u.userId = f.followUserId join whisperCntView as wc on u.userId = wc.userId join followCntView as fc on u.userId= fc.userId join followerCntView as fwc on u.userId = fwc.followUserId";
	$sql2 .=" Where f.userId = :userId;";
        
        
        $stmt2 = $pdo->prepare($sql2);   
        $stmt2 -> bindParam(":userId",$userId,PDO::PARAM_STR);
        $stmt2 -> execute();
        
        while($row = $stmt2->fetch()){
              
            $response["followlist"][] = [
                "userId" => $row["userId"],
                "userName" => $row["userName"],
                "whisperCount" => $row["whisperCount"],
                "followCount" => $row["followCount"],
                "followerCount" => $row["followerCount"],    
            ];
        }
        $stmt2 = null;   // 5.SQL情報クローズ
        
    }catch (PDOException $e) {
           throw new PDOException($e->getMessage(),(int)$e->getCode());
    }    
    
    require_once './common/mysqlClose.php';    //データベース接続解除
     //レスポンスの送信
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
    
    