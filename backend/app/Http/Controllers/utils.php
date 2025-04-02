<?php

function responseMessage($success, $statusCode, $message, $data = null)
{
    return response()->json([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ], $statusCode);
}
