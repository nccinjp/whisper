<?php
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
        // 送られてきたユーザIDとパスワードと一致するデータを取得する     
        $sql = "SELECT userName,profile,iconPath FROM user 
                WHERE userId=:userId  ";
        
        $stmt = $pdo->prepare($sql);   
        $stmt -> bindParam(":userId",$userId,PDO::PARAM_STR);
        $stmt -> execute();
        
        while($row = $stmt->fetch()){

            $response["list"][] = [
                "userName" => $row["userName"],
                "profile" => $row["profile"],
                "iconPath" => $row["iconPath"],
            ];
        }
        
        $response["result"]= "success";
        //データ件数が1件なら
        //var_dump(count($response["list"]));
        if(count($response["list"])==1){
            //成功パラメータセット
            $response["result"]= "success";
        }else{  
            //複数件ならエラー
            errResult('004');
        }
    
    } catch (PDOException $e) {
           throw new PDOException($e->getMessage(),(int)$e->getCode());
    }    
    $stmt = null;   //SQL情報クローズ
    
    require_once './common/mysqlClose.php';    //データベース接続解除
    
     //レスポンスの送信
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
