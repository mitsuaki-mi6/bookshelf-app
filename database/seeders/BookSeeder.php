<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;
use App\Models\Genre;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first(); // 登録者は山田太郎

        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_at' => '1905-01-01',
                'description' => '日本文学史に輝く夏目漱石のデビュー作。猫の視点から人間社会を風刺する名作。',
                'genres' => ['小説'],
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_at' => '1936-10-01',
                'description' => '人間関係の原則を説いた世界的ベストセラー。',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_at' => '2012-06-23',
                'description' => 'より良いコードを書くためのシンプルで実践的なテクニック。',
                'genres' => ['技術書'],
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_at' => '2013-08-30',
                'description' => '人格主義のパラダイムを提示する不朽の自己啓発書。',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_at' => '1906-04-01',
                'description' => '正義感あふれる江戸ッ子気質の青年が四国の旧制中学で巻き起こす騒動を描く。',
                'genres' => ['小説'],
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_at' => '2016-09-08',
                'description' => '人類の誕生から現代までを壮大なスケールで描く歴史書。',
                'genres' => ['歴史', '科学'],
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_at' => '2017-12-18',
                'description' => 'アジャイルソフトウェア達人によるクリーンコードの書き方。',
                'genres' => ['技術書'],
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_at' => '2013-12-13',
                'description' => 'アドラー心理学を対話形式で分かりやすく解説したベストセラー。',
                'genres' => ['自己啓発'],
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_at' => '2015-03-11',
                'description' => 'お笑い芸人の世界を描いた芥川賞受賞作。',
                'genres' => ['小説'],
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_at' => '2019-01-11',
                'description' => 'データに基づいて世界を正しく見る習慣を身につける。',
                'genres' => ['ビジネス', '科学'],
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_at' => '2007-01-18',
                'description' => '世界経済を変えたのは「箱」の発明だった。物流とグローバル化の歴史。',
                'genres' => ['ビジネス', '歴史'],
            ],
        ];

        foreach ($books as $index => $bookData) {
            $genreNames = $bookData['genres'];
            unset($bookData['genres']);

            $bookData['user_id'] = $user->id;
            $bookData['image_url'] = 'https://placehold.co/200x300/e2e8f0/475569?text=' . ($index + 1);

            $book = Book::firstOrCreate(
                ['isbn' => $bookData['isbn']],
                $bookData
            );

            // ジャンルの紐付け
            $genreIds = Genre::whereIn('name', $genreNames)->pluck('id');
            $book->genres()->sync($genreIds);
        }
    }
}
