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
    
    if($postData["followUserId"] != "" ){
        $followUserId = $postData["followUserId"];  
    }else{
       errResult('012');
       exit();
    }
    
    if($postData["followFlg"] != "" ){
        $followFlg = $postData["followFlg"];  
    }else{
       errResult('013');
       exit();
    }

    require_once "./common/mysqlConnect.php";  //database接続
        
    try{
        $pdo->beginTransaction();
        
        //３．フォローフラグがtrue(フォローする)の場合、以下の処理を行う。。
        if($followFlg == "True"){
            //３－１．フォローデータを挿入するSQL文を実行する。
            //登録テーブル：フォロー情報。
            //登録データ：inputパラーメータのユーザID、フォローユーザID
            $sql = "INSERT INTO follow(userId , followUserId) VALUES(:userId , :followUserId );";
            echo($sql);
             
            $stmt = $pdo->prepare($sql);   
            $stmt -> bindParam(":userId",$userId,PDO::PARAM_STR);
            $stmt -> bindParam(":followUserId",$followUserId, PDO::PARAM_STR);
         
        }
        
        //４．フォローフラグがfalse(フォロー外す)の場合、以下の処理を行う。
       if($followFlg == "False"){
            //４－１．フォローデータを削除するSQL文を実行する。
            //削除テーブル：フォロー情報
            //登録データ：inputパラーメータのユーザID、フォローユーザID
            $sql = "DELETE FROM follow WHERE userId = :userId AND followUserId = :followUserId ";
            echo($sql);
             
            $stmt = $pdo->prepare($sql);   
            $stmt -> bindParam(":userId",$userId, PDO::PARAM_STR);
            $stmt -> bindParam(":followUserId",$followUserId, PDO::PARAM_STR);
            
        }
        
        //５．データベースのコミット命令を実行する。
        if ($stmt -> execute() !== false) { // SQL文を実行し、結果チェックする
            echo "SQL文、実行完了 ";
            $pdo->commit(); // 成功したらコミット
            $response["result"]= "success";
        } else {
            echo "SQL文、実行失敗 " . $pdo->errorInfo()[2];
            errReuslt('001');
            $pdo->ROLLBACK(); // 失敗したらロールバック
            exit();
        }
        
        
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(),(int)$e->getCode());
    }    
    //7.SQL情報クローズ
    $stmt = null;   
    
    require_once './common/mysqlClose.php';    //8.データベース接続解除
    
    //9.レスポンスの送信
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
