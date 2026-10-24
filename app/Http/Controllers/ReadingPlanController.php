<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\IndexReadingPlanRequest;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;

class ReadingPlanController extends Controller
{
    // PG15 読書計画一覧
    public function index(IndexReadingPlanRequest $request)
    {
        // リクエストから status の値を取得
        $status = $request->input('status');

        // ログインユーザーの読書計画を取得（必要に応じて status で絞り込み）
        $readingPlans = ReadingPlan::where('user_id', auth()->id())
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->get();

        // 変数名をBlade側が求めている $currentStatus に合わせる
        return view('reading-plans.index', compact('readingPlans'))->with('currentStatus', $status);
    }

    // PG16 読書計画作成
    // PG16 読書計画作成画面表示
    public function create()
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    // PG16 読書計画作成処理
    public function store(StoreReadingPlanRequest $request)
    {
        $data = $request->validated();

        // 新規作成時は「進行中 (in_progress)」をデフォルト設定する
        $data['status'] = ReadingPlanStatus::IN_PROGRESS->value;

        // ログイン中のユーザーIDを動的に設定する
        $data['user_id'] = auth()->id();

        // 読書計画テーブルに登録
        ReadingPlan::create($data);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を登録しました。');
    }

    // PG17	読書計画編集
    // PG17	読書計画編集画面表示
    public function edit(ReadingPlan $reading_plan)
    {
        // 読書計画の所有者がログイン中のユーザーでなければ403エラーを返す
        if ($reading_plan->user_id !== auth()->id()) {
            abort(403);
        }

        // 変数名を Blade 側が求めている $readingPlan に合わせる
        return view('reading-plans.edit', ['readingPlan' => $reading_plan]);
    }

    // PG17	読書計画更新処理
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $reading_plan)
    {
        // 読書計画の所有者がログイン中のユーザーでなければ403エラーを返す
        if ($reading_plan->user_id !== auth()->id()) {
            abort(403);
        }

        // バリデーション済みのデータを取得し、読書計画情報を更新
        $reading_plan->update($request->validated());

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました。');
    }

    // 読書計画を完了（読了する）にする
    public function complete(ReadingPlan $reading_plan)
    {
        // 読書計画の所有者がログイン中のユーザーでなければ403エラーを返す
        if ($reading_plan->user_id !== auth()->id()) {
            abort(403);
        }

        // ステータスを完了にし、読了年月日に今日の日付を設定
        $reading_plan->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を完了しました。');
    }

    // 読書計画を削除する
    public function destroy(ReadingPlan $reading_plan)
    {
        // 読書計画の所有者がログイン中のユーザーでなければ403エラーを返す
        if ($reading_plan->user_id !== auth()->id()) {
            abort(403);
        }

        $reading_plan->delete();  // 読書計画を削除

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }
}
