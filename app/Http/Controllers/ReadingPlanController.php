<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\IndexReadingPlanRequest;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
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

        return view('reading_plans.index', compact('readingPlans', 'status'));
    }
    // PG16 読書計画作成
    // PG16 読書計画作成画面表示
    public function create()
    {
        return view('reading_plans.create');
    }

    // PG16 読書計画作成処理
    public function store(StoreReadingPlanRequest $request)
    {
        $data = $request->validated();

        // ログイン中のユーザーIDを動的に設定する
        $data['user_id'] = auth()->id();

        // 読書計画テーブルに登録
        ReadingPlan::create($data);

        return redirect()->route('reading_plans.index')->with('success', '読書計画を登録しました。');
    }

    // PG17	読書計画編集
    // PG17	読書計画編集画面表示
    public function edit(ReadingPlan $reading_plan)
    {
        // 読書計画の所有者がログイン中のユーザーでなければ403エラーを返す
        if ($reading_plan->user_id !== auth()->id()) {
            abort(403);
        }

        return view('reading_plans.edit', compact('reading_plan'));
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

        return redirect()->route('reading_plans.index')->with('success', '読書計画を更新しました。');
    }

    // 読書計画を削除する
    public function destroy(ReadingPlan $reading_plan)
    {
        // 読書計画の所有者がログイン中のユーザーでなければ403エラーを返す
        if ($reading_plan->user_id !== auth()->id()) {
            abort(403);
        }

        $reading_plan->delete();  // 読書計画を削除

        return redirect()->route('reading_plans.index')->with('success', '読書計画を削除しました。');
    }
}
