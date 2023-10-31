<?php

// ERRCODE LIST
$errorList = array(
    "001" => "データベース処理が異常終了しました。",
    "002" => "変更内容がありません。",
    "003" => "ユーザーIDまたはパスワードが違います。",
    "004" => "対象データが見つかりませんでした",
    "005" => "ささやき内容がありません",
    "006" => "ユーザIDが指定されていません。",
    "007" => "パスワードが指定されていません。",
    "008" => "ささやき管理番号が指定されていません",
    "009" => "検索区分が指定されていません",
    "010" => "検索文字列が指定されていません。",
    "011" => "ユーザー名が指定されていません",
    "012" => "フォローユーザが指定されていません",
    "013" => "フォローフラグが指定されていません",
    "014" => "イイねフラグが指定されていません",
    "015" => "ログインユーザIDが指定されていません。",
    "016" => "検索区分が不正です",
);

// Response
function errResult($errCode) {
    global $errorList;

    // ERRORE
    if (array_key_exists($errCode, $errorList)) {
        // 
        $errMsg = $errorList[$errCode];
    } else {
        // 如果错误代码未定义，返回默认错误消息
        $errMsg = "見つかりません";
    }

    // 创建包含错误代码和错误消息的数组
    $errData = array(
        $response["errCode"] => $errCode,
        $response["errMsg"] => $errMsg
    );

    // 将 $errData 转换为 JSON 格式并发送响应
   // header('Content-Type: application/json');
    return json_encode($errData, JSON_UNESCAPED_UNICODE);
  
}
