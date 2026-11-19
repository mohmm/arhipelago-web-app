<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maya, the travel assistant
    |--------------------------------------------------------------------------
    |
    | One assistant shared by every traveller. What she is told to do lives in
    | the admin panel; these are the technical limits around her.
    |
    */

    // The assistant's name, as travellers and the admin panel see it: Maya is
    // only the feature's name in the code. The texts in lang/*.json and in the
    // app write the name with each language's own grammar.
    'name' => 'Andrei',

    // The provider in config/ai.php and the model Maya answers with. She calls
    // tools and returns structured replies, so the model must support both.
    'provider' => env('MAYA_PROVIDER', 'openai'),
    'model' => env('MAYA_MODEL', 'gpt-5.4-mini'),

    // Seconds to wait for a reply before telling the traveller to try again.
    'timeout' => (int) env('MAYA_TIMEOUT', 60),

    // How many earlier messages of the conversation she reads before answering.
    'history' => (int) env('MAYA_HISTORY', 20),

    // The longest message a traveller can send, in characters.
    'max_message_length' => (int) env('MAYA_MAX_MESSAGE_LENGTH', 1000),

    // How many messages one traveller may send per minute. Each one is a paid model call.
    'messages_per_minute' => (int) env('MAYA_MESSAGES_PER_MINUTE', 10),

    // Days a conversation is kept after its last message. The Privacy Policy
    // states this period, so change both together.
    'keep_conversations_for_days' => (int) env('MAYA_KEEP_CONVERSATIONS_DAYS', 365),

];
