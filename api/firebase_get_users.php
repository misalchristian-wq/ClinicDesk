<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "firebase_init.php";

try {
    $users = [];

    foreach ($auth->listUsers() as $user) {
        $users[] = [
            "uid" => $user->uid,
            "displayName" => $user->displayName ?? "",
            "email" => $user->email ?? "",
            "emailVerified" => $user->emailVerified ? "Yes" : "No",
            "disabled" => $user->disabled ? "Disabled" : "Active",
            "createdAt" => $user->metadata->createdAt ? $user->metadata->createdAt->format("Y-m-d H:i:s") : "",
            "lastLoginAt" => $user->metadata->lastLoginAt ? $user->metadata->lastLoginAt->format("Y-m-d H:i:s") : ""
        ];
    }

    echo json_encode([
        "success" => true,
        "users" => $users
    ]);
} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "message" => "Firebase fetch failed: " . $e->getMessage()
    ]);
}
?>