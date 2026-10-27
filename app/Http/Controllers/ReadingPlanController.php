<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\IndexReadingPlanRequest;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * PG15 読書計画一覧
     */
    public function index(IndexReadingPlanRequest $request): View
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

    /**
     * PG16 読書計画作成画面表示
     */
    public function create(): View
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * PG16 読書計画作成処理
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // 新規作成時は「進行中 (in_progress)」をデフォルト設定する
        $data['status'] = ReadingPlanStatus::IN_PROGRESS->value;

        // ログイン中のユーザーIDを動的に設定する
        $data['user_id'] = auth()->id();

        // トランザクションで安全に登録
        DB::transaction(function () use ($data) {
            ReadingPlan::create($data);
        });

        return redirect()->route('reading-plans.index')->with('success', '読書計画を登録しました。');
    }

    /**
     * PG17 読書計画編集画面表示
     */
    public function edit(ReadingPlan $reading_plan): View
    {
        // 読書計画の所有者がログイン中のユーザーでなければ403エラーを返す
        $this->authorize('update', $reading_plan);

        // 変数名を Blade 側が求めている $readingPlan に合わせる
        return view('reading-plans.edit', ['readingPlan' => $reading_plan]);
    }

    /**
     * PG17 読書計画更新処理
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $reading_plan): RedirectResponse
    {
        // 読書計画の所有者がログイン中のユーザーでなければ403エラーを返す
        $this->authorize('update', $reading_plan);

        // トランザクションで安全に更新
        DB::transaction(function () use ($request, $reading_plan) {
            // バリデーション済みのデータを取得し、読書計画情報を更新
            $reading_plan->update($request->validated());
        });

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました。');
    }

    /**
     * 読書計画を完了（読了する）にする
     */
    public function complete(ReadingPlan $reading_plan): RedirectResponse
    {
        // 読書計画の所有者がログイン中のユーザーでなければ403エラーを返す
        $this->authorize('update', $reading_plan);

        // トランザクションで安全にステータス変更
        DB::transaction(function () use ($reading_plan) {
            // ステータスを完了にし、読了年月日に今日の日付を設定
            $reading_plan->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        return redirect()->route('reading-plans.index')->with('success', '読書計画を完了しました。');
    }

    /**
     * 読書計画を削除する
     */
    public function destroy(ReadingPlan $reading_plan): RedirectResponse
    {
        // 読書計画の所有者がログイン中のユーザーでなければ403エラーを返す
        $this->authorize('delete', $reading_plan);

        // トランザクションで安全に削除
        DB::transaction(function () use ($reading_plan) {
            $reading_plan->delete();
        });

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }
}
