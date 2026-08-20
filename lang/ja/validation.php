<?php

return [
    'required' => ':attributeを入力してください',
    'string' => ':attributeは文字列で入力してください。',
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください',
    ],
    'attributes' => [
        'title' => 'タイトル',
        'author' => '著者',
        'description' => '説明',
        'image_url' => '画像URL',
        'comment' => 'コメント',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'user' => [
            'name' => '氏名',
        ],
        'genre' => [
            'name' => 'ジャンル名',
        ],
    ],
];
