<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class NotificationController extends Controller
{
    // 通知一覧を取得するメソッド
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->get();
        return view('notifications.index', compact('notifications'));
    }

    // 通知を既読にするメソッド
    public function update(Request $request, $id)
    {
        // ログインユーザーに紐づく通知から該当のものを取得
        $notification = $request->user()->notifications()->findOrFail($id);

        // 標準の既読メソッドを使用
        $notification->markAsRead();

        return redirect()->route('notifications.index')->with('success', '通知を既読に更新しました。');
    }
}
