<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * 通知一覧を取得するメソッド
     *
     * @param  Request  $request：HTTPリクエスト
     * @return View：通知一覧画面ビュー
     */
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->get();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 通知を既読にするメソッド
     *
     * @param  Request  $request：HTTPリクエスト
     * @param  mixed  $id：通知ID
     * @return RedirectResponse：通知一覧画面へのリダイレクト
     */
    public function update(Request $request, $id): RedirectResponse
    {
        // ログインユーザーに紐づく通知から該当のものを取得
        $notification = $request->user()->notifications()->findOrFail($id);

        // 標準の既読メソッドを使用
        $notification->markAsRead();

        return redirect()->route('notifications.index')->with('success', '通知を既読に更新しました。');
    }
}
