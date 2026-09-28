<?php

return [
    'required' => ':attributeを入力してください',
    'string' => ':attributeは文字列で入力してください。',
    'email' => ':attributeはメール形式で入力してください',
    'confirmed' => ':attributeと一致しません',

    'max' => [
        'string' => ':attributeは:max文字以内で入力してください。',
    ],

    'min' => [
        'string' => ':attributeは:min文字以上で入力してください',
    ],

    'exists' => '選択された:attributeは存在しません。',

    'attributes' => [
        'title' => 'タイトル',
        'description' => '説明',
        'name' => 'お名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'genres' => 'ジャンル',
        'genres.*' => 'ジャンル',
    ],
];
