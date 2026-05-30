<?php

return [
    'project_id' => env('FIREBASE_PROJECT_ID'),

    'credentials_path' => storage_path('app/firebase/firebase.json'),

    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',

    'endpoint' => 'https://fcm.googleapis.com/v1/projects/%s/messages:send',

    'timeout' => env('FCM_HTTP_TIMEOUT', 30),
];
